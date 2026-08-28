<?php
require_once __DIR__ . '/includes/bootstrap.php';

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$token = isset($_GET['token']) ? trim($_GET['token']) : '';
if (!$orderId || !preg_match('/^[a-f0-9]{32}$/', $token)) {
    http_response_code(403);
    require __DIR__ . '/403.php';
    exit;
}

$stmt = db()->prepare('SELECT invoice_token FROM orders WHERE id = ? AND invoice_token = ?');
$stmt->execute([$orderId, $token]);
$order = $stmt->fetch();
$path = $order ? __DIR__ . '/invoices/facture_' . $order['invoice_token'] . '.pdf' : '';
if (!$order || !is_file($path)) {
    http_response_code(403);
    require __DIR__ . '/403.php';
    exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="facture_' . (int)$orderId . '.pdf"');
readfile($path);
exit;