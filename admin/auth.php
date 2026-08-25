<?php
require_once __DIR__ . '/db_connect.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
function adminEscape($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
