<?php

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function generateCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(function_exists('random_bytes') ? random_bytes(32) : openssl_random_pseudo_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken()
{
    $submitted = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    $stored = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';
    $valid = function_exists('hash_equals') ? hash_equals($stored, $submitted) : (strlen($stored) === strlen($submitted) && $stored === $submitted);
    if (!$stored || !$submitted || !$valid) {
        http_response_code(403);
        $GLOBALS['csrfFailureRendering'] = true;
        require dirname(__DIR__) . '/403.php';
        exit;
    }
}

function csrfField()
{
    return '<input type="hidden" name="csrf_token" value="' . e(generateCsrfToken()) . '">';
}

function redirect($path)
{
    header('Location: ' . $path);
    exit;
}

function sendOrderConfirmation($order)
{
    $autoload = dirname(__DIR__) . '/vendor/autoload.php';
    if (!$order['customer_email'] || !is_file($autoload)) {
        return false;
    }
    require_once $autoload;
    if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        return false;
    }
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        // TODO: configurer SMTP réel
        $mail->isSMTP();
        $mail->Host = envValue('SMTP_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = envValue('SMTP_USER');
        $mail->Password = envValue('SMTP_PASS');
        $mail->Port = (int)envValue('SMTP_PORT', 587);
        $mail->setFrom(envValue('SMTP_FROM', 'no-reply@woodin.cm'), 'Woodin Cameroun');
        $mail->addAddress($order['customer_email'], $order['customer_name']);
        $mail->Subject = 'Votre facture WOODIN Cameroun - Commande #' . $order['id'];
        $mail->Body = 'Votre commande #' . $order['id'] . ' a bien été enregistrée. Total : ' . $order['total_amount'] . ' FCFA.';
        if (!empty($order['invoice_path']) && is_file($order['invoice_path'])) {
            $mail->addAttachment($order['invoice_path'], 'facture_' . (int)$order['id'] . '.pdf');
        }
        return $mail->send();
    } catch (Exception $error) {
        error_log($error->getMessage());
        return false;
    }
}

function currentCustomer()
{
    static $customer = false;
    if ($customer !== false) {
        return $customer;
    }
    $customer = null;
    if (!empty($_SESSION['customer_id'])) {
        $stmt = db()->prepare('SELECT * FROM customers WHERE id = ?');
        $stmt->execute([(int)$_SESSION['customer_id']]);
        $customer = $stmt->fetch();
        if (!$customer) {
            unset($_SESSION['customer_id']);
        }
    }
    return $customer;
}

function requireCustomer($loginPath = 'login.php')
{
    if (!currentCustomer()) {
        redirect($loginPath);
    }
}

function productPromotion($productId)
{
    $stmt = db()->prepare('SELECT * FROM product_promotions WHERE product_id = ? AND starts_at <= CURDATE() AND ends_at >= CURDATE() ORDER BY discount_percentage DESC LIMIT 1');
    $stmt->execute([(int)$productId]);
    return $stmt->fetch();
}

function productSalePrice($product)
{
    $promotion = productPromotion($product['id']);
    if (!$promotion) return (float)$product['price'];
    return round((float)$product['price'] * (1 - ((float)$promotion['discount_percentage'] / 100)), 2);
}

function cartSubtotal()
{
    $total = 0;
    foreach (cartProducts() as $product) {
        $quantity = isset($_SESSION['cart'][$product['id']]) ? (int)$_SESSION['cart'][$product['id']] : 0;
        $total += productSalePrice($product) * $quantity;
    }
    return round($total, 2);
}

function validAppliedDiscount($subtotal)
{
    if (empty($_SESSION['applied_discount']['id'])) return null;
    $stmt = db()->prepare('SELECT * FROM discounts WHERE id = ? AND code = ? AND is_active = 1 AND valid_from <= CURDATE() AND valid_until >= CURDATE() AND (usage_limit IS NULL OR usage_count < usage_limit)');
    $stmt->execute([(int)$_SESSION['applied_discount']['id'], $_SESSION['applied_discount']['code']]);
    $discount = $stmt->fetch();
    if (!$discount || ($discount['min_purchase_amount'] !== null && $subtotal < (float)$discount['min_purchase_amount'])) return null;
    return $discount;
}

function cartDiscount()
{
    $discount = validAppliedDiscount(cartSubtotal());
    if (!$discount) return 0;
    $amount = $discount['type'] === 'percentage' ? cartSubtotal() * ((float)$discount['value'] / 100) : (float)$discount['value'];
    return round(min(cartSubtotal(), $amount), 2);
}

function cartGrandTotal()
{
    return round(max(0, cartSubtotal() - cartDiscount()), 2);
}