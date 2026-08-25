<?php
require_once __DIR__ . '/config.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
function cartCount() { return array_sum($_SESSION['cart']); }
function cartProducts() {
    if (!$_SESSION['cart']) return [];
    $ids = array_keys($_SESSION['cart']);
    $marks = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT * FROM products WHERE id IN ($marks)");
    $stmt->execute($ids);
    return $stmt->fetchAll();
}
function cartTotal() {
    $total = 0;
    foreach (cartProducts() as $product) $total += (float)$product['price'] * (isset($_SESSION['cart'][$product['id']]) ? $_SESSION['cart'][$product['id']] : 0);
    return $total;
}
