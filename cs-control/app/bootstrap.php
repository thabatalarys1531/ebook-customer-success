<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

// Carrega o arquivo .env manualmente (sem depender de Composer/bibliotecas externas,
// para funcionar em qualquer hospedagem cPanel sem configuração extra).
$envPath = APP_ROOT . '/.env';
if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim(trim($value), "\"'");
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
    }
}

function env(string $key, $default = null) {
    $value = $_ENV[$key] ?? getenv($key);
    return $value === false || $value === null || $value === '' ? $default : $value;
}

require_once APP_ROOT . '/app/Database.php';
require_once APP_ROOT . '/app/helpers.php';
require_once APP_ROOT . '/app/Auth.php';
require_once APP_ROOT . '/app/ActivityTypes.php';
require_once APP_ROOT . '/app/Clients.php';
require_once APP_ROOT . '/app/Alerts.php';
require_once APP_ROOT . '/app/Permissions.php';
require_once APP_ROOT . '/app/Insights.php';
require_once APP_ROOT . '/app/Users.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name(env('SESSION_NAME', 'cscontrol_session'));
    session_start();
}

date_default_timezone_set('America/Sao_Paulo');
