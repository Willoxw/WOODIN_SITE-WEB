<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../panier.php');
verifyCsrfToken();
$_SESSION['cart'] = [];
redirect('../panier.php');
