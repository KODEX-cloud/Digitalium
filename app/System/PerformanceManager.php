<?php
namespace App\System;

/**
 * PerformanceManager — Tests de performance et optimisation.
 *
 * Mesure les temps de réponse, vérifie les tailles de fichiers,
 * et propose des diagnostics.
 */
class PerformanceManager {

    private static array $targets = [
        '/'            => 'Homepage',
        '/blog'        => 'Blog index',
        '/realisations'=> 'Portfolio',
        '/sitemap.xml' => 'Sitemap',
    ];

    // Seuils (ms)
    private const FAST    = 300;
    private const SLOW    = 800;
    private const CRITICAL= 2000;

    // ─── Tests de performance HTTP ───────────────────────────────────────────
    public static function runTests(string $baseUrl = ''): array {
        $t       = DSMResult::timer();
        $results = [];

        if (empty($baseUrl)) {
            $s       = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $baseUrl = $s . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        }

        if (!function_exists('curl_init')) {
            return DSMResult::warning('Performance', 'cURL indisponible — tests ignorés', [], [], DSMResult::elapsed($t));
        }

        foreach (self::$targets as $path => $label) {
            $results[] = self::measureUrl($baseUrl . $path, $label);
        }

        // Score global
        $avgMs  = count($results) > 0
            ? array_sum(array_column($results, 'duration_ms')) / count($results)
            : 0;

        $slowRoutes = array_filter($results, fn($r) => ($r['duration_ms'] ?? 0) > self::SLOW);
        $critRoutes = array_filter($results, fn($r) => ($r['duration_ms'] ?? 0) > self::CRITICAL);

        $status  = count($critRoutes) > 0 ? 'error' : (count($slowRoutes) > 0 ? 'warning' : 'ok');
        $avgRound = round($avgMs);

        return DSMResult::make($status, 'Performance',
            "Avg: {$avgRound}ms — " . count($slowRoutes) . " route(s) lente(s) — " . count($critRoutes) . " critique(s)",
            [
                'routes'      => $results,
                'avg_ms'      => $avgRound,
                'slow_count'  => count($slowRoutes),
                'crit_count'  => count($critRoutes),
                'thresholds'  => ['fast' => self::FAST, 'slow' => self::SLOW, 'critical' => self::CRITICAL],
            ],
            [],
            DSMResult::elapsed($t)
        );
    }

    // ─── Vérification taille assets ──────────────────────────────────────────
    public static function checkAssetSizes(): array {
        $t        = DSMResult::timer();
        $assetDir = ROOT_PATH . '/public/assets';
        $warnings = [];
        $stats    = ['css' => 0, 'js' => 0, 'images' => 0, 'total_kb' => 0];

        if (!is_dir($assetDir)) {
            return DSMResult::warning('Perf:Assets', 'Répertoire assets introuvable', [], [], DSMResult::elapsed($t));
        }

        // CSS
        foreach (glob($assetDir . '/css/*.css') ?: [] as $f) {
            $kb = round(filesize($f) / 1024, 1);
            $stats['css']++;
            $stats['total_kb'] += $kb;
            if ($kb > 200) $warnings[] = basename($f) . " ({$kb} KB) — CSS volumineux";
        }

        // JS
        foreach (glob($assetDir . '/js/*.js') ?: [] as $f) {
            $kb = round(filesize($f) / 1024, 1);
            $stats['js']++;
            $stats['total_kb'] += $kb;
            if ($kb > 500) $warnings[] = basename($f) . " ({$kb} KB) — JS volumineux";
        }

        // Images dans uploads > 2MB
        $uploads = defined('UPLOAD_PATH') ? UPLOAD_PATH : ROOT_PATH . '/public/assets/uploads';
        foreach (glob($uploads . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE) ?: [] as $f) {
            $kb = round(filesize($f) / 1024, 1);
            $stats['images']++;
            $stats['total_kb'] += $kb;
            if ($kb > 2048) $warnings[] = basename($f) . " ({$kb} KB) — image volumineuse";
        }

        $stats['total_kb'] = round($stats['total_kb']);
        $status = count($warnings) > 3 ? 'warning' : 'ok';

        return DSMResult::make($status, 'Perf:Assets',
            "{$stats['total_kb']} KB total — {$stats['css']} CSS, {$stats['js']} JS, {$stats['images']} images" .
            (count($warnings) > 0 ? " — " . count($warnings) . " avertissement(s)" : ""),
            $stats, $warnings, DSMResult::elapsed($t)
        );
    }

    // ─── Rapport complet ─────────────────────────────────────────────────────
    public static function report(string $baseUrl = ''): array {
        $t       = DSMResult::timer();
        $perf    = self::runTests($baseUrl);
        $assets  = self::checkAssetSizes();

        $overallStatus = ($perf['status'] === 'error' || $assets['status'] === 'error') ? 'error'
            : (($perf['status'] === 'warning' || $assets['status'] === 'warning') ? 'warning' : 'ok');

        return DSMResult::make($overallStatus, 'Perf:Report',
            "Perf: {$perf['message']} | Assets: {$assets['message']}",
            ['performance' => $perf, 'assets' => $assets],
            [],
            DSMResult::elapsed($t)
        );
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────
    private static function measureUrl(string $url, string $label): array {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_NOBODY         => false,
            CURLOPT_USERAGENT      => 'DSM-PerformanceTest/1.0',
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $start  = microtime(true);
        curl_exec($ch);
        $ms     = round((microtime(true) - $start) * 1000, 2);
        $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $size   = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
        $error  = curl_error($ch);
        curl_close($ch);

        $status = ($code < 200 || $code >= 400 || !empty($error)) ? 'error'
            : ($ms > self::CRITICAL ? 'error' : ($ms > self::SLOW ? 'warning' : 'ok'));

        return [
            'status'      => $status,
            'label'       => $label,
            'url'         => $url,
            'http_code'   => $code,
            'duration_ms' => $ms,
            'size_kb'     => round($size / 1024, 1),
            'message'     => "{$label}: HTTP {$code} — {$ms}ms — " . round($size / 1024, 1) . "KB",
            'error'       => $error ?: null,
            'timestamp'   => date('Y-m-d H:i:s'),
        ];
    }
}
