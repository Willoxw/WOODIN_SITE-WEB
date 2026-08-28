<?php
require_once __DIR__ . '/auth.php';
requireAdminRole('super_admin');
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($action === 'deactivate' && $id && $id !== (int)$_SESSION['admin_id']) {
        $pdo->prepare('UPDATE admins SET is_active = 0 WHERE id = ?')->execute([$id]);
        $message = 'Compte désactivé.';
    } elseif ($action === 'create') {
        $username = trim(isset($_POST['username']) ? $_POST['username'] : '');
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $role = isset($_POST['role']) && $_POST['role'] === 'super_admin' ? 'super_admin' : 'gestionnaire';
        if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username) || strlen($password) < 8) {
            $message = 'Identifiant ou mot de passe invalide.';
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO admins (username, password, role) VALUES (?, ?, ?)');
                $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $role]);
                $message = 'Compte créé.';
            } catch (Exception $error) {
                $message = 'Cet identifiant existe déjà.';
            }
        }
    }
}
$admins = $pdo->query('SELECT id, username, role, is_active, last_login FROM admins ORDER BY username')->fetchAll();
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Comptes admin | Woodin</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body><main class="container py-4"><h1>Comptes administrateurs</h1><?php if ($message): ?><div class="alert alert-info"><?= e($message) ?></div><?php endif; ?><form method="post" class="row g-2 mb-4"><input type="hidden" name="action" value="create"><div class="col-md-3"><input class="form-control" name="username" placeholder="Identifiant" required></div><div class="col-md-3"><input class="form-control" name="password" type="password" placeholder="Mot de passe" minlength="8" required></div><div class="col-md-3"><select class="form-select" name="role"><option value="gestionnaire">Gestionnaire</option><option value="super_admin">Super admin</option></select></div><div class="col-md-3"><button class="btn btn-warning" type="submit">Ajouter</button></div></form><div class="table-responsive"><table class="table"><thead><tr><th>Identifiant</th><th>Rôle</th><th>État</th><th>Dernière connexion</th><th></th></tr></thead><tbody><?php foreach ($admins as $admin): ?><tr><td><?= e($admin['username']) ?></td><td><?= e($admin['role']) ?></td><td><?= $admin['is_active'] ? 'Actif' : 'Désactivé' ?></td><td><?= e($admin['last_login'] ?: 'Jamais') ?></td><td><?php if ($admin['is_active'] && (int)$admin['id'] !== (int)$_SESSION['admin_id']): ?><form method="post"><input type="hidden" name="action" value="deactivate"><input type="hidden" name="id" value="<?= (int)$admin['id'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit">Désactiver</button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></main></body></html>
