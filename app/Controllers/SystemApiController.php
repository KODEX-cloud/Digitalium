<?php
namespace App\Controllers;

use App\System\DeployPipeline;
use App\System\HealthManager;
use App\System\CacheManager;
use App\System\MigrationManager;
use App\System\BusinessMigrationManager;
use App\System\IntegrityManager;
use App\System\AuditManager;
use App\System\SEOManager;
use App\System\AssetManager;
use App\System\MediaAuditManager;
use App\System\BackupManager;
use App\System\ReportManager;
use App\System\SelfHealManager;
use App\System\GitManager;
use App\System\PerformanceManager;
use App\System\RouteManager;
use App\System\ProjectStateManager;
use App\System\DSMResult;
use App\System\DeploymentLog;
use App\System\RollbackManager;

/**
 * SystemApiController — API JSON interne du DSM.
 *
 * Routes : /admin/api/system/*
 * Toutes les réponses : JSON uniquement.
 * Auth Admin + CSRF obligatoires.
 * Compatible : HTTP, CLI, Webhook, GitHub Actions, Cron, SSH.
 */
class SystemApiController extends Controller {

    protected function boot(): void {
        $this->requireAuth();
        $this->requireCsrf();
        $this->logRequest();
    }

    // ─── DEPLOY — POST /admin/api/system/deploy ──────────────────────────────
    public function deploy(): void {
        $this->boot();

        $mode    = $this->sanitizeMode($_POST['mode'] ?? 'full');
        $baseUrl = $this->detectBaseUrl();

        $result = DeployPipeline::run($mode, ['base_url' => $baseUrl]);

        // Enregistrer dans le log de déploiements
        try {
            $user = \App\Services\Auth::user();
            DeploymentLog::record([
                'mode'       => $mode,
                'status'     => $result['status'] ?? 'unknown',
                'summary'    => $result['message'] ?? '',
                'steps'      => $result['steps'] ?? [],
                'duration_ms'=> $result['duration_ms'] ?? null,
                'actor'      => $user['username'] ?? 'admin',
                'base_url'   => $baseUrl,
                'commit'     => GitManager::getInfo()['commit'] ?? null,
            ]);
        } catch (\Throwable $e) {
            // non-bloquant
        }

        // Auto-update PROJECT_STATE après deploy
        try {
            ProjectStateManager::update($result['steps'] ?? [], 'v' . date('Y.m.d'));
        } catch (\Throwable $e) {
            // non-bloquant
        }

        $this->json($result);
    }

    // ─── MIGRATE SQL — POST /admin/api/system/migrate ───────────────────────
    public function migrate(): void {
        $this->boot();
        $this->json(MigrationManager::run());
    }

    // ─── CACHE — POST /admin/api/system/cache ───────────────────────────────
    public function cache(): void {
        $this->boot();
        $action  = trim($_POST['action'] ?? 'clear');
        $baseUrl = $this->detectBaseUrl();

        $result = match ($action) {
            'warm'  => CacheManager::warmup($baseUrl),
            'stats' => CacheManager::stats(),
            default => CacheManager::clear(),
        };

        $this->json($result);
    }

    // ─── REPAIR — POST /admin/api/system/repair ──────────────────────────────
    public function repair(): void {
        $this->boot();
        $this->json(SelfHealManager::run());
    }

    // ─── HEALTH — POST /admin/api/system/health ──────────────────────────────
    public function health(): void {
        $this->boot();

        $health  = HealthManager::check();
        $score   = HealthManager::score($health);
        $git     = GitManager::healthCheck();

        // Ajouter le check Git au tableau
        $health['git'] = $git;

        $this->json([
            'status'  => $score >= 8 ? 'ok' : ($score >= 5 ? 'warning' : 'error'),
            'label'   => 'Health Check',
            'message' => "Score: {$score}/10",
            'score'   => $score,
            'checks'  => $health,
            'git'     => $git,
        ]);
    }

    // ─── AUDIT — POST /admin/api/system/audit ────────────────────────────────
    public function audit(): void {
        $this->boot();

        $security  = AuditManager::run();
        $seo       = SEOManager::audit();
        $integrity = IntegrityManager::verify();
        $routes    = RouteManager::scan();

        $allResults = [$security, $seo, $integrity, $routes];
        $hasError   = count(array_filter($allResults, fn($r) => $r['status'] === 'error')) > 0;
        $hasWarn    = count(array_filter($allResults, fn($r) => $r['status'] === 'warning')) > 0;

        $this->json([
            'status'    => $hasError ? 'error' : ($hasWarn ? 'warning' : 'ok'),
            'label'     => 'Audit Complet',
            'message'   => 'Sécurité + SEO + Intégrité + Routes',
            'security'  => $security,
            'seo'       => $seo,
            'integrity' => $integrity,
            'routes'    => $routes,
        ]);
    }

    // ─── ROLLBACK — POST /admin/api/system/rollback ──────────────────────────
    public function rollback(): void {
        $this->boot();

        $backups = BackupManager::list();
        $list    = $backups['data']['backups'] ?? [];

        if (empty($list)) {
            $this->json(DSMResult::error('Rollback', 'Aucun backup disponible'));
        }

        // Sécurité : on ne restore pas automatiquement.
        // On retourne la liste des backups disponibles pour action manuelle.
        $this->json([
            'status'  => 'warning',
            'label'   => 'Rollback',
            'message' => count($list) . ' backup(s) disponible(s) — restore manuel requis pour éviter la perte de données',
            'data'    => ['backups' => $list],
        ]);
    }

    // ─── ROLLBACK LATEST — POST /admin/api/system/rollback-latest ───────────
    public function rollbackLatest(): void {
        $this->boot();

        $id = RollbackManager::getLatestId();
        if (!$id) {
            $this->json(DSMResult::error('Rollback', 'Aucun point de rollback disponible — lancez d\'abord un deploy'));
            return;
        }

        $result = RollbackManager::restore($id);
        $this->json($result);
    }

    // ─── DEPLOY LOG LIST — GET /admin/api/system/deploy-log ─────────────────
    public function deployLog(): void {
        $this->requireAuth();
        $limit = min(50, (int)($_GET['limit'] ?? 20));
        $logs  = DeploymentLog::getAll($limit);
        $this->json(['status' => 'ok', 'label' => 'Deploy Log', 'data' => $logs]);
    }

    // ─── ERROR LOG — GET /admin/api/system/error-log ─────────────────────────
    public function errorLog(): void {
        $this->requireAuth();
        $logPath = ROOT_PATH . '/storage/logs/errors.log';
        if (!file_exists($logPath)) {
            $this->json(['status' => 'ok', 'lines' => [], 'size' => 0]);
            return;
        }
        $limit   = min(100, (int)($_GET['limit'] ?? 50));
        $content = file_get_contents($logPath);
        $lines   = array_filter(explode("\n", trim($content)));
        $lines   = array_values(array_slice($lines, -$limit));
        $this->json(['status' => 'ok', 'size' => filesize($logPath), 'count' => count($lines), 'lines' => $lines]);
    }

    // ─── GIT INFO — POST /admin/api/system/git ───────────────────────────────
    public function git(): void {
        $this->boot();
        $this->json(GitManager::getInfo());
    }

    // ─── PERFORMANCE — POST /admin/api/system/performance ───────────────────
    public function performance(): void {
        $this->boot();
        $this->json(PerformanceManager::runTests($this->detectBaseUrl()));
    }

    // ─── BACKUP — POST /admin/api/system/backup ──────────────────────────────
    public function backup(): void {
        $this->boot();
        $this->json(BackupManager::create());
    }

    // ─── STATUS — POST /admin/api/system/status ──────────────────────────────
    public function status(): void {
        $this->boot();

        $health = HealthManager::check();
        $score  = HealthManager::score($health);
        $git    = GitManager::getInfo();
        $state  = ProjectStateManager::read();

        $this->json([
            'status'      => $score >= 8 ? 'ok' : ($score >= 5 ? 'warning' : 'error'),
            'label'       => 'System Status',
            'score'       => $score,
            'health'      => $health,
            'git'         => $git,
            'state'       => $state,
            'php_version' => PHP_VERSION,
            'environment' => ENVIRONMENT,
            'timestamp'   => date('Y-m-d H:i:s'),
        ]);
    }

    // ─── MODES LIST — GET /admin/api/system/modes ────────────────────────────
    public function modes(): void {
        $this->requireAuth();
        // GET endpoint — pas de CSRF requis (lecture seule)
        $modes = [];
        foreach (DeployPipeline::$modes as $key => $def) {
            $modes[] = [
                'key'         => $key,
                'label'       => $def['label'],
                'description' => $def['description'],
                'steps'       => $def['steps'],
                'step_count'  => count($def['steps']),
            ];
        }
        $this->json(['modes' => $modes]);
    }

    // ─── SELF-HEAL — POST /admin/api/system/heal ─────────────────────────────
    public function heal(): void {
        $this->boot();
        $this->json(SelfHealManager::run());
    }

    // ─── Helpers privés ──────────────────────────────────────────────────────
    private function requireAuth(): void {
        if (!\App\Services\Auth::check()) {
            http_response_code(401);
            $this->json(['status' => 'error', 'message' => 'Non authentifié — accès refusé']);
            exit;
        }
    }

    private function requireCsrf(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        if (!$this->validateCsrf()) {
            http_response_code(403);
            $this->json(['status' => 'error', 'message' => 'CSRF invalide — requête rejetée']);
            exit;
        }
    }

    private function logRequest(): void {
        $user  = \App\Services\Auth::user();
        $login = $user['username'] ?? 'unknown';
        $route = $_SERVER['REQUEST_URI'] ?? '';
        $mode  = $_POST['mode'] ?? '';

        $entry = date('Y-m-d H:i:s') . " [DSM API] {$_SERVER['REQUEST_METHOD']} {$route} — user:{$login} mode:{$mode}" . PHP_EOL;
        @file_put_contents(ROOT_PATH . '/storage/logs/dsm.log', $entry, FILE_APPEND | LOCK_EX);
    }

    private function detectBaseUrl(): string {
        $s = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $s . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }

    private function sanitizeMode(string $mode): string {
        return array_key_exists($mode, DeployPipeline::$modes) ? $mode : 'full';
    }
}
