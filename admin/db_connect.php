<?php
require_once __DIR__ . '/../includes/config.php';
try {
	$pdo = db();
} catch (Exception $error) {
	error_log($error->getMessage());
	http_response_code(500);
	exit('Une erreur est survenue, veuillez réessayer.');
}
