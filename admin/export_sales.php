<?php
require_once __DIR__ . '/auth.php';
$period = isset($_GET['period']) ? $_GET['period'] : 'month';
$conditions = '';
if ($period === 'week') $conditions = ' AND o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
elseif ($period === 'month') $conditions = ' AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
$stmt = $pdo->query("SELECT o.id, o.created_at, o.customer_name, o.customer_phone, o.total_amount, o.status FROM orders o WHERE o.status <> 'Annulée'" . $conditions . ' ORDER BY o.created_at DESC');
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="ventes_' . $period . '_' . date('Ymd_His') . '.csv"');
$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF");
fputcsv($output, ['N° commande', 'Date', 'Client', 'Téléphone', 'Total', 'Statut'], ';');
while ($order = $stmt->fetch()) fputcsv($output, [$order['id'], $order['created_at'], $order['customer_name'], $order['customer_phone'], $order['total_amount'], $order['status']], ';');
fclose($output);
exit;
