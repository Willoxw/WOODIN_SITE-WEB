<?php
require_once __DIR__ . '/auth.php';
$productId = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);
$productsStmt = $pdo->prepare('SELECT id, name FROM products ORDER BY name');
$productsStmt->execute();
$products = $productsStmt->fetchAll();
$sql = 'SELECT sm.*, p.name FROM stock_movements sm JOIN products p ON p.id = sm.product_id';
$params = [];
if ($productId) { $sql .= ' WHERE sm.product_id = ?'; $params[] = $productId; }
$sql .= ' ORDER BY sm.created_at DESC';
$historyStmt = $pdo->prepare($sql);
$historyStmt->execute($params);
$history = $historyStmt->fetchAll();
?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Historique stock | Woodin Admin</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body><nav class="navbar navbar-dark bg-dark"><div class="container"><a class="navbar-brand text-warning" href="index.php">WOODIN ADMIN</a><a class="btn btn-warning btn-sm" href="logout.php">Déconnexion</a></div></nav><main class="container py-4"><h1>Historique du stock</h1><form method="get" class="mb-4"><select name="product_id" class="form-select" onchange="this.form.submit()"><option value="">Tous les produits</option><?php foreach ($products as $product): ?><option value="<?= (int)$product['id'] ?>" <?= $productId == $product['id'] ? 'selected' : '' ?>><?= e($product['name']) ?></option><?php endforeach; ?></select></form><div class="table-responsive"><table class="table"><thead><tr><th>Produit</th><th>Variation</th><th>Motif</th><th>Date</th></tr></thead><tbody><?php foreach ($history as $movement): ?><tr><td><?= e($movement['name']) ?></td><td><?= (int)$movement['quantity_change'] ?></td><td><?= e($movement['reason']) ?></td><td><?= e($movement['created_at']) ?></td></tr><?php endforeach; ?></tbody></table></div></main></body></html>
