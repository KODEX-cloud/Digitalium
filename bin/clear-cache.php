<?php

// ─── Sécurité : CLI uniquement ────────────────────────────────────────────────
// Le dossier bin/ était servi par Apache : /bin/read_logs.php et /bin/deploy.php
// répondaient en HTTP. Le .htaccess les bloque désormais, mais un .htaccess perdu
// ou ignoré ne doit pas suffire à rendre ces scripts exécutables depuis le Web.
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Access denied — CLI only');
}
/**
 * CLI Utility: Clear CMS Cache
 */

define('SECURE_ACCESS', true);
require_once __DIR__ . '/../config/config.php';

// PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';
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

use App\Services\Cache;

try {
    Cache::clear();
    echo "CMS cache successfully cleared!\n";
} catch (\Exception $e) {
    echo "Error clearing cache: " . $e->getMessage() . "\n";
}
