<?php
namespace App\Services;

/**
 * ErrorHandler — Gestionnaire global d'erreurs et d'exceptions.
 *
 * En production : aucune stack trace visible. Tout va dans les logs.
 * En développement : affichage complet pour debugging.
 *
 * Enregistrer avec ErrorHandler::register() avant le dispatch.
 */
class ErrorHandler {

    private static string $logPath = '';
    private static string $env     = 'production';

    public static function register(): void {
        self::$logPath = ROOT_PATH . '/storage/logs/errors.log';
        self::$env     = defined('ENVIRONMENT') ? ENVIRONMENT : 'production';

        // Exceptions non catchées
        set_exception_handler([self::class, 'handleException']);

        // Erreurs PHP fatales
        register_shutdown_function([self::class, 'handleShutdown']);

        // Erreurs PHP non-fatales (en dev seulement)
        if (self::$env === 'development') {
            set_error_handler([self::class, 'handleError']);
        }
    }

    // ─── Exception handler ───────────────────────────────────────────────────
    public static function handleException(\Throwable $e): void {
        self::logException($e);

        if (!headers_sent()) {
            http_response_code(500);
        }

        if (self::$env === 'development') {
            self::renderDev($e);
        } else {
            self::renderProd();
        }
    }

    // ─── Shutdown handler (Fatal errors) ─────────────────────────────────────
    public static function handleShutdown(): void {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $entry = date('Y-m-d H:i:s')
                . ' [FATAL] ' . $error['message']
                . ' in ' . $error['file'] . ':' . $error['line'] . "\n";
            self::writeLog($entry);

            if (!headers_sent()) {
                http_response_code(500);
            }

            if (self::$env === 'development') {
                echo "<div style='background:#fee2e2;color:#991b1b;padding:1rem;font-family:monospace;margin:1rem;border-radius:6px;'>";
                echo "<b>Fatal Error:</b> " . htmlspecialchars($error['message']) . "<br>";
                echo "in <b>" . htmlspecialchars($error['file']) . "</b> on line <b>" . $error['line'] . "</b>";
                echo "</div>";
            } else {
                self::renderProd();
            }
        }
    }

    // ─── Error handler (non-fatal) ────────────────────────────────────────────
    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool {
        $entry = date('Y-m-d H:i:s') . " [PHP E#{$errno}] {$errstr} in {$errfile}:{$errline}\n";
        self::writeLog($entry);
        return false; // let PHP continue
    }

    // ─── Page d'erreur production (aucune info sensible) ─────────────────────
    private static function renderProd(): void {
        // Éviter double-output si le layout est déjà commencé
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        $view = ROOT_PATH . '/app/Views/errors/500.php';
        if (file_exists($view)) {
            include $view;
        } else {
            echo self::minimalErrorPage();
        }
        exit;
    }

    // ─── Page d'erreur développement (stack trace complète) ──────────────────
    private static function renderDev(\Throwable $e): void {
        if (ob_get_level() > 0) ob_end_clean();

        echo "<div style='background:#0f172a;color:#e2e8f0;padding:1.5rem;font-family:monospace;margin:0;min-height:100vh;'>";
        echo "<div style='max-width:900px;margin:0 auto;'>";
        echo "<h2 style='color:#f87171;margin-top:0;'>" . get_class($e) . "</h2>";
        echo "<p style='color:#fbbf24;font-size:1.1rem;'>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p style='color:#94a3b8;'>in <strong>" . htmlspecialchars($e->getFile()) . "</strong> on line <strong>" . $e->getLine() . "</strong></p>";
        echo "<hr style='border-color:#1e293b;'>";
        echo "<pre style='color:#cbd5e1;overflow:auto;background:#1e293b;padding:1rem;border-radius:8px;'>";
        echo htmlspecialchars($e->getTraceAsString());
        echo "</pre>";
        echo "</div></div>";
        exit;
    }

    // ─── Minimal fallback sans dépendances ───────────────────────────────────
    private static function minimalErrorPage(): string {
        $siteName = defined('APP_NAME') ? APP_NAME : 'Digitalium Group';
        return '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Service temporairement indisponible</title>
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:system-ui,sans-serif;background:#f1f5f9;display:flex;align-items:center;justify-content:center;min-height:100vh}
.card{background:#fff;border-radius:16px;padding:3rem;text-align:center;box-shadow:0 4px 24px rgba(0,0,0,.08);max-width:480px;width:90%}
h1{font-size:4rem;color:#2563eb;line-height:1}.title{font-size:1.4rem;font-weight:700;color:#1e293b;margin:.75rem 0}.sub{color:#64748b;line-height:1.6}
.btn{display:inline-block;margin-top:1.5rem;padding:.7rem 1.5rem;background:#2563eb;color:#fff;text-decoration:none;border-radius:8px;font-weight:600}
</style></head><body>
<div class="card">
<div style="font-size:3rem">⚡</div>
<h2 class="title">Service temporairement indisponible</h2>
<p class="sub">Nous travaillons à rétablir le service. Merci de réessayer dans quelques instants.</p>
<a href="/" class="btn">Réessayer</a>
</div></body></html>';
    }

    // ─── Log helper ──────────────────────────────────────────────────────────
    public static function logException(\Throwable $e): void {
        $entry = date('Y-m-d H:i:s')
            . ' [EXCEPTION] ' . get_class($e)
            . ': ' . $e->getMessage()
            . ' in ' . $e->getFile() . ':' . $e->getLine()
            . "\n" . $e->getTraceAsString()
            . "\n---\n";
        self::writeLog($entry);
    }

    public static function logError(string $context, string $message, string $sql = ''): void {
        $entry = date('Y-m-d H:i:s')
            . " [ERROR] [{$context}] {$message}"
            . ($sql ? " | SQL: {$sql}" : '')
            . "\n";
        self::writeLog($entry);
    }

    private static function writeLog(string $entry): void {
        $path = self::$logPath ?: ROOT_PATH . '/storage/logs/errors.log';
        @file_put_contents($path, $entry, FILE_APPEND | LOCK_EX);
    }
}
