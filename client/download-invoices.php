<?php
require_once __DIR__ . '/../includes/bootstrap.php';
requireCustomer('login.php');
if (!class_exists('ZipArchive')) { http_response_code(500); exit('Le téléchargement ZIP est indisponible sur ce serveur.'); }
$stmt = db()->prepare("SELECT id FROM orders WHERE customer_id = ? AND status <> 'Annulée'"); $stmt->execute([currentCustomer()['id']]); $orders = $stmt->fetchAll();
$zipPath = tempnam(sys_get_temp_dir(), 'woodin_'); $zip = new ZipArchive(); $zip->open($zipPath, ZipArchive::OVERWRITE);
$added = 0; foreach ($orders as $order) { $path = dirname(__DIR__) . '/invoices/facture_' . (int)$order['id'] . '.pdf'; if (is_file($path)) { $zip->addFile($path, basename($path)); $added++; } }
$zip->close(); if (!$added) { unlink($zipPath); exit('Aucune facture disponible.'); }
header('Content-Type: application/zip'); header('Content-Disposition: attachment; filename="mes-factures-woodin.zip"'); readfile($zipPath); unlink($zipPath); exit;
