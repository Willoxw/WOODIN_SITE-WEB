<?php
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    if ($id && $action === 'read') {
        $pdo->prepare('UPDATE messages SET is_read = 1 WHERE id = ?')->execute([$id]);
    } elseif ($id && $action === 'delete') {
        $pdo->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]);
    }
    redirect('messages.php?page=' . max(1, (int)(isset($_GET['page']) ? $_GET['page'] : 1)));
}

$unread = (int)$pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
$page = max(1, (int)(isset($_GET['page']) ? $_GET['page'] : 1));
$perPage = 20;
$total = (int)$pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$stmt = $pdo->prepare('SELECT * FROM messages ORDER BY is_read ASC, created_at DESC LIMIT ? OFFSET ?');
$stmt->bindValue(1, $perPage, PDO::PARAM_INT);
$stmt->bindValue(2, ($page - 1) * $perPage, PDO::PARAM_INT);
$stmt->execute();
$messages = $stmt->fetchAll();
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Messages | Woodin Admin</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body><nav class="navbar navbar-dark bg-dark"><div class="container"><a class="navbar-brand text-warning" href="index.php">WOODIN ADMIN</a><div><a class="btn btn-outline-light btn-sm" href="orders.php">Commandes</a> <a class="btn btn-warning btn-sm" href="logout.php">Déconnexion</a></div></div></nav><main class="container py-4"><div class="d-flex justify-content-between align-items-center"><h1>Messages <span class="badge bg-danger"><?= $unread ?></span></h1><a href="index.php">Tableau de bord</a></div><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Date</th><th>Nom</th><th>Email</th><th>Téléphone</th><th>Extrait</th><th>Statut</th><th>Actions</th></tr></thead><tbody><?php foreach ($messages as $message): ?><tr class="<?= !$message['is_read'] ? 'table-warning' : '' ?>"><td><?= e($message['created_at']) ?></td><td><?= e($message['nom']) ?></td><td><?= e($message['email']) ?></td><td><?= e($message['telephone']) ?></td><td><button class="btn btn-link p-0" data-bs-toggle="modal" data-bs-target="#message<?= (int)$message['id'] ?>"><?= e(mb_strimwidth($message['message'], 0, 50, '...')) ?></button></td><td><span class="badge <?= $message['is_read'] ? 'bg-success' : 'bg-warning text-dark' ?>"><?= $message['is_read'] ? 'Lu' : 'Non lu' ?></span></td><td><button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#message<?= (int)$message['id'] ?>">Voir</button></td></tr><div class="modal fade" id="message<?= (int)$message['id'] ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h2 class="modal-title h5"><?= e($message['nom']) ?></h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p><a href="mailto:<?= e($message['email']) ?>"><?= e($message['email']) ?></a><br><?= e($message['telephone']) ?></p><p><?= nl2br(e($message['message'])) ?></p></div><div class="modal-footer"><form method="post"><input type="hidden" name="id" value="<?= (int)$message['id'] ?>"><input type="hidden" name="action" value="read"><button class="btn btn-success" type="submit">Marquer comme traité</button></form><form method="post" onsubmit="return confirm('Supprimer ce message ?');"><input type="hidden" name="id" value="<?= (int)$message['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn btn-outline-danger" type="submit">Supprimer</button></form></div></div></div></div><?php endforeach; ?></tbody></table></div><?php if ($totalPages > 1): ?><nav>Page <?= $page ?> / <?= $totalPages ?> <?php if ($page > 1): ?><a href="?page=<?= $page - 1 ?>">Précédente</a><?php endif; ?> <?php if ($page < $totalPages): ?><a href="?page=<?= $page + 1 ?>">Suivante</a><?php endif; ?></nav><?php endif; ?></main><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script></body></html>
