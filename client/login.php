<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (currentCustomer()) redirect('mon-compte.php');
$error = '';
$identifier = trim(isset($_POST['identifier']) ? $_POST['identifier'] : '');
$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lock = db()->prepare('SELECT locked_until FROM customer_login_attempts WHERE identifier = ? AND ip_address = ? AND locked_until > NOW() ORDER BY locked_until DESC LIMIT 1');
    $lock->execute([$identifier, $ip]);
    if ($lock->fetchColumn()) {
        $error = 'Trop de tentatives. Réessayez dans 15 minutes.';
    } else {
        $stmt = db()->prepare('SELECT * FROM customers WHERE email = ? OR phone = ? LIMIT 1');
        $stmt->execute([strtolower($identifier), $identifier]);
        $customer = $stmt->fetch();
        if ($customer && password_verify(isset($_POST['password']) ? $_POST['password'] : '', $customer['password'])) {
            session_regenerate_id(true);
            $_SESSION['customer_id'] = $customer['id'];
            redirect('mon-compte.php');
        }
        $record = db()->prepare('INSERT INTO customer_login_attempts (identifier,ip_address) VALUES (?,?)');
        $record->execute([$identifier, $ip]);
        $attemptId = db()->lastInsertId();
        $attempts = db()->prepare('SELECT COUNT(*) FROM customer_login_attempts WHERE identifier = ? AND ip_address = ? AND attempted_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)');
        $attempts->execute([$identifier, $ip]);
        if ((int)$attempts->fetchColumn() >= 5) {
            $lock = db()->prepare('UPDATE customer_login_attempts SET locked_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE id = ?');
            $lock->execute([$attemptId]);
        }
        $error = 'Identifiants invalides.';
    }
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Connexion client | Woodin</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light"><main class="container py-5" style="max-width:520px"><h1>Mon compte</h1><?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?><form method="post" class="card p-4"><input class="form-control mb-3" name="identifier" placeholder="Email ou téléphone" required value="<?= e($identifier) ?>"><input class="form-control mb-3" type="password" name="password" placeholder="Mot de passe" required><button class="btn btn-warning" type="submit">Se connecter</button></form><p class="mt-3"><a href="register.php">Créer un compte</a> · <a href="forgot_password.php">Mot de passe oublié ?</a></p></main></body></html>
