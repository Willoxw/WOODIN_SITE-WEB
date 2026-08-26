<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../panier.php');
verifyCsrfToken();
$id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT); $action = isset($_POST['action']) ? $_POST['action'] : '';
if ($id && isset($_SESSION['cart'][$id])) {
	if ($action === 'increase') {
		$stmt = db()->prepare('SELECT stock FROM products WHERE id = ?');
		$stmt->execute([$id]);
		$product = $stmt->fetch();
		if (!$product || $_SESSION['cart'][$id] >= (int)$product['stock']) {
			$_SESSION['flash'] = 'Stock maximum atteint pour ce produit.';
			redirect('../panier.php');
		}
		$_SESSION['cart'][$id]++;
	}
	if ($action === 'decrease') $_SESSION['cart'][$id]--;
	if ($action === 'remove' || $_SESSION['cart'][$id] < 1) unset($_SESSION['cart'][$id]);
}
redirect('../panier.php');
