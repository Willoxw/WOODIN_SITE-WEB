<?php
require_once __DIR__ . '/db_connect.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../includes/functions.php';
generateCsrfToken();
if ($_SERVER['REQUEST_METHOD'] === 'POST') verifyCsrfToken();
$adminHeaderPath = dirname(__DIR__) . '/includes/admin_header.php';
$adminHeader = '';
if (is_file($adminHeaderPath)) {
	ob_start();
	include $adminHeaderPath;
	$adminHeader = ob_get_clean();
}
ob_start(function ($buffer) use ($adminHeader) {
	$buffer = preg_replace('/<nav class="navbar navbar-dark bg-dark">.*?<\/nav>/s', '', $buffer, 1);
	$buffer = preg_replace('/<body([^>]*)>/', '<body$1>' . $adminHeader, $buffer, 1);
	return preg_replace('/(<form\b(?=[^>]*\bmethod\s*=\s*["\']post["\'])[^>]*>)/i', '$1' . csrfField(), $buffer);
});
if (empty($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
$currentAdmin = $pdo->prepare('SELECT role, is_active FROM admins WHERE id = ?');
$currentAdmin->execute([(int)$_SESSION['admin_id']]);
$currentAdmin = $currentAdmin->fetch();
if (!$currentAdmin || !$currentAdmin['is_active']) { $_SESSION = []; session_destroy(); header('Location: login.php'); exit; }
$_SESSION['admin_role'] = $currentAdmin['role'];
if (!empty($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > 1200) {
	$_SESSION = [];
	session_destroy();
	header('Location: login.php?expired=1');
	exit;
}
$_SESSION['last_activity'] = time();
