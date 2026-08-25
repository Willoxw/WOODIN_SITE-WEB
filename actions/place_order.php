<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$_SESSION['cart']) redirect('../panier.php');
$name = trim(isset($_POST['customer_name']) ? $_POST['customer_name'] : ''); $phone = trim(isset($_POST['customer_phone']) ? $_POST['customer_phone'] : '');
if ($name === '' || $phone === '') { $_SESSION['flash'] = 'Nom et téléphone sont obligatoires.'; redirect('../panier.php'); }
$pdo = db(); $pdo->beginTransaction();
try {
  $ids = array_keys($_SESSION['cart']); $marks = implode(',', array_fill(0, count($ids), '?')); $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($marks) FOR UPDATE"); $stmt->execute($ids); $rows = $stmt->fetchAll(); $total = 0;
  foreach ($rows as $row) { $quantity = $_SESSION['cart'][$row['id']]; if ($row['stock'] < $quantity) throw new RuntimeException('Stock insuffisant pour ' . $row['name']); $total += $row['price'] * $quantity; }
  $order = $pdo->prepare("INSERT INTO orders (customer_name,customer_phone,total_amount) VALUES (?,?,?)"); $order->execute([$name,$phone,$total]); $orderId = $pdo->lastInsertId();
  foreach ($rows as $row) { $quantity = $_SESSION['cart'][$row['id']]; $pdo->prepare('INSERT INTO order_items (order_id,product_id,quantity,price) VALUES (?,?,?,?)')->execute([$orderId,$row['id'],$quantity,$row['price']]); $pdo->prepare('UPDATE products SET stock=stock-? WHERE id=?')->execute([$quantity,$row['id']]); }
  $pdo->commit(); $_SESSION['cart'] = []; $_SESSION['flash'] = 'Commande #' . $orderId . ' enregistrée avec succès.'; redirect('../index.php');
} catch (Exception $error) { $pdo->rollBack(); $_SESSION['flash'] = $error->getMessage(); redirect('../panier.php'); }
