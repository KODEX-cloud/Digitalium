<?php
/**
 * Digitalium Group - Root Front Controller
 * Bootstraps config, session, autoloader, and routes.
 */

// ─── MAINTENANCE MODE ─────────────────────────────────────────────────────────
$_root = dirname(__FILE__);
if (file_exists($_root . '/storage/maintenance.lock')) {
    http_response_code(503);
    header('Retry-After: 120');
    $mPage = $_root . '/public/maintenance.php';
    if (file_exists($mPage)) { include $mPage; }
    else { echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Maintenance</title></head><body style="font-family:system-ui;text-align:center;padding:5rem;"><h1>🔧 Maintenance</h1><p>Retour dans quelques minutes.</p></body></html>'; }
    exit;
}
unset($_root);

// ─── GESTIONNAIRE D'ERREURS — JAMAIS de stack trace publique ─────────────────
set_exception_handler(function (\Throwable $e) {
    $root    = dirname(__FILE__);
    $logPath = $root . '/storage/logs/errors.log';
    $env     = defined('ENVIRONMENT') ? ENVIRONMENT : 'production';
    $entry   = date('Y-m-d H:i:s') . ' [EXCEPTION] ' . get_class($e) . ': ' . $e->getMessage()
             . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString() . "\n---\n";
    @file_put_contents($logPath, $entry, FILE_APPEND | LOCK_EX);
    if (!headers_sent()) { http_response_code(500); header('Content-Type: text/html; charset=UTF-8'); }
    if ($env === 'development') {
        echo '<pre style="background:#1e293b;color:#e2e8f0;padding:2rem;border-radius:8px;margin:2rem;font-size:.85rem;">';
        echo htmlspecialchars(get_class($e) . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        echo '</pre>';
    } else {
        $view500 = $root . '/app/Views/errors/500.php';
        if (file_exists($view500)) { if (ob_get_level() > 0) ob_end_clean(); include $view500; }
        else { echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Erreur</title></head><body style="font-family:system-ui;text-align:center;padding:5rem;background:#f8fafc;"><h1 style="color:#1e293b;">Service temporairement indisponible</h1><p style="color:#64748b;">Nous travaillons à résoudre le problème. Réessayez dans quelques instants.</p></body></html>'; }
    }
    exit;
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $root    = dirname(__FILE__);
        $logPath = $root . '/storage/logs/errors.log';
        $entry   = date('Y-m-d H:i:s') . ' [FATAL] ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line'] . "\n---\n";
        @file_put_contents($logPath, $entry, FILE_APPEND | LOCK_EX);
        $env = defined('ENVIRONMENT') ? ENVIRONMENT : 'production';
        if ($env !== 'development') {
            if (!headers_sent()) { http_response_code(500); }
            $view500 = $root . '/app/Views/errors/500.php';
            if (ob_get_level() > 0) ob_end_clean();
            if (file_exists($view500)) { include $view500; }
            else { echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"></head><body style="font-family:system-ui;text-align:center;padding:5rem;"><h1>Service temporairement indisponible</h1></body></html>'; }
        }
    }
});

// 0. PHP Built-in Server static files fallback
if (php_sapi_name() === 'cli-server') {
    $filePath = __DIR__ . '/public' . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (file_exists($filePath) && is_file($filePath)) {
        return false;
    }
}

// Define access constant
define('SECURE_ACCESS', true);

// 1. Load Configuration
require_once __DIR__ . '/config/config.php';

// 2. PSR-4 Compliant Autoloader
spl_autoload_register(function ($class) {
    // Project-specific namespace prefix
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';

    // Check if the class uses the prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Get the relative class name and convert namespace backslash to path separator
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// 3. Start Secure Session
use App\Services\Session;
use App\Services\Router;

Session::start();

// 4. Initialize Router
$router = new Router();

// 5. Load Declared Routes
require_once __DIR__ . '/routes/web.php';

// 6. Dispatch current request
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';

// Clean subfolder paths if wamp runs in subdirectory
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptName !== '/' && $scriptName !== '\\') {
    $uri = str_replace($scriptName, '', $uri);
}

$router->dispatch($method, $uri);
