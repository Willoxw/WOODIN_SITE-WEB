<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/generate_invoice.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$_SESSION['cart']) redirect('../panier.php');
verifyCsrfToken();
$name = trim(isset($_POST['customer_name']) ? $_POST['customer_name'] : ''); $phone = trim(isset($_POST['customer_phone']) ? $_POST['customer_phone'] : ''); $email = trim(isset($_POST['customer_email']) ? $_POST['customer_email'] : '');
$customer = currentCustomer();
if ($customer) {
  $name = $customer['full_name'];
  $phone = $customer['phone'];
  $email = $customer['email'];
}
if ($name === '' || !preg_match('/^[\p{L}][\p{L} \-\']{1,99}$/u', $name)) { $_SESSION['flash'] = 'Veuillez saisir un nom valide.'; redirect('../panier.php'); }
if (!preg_match('/^(?:6\d{8}|\+2376\d{8})$/', $phone)) { $_SESSION['flash'] = 'Veuillez saisir un numéro camerounais valide.'; redirect('../panier.php'); }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $_SESSION['flash'] = 'Veuillez saisir une adresse email valide.'; redirect('../panier.php'); }
$cart = [];
foreach ($_SESSION['cart'] as $productId => $cartQuantity) {
    $productId = (int)$productId;
    $cartQuantity = filter_var($cartQuantity, FILTER_VALIDATE_INT);
    if ($productId < 1 || $cartQuantity === false || $cartQuantity < 1) {
        $_SESSION['flash'] = 'Le panier contient une quantité invalide.';
        redirect('../panier.php');
    }
    $cart[$productId] = $cartQuantity;
}
$ids = array_keys($cart);
sort($ids, SORT_NUMERIC);
$pdo = db();
try {
  $pdo->beginTransaction();
  $marks = implode(',', array_fill(0, count($ids), '?')); $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($marks) ORDER BY id ASC FOR UPDATE"); $stmt->execute($ids); $rows = $stmt->fetchAll(); $subtotal = 0;
  if (count($rows) !== count($ids)) throw new RuntimeException('Un produit du panier n’existe plus.');
  foreach ($rows as $row) { $quantity = $cart[(int)$row['id']]; if ((int)$row['stock'] < $quantity) throw new DomainException('Stock épuisé entre-temps, désolé.'); $subtotal += productSalePrice($row) * $quantity; }
  $discountAmount = cartDiscount(); $total = round(max(0, $subtotal - $discountAmount), 2);
  $discount = validAppliedDiscount($subtotal);
  if (!$discount) { $discountAmount = 0; $total = $subtotal; }
  $invoiceToken = bin2hex(random_bytes(16));
  $order = $pdo->prepare("INSERT INTO orders (customer_id,customer_name,customer_phone,customer_email,total_amount,discount_amount,invoice_token) VALUES (?,?,?,?,?,?,?)"); $order->execute([$customer ? $customer['id'] : null,$name,$phone,$email,$total,$discountAmount,$invoiceToken]); $orderId = $pdo->lastInsertId();
  if ($discount) { $usage = $pdo->prepare('UPDATE discounts SET usage_count = usage_count + 1 WHERE id = ? AND (usage_limit IS NULL OR usage_count < usage_limit)'); $usage->execute([$discount['id']]); if ($usage->rowCount() !== 1) throw new DomainException('Ce code promo vient d’être épuisé.'); if ($customer) $pdo->prepare('INSERT INTO discount_usage (customer_id,discount_id,order_id) VALUES (?,?,?)')->execute([$customer['id'],$discount['id'],$orderId]); }
  foreach ($rows as $row) {
    $quantity = $cart[(int)$row['id']];
    $stockUpdate = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
    $stockUpdate->execute([$quantity, $row['id'], $quantity]);
    if ($stockUpdate->rowCount() !== 1) throw new DomainException('Stock épuisé entre-temps, désolé.');
    $pdo->prepare('INSERT INTO order_items (order_id,product_id,quantity,price) VALUES (?,?,?,?)')->execute([$orderId,$row['id'],$quantity,$row['price']]);
    $pdo->prepare("INSERT INTO stock_movements (product_id, quantity_change, reason) VALUES (?, ?, 'vente')")->execute([$row['id'], -$quantity]);
  }
  $pdo->commit(); $invoicePath = generateInvoicePdf($orderId); $confirmation = ['id' => $orderId, 'customer_name' => $name, 'customer_email' => $email, 'total_amount' => $total, 'invoice_path' => $invoicePath]; sendOrderConfirmation($confirmation); $_SESSION['cart'] = []; unset($_SESSION['applied_discount']); $_SESSION['last_order'] = ['id' => (int)$orderId, 'invoice_token' => $invoiceToken]; unset($_SESSION['last_order_id']); redirect('../order_success.php');
} catch (Exception $error) { if ($pdo->inTransaction()) $pdo->rollBack(); error_log($error->getMessage()); $_SESSION['flash'] = $error instanceof DomainException ? $error->getMessage() : 'Une erreur est survenue, veuillez réessayer.'; redirect('../panier.php'); }
