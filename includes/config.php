<?php
define('ENVIRONMENT', getenv('APP_ENV') === 'prod' ? 'prod' : 'dev');

require_once __DIR__ . '/functions.php';

function loadEnv($path)
{
    if (!is_readable($path)) {
        return;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        $value = trim($value);
        if (strlen($value) >= 2 && $value[0] === '"' && substr($value, -1) === '"') {
            $value = substr($value, 1, -1);
        }
        $_ENV[trim($key)] = $value;
    }
}

$projectRoot = dirname(__DIR__);
$environmentFile = ENVIRONMENT === 'prod' ? $projectRoot . '/.env.production' : $projectRoot . '/.env';
if (is_readable($environmentFile)) {
    loadEnv($environmentFile);
} elseif (ENVIRONMENT !== 'prod') {
    loadEnv($projectRoot . '/.env.development');
}

function envValue($key, $default = '')
{
    return isset($_ENV[$key]) ? $_ENV[$key] : $default;
}

function db() {
    static $pdo;
    if (!$pdo) {
        try {
            $pdo = new PDO(
                'mysql:host=' . envValue('DB_HOST', '127.0.0.1') . ';dbname=' . envValue('DB_NAME', 'woodin_db') . ';charset=utf8mb4',
                envValue('DB_USER', 'root'),
                envValue('DB_PASS'),
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]
            );
        } catch (PDOException $error) {
            error_log($error->getMessage());
            if (ENVIRONMENT === 'dev') {
                throw $error;
            }
            throw new RuntimeException('Une erreur est survenue, veuillez réessayer.');
        }
    }
    return $pdo;
}
