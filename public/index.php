<?php
/**
 * Digitalium Group Front Controller
 * Entry point for all HTTP requests.
 */

// ─── MAINTENANCE MODE ─────────────────────────────────────────────────────────
// Créer storage/maintenance.lock pour activer la maintenance.
// Supprimer le fichier pour revenir en production.
$_root = dirname(__DIR__);
if (file_exists($_root . '/storage/maintenance.lock')) {
    http_response_code(503);
    header('Retry-After: 120');
    $mPage = $_root . '/public/maintenance.php';
    if (file_exists($mPage)) {
        include $mPage;
    } else {
        echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Maintenance</title></head><body style="font-family:system-ui;text-align:center;padding:5rem;"><h1>🔧 Maintenance en cours</h1><p>Retour dans quelques minutes.</p></body></html>';
    }
    exit;
}
unset($_root);

// ─── Bootstrap minimal pour ErrorHandler ─────────────────────────────────────
// On charge la config d'abord pour avoir ROOT_PATH et ENVIRONMENT disponibles
// dans le handler AVANT que les routes ne soient chargées.
// (le require config.php sera répété plus bas — idempotent via define())

// Gestionnaire d'erreurs global — JAMAIS de stack trace publique en production.
// Voir app/Services/ErrorHandler.php pour la logique complète.
set_exception_handler(function (\Throwable $e) {
    // Chemin log sans dépendances (ROOT_PATH peut ne pas être défini encore)
    $root    = dirname(__DIR__);
    $logPath = $root . '/storage/logs/errors.log';
    $env     = defined('ENVIRONMENT') ? ENVIRONMENT : 'production';

    // Log complet dans le fichier (jamais dans le navigateur)
    $entry = date('Y-m-d H:i:s')
        . ' [EXCEPTION] ' . get_class($e)
        . ': ' . $e->getMessage()
        . ' in ' . $e->getFile() . ':' . $e->getLine()
        . "\n" . $e->getTraceAsString()
        . "\n---\n";
    @file_put_contents($logPath, $entry, FILE_APPEND | LOCK_EX);

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }

    if ($env === 'development') {
        // Développement : stack trace visible
        echo "<div style='background:#0f172a;color:#e2e8f0;padding:1.5rem;font-family:monospace;'>";
        echo "<h2 style='color:#f87171;'>" . get_class($e) . "</h2>";
        echo "<p style='color:#fbbf24;'>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p style='color:#94a3b8;'>in " . htmlspecialchars($e->getFile()) . " line " . $e->getLine() . "</p>";
        echo "<pre style='color:#cbd5e1;overflow:auto;background:#1e293b;padding:1rem;border-radius:8px;margin-top:1rem;'>";
        echo htmlspecialchars($e->getTraceAsString());
        echo "</pre></div>";
    } else {
        // Production : page propre, aucune info sensible
        $view500 = $root . '/app/Views/errors/500.php';
        if (file_exists($view500)) {
            if (ob_get_level() > 0) ob_end_clean();
            include $view500;
        } else {
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Erreur</title></head><body style="font-family:sans-serif;text-align:center;padding:4rem;"><h1>Service temporairement indisponible</h1><p>Merci de réessayer dans quelques instants.</p><a href="/">Accueil</a></body></html>';
        }
    }
    exit;
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $root    = dirname(__DIR__);
        $logPath = $root . '/storage/logs/errors.log';
        $env     = defined('ENVIRONMENT') ? ENVIRONMENT : 'production';

        $entry = date('Y-m-d H:i:s') . ' [FATAL] ' . $error['message']
            . ' in ' . $error['file'] . ':' . $error['line'] . "\n";
        @file_put_contents($logPath, $entry, FILE_APPEND | LOCK_EX);

        if (!headers_sent()) {
            http_response_code(500);
        }

        if ($env !== 'development') {
            $view500 = $root . '/app/Views/errors/500.php';
            if (ob_get_level() > 0) @ob_end_clean();
            file_exists($view500) ? include $view500 : print('Service temporairement indisponible');
        }
    }
});

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
