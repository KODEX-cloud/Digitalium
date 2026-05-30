<?php
/**
 * Digitalium Group - Root Front Controller
 * Bootstraps config, session, autoloader, and routes.
 */

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
