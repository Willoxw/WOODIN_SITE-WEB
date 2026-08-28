<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$token = trim(isset($_REQUEST['token']) ? $_REQUEST['token'] : '');
$error = '';
$success = '';
$reset = null;
if (preg_match('/^[a-f0-9]{64}$/', $token)) {
    $stmt = db()->prepare('SELECT * FROM password_resets WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW() LIMIT 1');
    $stmt->execute([hash('sha256', $token)]);
    $reset = $stmt->fetch();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmation = isset($_POST['confirmation']) ? $_POST['confirmation'] : '';
    if (!$reset) $error = 'Ce lien est invalide ou expiré.';
    elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password) || $password !== $confirmation) $error = 'Le mot de passe doit contenir 8 caractères, une majuscule, une minuscule et un chiffre, et les deux champs doivent correspondre.';
    else {
        db()->prepare('UPDATE customers SET password = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $reset['customer_id']]);
        db()->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?')->execute([$reset['id']]);
        $success = 'Mot de passe modifié. Vous pouvez vous connecter.';
        $reset = null;
    }
}
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Nouveau mot de passe | Woodin</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light"><main class="container py-5" style="max-width:560px"><h1>Nouveau mot de passe</h1><?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?><?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><a href="login.php">Se connecter</a><?php elseif ($reset): ?><form method="post" class="card p-4"><?= csrfField() ?><input type="hidden" name="token" value="<?= e($token) ?>"><input class="form-control mb-3" type="password" name="password" placeholder="Nouveau mot de passe" required><input class="form-control mb-3" type="password" name="confirmation" placeholder="Confirmation" required><button class="btn btn-warning">Modifier le mot de passe</button></form><?php else: ?><p>Ce lien est invalide ou expiré.</p><?php endif; ?></main></body></html>