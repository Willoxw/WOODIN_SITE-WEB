<?php
require_once __DIR__ . '/config.php';
$isProduction = ENVIRONMENT === 'prod' || envValue('ENVIRONMENT') === 'production' || envValue('APP_ENV') === 'production';
if ($isProduction) {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', dirname(__DIR__) . '/logs/error.log');
    error_reporting(E_ALL);
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        error_log("[$errno] $errstr dans $errfile ligne $errline");
        return true;
    });
    set_exception_handler(function ($exception) {
        error_log('[EXCEPTION] ' . $exception->getMessage() . ' dans ' . $exception->getFile() . ' ligne ' . $exception->getLine());
        http_response_code(500);
        require dirname(__DIR__) . '/includes/500.php';
        exit;
    });
    register_shutdown_function(function () {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            error_log("[FATAL] {$error['message']} dans {$error['file']} ligne {$error['line']}");
            http_response_code(500);
            require dirname(__DIR__) . '/includes/500.php';
        }
    });
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
generateCsrfToken();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($GLOBALS['csrfFailureRendering'])) verifyCsrfToken();
ob_start(function ($buffer) {
    return preg_replace('/(<form\b(?=[^>]*\bmethod\s*=\s*["\']post["\'])[^>]*>)/i', '$1' . csrfField(), $buffer);
});
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
function cartCount() { return array_sum($_SESSION['cart']); }
function cartProducts() {
    if (!$_SESSION['cart']) return [];
    $ids = array_keys($_SESSION['cart']);
    $marks = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT * FROM products WHERE id IN ($marks)");
    $stmt->execute($ids);
    return $stmt->fetchAll();
}
function cartTotal() { return cartGrandTotal(); }
