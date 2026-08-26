<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$message = '';
$error = '';
$token = isset($_GET['token']) ? trim($_GET['token']) : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = trim(isset($_POST['token']) ? $_POST['token'] : '');
    if ($token !== '') {
        $stmt = db()->prepare('SELECT * FROM password_resets WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW() LIMIT 1');
        $stmt->execute([hash('sha256', $token)]);
        $reset = $stmt->fetch();
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        if (!$reset || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
            $error = 'Lien invalide ou mot de passe trop faible.';
        } else {
            $update = db()->prepare('UPDATE customers SET password = ? WHERE id = ?');
            $update->execute([password_hash($password, PASSWORD_DEFAULT), $reset['customer_id']]);
            db()->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?')->execute([$reset['id']]);
            $message = 'Mot de passe modifié. Vous pouvez vous connecter.';
            $token = '';
        }
    } else {
        $email = strtolower(trim(isset($_POST['email']) ? $_POST['email'] : ''));
        $stmt = db()->prepare('SELECT * FROM customers WHERE email = ?');
        $stmt->execute([$email]);
        $customer = $stmt->fetch();
        if ($customer && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $plainToken = bin2hex(function_exists('random_bytes') ? random_bytes(32) : openssl_random_pseudo_bytes(32));
            $reset = db()->prepare('INSERT INTO password_resets (customer_id,token_hash,expires_at) VALUES (?,?,DATE_ADD(NOW(), INTERVAL 1 HOUR))');
            $reset->execute([$customer['id'], hash('sha256', $plainToken)]);
            $resetUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/forgot_password.php?token=' . urlencode($plainToken);
            // TODO: configurer SMTP réel
            $message = 'Si cette adresse existe, un lien de réinitialisation a été envoyé. En local : ' . e($resetUrl);
        } else {
            $message = 'Si cette adresse existe, un lien de réinitialisation a été envoyé.';
        }
    }
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Mot de passe oublié | Woodin</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light"><main class="container py-5" style="max-width:560px"><h1>Réinitialiser le mot de passe</h1><?php if ($message): ?><div class="alert alert-info"><?= $message ?></div><?php endif; ?><?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?><?php if ($token): ?><form method="post" class="card p-4"><input type="hidden" name="token" value="<?= e($token) ?>"><input class="form-control mb-3" type="password" name="password" placeholder="Nouveau mot de passe fort" required><button class="btn btn-warning">Modifier le mot de passe</button></form><?php else: ?><form method="post" class="card p-4"><input class="form-control mb-3" type="email" name="email" placeholder="Votre email" required><button class="btn btn-warning">Recevoir le lien</button></form><?php endif; ?><p class="mt-3"><a href="login.php">Retour à la connexion</a></p></main></body></html>
