<?php
/**
 * Digitalium Group - Root Front Controller
 * Bootstraps config, session, autoloader, and routes.
 */

// Custom Error & Exception Handler to bypass server blockages and show detailed logs
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }
        echo "<div style='background:#fee2e2;color:#991b1b;padding:20px;border:1px solid #f87171;border-radius:6px;font-family:monospace;margin:20px;'>";
        echo "<b>Fatal Error:</b> " . htmlspecialchars($error['message']) . "<br>";
        echo "in <b>" . htmlspecialchars($error['file']) . "</b> on line <b>" . $error['line'] . "</b><br>";
        echo "</div>";
    }
});

set_exception_handler(function($e) {
    if (!headers_sent()) {
        header('HTTP/1.1 500 Internal Server Error');
    }
    echo "<div style='background:#fee2e2;color:#991b1b;padding:20px;border:1px solid #f87171;border-radius:6px;font-family:monospace;margin:20px;'>";
    echo "<b>Exception:</b> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "in <b>" . htmlspecialchars($e->getFile()) . "</b> on line <b>" . $e->getLine() . "</b><br><br>";
    echo "<b>Stack trace:</b><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
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
