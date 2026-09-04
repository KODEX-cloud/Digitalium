<?php
/**
 * Digitalium Configuration File
 * Built for PHP 8.1+ & Hostinger / Local WAMP deployment.
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
}

// Define Root Path
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Load Environment variables from .env file if present
$envData = [];
if (file_exists(ROOT_PATH . '/.env')) {
    $envLines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $envData[trim($key)] = trim($val);
        }
    }
}

// 1. Error Reporting (Dev mode vs Production mode)
define('ENVIRONMENT', $envData['APP_ENV'] ?? 'development');

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . '/storage/logs/php_error.log');
}

// 2. Paths Configuration
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/assets/uploads');
define('UPLOAD_URL', '/assets/uploads');

// 3. Database Credentials
define('DB_HOST', $envData['DB_HOST'] ?? '127.0.0.1');
define('DB_PORT', $envData['DB_PORT'] ?? '3306');
define('DB_NAME', $envData['DB_NAME'] ?? 'digitalium_db');
define('DB_USER', $envData['DB_USER'] ?? 'root');
define('DB_PASS', $envData['DB_PASS'] ?? '');
define('DB_CHARSET', $envData['DB_CHARSET'] ?? 'utf8mb4');

// 4. Session & Security
define('SESSION_LIFETIME', 3600 * 2); // 2 hours
define('PASSWORD_MIN_LENGTH', 8);
define('APP_SECRET', $envData['APP_SECRET'] ?? '4f923b784a92c0199e8293e9da2884a0d927d6d5ef18c7c90b0be83984d76fe2');

// 5. Site Constants
define('DEFAULT_PAGE_SLUG', 'home');
define('ADMIN_PREFIX', '/admin');

/**
 * Global Helper to generate absolute URLs relative to the application base directory.
 * Works seamlessly in subdirectories (like /Digitalium) and production root.
 */
if (!function_exists('url')) {
    function url(string $path = ''): string {
        if (empty($path)) {
            return '';
        }
        // Un schéma exécutable dans un href transforme n'importe quel champ
        // « lien » du CMS en vecteur de script : il suffit de saisir
        // javascript:… dans un bouton pour que le clic exécute du code. Aucun
        // lien du site n'en a besoin — vérifié par grep sur tout l'arbre, zéro
        // occurrence. Ils deviennent une ancre morte plutôt qu'un lien actif.
        if (preg_match('/^\s*(javascript|data|vbscript)\s*:/i', $path)) {
            return '#';
        }

        // Skip external protocols, phone/mail schemes, or fragments
        if (preg_match('/^(https?:\/\/|mailto:|tel:|#)/i', $path)) {
            return $path;
        }
        static $basePath = null;
        if ($basePath === null) {
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            $dir = dirname($scriptName);
            if ($dir === '/' || $dir === '\\') {
                $basePath = '';
            } else {
                $basePath = rtrim(str_replace('\\', '/', $dir), '/');
            }
        }
        
        $path = '/' . ltrim($path, '/');
        
        // If the path already begins with $basePath (and $basePath is not empty), don't prepend it again
        if ($basePath !== '' && strpos($path, $basePath . '/') === 0) {
            return $path;
        }
        
        return $basePath . $path;
    }
}
