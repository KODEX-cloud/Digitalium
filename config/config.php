<?php
/**
 * Digitalium Configuration File
 * Built for PHP 8.1+ & Hostinger / Local WAMP deployment.
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
}

// 1. Error Reporting (Dev mode vs Production mode)
define('ENVIRONMENT', 'development'); // Change to 'production' on Hostinger

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../storage/logs/php_error.log');
}

// 2. Paths Configuration
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/assets/uploads');
define('UPLOAD_URL', '/assets/uploads');

// 3. Database Credentials
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'digitalium_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// 4. Session & Security
define('SESSION_LIFETIME', 3600 * 2); // 2 hours
define('PASSWORD_MIN_LENGTH', 8);
define('APP_SECRET', '4f923b784a92c0199e8293e9da2884a0d927d6d5ef18c7c90b0be83984d76fe2'); // Static secure key

// 5. Site Constants
define('DEFAULT_PAGE_SLUG', 'home');
define('ADMIN_PREFIX', '/admin');
