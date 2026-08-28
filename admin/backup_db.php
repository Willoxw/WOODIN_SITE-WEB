<?php
require_once __DIR__ . '/auth.php';
requireAdminRole('super_admin');
if (!function_exists('exec')) { http_response_code(500); exit('La sauvegarde est indisponible : exec() est désactivé sur cet hébergement.'); }
$directory = dirname(__DIR__) . '/backups';
if (!is_dir($directory)) mkdir($directory, 0755, true);
$file = $directory . '/woodin_db_' . date('Ymd_His') . '.sql';
$mysqldump = envValue('MYSQLDUMP_PATH', 'mysqldump');
if ($mysqldump === 'mysqldump' && PHP_OS_FAMILY === 'Windows') {
	$possiblePaths = ['C:\\wamp64\\bin\\mysql\\mysql8.0.31\\bin\\mysqldump.exe', 'C:\\wamp64\\bin\\mariadb\\mariadb10.x\\bin\\mysqldump.exe', 'C:\\wamp\\bin\\mysql\\mysql8.0.31\\bin\\mysqldump.exe'];
	foreach ($possiblePaths as $path) if (is_file($path)) { $mysqldump = $path; break; }
}
$command = escapeshellarg($mysqldump) . ' --host=' . escapeshellarg(envValue('DB_HOST', '127.0.0.1')) . ' --user=' . escapeshellarg(envValue('DB_USER', 'root')) . ' --password=' . escapeshellarg(envValue('DB_PASS')) . ' ' . escapeshellarg(envValue('DB_NAME', 'woodin_db')) . ' > ' . escapeshellarg($file) . ' 2>&1';
$output = [];
$returnCode = 0;
exec($command, $output, $returnCode);
if ($returnCode !== 0 || !is_file($file) || filesize($file) === 0) { if (is_file($file)) unlink($file); http_response_code(500); exit('La sauvegarde de la base de données a échoué.'); }
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . basename($file) . '"');
readfile($file);
unlink($file);
exit;
