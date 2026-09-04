<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../catalogue.php');
verifyCsrfToken();

$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
$quantity = ($quantity === false || $quantity < 1) ? 1 : (int)$quantity;

if (!$productId) {
	$_SESSION['flash'] = 'Produit introuvable.';
	redirect('../catalogue.php');
}

$stmt = db()->prepare('SELECT id, stock, name FROM products WHERE id = ?');
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
	$_SESSION['flash'] = 'Produit introuvable.';
	redirect('../catalogue.php');
}

$cartQuantity = isset($_SESSION['cart'][$productId]) ? (int)$_SESSION['cart'][$productId] : 0;
if ((int)$product['stock'] < $cartQuantity + $quantity) {
	$_SESSION['flash'] = 'Stock insuffisant pour ce produit.';
	redirect('../catalogue.php');
}

$_SESSION['cart'][$productId] = $cartQuantity + $quantity;
$_SESSION['flash'] = 'Produit ajouté au panier.';

redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../catalogue.php');
