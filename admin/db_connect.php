<?php
require_once __DIR__ . '/../includes/config.php';
try { $pdo = db(); } catch (Exception $error) { http_response_code(500); exit('Connexion à la base impossible. Vérifiez WampServer et les paramètres de includes/config.php.'); }
