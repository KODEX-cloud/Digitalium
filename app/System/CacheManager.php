<?php
namespace App\System;

use App\Services\Cache;

class CacheManager {
    private static string $cacheDir = '';

    private static function dir(): string {
        if (self::$cacheDir === '') {
            self::$cacheDir = ROOT_PATH . '/storage/cache';
        }
        return self::$cacheDir;
    }

    /**
     * Clear all cached files.
     */
    public static function clear(): array {
        $t     = DSMResult::timer();
        $files = glob(self::dir() . '/*.cache') ?: [];
        $count = 0;
        $errors = [];

        foreach ($files as $f) {
            if (unlink($f)) {
                $count++;
            } else {
                $errors[] = "Impossible de supprimer: " . basename($f);
            }
        }

        $failed = count($files) - $count;
        $msg    = "Cache vidé — {$count} fichier(s) supprimé(s)";
        if ($failed > 0) $msg .= " — {$failed} échec(s)";

        $status = $failed > 0 ? 'warning' : 'ok';
        return DSMResult::make($status, 'Cache Clear', $msg, ['cleared' => $count, 'failed' => $failed], $errors, DSMResult::elapsed($t));
    }

    /**
     * Return cache statistics without modifying anything.
     */
    public static function stats(): array {
        $t     = DSMResult::timer();
        $files = glob(self::dir() . '/*.cache') ?: [];
        $size  = array_sum(array_map('filesize', $files));

        $keys = array_map(fn($f) => basename($f, '.cache'), $files);

        return DSMResult::ok('Cache Stats',
            count($files) . " fichiers en cache (" . self::formatBytes($size) . ")",
            ['count' => count($files), 'size_bytes' => $size, 'keys' => $keys],
            DSMResult::elapsed($t)
        );
    }

    /**
     * Warm the cache by making HTTP requests to key public URLs.
     */
    public static function warmup(string $baseUrl = ''): array {
        $t = DSMResult::timer();

        if (empty($baseUrl)) {
            $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseUrl = $scheme . '://' . $host;
        }

        // Préchauffer une redirection ne met rien en cache d'utile : ce sont les
        // pages de destination qu'il faut réchauffer.
        $urls = ['/', '/insights', '/solutions', '/realisations'];
        $warmed  = 0;
        $errors  = [];

        foreach ($urls as $path) {
            $url = $baseUrl . $path;
            $ctx = stream_context_create(['http' => ['timeout' => 8, 'follow_location' => true, 'max_redirects' => 5]]);
            $r   = @file_get_contents($url, false, $ctx);
            if ($r !== false) {
                $warmed++;
            } else {
                $errors[] = "Warm failed: {$url}";
            }
        }

        $status = ($warmed === count($urls)) ? 'ok' : 'warning';
        return DSMResult::make($status, 'Cache Warmup',
            "Cache préchauffé — {$warmed}/" . count($urls) . " URLs",
            ['warmed' => $warmed, 'urls' => $urls],
            $errors,
            DSMResult::elapsed($t)
        );
    }

    private static function formatBytes(int $bytes): string {
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 2)    . ' KB';
        return $bytes . ' B';
    }
}
