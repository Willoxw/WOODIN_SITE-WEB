<?php
require_once __DIR__ . '/db_connect.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../includes/functions.php';
generateCsrfToken();
if ($_SERVER['REQUEST_METHOD'] === 'POST') verifyCsrfToken();
ob_start(function ($buffer) {
	return preg_replace('/(<form\b(?=[^>]*\bmethod\s*=\s*["\']post["\'])[^>]*>)/i', '$1' . csrfField(), $buffer);
});
if (empty($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
if (!empty($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > 1200) {
	$_SESSION = [];
	session_destroy();
	header('Location: login.php?expired=1');
	exit;
}
$_SESSION['last_activity'] = time();
