<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/db_connect.php';
generateCsrfToken();

if (!empty($_SESSION['admin_id'])) { header('Location: index.php'); exit; }
$error = isset($_GET['expired']) ? 'Session expirée. Veuillez vous reconnecter.' : '';
$username = trim(isset($_POST['username']) ? $_POST['username'] : '');
$ipAddress = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $lock = $pdo->prepare('SELECT locked_until FROM login_attempts WHERE username = ? AND ip_address = ? AND locked_until > NOW() ORDER BY locked_until DESC LIMIT 1');
    $lock->execute([$username, $ipAddress]);
    $lockUntil = $lock->fetchColumn();

    if ($lockUntil) {
        $lockTimestamp = strtotime($lockUntil);
        $remainingSeconds = max(0, $lockTimestamp - time());
        $remainingMinutes = max(1, (int) ceil($remainingSeconds / 60));
        $error = 'Trop de tentatives, réessayez dans ' . $remainingMinutes . ' minute' . ($remainingMinutes > 1 ? 's' : '') . '.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = ?');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        if ($admin && !empty($admin['is_active']) && password_verify(isset($_POST['password']) ? $_POST['password'] : '', $admin['password'])) {
            session_regenerate_id(true);
            $pdo->prepare('UPDATE admins SET last_login = NOW() WHERE id = ?')->execute([$admin['id']]);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['last_activity'] = time();
            header('Location: index.php');
            exit;
        }
        $record = $pdo->prepare('INSERT INTO login_attempts (username, ip_address) VALUES (?, ?)');
        $record->execute([$username, $ipAddress]);
        $attemptId = $pdo->lastInsertId();
        $attempts = $pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE username = ? AND ip_address = ? AND attempted_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)');
        $attempts->execute([$username, $ipAddress]);
        if ((int)$attempts->fetchColumn() >= 5) {
            $lock = $pdo->prepare('UPDATE login_attempts SET locked_until = DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE id = ?');
            $lock->execute([$attemptId]);
        }
        $error = 'Identifiants incorrects.';
    }
}
?>
<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow"><title>Connexion administration | Woodin</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><style>body{min-height:100vh;display:grid;place-items:center;background:#1a1a1a;color:#fff}.card{max-width:420px;width:100%;border:0;border-radius:0;background:#111;box-shadow:0 20px 40px rgba(0,0,0,.35)}.brand{color:#f5c518;font:900 2rem Georgia;letter-spacing:.1em}.btn-warning{background:#f5c518;border-color:#f5c518;color:#1a1a1a;font-weight:700}.form-control{background:#1a1a1a;color:#fff;border-color:#2a2a2a}.form-control:focus{background:#111;border-color:#f5c518;box-shadow:0 0 0 .25rem rgba(245,197,24,.12)}.text-muted{color:#d0d0d0!important}</style></head>
<body><main class="card shadow p-4"><div class="brand mb-3">WOODIN</div><h1 class="h3">Administration</h1><p class="text-muted mb-3">Connectez-vous pour gérer la boutique.</p><?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?><form method="post"><?= csrfField() ?><label class="form-label">Identifiant</label><input class="form-control mb-3" name="username" required value="<?= e($username) ?>"><label class="form-label">Mot de passe</label><input class="form-control mb-3" type="password" name="password" required><button class="btn btn-warning w-100" type="submit">Se connecter</button></form></main></body>
</html>