<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../catalogue.php');
$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$quantity = max(1, filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT) ?: 1);
$stmt = db()->prepare('SELECT stock FROM products WHERE id = ?'); $stmt->execute([$productId]); $product = $stmt->fetch();
if (!$product || $product['stock'] < $quantity) { $_SESSION['flash'] = 'Stock insuffisant pour ce produit.'; redirect('../catalogue.php'); }
$_SESSION['cart'][$productId] = (isset($_SESSION['cart'][$productId]) ? $_SESSION['cart'][$productId] : 0) + $quantity;
$_SESSION['flash'] = 'Produit ajouté au panier.';
redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../catalogue.php');
