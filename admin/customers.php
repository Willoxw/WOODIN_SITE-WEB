<?php
require_once __DIR__ . '/auth.php';
$search = trim(isset($_GET['q']) ? $_GET['q'] : '');
$page = max(1, (int)(isset($_GET['page']) ? $_GET['page'] : 1));
$perPage = 20;
$sql = 'SELECT c.*, COUNT(o.id) AS order_count FROM customers c LEFT JOIN orders o ON o.customer_id = c.id';
$params = [];
if ($search !== '') { $sql .= ' WHERE c.full_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?'; $term = '%' . $search . '%'; $params = [$term, $term, $term]; }
$countSql = 'SELECT COUNT(*) FROM customers c';
if ($search !== '') { $countSql .= ' WHERE c.full_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?'; }
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalPages = (int)ceil($countStmt->fetchColumn() / $perPage);
$page = min($page, max(1, $totalPages));
$sql .= ' GROUP BY c.id ORDER BY c.created_at DESC LIMIT ? OFFSET ?';
$stmt = $pdo->prepare($sql);
$params[] = $perPage;
$params[] = ($page - 1) * $perPage;
$stmt->execute($params);
$customers = $stmt->fetchAll();
$paginationHtml = '';
if ($totalPages > 1) {
	$paginationHtml = '<nav aria-label="Pagination des clients"><ul class="pagination justify-content-center">';
	$paginationHtml .= '<li class="page-item' . ($page <= 1 ? ' disabled' : '') . '"><a class="page-link" href="?' . e(http_build_query(['page' => max(1, $page - 1), 'q' => $search])) . '">Précédent</a></li>';
	for ($i = 1; $i <= $totalPages; $i++) {
		$paginationHtml .= '<li class="page-item' . ($i === $page ? ' active' : '') . '"><a class="page-link" href="?' . e(http_build_query(['page' => $i, 'q' => $search])) . '">' . $i . '</a></li>';
	}
	$paginationHtml .= '<li class="page-item' . ($page >= $totalPages ? ' disabled' : '') . '"><a class="page-link" href="?' . e(http_build_query(['page' => min($totalPages, $page + 1), 'q' => $search])) . '">Suivant</a></li></ul></nav>';
}
ob_start(function ($buffer) use ($paginationHtml) {
	return str_replace('</main>', $paginationHtml . '</main>', $buffer);
});
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Clients | Woodin Admin</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body><nav class="navbar navbar-dark bg-dark"><div class="container"><a class="navbar-brand text-warning" href="index.php">WOODIN ADMIN</a><a class="btn btn-warning btn-sm" href="logout.php">Déconnexion</a></div></nav><main class="container py-4"><div class="d-flex justify-content-between align-items-center"><h1>Clients</h1><a href="index.php">Tableau de bord</a></div><form method="get" class="my-4 d-flex gap-2"><input class="form-control" name="q" placeholder="Nom, email ou téléphone" value="<?= e($search) ?>"><button class="btn btn-dark">Rechercher</button></form><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Nom</th><th>Email</th><th>Téléphone</th><th>Ville</th><th>Commandes</th><th></th></tr></thead><tbody><?php foreach ($customers as $customer): ?><tr><td><?= e($customer['full_name']) ?></td><td><?= e($customer['email']) ?></td><td><?= e($customer['phone']) ?></td><td><?= e($customer['city']) ?></td><td><?= (int)$customer['order_count'] ?></td><td><a href="customer_orders.php?id=<?= (int)$customer['id'] ?>">Historique</a></td></tr><?php endforeach; ?><?php if (!$customers): ?><tr><td colspan="6">Aucun client trouvé.</td></tr><?php endif; ?></tbody></table></div></main></body></html>
