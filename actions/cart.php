<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../panier.php');
$id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT); $action = isset($_POST['action']) ? $_POST['action'] : '';
if ($id && isset($_SESSION['cart'][$id])) { if ($action === 'increase') $_SESSION['cart'][$id]++; if ($action === 'decrease') $_SESSION['cart'][$id]--; if ($action === 'remove' || $_SESSION['cart'][$id] < 1) unset($_SESSION['cart'][$id]); }
redirect('../panier.php');
