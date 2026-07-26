<?php
namespace App\Controllers;

use App\System\SyncProductionManager;
use App\System\HealthManager;
use App\System\CacheManager;
use App\System\RouteManager;
use App\System\DSMResult;
use App\Services\BootCheck;
use App\Models\Setting;
use App\Models\Menu;

/**
 * SyncProductionController — Diagnostic et synchronisation base de production.
 *
 * GET  /admin/system/sync-production  → dashboard diagnostic
 * POST /admin/api/system/sync-db      → exécution migration + rapport JSON
 */
class SyncProductionController extends Controller {

    protected function middlewareAuth(): void {
        if (!\App\Services\Auth::check()) {
            $this->redirect('/admin/login', 'error', 'Accès réservé aux administrateurs.');
        }
    }

    // ─── GET /admin/system/sync-production ───────────────────────────────────
    public function index(): void {
        $this->middlewareAuth();

        $diff    = SyncProductionManager::diff();
        $inspect = SyncProductionManager::inspect();
        $boot    = BootCheck::run();
        $health  = [];
        try { $health = HealthManager::check(); } catch (\Throwable $e) { $health = []; }
        $settings = Setting::getAll() ?: [];

        // Récupération tables locales attendues (toutes dans CLAUDE.md §7)
        $expectedTables = [
            'users','pages','sections','blocks','settings','media','hero_slides',
            'projects','blog_posts','blog_categories','blog_tags','blog_post_tags',
            'blog_comments','contact_messages','menus','menu_items',
        ];

        $this->render('admin/system/sync_production', [
            'title'          => 'Sync Production — Diagnostic DB',
            'currentUser'    => \App\Services\Auth::user(),
            'csrf_token'     => \App\Services\CSRF::getToken(),
            'diff'           => $diff,
            'inspect'        => $inspect,
            'expectedTables' => $expectedTables,
            'boot'           => $boot,
            'health'         => $health,
            'settings'       => $settings,
        ], 'admin/layout');
    }

    // ─── POST /admin/api/system/sync-db ──────────────────────────────────────
    public function runSync(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $t       = DSMResult::timer();
        $report  = [];

        // 1. Migration DB
        $syncResult = SyncProductionManager::run();
        $report['db_sync'] = $syncResult;

        // 2. Cache clear
        try {
            $cache = CacheManager::clear();
            $report['cache'] = $cache;
        } catch (\Throwable $e) {
            $report['cache'] = DSMResult::error('Cache Clear', $e->getMessage());
        }

        // 3. BootCheck
        try {
            $boot = BootCheck::run();
            $report['boot_check'] = DSMResult::make(
                $boot['ok'] ? 'ok' : ($boot['critical'] ? 'error' : 'warning'),
                'Boot Check',
                $boot['summary'],
                $boot['checks']
            );
        } catch (\Throwable $e) {
            $report['boot_check'] = DSMResult::error('Boot Check', $e->getMessage());
        }

        // 4. Health check
        try {
            $checks = HealthManager::check();
            $score  = HealthManager::score($checks);
            $report['health'] = DSMResult::make(
                $score >= 8 ? 'ok' : ($score >= 5 ? 'warning' : 'error'),
                'Health Check',
                "Score {$score}/10",
                ['score' => $score, 'checks' => $checks]
            );
        } catch (\Throwable $e) {
            $report['health'] = DSMResult::error('Health Check', $e->getMessage());
        }

        // 5. Routes scan
        try {
            $report['routes'] = RouteManager::scan();
        } catch (\Throwable $e) {
            $report['routes'] = DSMResult::error('Routes Scan', $e->getMessage());
        }

        // 6. HTTP probes (frontend + backend)
        $report['http'] = $this->probeHttp();

        // 7. Settings verification
        $report['settings'] = $this->verifySettings();

        // 8. Uploads check
        $report['uploads'] = $this->verifyUploads();

        // 9. Assets check
        $report['assets']  = $this->verifyAssets();

        // 10. Permissions check
        $report['permissions'] = $this->verifyPermissions();

        // Global status
        $errors  = array_filter($report, fn($r) => ($r['status'] ?? '') === 'error');
        $status  = empty($errors) ? 'ok' : 'error';
        $elapsed = DSMResult::elapsed($t);

        $final = [
            'status'      => $status,
            'label'       => 'Sync Production Complete',
            'message'     => count($errors) === 0
                ? 'Synchronisation réussie — base de production à jour'
                : count($errors) . ' erreur(s) détectée(s)',
            'steps'       => $report,
            'duration_ms' => round($elapsed, 2),
            'timestamp'   => date('Y-m-d H:i:s'),
            'incident_root_cause' => [
                'sql'     => 'SELECT * FROM menus WHERE location = :l LIMIT 1',
                'file'    => 'app/Models/Menu.php:14',
                'layout'  => 'app/Views/frontend/layout.php:313',
                'column'  => 'menus.location',
                'fix'     => 'ALTER TABLE menus ADD COLUMN location VARCHAR(50) DEFAULT primary',
            ],
        ];

        // Log
        $logEntry = date('Y-m-d H:i:s') . " [SYNC_PRODUCTION] status={$status} duration={$elapsed}ms\n";
        @file_put_contents(ROOT_PATH . '/storage/logs/errors.log', $logEntry, FILE_APPEND | LOCK_EX);

        $this->json($final, $status === 'ok' ? 200 : 500);
    }

    // ─── Vérifications autonomes ──────────────────────────────────────────────

    private function probeHttp(): array {
        $t = DSMResult::timer();
        $baseUrl = $this->detectBaseUrl();
        $probes  = [];
        $urls    = [
            'frontend'   => $baseUrl . '/',
            'backend'    => $baseUrl . '/admin',
            'blog'       => $baseUrl . '/blog',
            'sitemap'    => $baseUrl . '/sitemap.xml',
        ];

        foreach ($urls as $name => $url) {
            try {
                $ctx  = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
                $resp = @get_headers($url, true, $ctx);
                $code = $resp ? (int)substr($resp[0], 9, 3) : 0;
                $probes[$name] = [
                    'url'    => $url,
                    'code'   => $code,
                    'status' => in_array($code, [200, 301, 302]) ? 'ok' : 'error',
                ];
            } catch (\Throwable $e) {
                $probes[$name] = ['url' => $url, 'code' => 0, 'status' => 'error', 'error' => $e->getMessage()];
            }
        }

        $errors = array_filter($probes, fn($p) => $p['status'] !== 'ok');
        $status = empty($errors) ? 'ok' : 'warning';

        return DSMResult::make($status, 'HTTP Probes', count($errors) . ' probe(s) en erreur / ' . count($probes), $probes, [], DSMResult::elapsed($t));
    }

    private function verifySettings(): array {
        $t       = DSMResult::timer();
        $missing = [];
        $required = ['site_name','contact_email','footer_copyright'];
        try {
            $settings = Setting::getAll();
            foreach ($required as $k) {
                if (!isset($settings[$k])) $missing[] = $k;
            }
            $status = empty($missing) ? 'ok' : 'warning';
            return DSMResult::make($status, 'Settings', empty($missing) ? 'Clés requises présentes' : 'Manquantes : ' . implode(', ', $missing), ['missing' => $missing], [], DSMResult::elapsed($t));
        } catch (\Throwable $e) {
            return DSMResult::error('Settings', $e->getMessage(), [], [], DSMResult::elapsed($t));
        }
    }

    private function verifyUploads(): array {
        $t    = DSMResult::timer();
        $path = ROOT_PATH . '/public/uploads';
        $ok   = is_dir($path) && is_writable($path);
        return DSMResult::make($ok ? 'ok' : 'warning', 'Uploads', $ok ? 'Dossier uploads accessible' : 'Dossier uploads manquant ou non accessible en écriture');
    }

    private function verifyAssets(): array {
        $t     = DSMResult::timer();
        $paths = ['/public/assets', '/public/assets/css', '/public/assets/js'];
        $missing = [];
        foreach ($paths as $p) {
            if (!is_dir(ROOT_PATH . $p)) $missing[] = $p;
        }
        $status = empty($missing) ? 'ok' : 'warning';
        return DSMResult::make($status, 'Assets', empty($missing) ? 'Dossiers assets présents' : 'Manquants : ' . implode(', ', $missing));
    }

    private function verifyPermissions(): array {
        $t     = DSMResult::timer();
        $dirs  = ['storage/logs', 'storage/cache', 'public/uploads'];
        $errors = [];
        foreach ($dirs as $d) {
            $path = ROOT_PATH . '/' . $d;
            if (!is_dir($path)) {
                @mkdir($path, 0755, true);
            }
            if (!is_writable($path)) {
                $errors[] = $d;
            }
        }
        $status = empty($errors) ? 'ok' : 'warning';
        return DSMResult::make($status, 'Permissions', empty($errors) ? 'Tous les dossiers accessibles' : 'Non accessibles : ' . implode(', ', $errors));
    }

    private function detectBaseUrl(): string {
        $s = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $s . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }
}
