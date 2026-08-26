<?php
require_once __DIR__ . '/config.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
generateCsrfToken();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($GLOBALS['csrfFailureRendering'])) verifyCsrfToken();
ob_start(function ($buffer) {
    return preg_replace('/(<form\b(?=[^>]*\bmethod\s*=\s*["\']post["\'])[^>]*>)/i', '$1' . csrfField(), $buffer);
});
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
function cartTotal() { return cartGrandTotal(); }
