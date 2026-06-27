<?php
namespace App\System;

class RouteManager {
    /**
     * Parse routes/web.php, validate each controller+method, return status.
     */
    public static function scan(): array {
        $t    = DSMResult::timer();
        $file = ROOT_PATH . '/routes/web.php';

        if (!file_exists($file)) {
            return DSMResult::error('Routes', 'routes/web.php introuvable', [$file], [], DSMResult::elapsed($t));
        }

        $src  = file_get_contents($file);
        preg_match_all('/["\']([A-Z][a-zA-Z]+Controller)@([a-zA-Z]+)["\']/', $src, $m);

        $routes  = [];
        $dead    = [];
        $checked = [];

        for ($i = 0; $i < count($m[0]); $i++) {
            $ctrl   = $m[1][$i];
            $method = $m[2][$i];
            $key    = $ctrl . '@' . $method;

            if (isset($checked[$key])) continue;
            $checked[$key] = true;

            $ctrlFile = APP_PATH . '/Controllers/' . $ctrl . '.php';
            $exists   = file_exists($ctrlFile);
            $hasMethod = false;

            if ($exists) {
                $ctrlSrc  = file_get_contents($ctrlFile);
                $hasMethod = str_contains($ctrlSrc, 'function ' . $method);
            }

            $status = ($exists && $hasMethod) ? 'ok' : 'dead';
            if ($status === 'dead') {
                $dead[] = $exists ? "Méthode introuvable: {$key}" : "Contrôleur introuvable: {$ctrl}";
            }

            $routes[] = ['controller' => $ctrl, 'method' => $method, 'status' => $status];
        }

        $total   = count($routes);
        $okCount = count(array_filter($routes, fn($r) => $r['status'] === 'ok'));
        $deadCount = count($dead);

        if ($deadCount > 0) {
            return DSMResult::error('Routes', "{$deadCount} route(s) morte(s) sur {$total}", $dead, ['total' => $total, 'ok' => $okCount, 'routes' => $routes], DSMResult::elapsed($t));
        }

        return DSMResult::ok('Routes', "{$okCount}/{$total} routes valides — 0 route morte",
            ['total' => $total, 'ok' => $okCount, 'routes' => $routes],
            DSMResult::elapsed($t)
        );
    }

    /**
     * HTTP connectivity test for key public routes.
     */
    public static function httpTest(string $baseUrl): array {
        $t     = DSMResult::timer();
        $tests = ['/', '/blog', '/realisations', '/sitemap.xml'];
        $results = [];
        $errors  = [];

        foreach ($tests as $path) {
            $url = $baseUrl . $path;
            $ctx = stream_context_create(['http' => ['timeout' => 8, 'follow_location' => true, 'max_redirects' => 5, 'ignore_errors' => true]]);
            $t0  = microtime(true);
            $r   = @file_get_contents($url, false, $ctx);
            $ms  = round((microtime(true) - $t0) * 1000);

            if ($r === false) {
                $results[] = ['url' => $path, 'status' => 'error', 'ms' => $ms];
                $errors[]  = "GET {$path} — pas de réponse";
            } else {
                $code = 200;
                if (isset($http_response_header[0]) && preg_match('/HTTP\/\S+ (\d+)/', $http_response_header[0], $mm)) {
                    $code = (int)$mm[1];
                }
                $ok = ($code >= 200 && $code < 400);
                $results[] = ['url' => $path, 'status' => $ok ? 'ok' : 'error', 'http' => $code, 'ms' => $ms];
                if (!$ok) $errors[] = "GET {$path} → HTTP {$code}";
            }
        }

        $okCount   = count(array_filter($results, fn($r) => $r['status'] === 'ok'));
        $total     = count($results);
        $status    = $okCount === $total ? 'ok' : ($okCount > 0 ? 'warning' : 'error');

        return DSMResult::make($status, 'Tests HTTP',
            "{$okCount}/{$total} routes répondent HTTP 200",
            ['tests' => $results],
            $errors,
            DSMResult::elapsed($t)
        );
    }
}
