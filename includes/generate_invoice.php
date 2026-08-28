<?php
require_once __DIR__ . '/config.php';

function generateInvoicePdf($orderId)
{
    $autoload = dirname(__DIR__) . '/vendor/autoload.php';
    if (!is_file($autoload)) return false;
    require_once $autoload;
    if (!class_exists('Dompdf\\Dompdf')) return false;
    $stmt = db()->prepare('SELECT o.*, c.city FROM orders o LEFT JOIN customers c ON c.id = o.customer_id WHERE o.id = ?');
    $stmt->execute([(int)$orderId]);
    $order = $stmt->fetch();
    if (!$order) return false;
    $order['customer_email'] = isset($order['customer_email']) ? $order['customer_email'] : '';
    $order['customer_name'] = isset($order['customer_name']) ? $order['customer_name'] : '';
    $order['customer_phone'] = isset($order['customer_phone']) ? $order['customer_phone'] : '';
    $itemsStmt = db()->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?');
    $itemsStmt->execute([(int)$orderId]);
    $items = $itemsStmt->fetchAll();
    $rows = '';
    foreach ($items as $item) {
        $rows .= '<tr><td>' . e($item['name']) . '</td><td>' . (int)$item['quantity'] . '</td><td>' . number_format($item['price'], 0, ',', ' ') . ' FCFA</td><td>' . number_format($item['price'] * $item['quantity'], 0, ',', ' ') . ' FCFA</td></tr>';
    }
    $discount = (float)$order['discount_amount'];
    $html = '<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans, sans-serif;color:#1a1a1a;font-size:12px}h1{color:#8b0000;margin-bottom:4px}.muted{color:#666}.meta{margin:20px 0;border-bottom:1px solid #ddd;padding-bottom:12px}table{width:100%;border-collapse:collapse;margin-top:20px}th{background:#1a1a1a;color:#fff;text-align:left}th,td{padding:9px;border-bottom:1px solid #ddd}.total{text-align:right;font-size:18px;font-weight:bold;color:#8b0000;margin-top:20px}.discount{text-align:right;color:#278b4e}footer{position:fixed;bottom:0;width:100%;text-align:center;color:#666;font-size:10px}</style></head><body><h1>WOODIN CAMEROUN</h1><div class="muted">Belife Groupe<br>1944 Boulevard de la Liberté, Douala<br>contact.cm@belifegroupe.com</div><div class="meta"><strong>Facture commande #' . (int)$order['id'] . '</strong><br>Date : ' . e($order['created_at']) . '<br><br><strong>Client</strong><br>' . e($order['customer_name']) . '<br>' . e($order['customer_phone']) . '<br>' . e($order['customer_email']) . '<br>' . e($order['city'] ? $order['city'] : '') . '</div><table><thead><tr><th>Article</th><th>Qté</th><th>Prix unitaire</th><th>Sous-total</th></tr></thead><tbody>' . $rows . '</tbody></table>' . ($discount > 0 ? '<p class="discount">Réduction : -' . number_format($discount, 0, ',', ' ') . ' FCFA</p>' : '') . '<p class="total">TOTAL GÉNÉRAL : ' . number_format($order['total_amount'], 0, ',', ' ') . ' FCFA</p><footer>Merci pour votre confiance - WOODIN Cameroun</footer></body></html>';
    $directory = dirname(__DIR__) . '/invoices';
    if (!is_dir($directory)) mkdir($directory, 0755, true);
    $dompdf = new Dompdf\Dompdf();
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    if (empty($order['invoice_token'])) return false;
    $path = $directory . '/facture_' . $order['invoice_token'] . '.pdf';
    file_put_contents($path, $dompdf->output());
    return $path;
}
