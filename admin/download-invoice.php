<?php
require_once __DIR__ . '/auth.php';

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$stmt = $pdo->prepare('SELECT invoice_token FROM orders WHERE id = ?');
$stmt->execute([$orderId]);
$order = $stmt->fetch();
$path = $order && $order['invoice_token'] ? dirname(__DIR__) . '/invoices/facture_' . $order['invoice_token'] . '.pdf' : '';

if (!$order || !is_file($path)) {
    http_response_code(404);
    exit('Facture indisponible.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="facture_' . (int)$orderId . '.pdf"');
readfile($path);
exit;