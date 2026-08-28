<?php
require_once __DIR__ . '/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('orders.php');
verifyCsrfToken();
$orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$status = isset($_POST['status']) ? $_POST['status'] : '';
$allowedStatuses = ['En attente', 'Confirmée', 'Expédiée', 'Annulée'];
if (!$orderId || !in_array($status, $allowedStatuses, true)) {
    $_SESSION['admin_flash'] = 'Statut de commande invalide.';
    redirect('orders.php');
}
$pdo->beginTransaction();
try {
    $orderStmt = $pdo->prepare('SELECT status FROM orders WHERE id = ? FOR UPDATE');
    $orderStmt->execute([$orderId]);
    $order = $orderStmt->fetch();
    if (!$order) {
        throw new RuntimeException('Commande introuvable.');
    }

    $oldStatus = $order['status'];
    if ($oldStatus !== $status && ($status === 'Annulée' || $oldStatus === 'Annulée')) {
        $itemsStmt = $pdo->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ? ORDER BY product_id ASC');
        $itemsStmt->execute([$orderId]);
        $items = $itemsStmt->fetchAll();

        foreach ($items as $item) {
            $productStmt = $pdo->prepare('SELECT id, name, stock FROM products WHERE id = ? FOR UPDATE');
            $productStmt->execute([(int)$item['product_id']]);
            $product = $productStmt->fetch();
            $quantity = (int)$item['quantity'];
            if (!$product) {
                throw new RuntimeException('Produit associé introuvable.');
            }
            if ($oldStatus === 'Annulée' && (int)$product['stock'] < $quantity) {
                throw new DomainException('Stock insuffisant pour réactiver la commande : ' . $product['name'] . '.');
            }

            $change = $oldStatus === 'Annulée' ? -$quantity : $quantity;
            $updateStock = $pdo->prepare('UPDATE products SET stock = stock + ? WHERE id = ? AND stock + ? >= 0');
            $updateStock->execute([$change, $product['id'], $change]);
            if ($updateStock->rowCount() !== 1) {
                throw new DomainException('Stock insuffisant pour réactiver la commande : ' . $product['name'] . '.');
            }

            $reason = $oldStatus === 'Annulée' ? 'vente' : 'annulation_commande';
            $movement = $pdo->prepare('INSERT INTO stock_movements (product_id, quantity_change, reason) VALUES (?, ?, ?)');
            $movement->execute([$product['id'], $change, $reason]);
        }
    }

    $updateOrder = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $updateOrder->execute([$status, $orderId]);
    $history = $pdo->prepare('INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, note) VALUES (?, ?, ?, ?, ?)');
    $history->execute([$orderId, $oldStatus, $status, (int)$_SESSION['admin_id'], null]);
    $pdo->commit();
    $_SESSION['admin_flash'] = 'Statut de la commande mis à jour.';
} catch (Exception $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log($error->getMessage());
    $_SESSION['admin_flash'] = $error instanceof DomainException ? $error->getMessage() : 'Impossible de mettre à jour cette commande.';
}
redirect('orders.php');