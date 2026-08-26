<?php
require_once __DIR__ . '/auth.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($action === 'delete' && $id) {
        $pdo->prepare('DELETE FROM product_promotions WHERE id = ?')->execute([$id]);
    } elseif ($action === 'save') {
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $percentage = filter_var(isset($_POST['discount_percentage']) ? $_POST['discount_percentage'] : '', FILTER_VALIDATE_FLOAT);
        $starts = isset($_POST['starts_at']) ? $_POST['starts_at'] : '';
        $ends = isset($_POST['ends_at']) ? $_POST['ends_at'] : '';
        if (!$productId || $percentage === false || $percentage <= 0 || $percentage > 100 || !$starts || !$ends || $starts > $ends) $message = 'Données de promotion invalides.';
        else { $pdo->prepare('INSERT INTO product_promotions (product_id,discount_percentage,starts_at,ends_at) VALUES (?,?,?,?)')->execute([$productId,$percentage,$starts,$ends]); $message = 'Promotion produit ajoutée.'; }
    }
}
$products = $pdo->query('SELECT id,name FROM products ORDER BY name')->fetchAll();
$promotions = $pdo->query('SELECT pp.*, p.name FROM product_promotions pp JOIN products p ON p.id=pp.product_id ORDER BY pp.starts_at DESC')->fetchAll();
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Promotions produits | Woodin Admin</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body><main class="container py-4"><a href="index.php">Tableau de bord</a><h1>Promotions produits</h1><?php if ($message): ?><div class="alert alert-info"><?= e($message) ?></div><?php endif; ?><form method="post" class="row g-2 mb-4"><input type="hidden" name="action" value="save"><div class="col-md-4"><select class="form-select" name="product_id" required><option value="">Produit</option><?php foreach ($products as $product): ?><option value="<?= (int)$product['id'] ?>"><?= e($product['name']) ?></option><?php endforeach; ?></select></div><div class="col-md-2"><input class="form-control" name="discount_percentage" type="number" min="0.01" max="100" step=".01" placeholder="%" required></div><div class="col-md-2"><input class="form-control" name="starts_at" type="date" required></div><div class="col-md-2"><input class="form-control" name="ends_at" type="date" required></div><div class="col-md-2"><button class="btn btn-warning">Ajouter</button></div></form><table class="table"><thead><tr><th>Produit</th><th>Réduction</th><th>Période</th><th></th></tr></thead><tbody><?php foreach ($promotions as $promotion): ?><tr><td><?= e($promotion['name']) ?></td><td>-<?= e($promotion['discount_percentage']) ?>%</td><td><?= e($promotion['starts_at']) ?> au <?= e($promotion['ends_at']) ?></td><td><form method="post"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$promotion['id'] ?>"><button class="btn btn-sm btn-outline-danger">Supprimer</button></form></td></tr><?php endforeach; ?></tbody></table></main></body></html>
