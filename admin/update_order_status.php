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
$stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
$stmt->execute([$status, $orderId]);
$_SESSION['admin_flash'] = 'Statut de la commande mis à jour.';
redirect('orders.php');