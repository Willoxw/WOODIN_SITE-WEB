<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireCustomer('login.php');

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$stmt = db()->prepare('SELECT invoice_token FROM orders WHERE id = ? AND customer_id = ?');
$stmt->execute([$orderId, currentCustomer()['id']]);
$order = $stmt->fetch();
$path = $order && $order['invoice_token'] ? dirname(__DIR__) . '/invoices/facture_' . $order['invoice_token'] . '.pdf' : '';

if (!$order || !is_file($path)) {
    http_response_code(403);
    require dirname(__DIR__) . '/403.php';
    exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="facture_' . (int)$orderId . '.pdf"');
readfile($path);
exit;