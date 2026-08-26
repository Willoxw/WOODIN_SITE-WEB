<?php
require_once __DIR__ . '/auth.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$customerStmt = $pdo->prepare('SELECT * FROM customers WHERE id = ?');
$customerStmt->execute([$id]);
$customer = $customerStmt->fetch();
if (!$customer) { http_response_code(404); exit('Client introuvable.'); }
$stmt = $pdo->prepare('SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC');
$stmt->execute([$id]);
$orders = $stmt->fetchAll();
?><!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Commandes client | Woodin Admin</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body><main class="container py-4"><a href="customers.php">Retour aux clients</a><h1 class="mt-3"><?= e($customer['full_name']) ?></h1><p><?= e($customer['email']) ?> · <?= e($customer['phone']) ?> · <?= e($customer['city']) ?></p><div class="table-responsive"><table class="table"><thead><tr><th>#</th><th>Date</th><th>Total</th><th>Statut</th></tr></thead><tbody><?php foreach ($orders as $order): ?><tr><td><?= (int)$order['id'] ?></td><td><?= e($order['created_at']) ?></td><td><?= number_format($order['total_amount'], 0, ',', ' ') ?> FCFA</td><td><?= e($order['status']) ?></td></tr><?php endforeach; ?></tbody></table></div></main></body></html>
