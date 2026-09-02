<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$provider = isset($_GET['provider']) ? strtolower(trim($_GET['provider'])) : '';
if (!in_array($provider, ['google', 'apple'], true)) {
    http_response_code(400);
    exit('Fournisseur de connexion invalide.');
}

function oauthRandom($bytes = 32)
{
    if (function_exists('random_bytes')) {
        return random_bytes($bytes);
    }
    if (function_exists('openssl_random_pseudo_bytes')) {
        return openssl_random_pseudo_bytes($bytes);
    }
    return hash('sha256', uniqid((string)mt_rand(), true), true);
}

function oauthBase64UrlEncode($value)
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function oauthBase64UrlDecode($value)
{
    $padding = strlen($value) % 4;
    if ($padding) {
        $value .= str_repeat('=', 4 - $padding);
    }
    return base64_decode(strtr($value, '-_', '+/'));
}

function oauthHashEquals($known, $user)
{
    if (function_exists('hash_equals')) {
        return hash_equals($known, $user);
    }
    return strlen($known) === strlen($user) && $known === $user;
}

function oauthDerInteger($value)
{
    if (ord($value[0]) & 0x80) {
        $value = "\x00" . $value;
    }
    return "\x02" . oauthDerLength(strlen($value)) . $value;
}

function oauthEcdsaDerToJose($signature)
{
    $offset = 3;
    $rLength = ord($signature[$offset - 1]);
    $r = substr($signature, $offset, $rLength);
    $offset += $rLength + 2;
    $sLength = ord($signature[$offset - 1]);
    $s = substr($signature, $offset, $sLength);
    return str_pad(ltrim($r, "\x00"), 32, "\x00", STR_PAD_LEFT) . str_pad(ltrim($s, "\x00"), 32, "\x00", STR_PAD_LEFT);
}

function oauthEcdsaJoseToDer($signature)
{
    if (strlen($signature) !== 64) {
        throw new RuntimeException('Signature Apple invalide.');
    }
    $r = oauthDerInteger(substr($signature, 0, 32));
    $s = oauthDerInteger(substr($signature, 32, 32));
    $sequence = $r . $s;
    return "\x30" . oauthDerLength(strlen($sequence)) . $sequence;
}

function oauthRedirectUri($provider)
{
    $configured = envValue(strtoupper($provider) . '_REDIRECT_URI');
    if ($configured !== '') {
        return $configured;
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . $_SERVER['HTTP_HOST'] . '/WOODIN_SITE-WEB/client/oauth.php?provider=' . $provider;
}

function oauthHttp($url, $postFields = null, $headers = [])
{
    if (!function_exists('curl_init')) {
        throw new RuntimeException('cURL est requis pour la connexion sociale.');
    }
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 15);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    if ($postFields !== null) {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
    }
    $body = curl_exec($curl);
    $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    if ($body === false || $status < 200 || $status >= 300) {
        throw new RuntimeException($error ?: 'Réponse OAuth invalide.');
    }
    $decoded = json_decode($body, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Réponse OAuth illisible.');
    }
    return $decoded;
}

function oauthDecodeJwt($token)
{
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        throw new RuntimeException('Jeton OAuth invalide.');
    }
    $header = json_decode(oauthBase64UrlDecode($parts[0]), true);
    $claims = json_decode(oauthBase64UrlDecode($parts[1]), true);
    if (!is_array($header) || !is_array($claims)) {
        throw new RuntimeException('Jeton OAuth illisible.');
    }
    return [$parts, $header, $claims];
}

function oauthVerifyAppleToken($token, $nonce)
{
    list($parts, $header, $claims) = oauthDecodeJwt($token);
    if (empty($header['kid']) || $header['alg'] !== 'RS256') {
        throw new RuntimeException('Signature Apple non supportée.');
    }
    $keys = oauthHttp('https://appleid.apple.com/auth/keys');
    $publicKey = null;
    foreach (isset($keys['keys']) ? $keys['keys'] : [] as $key) {
        if (isset($key['kid'], $key['n'], $key['e']) && $key['kid'] === $header['kid']) {
            $modulus = oauthBase64UrlDecode($key['n']);
            $exponent = oauthBase64UrlDecode($key['e']);
            $publicKey = openssl_pkey_get_public("-----BEGIN RSA PUBLIC KEY-----\n" . chunk_split(base64_encode(oauthRsaPublicKeyDer($modulus, $exponent)), 64, "\n") . "-----END RSA PUBLIC KEY-----");
            break;
        }
    }
    if (!$publicKey || openssl_verify($parts[0] . '.' . $parts[1], oauthEcdsaJoseToDer(oauthBase64UrlDecode($parts[2])), $publicKey, OPENSSL_ALGO_SHA256) !== 1) {
        throw new RuntimeException('Signature Apple invalide.');
    }
    if ((isset($claims['iss']) ? $claims['iss'] : '') !== 'https://appleid.apple.com' || (isset($claims['aud']) ? $claims['aud'] : '') !== envValue('APPLE_CLIENT_ID') || (int)(isset($claims['exp']) ? $claims['exp'] : 0) < time() || (isset($claims['nonce']) ? $claims['nonce'] : '') !== $nonce) {
        throw new RuntimeException('Claims Apple invalides.');
    }
    return $claims;
}

function oauthDerLength($length)
{
    if ($length < 128) return chr($length);
    $output = '';
    while ($length > 0) {
        $output = chr($length & 255) . $output;
        $length >>= 8;
    }
    return chr(128 | strlen($output)) . $output;
}

function oauthRsaPublicKeyDer($modulus, $exponent)
{
    $modulus = "\x00" . $modulus;
    $modulus = "\x02" . oauthDerLength(strlen($modulus)) . $modulus;
    $exponent = "\x02" . oauthDerLength(strlen($exponent)) . $exponent;
    $sequence = $modulus . $exponent;
    return "\x30" . oauthDerLength(strlen($sequence)) . $sequence;
}

function oauthAppleClientSecret()
{
    $privateKeyPath = envValue('APPLE_PRIVATE_KEY_PATH');
    if (!$privateKeyPath || !is_file($privateKeyPath)) {
        throw new RuntimeException('La clé privée Apple n’est pas configurée.');
    }
    $privateKey = openssl_pkey_get_private(file_get_contents($privateKeyPath));
    if (!$privateKey) {
        throw new RuntimeException('La clé privée Apple est invalide.');
    }
    $header = oauthBase64UrlEncode(json_encode(['alg' => 'ES256', 'kid' => envValue('APPLE_KEY_ID'), 'typ' => 'JWT']));
    $claims = oauthBase64UrlEncode(json_encode(['iss' => envValue('APPLE_TEAM_ID'), 'iat' => time(), 'exp' => time() + 86400 * 180, 'aud' => 'https://appleid.apple.com', 'sub' => envValue('APPLE_CLIENT_ID')]));
    $input = $header . '.' . $claims;
    if (!openssl_sign($input, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
        throw new RuntimeException('Impossible de signer le secret Apple.');
    }
    return $input . '.' . oauthBase64UrlEncode(oauthEcdsaDerToJose($signature));
}

function oauthFindOrCreateCustomer($provider, $providerId, $email, $name)
{
    $column = $provider . '_id';
    $stmt = db()->prepare("SELECT * FROM customers WHERE {$column} = ? LIMIT 1");
    $stmt->execute([$providerId]);
    $customer = $stmt->fetch();
    if (!$customer && $email !== '') {
        $stmt = db()->prepare('SELECT * FROM customers WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $customer = $stmt->fetch();
    }
    if ($customer) {
        db()->prepare("UPDATE customers SET {$column} = ? WHERE id = ?")->execute([$providerId, $customer['id']]);
        return $customer['id'];
    }
    if ($email === '') {
        throw new RuntimeException('Votre fournisseur n’a pas transmis d’adresse email.');
    }
    $password = password_hash(bin2hex(oauthRandom(24)), PASSWORD_DEFAULT);
    $insert = db()->prepare("INSERT INTO customers (full_name,email,phone,password,city,{$column}) VALUES (?,?,NULL,?,'',?)");
    $insert->execute([$name !== '' ? $name : 'Client WOODIN', $email, $password, $providerId]);
    return db()->lastInsertId();
}

function oauthStart($provider)
{
    $state = oauthBase64UrlEncode(oauthRandom());
    $nonce = oauthBase64UrlEncode(oauthRandom());
    $_SESSION['oauth_state'] = $state;
    $_SESSION['oauth_nonce'] = $nonce;
    $_SESSION['oauth_provider'] = $provider;
    $redirectUri = oauthRedirectUri($provider);
    if ($provider === 'google') {
        $query = http_build_query(['client_id' => envValue('GOOGLE_CLIENT_ID'), 'redirect_uri' => $redirectUri, 'response_type' => 'code', 'scope' => 'openid email profile', 'state' => $state, 'nonce' => $nonce, 'access_type' => 'online', 'prompt' => 'select_account']);
        redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }
    $query = http_build_query(['client_id' => envValue('APPLE_CLIENT_ID'), 'redirect_uri' => $redirectUri, 'response_type' => 'code', 'response_mode' => 'form_post', 'scope' => 'name email', 'state' => $state, 'nonce' => $nonce]);
    redirect('https://appleid.apple.com/auth/authorize?' . $query);
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && empty($_GET['code'])) {
        oauthStart($provider);
    }
    $state = isset($_POST['state']) ? $_POST['state'] : (isset($_GET['state']) ? $_GET['state'] : '');
    if (empty($_SESSION['oauth_state']) || !oauthHashEquals($_SESSION['oauth_state'], $state) || $_SESSION['oauth_provider'] !== $provider) {
        throw new RuntimeException('Session OAuth expirée.');
    }
    $nonce = $_SESSION['oauth_nonce'];
    $code = isset($_POST['code']) ? $_POST['code'] : (isset($_GET['code']) ? $_GET['code'] : '');
    if ($code === '') throw new RuntimeException('Code OAuth manquant.');
    if ($provider === 'google') {
        $token = oauthHttp('https://oauth2.googleapis.com/token', http_build_query(['code' => $code, 'client_id' => envValue('GOOGLE_CLIENT_ID'), 'client_secret' => envValue('GOOGLE_CLIENT_SECRET'), 'redirect_uri' => oauthRedirectUri('google'), 'grant_type' => 'authorization_code']), ['Content-Type: application/x-www-form-urlencoded']);
        if (empty($token['access_token']) || empty($token['id_token'])) throw new RuntimeException('Jeton Google manquant.');
        $googleClaims = oauthHttp('https://oauth2.googleapis.com/tokeninfo?id_token=' . rawurlencode($token['id_token']));
        if ((isset($googleClaims['aud']) ? $googleClaims['aud'] : '') !== envValue('GOOGLE_CLIENT_ID') || (isset($googleClaims['nonce']) ? $googleClaims['nonce'] : '') !== $nonce || (int)(isset($googleClaims['exp']) ? $googleClaims['exp'] : 0) < time()) throw new RuntimeException('Jeton Google invalide.');
        $profile = oauthHttp('https://openidconnect.googleapis.com/v1/userinfo', null, ['Authorization: Bearer ' . $token['access_token']]);
        if (empty($profile['sub']) || empty($profile['email']) || empty($profile['email_verified'])) throw new RuntimeException('Profil Google non vérifié.');
        $providerId = $profile['sub'];
        $email = strtolower($profile['email']);
        $name = isset($profile['name']) ? trim($profile['name']) : '';
    } else {
        $token = oauthHttp('https://appleid.apple.com/auth/token', http_build_query(['client_id' => envValue('APPLE_CLIENT_ID'), 'client_secret' => oauthAppleClientSecret(), 'code' => $code, 'grant_type' => 'authorization_code', 'redirect_uri' => oauthRedirectUri('apple')]), ['Content-Type: application/x-www-form-urlencoded']);
        if (empty($token['id_token'])) throw new RuntimeException('Jeton Apple manquant.');
        $claims = oauthVerifyAppleToken($token['id_token'], $nonce);
        $providerId = $claims['sub'];
        $email = !empty($claims['email']) && !empty($claims['email_verified']) ? strtolower($claims['email']) : '';
        $name = '';
        if (!empty($_POST['user'])) {
            $user = json_decode($_POST['user'], true);
            if (is_array($user) && !empty($user['name'])) $name = trim(trim($user['name']['firstName'] . ' ' . $user['name']['lastName']));
        }
    }
    $customerId = oauthFindOrCreateCustomer($provider, $providerId, $email, $name);
    unset($_SESSION['oauth_state'], $_SESSION['oauth_nonce'], $_SESSION['oauth_provider']);
    session_regenerate_id(true);
    $_SESSION['customer_id'] = $customerId;
    redirect('mon-compte.php');
} catch (Exception $exception) {
    unset($_SESSION['oauth_state'], $_SESSION['oauth_nonce'], $_SESSION['oauth_provider']);
    error_log('OAuth ' . $provider . ': ' . $exception->getMessage());
    redirect('login.php?oauth_error=1');
}
