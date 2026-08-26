<?php
require_once __DIR__ . '/../includes/bootstrap.php';
unset($_SESSION['customer_id']);
redirect('login.php');
