<?php
namespace App\Controllers;

use App\System\HealthManager;
use App\System\CacheManager;
use App\System\RouteManager;
use App\System\MigrationManager;
use App\System\BusinessMigrationManager;
use App\System\IntegrityManager;
use App\System\AuditManager;
use App\System\AssetManager;
use App\System\SEOManager;
use App\System\MediaAuditManager;
use App\System\BackupManager;
use App\System\ReportManager;
use App\System\DeployManager;
use App\System\ConfigManager;
use App\System\ProjectStateManager;
use App\System\GitManager;
use App\System\DeployPipeline;
use App\System\PerformanceManager;
use App\System\DeploymentLog;
use App\System\RollbackManager;

class SystemController extends Controller {
    protected function middlewareAuth(): void {
        if (!\App\Services\Auth::check()) {
            $this->redirect('/admin/login', 'error', 'Accès réservé aux administrateurs.');
        }
    }

    // ─────────────────────────────────────────
    // DEPLOY CENTER — GET /admin/system/deploy-center
    // ─────────────────────────────────────────
    public function deployCenter(): void {
        $this->middlewareAuth();

        $health = [];
        try { $health = HealthManager::check(); } catch (\Throwable $e) { $health = []; }
        $score = 0;
        try { $score = HealthManager::score($health); } catch (\Throwable $e) {}
        $state = [];
        try { $state = ProjectStateManager::read(); } catch (\Throwable $e) {}
        $backups = [];
        try { $raw = BackupManager::list(); $backups = $raw['data']['backups'] ?? []; } catch (\Throwable $e) {}
        $git = [];
        try { $git = GitManager::getInfo(); } catch (\Throwable $e) {}
        $deployLogs = [];
        try { $deployLogs = DeploymentLog::getAll(10); } catch (\Throwable $e) {}
        $rollbackIds = [];
        try { $raw2 = RollbackManager::list(); $rollbackIds = $raw2['data'] ?? []; } catch (\Throwable $e) {}

        // Préparer les modes pour la vue
        $modes = [];
        try {
            foreach (DeployPipeline::$modes as $key => $def) {
                $modes[] = [
                    'key'         => $key,
                    'label'       => $def['label'],
                    'description' => $def['description'],
                    'steps'       => $def['steps'],
                    'step_count'  => count($def['steps']),
                ];
            }
        } catch (\Throwable $e) {}

        $this->render('admin/system/deploy_center', [
            'title'        => 'Deploy Center — DSM Enterprise',
            'health'       => $health,
            'score'        => $score,
            'state'        => $state,
            'backups'      => $backups,
            'git'          => $git,
            'modes'        => $modes,
            'current_mode' => 'full',
            'csrf_token'   => $this->generateCsrf(),
            'currentUser'  => \App\Services\Auth::user(),
            'reports'      => [],
            'deploy_logs'  => $deployLogs,
            'rollback_ids' => $rollbackIds,
        ], 'admin/layout');
    }

    // ─────────────────────────────────────────
    // DASHBOARD — GET /admin/system/status
    // ─────────────────────────────────────────
    public function status(): void {
        $this->middlewareAuth();

        $health  = HealthManager::check();
        $score   = HealthManager::score($health);
        $state   = ProjectStateManager::read();
        $reports = ReportManager::list(5);
        $backups = BackupManager::list();
        $routes  = RouteManager::scan();

        $this->render('admin/system/dashboard', [
            'title'       => 'Digitalium System Manager',
            'health'      => $health,
            'score'       => $score,
            'state'       => $state,
            'reports'     => $reports,
            'backups'     => $backups['data']['backups'] ?? [],
            'routes'      => $routes,
            'csrf_token'  => $this->generateCsrf(),
            'currentUser' => \App\Services\Auth::user(),
            'migrations'  => BusinessMigrationManager::list(),
        ], 'admin/layout');
    }

    // ─────────────────────────────────────────
    // DEPLOY — POST /admin/system/deploy
    // ─────────────────────────────────────────
    public function deploy(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl = $scheme . '://' . $host;

        $result = DeployManager::run(['base_url' => $baseUrl]);
        $this->json($result);
    }

    // ─────────────────────────────────────────
    // MIGRATE SQL — POST /admin/system/migrate
    // ─────────────────────────────────────────
    public function migrate(): void {
        $this->middlewareAuth();
        $this->validateCsrf();
        $this->json(MigrationManager::run());
    }

    // ─────────────────────────────────────────
    // BUSINESS MIGRATE — POST /admin/system/business-migrate
    // ─────────────────────────────────────────
    public function businessMigrate(): void {
        $this->middlewareAuth();
        $this->validateCsrf();
        $name = trim($_POST['migration'] ?? '');
        $this->json(BusinessMigrationManager::run($name ?: null));
    }

    // ─────────────────────────────────────────
    // CACHE — POST /admin/system/cache
    // ─────────────────────────────────────────
    public function cache(): void {
        $this->middlewareAuth();
        $this->validateCsrf();
        $action = trim($_POST['action'] ?? 'clear');

        $result = match ($action) {
            'warm'  => CacheManager::warmup(),
            'stats' => CacheManager::stats(),
            default => CacheManager::clear(),
        };

        $this->json($result);
    }

    // ─────────────────────────────────────────
    // HEALTH — GET /admin/system/health
    // ─────────────────────────────────────────
    public function health(): void {
        $this->middlewareAuth();

        if ($this->isAjax()) {
            $health = HealthManager::check();
            $this->json([
                'checks' => $health,
                'score'  => HealthManager::score($health),
            ]);
        }

        $health = HealthManager::check();
        $this->render('admin/system/dashboard', [
            'title'       => 'Health Check — DSM',
            'health'      => $health,
            'score'       => HealthManager::score($health),
            'state'       => ProjectStateManager::read(),
            'reports'     => [],
            'backups'     => [],
            'routes'      => [],
            'csrf_token'  => $this->generateCsrf(),
            'currentUser' => \App\Services\Auth::user(),
            'migrations'  => [],
        ], 'admin/layout');
    }

    // ─────────────────────────────────────────
    // VERIFY — POST /admin/system/verify
    // ─────────────────────────────────────────
    public function verify(): void {
        $this->middlewareAuth();
        $this->validateCsrf();
        $this->json(IntegrityManager::verify());
    }

    // ─────────────────────────────────────────
    // AUDIT — POST /admin/system/audit
    // ─────────────────────────────────────────
    public function audit(): void {
        $this->middlewareAuth();
        $this->validateCsrf();
        $this->json(AuditManager::run());
    }

    // ─────────────────────────────────────────
    // SEO — GET/POST /admin/system/seo
    // ─────────────────────────────────────────
    public function seo(): void {
        $this->middlewareAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
        }
        $this->json(SEOManager::audit());
    }

    // ─────────────────────────────────────────
    // ASSETS — POST /admin/system/assets
    // ─────────────────────────────────────────
    public function assets(): void {
        $this->middlewareAuth();
        $this->validateCsrf();
        $this->json(AssetManager::check());
    }

    // ─────────────────────────────────────────
    // UPLOADS — POST /admin/system/uploads
    // ─────────────────────────────────────────
    public function uploads(): void {
        $this->middlewareAuth();
        $this->validateCsrf();
        $this->json(MediaAuditManager::audit());
    }

    // ─────────────────────────────────────────
    // ROUTES SCAN — POST /admin/system/routes
    // ─────────────────────────────────────────
    public function routes(): void {
        $this->middlewareAuth();
        $this->validateCsrf();
        $this->json(RouteManager::scan());
    }

    // ─────────────────────────────────────────
    // BACKUP — POST /admin/system/backup
    // ─────────────────────────────────────────
    public function backup(): void {
        $this->middlewareAuth();
        $this->validateCsrf();
        $this->json(BackupManager::create());
    }

    // ─────────────────────────────────────────
    // ROLLBACK — POST /admin/system/rollback
    // ─────────────────────────────────────────
    public function rollback(): void {
        $this->middlewareAuth();
        $this->validateCsrf();
        // Rollback = restore last SQL backup
        $backups = BackupManager::list();
        $list    = $backups['data']['backups'] ?? [];

        if (empty($list)) {
            $this->json(['status' => 'error', 'message' => 'Aucun backup disponible pour rollback']);
        }

        $this->json([
            'status'  => 'warning',
            'message' => 'Rollback disponible — ' . count($list) . ' backup(s). Opération manuelle requise pour éviter la perte de données.',
            'data'    => ['backups' => $list],
        ]);
    }

    // ─────────────────────────────────────────
    // REPORT — GET /admin/system/report
    // ─────────────────────────────────────────
    public function report(): void {
        $this->middlewareAuth();
        $this->json(['reports' => ReportManager::list(50)]);
    }

    // ─────────────────────────────────────────
    // REBUILD — POST /admin/system/rebuild
    // ─────────────────────────────────────────
    public function rebuild(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $results = [
            CacheManager::clear(),
            RouteManager::scan(),
            IntegrityManager::verify(),
            AssetManager::check(),
        ];

        $ok   = count(array_filter($results, fn($r) => $r['status'] === 'ok'));
        $this->json([
            'status'  => $ok === count($results) ? 'ok' : 'warning',
            'message' => "Rebuild: {$ok}/" . count($results) . " opérations OK",
            'results' => $results,
        ]);
    }

    // ─────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────
    private function isAjax(): bool {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }
}
