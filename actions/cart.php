<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../panier.php');
verifyCsrfToken();

$id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$action = isset($_POST['action']) ? strtolower(trim((string)$_POST['action'])) : '';

if (!$id) {
	$_SESSION['flash'] = 'Produit introuvable.';
	redirect('../panier.php');
}

if (!isset($_SESSION['cart'][$id])) {
	$_SESSION['flash'] = 'Produit absent du panier.';
	redirect('../panier.php');
}

if ($action === 'remove') {
	unset($_SESSION['cart'][$id]);
	redirect('../panier.php');
}

$stmt = db()->prepare('SELECT stock FROM products WHERE id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
	unset($_SESSION['cart'][$id]);
	$_SESSION['flash'] = 'Produit introuvable.';
	redirect('../panier.php');
}

if ($action === 'increase') {
	if ((int)$_SESSION['cart'][$id] >= (int)$product['stock']) {
		$_SESSION['flash'] = 'Stock maximum atteint pour ce produit.';
		redirect('../panier.php');
	}
	$_SESSION['cart'][$id] = (int)$_SESSION['cart'][$id] + 1;
}

if ($action === 'decrease') {
	$_SESSION['cart'][$id] = max(0, (int)$_SESSION['cart'][$id] - 1);
	if ((int)$_SESSION['cart'][$id] <= 0) {
		unset($_SESSION['cart'][$id]);
	}
}

redirect('../panier.php');
