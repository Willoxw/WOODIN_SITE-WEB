<?php
require_once __DIR__ . '/includes/bootstrap.php';
$lastOrder = isset($_SESSION['last_order']) && is_array($_SESSION['last_order']) ? $_SESSION['last_order'] : [];
$orderId = isset($lastOrder['id']) ? (int)$lastOrder['id'] : (isset($_SESSION['last_order_id']) ? (int)$_SESSION['last_order_id'] : 0);
$invoiceToken = isset($lastOrder['invoice_token']) ? $lastOrder['invoice_token'] : '';
if (!$orderId) redirect('index.php');
$orderStmt = db()->prepare('SELECT * FROM orders WHERE id = ?');
$orderStmt->execute([$orderId]);
$order = $orderStmt->fetch();
if (!$order) redirect('index.php');
$invoiceToken = $invoiceToken !== '' ? $invoiceToken : $order['invoice_token'];
$itemsStmt = db()->prepare('SELECT oi.*, p.name FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?');
$itemsStmt->execute([$orderId]);
$items = $itemsStmt->fetchAll();
unset($_SESSION['last_order'], $_SESSION['last_order_id']);
$pageTitle = 'Commande confirmée | Woodin Cameroun';
$pageDescription = 'Confirmation de votre commande Woodin Cameroun.';
include __DIR__ . '/includes/header.php';
?>
<div class="container pt-4"><a class="btn btn-gold" href="download_invoice.php?id=<?= (int)$order['id'] ?>&amp;token=<?= urlencode($invoiceToken) ?>" download>Telecharger la facture PDF</a><?php if (currentCustomer()): ?><a class="btn btn-outline-dark ms-2" href="client/mes-factures.php">Voir toutes mes factures</a><?php else: ?><p class="mt-2">Conservez ce lien, il vous permettra de retrouver votre facture à tout moment.</p><?php endif; ?></div>
<main><section class="section section-light"><div class="container"><div class="section-heading"><p class="eyebrow">Merci pour votre confiance</p><h1>Commande <em>#<?= (int)$order['id'] ?></em> confirmée</h1><p>Un récapitulatif a été préparé pour <?= e($order['customer_email']) ?>.</p></div><div class="table-responsive"><table class="table"><thead><tr><th>Produit</th><th>Quantité</th><th>Prix</th><th>Total</th></tr></thead><tbody><?php foreach ($items as $item): ?><tr><td><?= e($item['name']) ?></td><td><?= (int)$item['quantity'] ?></td><td><?= number_format($item['price'], 0, ',', ' ') ?> FCFA</td><td><?= number_format($item['price'] * $item['quantity'], 0, ',', ' ') ?> FCFA</td></tr><?php endforeach; ?></tbody></table></div><p class="h4 text-end">Total : <?= number_format($order['total_amount'], 0, ',', ' ') ?> FCFA</p><a class="btn btn-gold" href="catalogue.php">Retour au catalogue</a></div></section></main><?php include __DIR__ . '/includes/footer.php'; ?>
