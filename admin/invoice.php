<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/generate_invoice.php';
$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$orderId || !generateInvoicePdf($orderId)) { http_response_code(404); exit('Facture indisponible.'); }
header('Location: ../invoices/facture_' . (int)$orderId . '.pdf');
exit;
