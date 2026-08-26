<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireCustomer('login.php');
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$stmt = db()->prepare('SELECT * FROM orders WHERE id = ? AND customer_id = ?');
$stmt->execute([$id, currentCustomer()['id']]);
$order = $stmt->fetch();
if (!$order) { http_response_code(404); exit('Commande introuvable.'); }
$itemsStmt = db()->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?');
$itemsStmt->execute([$order['id']]);
$items = $itemsStmt->fetchAll();
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Commande #<?= (int)$order['id'] ?> | Woodin</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light"><main class="container py-5"><h1>Commande #<?= (int)$order['id'] ?></h1><p>Statut : <strong><?= e($order['status']) ?></strong></p><div class="table-responsive"><table class="table bg-white"><thead><tr><th>Produit</th><th>Quantité</th><th>Prix</th><th>Sous-total</th></tr></thead><tbody><?php foreach ($items as $item): ?><tr><td><?= e($item['name']) ?></td><td><?= (int)$item['quantity'] ?></td><td><?= number_format($item['price'], 0, ',', ' ') ?> FCFA</td><td><?= number_format($item['price'] * $item['quantity'], 0, ',', ' ') ?> FCFA</td></tr><?php endforeach; ?></tbody></table></div><p class="text-end"><strong>Total : <?= number_format($order['total_amount'], 0, ',', ' ') ?> FCFA</strong></p><a href="mes-commandes.php">Retour à mes commandes</a></main></body></html>
