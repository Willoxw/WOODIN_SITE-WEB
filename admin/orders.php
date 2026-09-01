<?php
require_once __DIR__ . '/auth.php';
$flash = isset($_SESSION['admin_flash']) ? $_SESSION['admin_flash'] : '';
unset($_SESSION['admin_flash']);
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$page = max(1, (int)(isset($_GET['page']) ? $_GET['page'] : 1));
$perPage = 20;
$orderWhere = $filter === 'en_attente' ? " WHERE o.status = 'En attente'" : ($filter === 'en_attente_48h' ? " WHERE o.status = 'En attente' AND o.created_at < DATE_SUB(NOW(), INTERVAL 48 HOUR)" : '');
$totalStmt = $pdo->prepare('SELECT COUNT(*) FROM orders' . $orderWhere);
$totalStmt->execute();
$totalPages = (int)ceil($totalStmt->fetchColumn() / $perPage);
$page = min($page, max(1, $totalPages));
$stmt = $pdo->prepare('SELECT o.*, c.city FROM orders o LEFT JOIN customers c ON c.id = o.customer_id' . $orderWhere . ' ORDER BY o.created_at DESC LIMIT :perPage OFFSET :offset');
$stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll();
$itemsByOrder = [];
$historyByOrder = [];
if ($orders) {
	$orderIds = array_column($orders, 'id');
	$marks = implode(',', array_fill(0, count($orderIds), '?'));
	$itemsStmt = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id IN ($marks) ORDER BY oi.order_id, oi.id");
	$itemsStmt->execute($orderIds);
	foreach ($itemsStmt->fetchAll() as $item) $itemsByOrder[$item['order_id']][] = $item;
	$historyStmt = $pdo->prepare("SELECT osh.*, a.username FROM order_status_history osh JOIN admins a ON a.id = osh.changed_by WHERE osh.order_id IN ($marks) ORDER BY osh.order_id, osh.changed_at ASC");
	$historyStmt->execute($orderIds);
	foreach ($historyStmt->fetchAll() as $history) $historyByOrder[$history['order_id']][] = $history;
}
$paginationHtml = '';
if ($totalPages > 1) {
	$paginationHtml = '<nav aria-label="Pagination des commandes"><ul class="pagination justify-content-center">';
	$paginationHtml .= '<li class="page-item' . ($page <= 1 ? ' disabled' : '') . '"><a class="page-link" href="?' . e(http_build_query(['page' => max(1, $page - 1), 'filter' => $filter])) . '">Précédent</a></li>';
	for ($i = 1; $i <= $totalPages; $i++) {
		$paginationHtml .= '<li class="page-item' . ($i === $page ? ' active' : '') . '"><a class="page-link" href="?' . e(http_build_query(['page' => $i, 'filter' => $filter])) . '">' . $i . '</a></li>';
	}
	$paginationHtml .= '<li class="page-item' . ($page >= $totalPages ? ' disabled' : '') . '"><a class="page-link" href="?' . e(http_build_query(['page' => min($totalPages, $page + 1), 'filter' => $filter])) . '">Suivant</a></li></ul></nav>';
}
$statuses = ['En attente', 'Confirmée', 'Expédiée', 'Annulée'];
$orderDetails = '';
foreach ($orders as $order) {
	$orderDetails .= '<div class="modal fade" id="order-items-' . (int)$order['id'] . '" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h2 class="modal-title h5">Articles de la commande #' . (int)$order['id'] . '</h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p>' . e($order['customer_name']) . '<br>' . e($order['customer_phone']) . '<br>' . e($order['city'] ? $order['city'] : 'Ville non renseignée') . '</p><table class="table"><thead><tr><th>Produit</th><th>Quantité</th><th>Prix unitaire</th><th>Sous-total</th></tr></thead><tbody>';
	foreach (isset($itemsByOrder[$order['id']]) ? $itemsByOrder[$order['id']] : [] as $item) {
		$orderDetails .= '<tr><td>' . e($item['name']) . '</td><td>' . (int)$item['quantity'] . '</td><td>' . number_format($item['price'], 0, ',', ' ') . ' FCFA</td><td>' . number_format($item['price'] * $item['quantity'], 0, ',', ' ') . ' FCFA</td></tr>';
	}
	$orderDetails .= '</tbody></table>' . ((float)$order['discount_amount'] > 0 ? '<p class="text-success">Réduction : -' . number_format($order['discount_amount'], 0, ',', ' ') . ' FCFA</p>' : '') . '<p class="text-end"><strong>Total : ' . number_format($order['total_amount'], 0, ',', ' ') . ' FCFA</strong></p><h3 class="h6">Historique des statuts</h3><ul class="list-group list-group-flush">';
	foreach (isset($historyByOrder[$order['id']]) ? $historyByOrder[$order['id']] : [] as $history) $orderDetails .= '<li class="list-group-item px-0">' . e($history['old_status'] ? $history['old_status'] . ' → ' : '') . e($history['new_status']) . ' · ' . e($history['username']) . ' · ' . e($history['changed_at']) . '</li>';
	$orderDetails .= '</ul></div><div class="modal-footer"><a class="btn btn-warning" href="download-invoice.php?id=' . (int)$order['id'] . '">Télécharger la facture</a></div></div></div></div>';
}
ob_start(function ($buffer) use ($orderDetails, $paginationHtml) {
	$script = '<script>document.querySelectorAll("table tbody tr").forEach(function(row){var id=row.cells[0]&&row.cells[0].textContent.trim();if(!id||!/^\\d+$/.test(id))return;var cell=document.createElement("td");cell.innerHTML="<button class=\\"btn btn-sm btn-outline-dark\\" data-bs-toggle=\\"modal\\" data-bs-target=\\"#order-items-"+id+"\\">Voir les articles</button>";row.appendChild(cell);});</script>';
	return str_replace('</main>', $orderDetails . $script . $paginationHtml . '</main>', $buffer);
});
?>
<script>document.addEventListener('DOMContentLoaded', function () { document.querySelectorAll('table tbody tr').forEach(function (row) { var id = row.querySelector('td'); var target = row.lastElementChild; if (id && target) { var link = document.createElement('a'); link.className = 'btn btn-sm btn-outline-dark ms-2'; link.href = 'download-invoice.php?id=' + encodeURIComponent(id.textContent.trim()); link.textContent = 'Télécharger la facture PDF'; target.appendChild(link); } }); });</script>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Commandes | Woodin Admin</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body><nav class="navbar navbar-dark bg-dark"><div class="container"><a class="navbar-brand text-warning" href="index.php">WOODIN ADMIN</a><a class="btn btn-warning btn-sm" href="logout.php">Déconnexion</a></div></nav><main class="container py-4"><h1>Commandes</h1><?php if ($flash): ?><div class="alert alert-info"><?= e($flash) ?></div><?php endif; ?><div class="table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th>Client</th><th>Téléphone</th><th>Total</th><th>Statut</th><th>Date</th></tr></thead><tbody><?php foreach ($orders as $order): ?><tr><td><?= (int)$order['id'] ?></td><td><?= e($order['customer_name']) ?></td><td><?= e($order['customer_phone']) ?></td><td><?= number_format($order['total_amount'], 0, ',', ' ') ?> FCFA</td><td><form method="post" action="update_order_status.php" class="d-flex gap-2"><input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>"><select name="status" class="form-select form-select-sm"><?php foreach ($statuses as $status): ?><option value="<?= e($status) ?>" <?= $status === $order['status'] ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select><button class="btn btn-sm btn-warning" type="submit">Mettre à jour</button></form></td><td><?= e($order['created_at']) ?></td></tr><?php endforeach; ?></tbody></table></div></main></body></html>
