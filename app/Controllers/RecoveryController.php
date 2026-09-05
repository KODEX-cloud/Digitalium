<?php
namespace App\Controllers;

use App\Services\Auth;
use App\Services\BootCheck;
use App\Services\Database;
use App\System\SyncProductionManager;
use App\System\CacheManager;
use App\System\HealthManager;
use App\System\RollbackManager;
use App\System\DeploymentLog;
use App\System\DSMResult;

/**
 * RecoveryController — Recovery Center sans SSH.
 *
 * Routes :
 *   GET  /admin/system/recovery                  → index()
 *   POST /admin/api/system/recovery-run          → run()
 *   GET  /admin/api/system/recovery-diagnostic   → diagnostic()
 *   POST /admin/api/system/recovery-maintenance  → maintenance()
 */
class RecoveryController extends Controller {

    protected function middlewareAuth(): void {
        if (!Auth::check()) {
            $this->redirect('/admin/login', 'error', 'Accès réservé aux administrateurs.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /admin/system/recovery — Dashboard
    // ─────────────────────────────────────────────────────────────────────────
    public function index(): void {
        $this->middlewareAuth();

        $diagnostics = $this->runDiagnostics();
        $maintenance = file_exists(ROOT_PATH . '/storage/maintenance.lock');
        $lastLog     = DeploymentLog::getLatest();
        $rollbacks   = RollbackManager::list();

        // Score global
        $errors   = count(array_filter($diagnostics, fn($d) => ($d['status'] ?? '') === 'error'));
        $warnings = count(array_filter($diagnostics, fn($d) => ($d['status'] ?? '') === 'warning'));
        $oks      = count(array_filter($diagnostics, fn($d) => ($d['status'] ?? '') === 'ok'));
        $total    = count($diagnostics);

        $this->render('admin/system/recovery', [
            'title'        => 'Recovery Center',
            'diagnostics'  => $diagnostics,
            'maintenance'  => $maintenance,
            'lastLog'      => $lastLog,
            'rollbacks'    => $rollbacks['data'] ?? [],
            'errors'       => $errors,
            'warnings'     => $warnings,
            'oks'          => $oks,
            'total'        => $total,
            'csrf_token'   => $this->generateCsrf(),
            'currentUser'  => Auth::user(),
        ], 'admin/layout');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /admin/api/system/recovery-diagnostic — Diagnostics JSON
    // ─────────────────────────────────────────────────────────────────────────
    public function diagnostic(): void {
        $this->middlewareAuth();
        $diagnostics = $this->runDiagnostics();
        $errors      = count(array_filter($diagnostics, fn($d) => ($d['status'] ?? '') === 'error'));
        $this->json([
            'status'      => $errors > 0 ? 'error' : 'ok',
            'label'       => 'Diagnostic',
            'diagnostics' => $diagnostics,
            'maintenance' => file_exists(ROOT_PATH . '/storage/maintenance.lock'),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /admin/api/system/recovery-run — Pipeline complet
    // ─────────────────────────────────────────────────────────────────────────
    public function run(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $steps     = [];
        $hasError  = false;
        $startTime = microtime(true);
        $baseUrl   = $this->detectBaseUrl();

        // ── Étape 1 : BootCheck ──────────────────────────────────────────────
        $t    = microtime(true);
        try {
            $boot = BootCheck::run();
            $bootOk = !($boot['critical'] ?? false);
            $steps[] = DSMResult::make(
                $bootOk ? 'ok' : 'error',
                'BootCheck',
                $boot['summary'] ?? ($bootOk ? '7/7 checks OK' : 'Checks critiques échoués'),
                $boot, $boot['errors'] ?? [], self::ms($t)
            );
            if (!$bootOk) $hasError = true;
        } catch (\Throwable $e) {
            $steps[] = DSMResult::error('BootCheck', $e->getMessage(), [], [], self::ms($t));
            $hasError = true;
        }

        // ── Étape 2 : Backup (point de rollback avant modifications) ─────────
        $t = microtime(true);
        try {
            $backup  = RollbackManager::create();
            $steps[] = $backup;
        } catch (\Throwable $e) {
            $steps[] = DSMResult::warning('Backup', 'Impossible de créer un backup : ' . $e->getMessage(), [], [], self::ms($t));
        }

        // ── Étape 3 : Master Migration ───────────────────────────────────────
        $t = microtime(true);
        try {
            ob_start();
            $masterMig = ROOT_PATH . '/database/master_migration.php';
            if (file_exists($masterMig)) {
                if (!defined('SECURE_ACCESS')) define('SECURE_ACCESS', true);
                include $masterMig;
                ob_end_clean();
                $steps[] = DSMResult::make('ok', 'Master Migration', 'Tables vérifiées / créées', [], [], self::ms($t));
            } else {
                ob_end_clean();
                $steps[] = DSMResult::warning('Master Migration', 'database/master_migration.php introuvable', [], [], self::ms($t));
            }
        } catch (\Throwable $e) {
            @ob_end_clean();
            $steps[] = DSMResult::warning('Master Migration', $e->getMessage(), [], [], self::ms($t));
        }

        // ── Étape 4 : Sync Production ────────────────────────────────────────
        $t = microtime(true);
        try {
            $sync    = SyncProductionManager::run();
            $steps[] = $sync;
            if (($sync['status'] ?? 'ok') === 'error') $hasError = true;
        } catch (\Throwable $e) {
            $steps[] = DSMResult::error('Sync Production', $e->getMessage(), [], [], self::ms($t));
            $hasError = true;
        }

        // ── Étape 5 : Cache Clear ────────────────────────────────────────────
        $t = microtime(true);
        try {
            $cache   = CacheManager::clear();
            $steps[] = $cache;
        } catch (\Throwable $e) {
            $steps[] = DSMResult::warning('Cache Clear', $e->getMessage(), [], [], self::ms($t));
        }

        // ── Étape 6 : Asset Verify ───────────────────────────────────────────
        $t = microtime(true);
        $assetChecks = $this->verifyAssets();
        $assetErrors = array_filter($assetChecks, fn($a) => $a['ok'] === false);
        $steps[] = DSMResult::make(
            empty($assetErrors) ? 'ok' : 'warning',
            'Asset Verify',
            count($assetChecks) . ' assets vérifiés, ' . count($assetErrors) . ' manquant(s)',
            $assetChecks, [], self::ms($t)
        );

        // ── Étape 7 : Upload Verify ──────────────────────────────────────────
        $t = microtime(true);
        $uploadsDir = ROOT_PATH . '/public/uploads';
        if (!is_dir($uploadsDir)) { @mkdir($uploadsDir, 0755, true); }
        $uploadOk      = is_dir($uploadsDir) && is_writable($uploadsDir);
        $uploadCount   = count(glob($uploadsDir . '/*') ?: []);
        $steps[] = DSMResult::make(
            $uploadOk ? 'ok' : 'warning',
            'Upload Verify',
            $uploadOk ? "{$uploadCount} fichier(s) — accessible en écriture" : 'Répertoire non accessible en écriture',
            ['writable' => $uploadOk, 'count' => $uploadCount], [], self::ms($t)
        );

        // ── Étape 8 : Menu Rebuild ───────────────────────────────────────────
        $t = microtime(true);
        try {
            $menuResult = $this->rebuildMenus();
            $steps[]    = $menuResult;
        } catch (\Throwable $e) {
            $steps[] = DSMResult::warning('Menu Rebuild', $e->getMessage(), [], [], self::ms($t));
        }

        // ── Étape 9 : Settings Sync ──────────────────────────────────────────
        $t = microtime(true);
        try {
            $settingsResult = $this->syncSettings();
            $steps[]        = $settingsResult;
        } catch (\Throwable $e) {
            $steps[] = DSMResult::warning('Settings Sync', $e->getMessage(), [], [], self::ms($t));
        }

        // ── Étape 10 : Health Check ──────────────────────────────────────────
        $t = microtime(true);
        try {
            $health      = HealthManager::check();
            $score       = HealthManager::score($health);
            $healthStatus= $score >= 8 ? 'ok' : ($score >= 5 ? 'warning' : 'error');
            $steps[]     = DSMResult::make($healthStatus, 'Health Check', "Score {$score}/10", $health, [], self::ms($t));
            if ($score < 5) $hasError = true;
        } catch (\Throwable $e) {
            $steps[] = DSMResult::error('Health Check', $e->getMessage(), [], [], self::ms($t));
            $hasError = true;
        }

        // ── Étape 11 : Smoke Tests ───────────────────────────────────────────
        $t = microtime(true);
        $smokeResults = $this->runSmokeTests($baseUrl);
        $smokeFailed  = array_filter($smokeResults, fn($s) => !$s['ok']);
        $steps[] = DSMResult::make(
            empty($smokeFailed) ? 'ok' : 'warning',
            'Smoke Tests',
            count($smokeResults) . ' tests — ' . count($smokeFailed) . ' échec(s)',
            $smokeResults, [], self::ms($t)
        );

        // ── Étape 12 : Auto-Rollback si erreur critique ──────────────────────
        $rollbackDone = false;
        if ($hasError) {
            $latestId = RollbackManager::getLatestId();
            if ($latestId) {
                $t = microtime(true);
                try {
                    $rollback    = RollbackManager::restore($latestId);
                    $steps[]     = $rollback;
                    $rollbackDone= ($rollback['status'] ?? '') === 'ok';
                } catch (\Throwable $e) {
                    $steps[] = DSMResult::error('Auto-Rollback', $e->getMessage(), [], [], self::ms($t));
                }
            }
        }

        // ── Résumé ───────────────────────────────────────────────────────────
        $totalMs  = round((microtime(true) - $startTime) * 1000);
        $errCount = count(array_filter($steps, fn($s) => ($s['status'] ?? '') === 'error'));
        $warnCount= count(array_filter($steps, fn($s) => ($s['status'] ?? '') === 'warning'));
        $okCount  = count(array_filter($steps, fn($s) => ($s['status'] ?? '') === 'ok'));
        $globalSt = $errCount > 0 ? 'error' : ($warnCount > 0 ? 'warning' : 'ok');

        // Enregistrer dans le log de déploiements
        try {
            DeploymentLog::record([
                'mode'        => 'recovery',
                'status'      => $globalSt,
                'summary'     => "Recovery — {$okCount} OK / {$warnCount} warn / {$errCount} err",
                'steps'       => $steps,
                'duration_ms' => $totalMs,
                'actor'       => Auth::user()['username'] ?? 'admin',
                'base_url'    => $baseUrl,
                'rollback_done' => $rollbackDone,
            ]);
        } catch (\Throwable $e) {}

        $this->json([
            'status'       => $globalSt,
            'label'        => 'Recovery',
            'message'      => "Recovery terminée — {$okCount} OK / {$warnCount} warning / {$errCount} erreur",
            'steps'        => $steps,
            'duration_ms'  => $totalMs,
            'ok'           => $okCount,
            'warning'      => $warnCount,
            'error'        => $errCount,
            'total'        => count($steps),
            'rollback_done'=> $rollbackDone,
            'smoke'        => $smokeResults,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /admin/api/system/recovery-maintenance — Toggle maintenance lock
    // ─────────────────────────────────────────────────────────────────────────
    public function maintenance(): void {
        $this->middlewareAuth();
        $this->validateCsrf();

        $lock   = ROOT_PATH . '/storage/maintenance.lock';
        $action = trim($_POST['action'] ?? 'toggle');

        if ($action === 'enable') {
            if (!is_dir(ROOT_PATH . '/storage')) { @mkdir(ROOT_PATH . '/storage', 0755, true); }
            $ok = (bool) file_put_contents($lock, date('Y-m-d H:i:s') . ' — activé depuis Recovery Center');
            $this->json(['status' => $ok ? 'ok' : 'error', 'label' => 'Maintenance', 'message' => $ok ? 'Mode maintenance activé' : 'Erreur activation', 'active' => $ok]);
        } elseif ($action === 'disable') {
            $ok = !file_exists($lock) || @unlink($lock);
            $this->json(['status' => $ok ? 'ok' : 'error', 'label' => 'Maintenance', 'message' => $ok ? 'Mode maintenance désactivé' : 'Impossible de supprimer le fichier lock', 'active' => false]);
        } else {
            // toggle
            if (file_exists($lock)) {
                $ok = @unlink($lock);
                $this->json(['status' => $ok ? 'ok' : 'error', 'label' => 'Maintenance', 'message' => $ok ? 'Désactivé' : 'Erreur désactivation', 'active' => false]);
            } else {
                if (!is_dir(ROOT_PATH . '/storage')) { @mkdir(ROOT_PATH . '/storage', 0755, true); }
                $ok = (bool) file_put_contents($lock, date('Y-m-d H:i:s') . ' — activé depuis Recovery Center');
                $this->json(['status' => $ok ? 'ok' : 'error', 'label' => 'Maintenance', 'message' => $ok ? 'Activé' : 'Erreur activation', 'active' => $ok]);
            }
        }
    }

    // ═════════════════════════════════════════════════════════════════════════
    // HELPERS PRIVÉS
    // ═════════════════════════════════════════════════════════════════════════

    private function runDiagnostics(): array {
        $checks = [];

        // PHP Version
        $t = microtime(true);
        $phpOk = version_compare(PHP_VERSION, '8.0', '>=');
        $checks[] = ['key' => 'php', 'label' => 'PHP Version', 'status' => $phpOk ? 'ok' : 'error', 'message' => 'PHP ' . PHP_VERSION, 'duration_ms' => self::ms($t)];

        // Constantes config
        $t = microtime(true);
        $required = ['ROOT_PATH', 'APP_PATH', 'DB_HOST', 'DB_NAME', 'DB_USER', 'ENVIRONMENT'];
        $missing  = array_filter($required, fn($c) => !defined($c));
        $checks[] = ['key' => 'constants', 'label' => 'Constantes Config', 'status' => empty($missing) ? 'ok' : 'error', 'message' => empty($missing) ? 'Toutes présentes' : 'Manquantes : ' . implode(', ', $missing), 'duration_ms' => self::ms($t)];

        // .env
        $t = microtime(true);
        $envPath = ROOT_PATH . '/.env';
        $envOk   = file_exists($envPath);
        $envMsg  = $envOk ? ('APP_ENV=' . (defined('ENVIRONMENT') ? ENVIRONMENT : '?')) : 'Absent';
        $checks[] = ['key' => 'env', 'label' => 'Fichier .env', 'status' => $envOk ? 'ok' : 'warning', 'message' => $envMsg, 'duration_ms' => self::ms($t)];

        // Connexion SQL
        $t = microtime(true);
        try {
            Database::query("SELECT 1");
            $checks[] = ['key' => 'sql', 'label' => 'Connexion SQL', 'status' => 'ok', 'message' => 'Connecté à ' . (defined('DB_NAME') ? DB_NAME : '?'), 'duration_ms' => self::ms($t)];
        } catch (\Throwable $e) {
            $checks[] = ['key' => 'sql', 'label' => 'Connexion SQL', 'status' => 'error', 'message' => 'ÉCHEC : ' . $e->getMessage(), 'duration_ms' => self::ms($t)];
        }

        // Tables critiques
        $t = microtime(true);
        $criticalTables = ['pages', 'settings', 'menus', 'menu_items', 'sections', 'blocks', 'media', 'blog_posts', 'projects', 'contact_messages'];
        $missing = [];
        try {
            $existing = Database::fetchAll("SHOW TABLES") ?: [];
            $existing = array_map(fn($r) => array_values($r)[0], $existing);
            $missing  = array_diff($criticalTables, $existing);
        } catch (\Throwable $e) { $missing = $criticalTables; }
        $checks[] = ['key' => 'tables', 'label' => 'Tables SQL', 'status' => empty($missing) ? 'ok' : 'error', 'message' => empty($missing) ? count($criticalTables) . ' tables présentes' : 'Manquantes : ' . implode(', ', $missing), 'duration_ms' => self::ms($t)];

        // menus.location (root cause incident)
        $t = microtime(true);
        try {
            $cols = Database::fetchAll("SHOW COLUMNS FROM menus") ?: [];
            $colNames = array_column($cols, 'Field');
            $hasLoc = in_array('location', $colNames);
            $checks[] = ['key' => 'menus_location', 'label' => 'menus.location', 'status' => $hasLoc ? 'ok' : 'error', 'message' => $hasLoc ? 'Colonne présente ✓' : 'MANQUANTE — cause de l\'incident production !', 'duration_ms' => self::ms($t)];
        } catch (\Throwable $e) {
            $checks[] = ['key' => 'menus_location', 'label' => 'menus.location', 'status' => 'warning', 'message' => 'Vérification impossible (table absente ?)', 'duration_ms' => self::ms($t)];
        }

        // Routes
        $t = microtime(true);
        $routesFile = ROOT_PATH . '/routes/web.php';
        $routesOk   = file_exists($routesFile);
        $routeCount = $routesOk ? substr_count(file_get_contents($routesFile), '$router->') : 0;
        $checks[] = ['key' => 'routes', 'label' => 'Routes', 'status' => $routesOk ? 'ok' : 'error', 'message' => $routesOk ? "{$routeCount} routes déclarées" : 'routes/web.php introuvable', 'duration_ms' => self::ms($t)];

        // index.php — exception handler
        $t = microtime(true);
        $idxFile    = ROOT_PATH . '/index.php';
        $idxContent = file_exists($idxFile) ? file_get_contents($idxFile) : '';
        $hasHandler = str_contains($idxContent, 'set_exception_handler');
        $hasMaint   = str_contains($idxContent, 'maintenance.lock');
        $checks[] = ['key' => 'index', 'label' => 'index.php (Error Handler)', 'status' => $hasHandler ? 'ok' : 'error', 'message' => ($hasHandler ? 'Exception handler ✓' : 'ABSENT ✗') . ' | Maintenance: ' . ($hasMaint ? '✓' : '✗'), 'duration_ms' => self::ms($t)];

        // Assets CSS
        $t = microtime(true);
        $assetChecks = $this->verifyAssets();
        $assetMissing = array_filter($assetChecks, fn($a) => !$a['ok']);
        $checks[] = ['key' => 'assets', 'label' => 'Assets CSS/JS', 'status' => empty($assetMissing) ? 'ok' : 'warning', 'message' => count($assetChecks) . ' assets — ' . count($assetMissing) . ' manquant(s)', 'duration_ms' => self::ms($t)];

        // Uploads
        $t = microtime(true);
        $uploadsDir  = ROOT_PATH . '/public/uploads';
        $uploadsOk   = is_dir($uploadsDir) && is_writable($uploadsDir);
        $uploadCount = is_dir($uploadsDir) ? count(glob($uploadsDir . '/*') ?: []) : 0;
        $checks[] = ['key' => 'uploads', 'label' => 'Répertoire Uploads', 'status' => $uploadsOk ? 'ok' : 'warning', 'message' => $uploadsOk ? "{$uploadCount} fichier(s)" : 'Non accessible ou manquant', 'duration_ms' => self::ms($t)];

        // Cache
        $t = microtime(true);
        $cacheDir   = ROOT_PATH . '/storage/cache';
        $cacheOk    = is_dir($cacheDir) && is_writable($cacheDir);
        $cacheCount = $cacheOk ? count(glob($cacheDir . '/*.cache') ?: []) : 0;
        $checks[] = ['key' => 'cache', 'label' => 'Cache', 'status' => $cacheOk ? 'ok' : 'warning', 'message' => $cacheOk ? "{$cacheCount} fichier(s) en cache" : 'Répertoire cache inaccessible', 'duration_ms' => self::ms($t)];

        // Menus
        $t = microtime(true);
        try {
            $menuCount = (int)(Database::fetch("SELECT COUNT(*) as n FROM menus")['n'] ?? 0);
            $checks[] = ['key' => 'menus', 'label' => 'Menus', 'status' => $menuCount > 0 ? 'ok' : 'warning', 'message' => $menuCount > 0 ? "{$menuCount} menu(s) actif(s)" : 'Aucun menu — sera reconstruit', 'duration_ms' => self::ms($t)];
        } catch (\Throwable $e) {
            $checks[] = ['key' => 'menus', 'label' => 'Menus', 'status' => 'warning', 'message' => 'Vérification impossible', 'duration_ms' => self::ms($t)];
        }

        // Settings
        $t = microtime(true);
        try {
            $settingsCount = (int)(Database::fetch("SELECT COUNT(*) as n FROM settings")['n'] ?? 0);
            $checks[] = ['key' => 'settings', 'label' => 'Settings', 'status' => $settingsCount > 0 ? 'ok' : 'warning', 'message' => $settingsCount > 0 ? "{$settingsCount} paramètre(s)" : 'Aucun paramètre', 'duration_ms' => self::ms($t)];
        } catch (\Throwable $e) {
            $checks[] = ['key' => 'settings', 'label' => 'Settings', 'status' => 'warning', 'message' => 'Vérification impossible', 'duration_ms' => self::ms($t)];
        }

        // Hero slides
        $t = microtime(true);
        try {
            $heroCount = (int)(Database::fetch("SELECT COUNT(*) as n FROM hero_slides")['n'] ?? 0);
            $checks[] = ['key' => 'hero', 'label' => 'Hero Slides', 'status' => 'ok', 'message' => "{$heroCount} slide(s)", 'duration_ms' => self::ms($t)];
        } catch (\Throwable $e) {
            $checks[] = ['key' => 'hero', 'label' => 'Hero Slides', 'status' => 'warning', 'message' => 'Table hero_slides inaccessible', 'duration_ms' => self::ms($t)];
        }

        // Permissions storage
        $t = microtime(true);
        $permDirs = ['storage/logs', 'storage/cache', 'storage/backups', 'public/uploads'];
        $permIssues = [];
        foreach ($permDirs as $d) {
            $p = ROOT_PATH . '/' . $d;
            if (!is_dir($p) || !is_writable($p)) { $permIssues[] = $d; }
        }
        $checks[] = ['key' => 'permissions', 'label' => 'Permissions', 'status' => empty($permIssues) ? 'ok' : 'warning', 'message' => empty($permIssues) ? 'Tous les répertoires accessibles' : 'Problèmes : ' . implode(', ', $permIssues), 'duration_ms' => self::ms($t)];

        // Composer / Autoloader
        $t = microtime(true);
        $autoloadOk = file_exists(ROOT_PATH . '/app/Services/Database.php') && file_exists(ROOT_PATH . '/app/Controllers/HomeController.php');
        $checks[] = ['key' => 'composer', 'label' => 'Autoloader / Classes', 'status' => $autoloadOk ? 'ok' : 'error', 'message' => $autoloadOk ? 'PSR-4 autoloader OK' : 'Classes manquantes — déploiement incomplet ?', 'duration_ms' => self::ms($t)];

        return $checks;
    }

    private function verifyAssets(): array {
        $assets = [
            'public/assets/css/index.css' => 'CSS Principal',
            'public/css/style.css'        => 'CSS Style',
        ];
        $result = [];
        foreach ($assets as $path => $label) {
            $full = ROOT_PATH . '/' . $path;
            $result[] = [
                'path'  => $path,
                'label' => $label,
                'ok'    => file_exists($full),
                'size'  => file_exists($full) ? filesize($full) : 0,
            ];
        }
        return $result;
    }

    private function rebuildMenus(): array {
        $t = microtime(true);
        try {
            $menuCount = (int)(Database::fetch("SELECT COUNT(*) as n FROM menus")['n'] ?? 0);
            if ($menuCount === 0) {
                Database::query("INSERT IGNORE INTO menus (name, location, is_active) VALUES ('Menu Principal', 'primary', 1)");
                $menuId = Database::getConnection()->lastInsertId();
                $pages  = Database::fetchAll("SELECT id, title, slug FROM pages WHERE status = 'published' ORDER BY sort_order ASC, id ASC LIMIT 10") ?: [];
                $pos    = 0;
                foreach ($pages as $page) {
                    Database::query("INSERT IGNORE INTO menu_items (menu_id, label, url, sort_order, is_active) VALUES (?, ?, ?, ?, 1)", [$menuId, $page['title'], '/' . $page['slug'], $pos]);
                    $pos++;
                }
                return DSMResult::make('ok', 'Menu Rebuild', "Menu principal créé avec {$pos} items", [], [], self::ms($t));
            }
            return DSMResult::make('ok', 'Menu Rebuild', "{$menuCount} menu(s) déjà présent(s)", [], [], self::ms($t));
        } catch (\Throwable $e) {
            return DSMResult::warning('Menu Rebuild', $e->getMessage(), [], [], self::ms($t));
        }
    }

    private function syncSettings(): array {
        $t = microtime(true);
        $requiredSettings = [
            'site_name'     => 'Digitalium Group',
            'site_email'    => 'contact@digitaliumgroup.com',
            'contact_email' => 'contact@digitaliumgroup.com',
        ];
        $synced = 0;
        $errors = [];
        foreach ($requiredSettings as $key => $default) {
            try {
                $exists = Database::fetch("SELECT id FROM settings WHERE `key` = ?", [$key]);
                if (!$exists) {
                    Database::query("INSERT INTO settings (`key`, `value`) VALUES (?, ?)", [$key, $default]);
                    $synced++;
                }
            } catch (\Throwable $e) {
                $errors[] = $key . ': ' . $e->getMessage();
            }
        }
        $status = empty($errors) ? 'ok' : 'warning';
        return DSMResult::make($status, 'Settings Sync', "{$synced} clé(s) ajoutée(s) — " . count($requiredSettings) . ' vérifiées', [], $errors, self::ms($t));
    }

    private function runSmokeTests(string $baseUrl): array {
        $results = [];
        $paths   = [
            '/'            => 'Homepage',
            // /blog et /service ne sont plus que des redirections 301 : les
            // sonder ne prouverait pas que la page qui les remplace répond.
            '/insights'    => 'Insights',
            '/solutions'   => 'Solutions',
            '/realisations'=> 'Réalisations',
            '/sitemap.xml' => 'Sitemap',
        ];
        foreach ($paths as $path => $label) {
            $url = rtrim($baseUrl, '/') . $path;
            $t   = microtime(true);
            try {
                $ctx  = stream_context_create(['http' => ['method' => 'GET', 'timeout' => 8, 'follow_location' => true, 'ignore_errors' => true]]);
                $resp = @file_get_contents($url, false, $ctx);
                $meta = $http_response_header ?? [];
                $code = 0;
                foreach ($meta as $header) {
                    if (preg_match('/HTTP\/\d\.\d (\d{3})/', $header, $m)) { $code = (int)$m[1]; }
                }
                $ok = in_array($code, [200, 301, 302]);
                $results[] = ['path' => $path, 'label' => $label, 'url' => $url, 'code' => $code, 'ok' => $ok, 'ms' => round((microtime(true) - $t) * 1000)];
            } catch (\Throwable $e) {
                $results[] = ['path' => $path, 'label' => $label, 'url' => $url, 'code' => 0, 'ok' => false, 'ms' => round((microtime(true) - $t) * 1000)];
            }
        }
        return $results;
    }

    private function detectBaseUrl(): string {
        $s = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $s . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }

    private static function ms(float $start): float {
        return round((microtime(true) - $start) * 1000, 2);
    }
}
