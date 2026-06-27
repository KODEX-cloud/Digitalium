<?php
namespace App\System;

use App\Services\Database;

class HealthManager {
    /**
     * Run all health checks and return a complete status map.
     */
    public static function check(): array {
        return [
            'database'   => self::checkDatabase(),
            'cache'      => self::checkCache(),
            'uploads'    => self::checkUploads(),
            'config'     => self::checkConfig(),
            'logs'       => self::checkLogs(),
            'php'        => self::checkPHP(),
            'disk'       => self::checkDisk(),
            'memory'     => self::checkMemory(),
            'storage'    => self::checkStorage(),
        ];
    }

    public static function checkDatabase(): array {
        $t = DSMResult::timer();
        try {
            Database::query("SELECT 1");
            $tables = Database::fetchAll("SHOW TABLES");
            $count  = count($tables);

            // Count rows in key tables
            $pages    = Database::fetch("SELECT COUNT(*) as n FROM pages")['n']   ?? 0;
            $posts    = Database::fetch("SELECT COUNT(*) as n FROM blog_posts")['n'] ?? 0;
            $settings = Database::fetch("SELECT COUNT(*) as n FROM settings")['n'] ?? 0;

            return DSMResult::ok('Base de données',
                "Connectée — {$count} tables — {$pages} pages, {$posts} articles, {$settings} settings",
                ['tables' => $count, 'pages' => $pages, 'posts' => $posts, 'settings' => $settings],
                DSMResult::elapsed($t)
            );
        } catch (\Exception $e) {
            return DSMResult::error('Base de données', 'Connexion échouée', [$e->getMessage()], [], DSMResult::elapsed($t));
        }
    }

    public static function checkCache(): array {
        $t        = DSMResult::timer();
        $cacheDir = ROOT_PATH . '/storage/cache';

        if (!is_dir($cacheDir)) {
            return DSMResult::warning('Cache', 'Répertoire cache absent — sera créé à la première écriture', [], [], DSMResult::elapsed($t));
        }

        $writable = is_writable($cacheDir);
        $files    = glob($cacheDir . '/*.cache') ?: [];
        $size     = array_sum(array_map('filesize', $files));

        if (!$writable) {
            return DSMResult::error('Cache', 'Répertoire cache non accessible en écriture', ['path' => $cacheDir], [], DSMResult::elapsed($t));
        }

        $env = defined('ENVIRONMENT') ? ENVIRONMENT : 'unknown';
        $msg = ($env === 'production')
            ? "Cache actif — " . count($files) . " fichiers (" . self::formatBytes($size) . ")"
            : "Cache désactivé (dev) — " . count($files) . " fichiers présents";

        $status = ($env === 'production') ? 'ok' : 'warning';

        return DSMResult::make($status, 'Cache', $msg,
            ['files' => count($files), 'size_bytes' => $size, 'env' => $env, 'writable' => true],
            [],
            DSMResult::elapsed($t)
        );
    }

    public static function checkUploads(): array {
        $t = DSMResult::timer();

        if (!is_dir(UPLOAD_PATH)) {
            @mkdir(UPLOAD_PATH, 0755, true);
        }

        if (!is_dir(UPLOAD_PATH)) {
            return DSMResult::error('Uploads', 'Répertoire upload introuvable', [UPLOAD_PATH], [], DSMResult::elapsed($t));
        }

        $writable = is_writable(UPLOAD_PATH);
        $files    = glob(UPLOAD_PATH . '/*') ?: [];
        $images   = array_filter($files, fn($f) => preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $f));
        $size     = array_sum(array_map(fn($f) => is_file($f) ? filesize($f) : 0, $files));

        if (!$writable) {
            return DSMResult::error('Uploads', 'Répertoire upload non accessible en écriture', [UPLOAD_PATH], [], DSMResult::elapsed($t));
        }

        return DSMResult::ok('Uploads',
            count($files) . " fichiers (" . self::formatBytes($size) . ") — " . count($images) . " images",
            ['total' => count($files), 'images' => count($images), 'size_bytes' => $size, 'writable' => true],
            DSMResult::elapsed($t)
        );
    }

    public static function checkConfig(): array {
        $t      = DSMResult::timer();
        $envFile = ROOT_PATH . '/.env';
        $errors  = [];

        if (!file_exists($envFile)) {
            $errors[] = '.env absent — utilise les valeurs par défaut';
            return DSMResult::warning('Configuration', '.env absent — valeurs par défaut actives', [], $errors, DSMResult::elapsed($t));
        }

        $env = defined('ENVIRONMENT') ? ENVIRONMENT : 'unknown';
        $dbOk = (DB_HOST && DB_NAME && DB_USER !== null);

        if (!$dbOk) {
            return DSMResult::error('Configuration', 'DB credentials incomplets dans .env', $errors, [], DSMResult::elapsed($t));
        }

        return DSMResult::ok('Configuration',
            ".env présent — ENV: {$env} — DB: " . DB_NAME . "@" . DB_HOST,
            ['env' => $env, 'db_host' => DB_HOST, 'db_name' => DB_NAME],
            DSMResult::elapsed($t)
        );
    }

    public static function checkLogs(): array {
        $t      = DSMResult::timer();
        $logDir = ROOT_PATH . '/storage/logs';

        if (!is_dir($logDir) || !is_writable($logDir)) {
            return DSMResult::warning('Logs', 'Répertoire logs non accessible en écriture', [], [], DSMResult::elapsed($t));
        }

        $logFiles = glob($logDir . '/*.log') ?: [];
        $totalSize = 0;
        $warnings  = [];

        foreach ($logFiles as $lf) {
            $size = filesize($lf);
            $totalSize += $size;
            if ($size > 5 * 1024 * 1024) { // 5MB
                $warnings[] = basename($lf) . ' dépasse 5MB (' . self::formatBytes($size) . ')';
            }
        }

        $status = empty($warnings) ? 'ok' : 'warning';
        $msg    = count($logFiles) . " fichiers log (" . self::formatBytes($totalSize) . ")";
        if (!empty($warnings)) $msg .= ' — ' . implode(', ', $warnings);

        return DSMResult::make($status, 'Logs', $msg,
            ['files' => count($logFiles), 'size_bytes' => $totalSize],
            $warnings,
            DSMResult::elapsed($t)
        );
    }

    public static function checkPHP(): array {
        $t        = DSMResult::timer();
        $required = ['pdo_mysql', 'fileinfo', 'gd', 'mbstring', 'curl', 'json'];
        $missing  = array_filter($required, fn($ext) => !extension_loaded($ext));

        $version = PHP_VERSION;
        $minVersion = '8.0.0';

        if (!empty($missing)) {
            return DSMResult::error('PHP', "Extensions manquantes : " . implode(', ', $missing),
                array_values($missing), ['version' => $version], DSMResult::elapsed($t)
            );
        }

        if (version_compare($version, $minVersion, '<')) {
            return DSMResult::warning('PHP', "PHP {$version} — Minimum recommandé : {$minVersion}", [], [], DSMResult::elapsed($t));
        }

        return DSMResult::ok('PHP', "PHP {$version} — Toutes les extensions requises présentes",
            ['version' => $version, 'extensions' => $required],
            DSMResult::elapsed($t)
        );
    }

    public static function checkDisk(): array {
        $t     = DSMResult::timer();
        $free  = disk_free_space(ROOT_PATH);
        $total = disk_total_space(ROOT_PATH);

        if ($free === false || $total === false) {
            return DSMResult::warning('Disque', 'Impossible de lire l\'espace disque', [], [], DSMResult::elapsed($t));
        }

        $pctFree = round($free / $total * 100);
        $status  = $pctFree < 10 ? 'error' : ($pctFree < 20 ? 'warning' : 'ok');

        return DSMResult::make($status, 'Disque',
            "Libre: " . self::formatBytes($free) . " / " . self::formatBytes($total) . " ({$pctFree}% libre)",
            ['free_bytes' => $free, 'total_bytes' => $total, 'pct_free' => $pctFree],
            [],
            DSMResult::elapsed($t)
        );
    }

    public static function checkMemory(): array {
        $t       = DSMResult::timer();
        $used    = memory_get_usage(true);
        $peak    = memory_get_peak_usage(true);
        $limit   = ini_get('memory_limit');
        $limitBytes = self::parseBytes($limit);

        $pctUsed = $limitBytes > 0 ? round($peak / $limitBytes * 100) : 0;
        $status  = $pctUsed > 90 ? 'error' : ($pctUsed > 70 ? 'warning' : 'ok');

        return DSMResult::make($status, 'Mémoire PHP',
            "Actuelle: " . self::formatBytes($used) . " | Peak: " . self::formatBytes($peak) . " / Limit: {$limit} ({$pctUsed}%)",
            ['current_bytes' => $used, 'peak_bytes' => $peak, 'limit' => $limit],
            [],
            DSMResult::elapsed($t)
        );
    }

    public static function checkStorage(): array {
        $t   = DSMResult::timer();
        $dirs = [
            'cache'    => ROOT_PATH . '/storage/cache',
            'logs'     => ROOT_PATH . '/storage/logs',
            'reports'  => ROOT_PATH . '/storage/reports',
            'backups'  => ROOT_PATH . '/storage/backups',
            'sessions' => ROOT_PATH . '/storage/sessions',
        ];

        $errors = [];
        $ok     = [];

        foreach ($dirs as $name => $path) {
            if (!is_dir($path)) {
                @mkdir($path, 0755, true);
            }
            if (is_dir($path) && is_writable($path)) {
                $ok[] = $name;
            } else {
                $errors[] = "$name non accessible";
            }
        }

        if (!empty($errors)) {
            return DSMResult::warning('Storage', 'Certains répertoires inaccessibles', ['ok' => $ok], $errors, DSMResult::elapsed($t));
        }

        return DSMResult::ok('Storage', count($ok) . " répertoires accessibles: " . implode(', ', $ok), ['dirs' => $ok], DSMResult::elapsed($t));
    }

    /**
     * Compute an overall health score (0-100) from check results.
     */
    public static function score(array $checks): int {
        $total  = count($checks);
        $points = 0;
        foreach ($checks as $check) {
            $points += match($check['status'] ?? 'error') {
                'ok'      => 10,
                'warning' => 5,
                default   => 0,
            };
        }
        return $total > 0 ? (int)round($points / $total) : 0;
    }

    private static function formatBytes(int $bytes): string {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)    return round($bytes / 1048576, 2)    . ' MB';
        if ($bytes >= 1024)       return round($bytes / 1024, 2)       . ' KB';
        return $bytes . ' B';
    }

    private static function parseBytes(string $val): int {
        $val  = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        $num  = (int)$val;
        return match($last) {
            'g' => $num * 1024 * 1024 * 1024,
            'm' => $num * 1024 * 1024,
            'k' => $num * 1024,
            default => $num,
        };
    }
}
