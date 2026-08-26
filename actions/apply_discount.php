<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../panier.php');
verifyCsrfToken();
$code = strtoupper(trim(isset($_POST['code']) ? $_POST['code'] : ''));
$subtotal = cartSubtotal();
if ($code === '' || $subtotal <= 0) {
    $_SESSION['flash'] = 'Saisissez un code promo avec un panier non vide.';
    redirect('../panier.php');
}
$stmt = db()->prepare('SELECT * FROM discounts WHERE code = ? AND is_active = 1 AND valid_from <= CURDATE() AND valid_until >= CURDATE() AND (usage_limit IS NULL OR usage_count < usage_limit)');
$stmt->execute([$code]);
$discount = $stmt->fetch();
if (!$discount) {
    unset($_SESSION['applied_discount']);
    $_SESSION['flash'] = 'Code promo invalide, expiré ou épuisé.';
    redirect('../panier.php');
}
if ($discount['min_purchase_amount'] !== null && $subtotal < (float)$discount['min_purchase_amount']) {
    $_SESSION['flash'] = 'Le minimum d’achat pour ce code est de ' . number_format($discount['min_purchase_amount'], 0, ',', ' ') . ' FCFA.';
    redirect('../panier.php');
}
$_SESSION['applied_discount'] = ['id' => (int)$discount['id'], 'code' => $discount['code']];
$_SESSION['flash'] = 'Code ' . $discount['code'] . ' appliqué. Réduction : ' . number_format(cartDiscount(), 0, ',', ' ') . ' FCFA.';
redirect('../panier.php');
