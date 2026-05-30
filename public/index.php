<?php
/**
 * Digitalium Group Front Controller
 * Entry point for all HTTP requests.
 */

// 0. PHP Built-in Server static files fallback
if (php_sapi_name() === 'cli-server') {
    $filePath = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (file_exists($filePath) && is_file($filePath)) {
        return false;
    }
}

// Define access constant
define('SECURE_ACCESS', true);

// 1. Load Configuration
require_once __DIR__ . '/../config/config.php';

// 2. Class Autoloader (PSR-4 compliant structure)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = APP_PATH . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

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

// 4. Initialize Router & Load Routes
$router = new Router();
require_once ROOT_PATH . '/routes/web.php';

// 5. Dispatch Request
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';

// Clean subfolder paths if wamp runs in subdirectory
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptName !== '/' && $scriptName !== '\\') {
    $uri = str_replace($scriptName, '', $uri);
}

$router->dispatch($method, $uri);
