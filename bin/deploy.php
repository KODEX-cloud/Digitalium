#!/usr/bin/env php
<?php

// ─── Sécurité : CLI uniquement ────────────────────────────────────────────────
// Le dossier bin/ était servi par Apache : /bin/read_logs.php et /bin/deploy.php
// répondaient en HTTP. Le .htaccess les bloque désormais, mais un .htaccess perdu
// ou ignoré ne doit pas suffire à rendre ces scripts exécutables depuis le Web.
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Access denied — CLI only');
}
/**
 * DIGITALIUM DEPLOYMENT PIPELINE — CLI Runner
 *
 * Orchestre le déploiement complet avec rollback automatique sur échec.
 *
 * Usage :
 *   php bin/deploy.php [--mode=full|quick|repair] [--no-rollback] [--dry-run]
 *
 * Exit codes :
 *   0 = succès
 *   1 = erreur critique (rollback effectué si possible)
 *   2 = erreur non-critique (avertissement, déploiement considéré OK)
 *
 * Pipeline :
 *   BootCheck → Backup → MasterMigration → SyncProduction →
 *   CacheClear → SelfHeal → HealthCheck → SmokeTests →
 *   Log → [Rollback si critique]
 */

define('SECURE_ACCESS', true);
$root = dirname(__DIR__);
if (!defined('ROOT_PATH')) define('ROOT_PATH', $root);
require $root . '/config/config.php';

// PSR-4 autoloader
spl_autoload_register(function ($class) use ($root) {
    if (strpos($class, 'App\\') !== 0) return;
    $file = $root . '/app/' . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 4)) . '.php';
    if (file_exists($file)) require_once $file;
});

use App\Services\BootCheck;
use App\Services\Database;
use App\System\DSMResult;
use App\System\RollbackManager;
use App\System\DeploymentLog;
use App\System\SyncProductionManager;
use App\System\CacheManager;
use App\System\SelfHealManager;
use App\System\HealthManager;
use App\System\AssetManager;
use App\System\RouteManager;

// ─── Parse args ──────────────────────────────────────────────────────────────

$opts       = getopt('', ['mode:', 'no-rollback', 'dry-run', 'base-url:']);
$mode       = $opts['mode']       ?? 'full';
$noRollback = isset($opts['no-rollback']);
$dryRun     = isset($opts['dry-run']);
$baseUrl    = $opts['base-url']   ?? self_detect_base_url();

function self_detect_base_url(): string {
    // Try APP_URL constant, then environment, then localhost fallback
    if (defined('APP_URL') && APP_URL) return rtrim(APP_URL, '/');
    if (!empty($_ENV['APP_URL']))       return rtrim($_ENV['APP_URL'], '/');
    if (!empty(getenv('APP_URL')))      return rtrim(getenv('APP_URL'), '/');
    return 'https://digitaliumgroup.com';
}

// ─── Output helpers ──────────────────────────────────────────────────────────

$isCli = PHP_SAPI === 'cli';

function log_step(string $status, string $label, string $msg = '', float $ms = 0): void {
    $icon = match($status) {
        'ok'      => '✓',
        'warning' => '⚠',
        'error'   => '✗',
        default   => '·',
    };
    $dur = $ms > 0 ? sprintf(' [%.0fms]', $ms) : '';
    echo "  {$icon} [{$status}] {$label}{$dur}";
    if ($msg) echo " — {$msg}";
    echo "\n";
}

function log_section(string $title): void {
    echo "\n=== {$title} ===\n";
}

function write_log(string $level, string $msg): void {
    $logPath = ROOT_PATH . '/storage/logs/errors.log';
    @file_put_contents($logPath, date('Y-m-d H:i:s') . " [DEPLOY:{$level}] {$msg}\n", FILE_APPEND | LOCK_EX);
}

// ─── Main pipeline ────────────────────────────────────────────────────────────

$globalStart = DSMResult::timer();
$steps       = [];
$rollbackId  = null;
$exitCode    = 0;
$hasCritical = false;

echo "\n";
echo "╔════════════════════════════════════════════════════╗\n";
echo "║  DIGITALIUM DEPLOYMENT PIPELINE — {$mode}           \n";
echo "║  " . date('Y-m-d H:i:s') . "                         \n";
if ($dryRun) echo "║  ⚠ DRY RUN — Aucune modification réelle           \n";
echo "╚════════════════════════════════════════════════════╝\n";

// ─── PHASE 1 : BootCheck ─────────────────────────────────────────────────────
log_section('PHASE 1 — Boot Check');
$t    = DSMResult::timer();
$boot = BootCheck::run();
$dur  = DSMResult::elapsed($t);
foreach ($boot['checks'] as $key => $check) {
    log_step($check['status'], $check['label'], $check['message'], 0);
}
$steps['boot_check'] = DSMResult::make(
    $boot['critical'] ? 'error' : ($boot['ok'] ? 'ok' : 'warning'),
    'Boot Check', $boot['summary'], $boot['checks'], [], $dur
);

if ($boot['critical']) {
    log_step('error', 'ABORT', 'Checks critiques échoués — déploiement annulé');
    write_log('CRITICAL', 'BootCheck failed: ' . $boot['summary']);
    DeploymentLog::record(['status' => 'aborted', 'reason' => 'bootcheck_critical', 'boot' => $boot, 'mode' => $mode]);
    exit(1);
}

// ─── PHASE 2 : Backup (Rollback Point) ────────────────────────────────────────
log_section('PHASE 2 — Rollback Point');
if (!$dryRun) {
    $t              = DSMResult::timer();
    $backupResult   = RollbackManager::create();
    $rollbackId     = $backupResult['data']['id'] ?? null;
    $steps['backup'] = $backupResult;
    log_step($backupResult['status'], 'Backup DB', $backupResult['message'], DSMResult::elapsed($t));
} else {
    log_step('skip', 'Backup DB', 'Dry run — skip');
}

// ─── PHASE 3 : Master Migration ───────────────────────────────────────────────
log_section('PHASE 3 — Master Migration');
$t = DSMResult::timer();
try {
    // Run master_migration via include (already defines helpers + runs queries)
    // We capture output
    ob_start();
    if (!$dryRun) {
        // Re-run master migration logic inline
        $pdo = Database::getConnection();
        $migDone = $migErrors = [];

        // Helper function (may already exist from boot)
        if (!function_exists('addColumnIfNotExists')) {
            function addColumnIfNotExists(\PDO $pdo, string $table, string $column, string $definition): void {
                global $migDone, $migErrors;
                $row = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'")->fetch();
                if (!$row) {
                    $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
                    $migDone[] = "ALTER {$table}: added {$column}";
                }
            }
        }

        // Core tables
        foreach ([
            "CREATE TABLE IF NOT EXISTS `menus` (`id` INT AUTO_INCREMENT PRIMARY KEY,`name` VARCHAR(100) NOT NULL,`slug` VARCHAR(100) NOT NULL UNIQUE,`location` VARCHAR(50) DEFAULT 'primary',`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
            "CREATE TABLE IF NOT EXISTS `menu_items` (`id` INT AUTO_INCREMENT PRIMARY KEY,`menu_id` INT NOT NULL,`parent_id` INT NULL,`page_id` INT NULL,`label` VARCHAR(150) NOT NULL,`url` VARCHAR(500) NULL,`target` VARCHAR(20) DEFAULT '_self',`icon` VARCHAR(50) NULL,`sort_order` INT DEFAULT 0,`is_active` TINYINT DEFAULT 1,`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        ] as $ddl) {
            try { $pdo->exec($ddl); $migDone[] = "OK"; } catch (\Throwable $e) { $migErrors[] = $e->getMessage(); }
        }
    }
    ob_end_clean();

    $migStatus = empty($migErrors ?? []) ? 'ok' : 'warning';
    $migMsg    = 'Master migration: ' . count($migDone ?? []) . ' OK';
    $steps['master_migration'] = DSMResult::make($migStatus, 'Master Migration', $migMsg, [], $migErrors ?? [], DSMResult::elapsed($t));
    log_step($migStatus, 'Master Migration', $migMsg, DSMResult::elapsed($t));
} catch (\Throwable $e) {
    $steps['master_migration'] = DSMResult::error('Master Migration', $e->getMessage(), [], [], DSMResult::elapsed($t));
    log_step('error', 'Master Migration', $e->getMessage(), DSMResult::elapsed($t));
    write_log('ERROR', 'Master migration: ' . $e->getMessage());
}

// ─── PHASE 4 : Sync Production ────────────────────────────────────────────────
log_section('PHASE 4 — Sync Production DB');
$t = DSMResult::timer();
try {
    if (!$dryRun) {
        $syncResult = SyncProductionManager::run();
    } else {
        $diff = SyncProductionManager::diff();
        $syncResult = DSMResult::make('ok', 'Sync Production (dry)', 'Dry run — ' . count($diff['columns']) . ' colonne(s) seraient ajoutées', $diff);
    }
    $steps['sync_production'] = $syncResult;
    log_step($syncResult['status'], 'Sync Production', $syncResult['message'], DSMResult::elapsed($t));

    // Détail des corrections
    foreach (($syncResult['data']['results'] ?? []) as $r) {
        if ($r['status'] === 'ok') {
            log_step('ok', '  ' . $r['action'], $r['detail'] ?? '');
        } elseif ($r['status'] === 'error') {
            log_step('error', '  ' . $r['action'], $r['detail'] ?? '');
        }
    }
} catch (\Throwable $e) {
    $steps['sync_production'] = DSMResult::error('Sync Production', $e->getMessage(), [], [], DSMResult::elapsed($t));
    log_step('error', 'Sync Production', $e->getMessage(), DSMResult::elapsed($t));
    $hasCritical = true;
    write_log('CRITICAL', 'Sync production: ' . $e->getMessage());
}

// ─── PHASE 5 : Cache Clear ────────────────────────────────────────────────────
log_section('PHASE 5 — Cache + Assets');
$t = DSMResult::timer();
try {
    if (!$dryRun) {
        $cache = CacheManager::clear();
        $steps['cache'] = $cache;
        log_step($cache['status'], 'Cache Clear', $cache['message'], DSMResult::elapsed($t));
    } else {
        log_step('skip', 'Cache Clear', 'Dry run');
    }
} catch (\Throwable $e) {
    log_step('warning', 'Cache Clear', $e->getMessage());
    $steps['cache'] = DSMResult::error('Cache', $e->getMessage());
}

// Assets verification
$t = DSMResult::timer();
try {
    $assets = AssetManager::check();
    $steps['assets'] = $assets;
    log_step($assets['status'], 'Assets Check', $assets['message'], DSMResult::elapsed($t));
} catch (\Throwable $e) {
    log_step('warning', 'Assets Check', $e->getMessage());
    $steps['assets'] = DSMResult::error('Assets', $e->getMessage());
}

// ─── PHASE 6 : Self-Heal ─────────────────────────────────────────────────────
log_section('PHASE 6 — Self-Heal');
$t = DSMResult::timer();
try {
    if (!$dryRun) {
        $heal = SelfHealManager::run();
        $steps['self_heal'] = $heal;
        log_step($heal['status'], 'Self-Heal', $heal['message'], DSMResult::elapsed($t));
    } else {
        log_step('skip', 'Self-Heal', 'Dry run');
    }
} catch (\Throwable $e) {
    log_step('warning', 'Self-Heal', $e->getMessage());
    $steps['self_heal'] = DSMResult::error('Self-Heal', $e->getMessage());
}

// ─── PHASE 7 : Health Check ───────────────────────────────────────────────────
log_section('PHASE 7 — Health Check');
$t = DSMResult::timer();
try {
    $checks = HealthManager::check();
    $score  = HealthManager::score($checks);
    $hStatus = $score >= 8 ? 'ok' : ($score >= 5 ? 'warning' : 'error');
    $steps['health'] = DSMResult::make($hStatus, 'Health Check', "Score {$score}/10", ['score' => $score, 'checks' => $checks], [], DSMResult::elapsed($t));
    log_step($hStatus, 'Health Check', "Score {$score}/10", DSMResult::elapsed($t));

    if ($score < 5) {
        $hasCritical = true;
        write_log('CRITICAL', "Health score {$score}/10 — below threshold");
    }
} catch (\Throwable $e) {
    log_step('warning', 'Health Check', $e->getMessage());
    $steps['health'] = DSMResult::error('Health', $e->getMessage());
}

// ─── PHASE 8 : Routes ─────────────────────────────────────────────────────────
log_section('PHASE 8 — Routes + Settings');
$t = DSMResult::timer();
try {
    $routes = RouteManager::scan();
    $steps['routes'] = $routes;
    log_step($routes['status'], 'Routes Scan', $routes['message'], DSMResult::elapsed($t));
} catch (\Throwable $e) {
    log_step('warning', 'Routes Scan', $e->getMessage());
    $steps['routes'] = DSMResult::error('Routes', $e->getMessage());
}

// Settings check
$t = DSMResult::timer();
try {
    $settingsResult = self_verify_settings();
    $steps['settings'] = $settingsResult;
    log_step($settingsResult['status'], 'Settings', $settingsResult['message'], DSMResult::elapsed($t));
} catch (\Throwable $e) {
    $steps['settings'] = DSMResult::error('Settings', $e->getMessage());
}

function self_verify_settings(): array {
    $t    = DSMResult::timer();
    $miss = [];
    try {
        $all  = \App\Models\Setting::getAll();
        $req  = ['site_name', 'contact_email', 'footer_copyright'];
        foreach ($req as $k) {
            if (!isset($all[$k])) $miss[] = $k;
        }
    } catch (\Throwable $e) { return DSMResult::error('Settings', $e->getMessage()); }
    return DSMResult::make(empty($miss) ? 'ok' : 'warning', 'Settings', empty($miss) ? 'Toutes les clés présentes' : 'Manquantes: ' . implode(', ', $miss));
}

// ─── PHASE 9 : Smoke Tests HTTP ───────────────────────────────────────────────
log_section('PHASE 9 — Smoke Tests HTTP');
global $baseUrl;
$smokeUrls = [
    'homepage'   => $baseUrl . '/',
    'admin'      => $baseUrl . '/admin',
    'blog'       => $baseUrl . '/blog',
    'sitemap'    => $baseUrl . '/sitemap.xml',
];
$smokeResults = [];
$smokeFailed  = 0;

foreach ($smokeUrls as $name => $url) {
    $t    = DSMResult::timer();
    $code = 0;
    try {
        $ctx  = stream_context_create(['http' => ['timeout' => 8, 'ignore_errors' => true, 'follow_location' => true]]);
        $h    = @get_headers($url, true, $ctx);
        $code = $h ? (int) substr($h[0], 9, 3) : 0;
    } catch (\Throwable $e) {
        $code = 0;
    }
    $ok = in_array($code, [200, 301, 302]);
    if (!$ok) $smokeFailed++;
    $smokeResults[$name] = ['url' => $url, 'code' => $code, 'status' => $ok ? 'ok' : 'error'];
    log_step($ok ? 'ok' : 'error', "{$name}", "HTTP {$code} — {$url}", DSMResult::elapsed($t));
}
$smokeStatus = $smokeFailed === 0 ? 'ok' : ($smokeFailed <= 1 ? 'warning' : 'error');
$steps['smoke_tests'] = DSMResult::make($smokeStatus, 'Smoke Tests', "{$smokeFailed} failure(s) / " . count($smokeUrls) . ' URLs', $smokeResults);

if ($smokeFailed >= 2) {
    $hasCritical = true;
    write_log('CRITICAL', "Smoke tests: {$smokeFailed} failures");
}

// ─── PHASE 10 : Rollback si critique ──────────────────────────────────────────
if ($hasCritical && !$noRollback && !$dryRun && $rollbackId) {
    log_section('PHASE 10 — ROLLBACK AUTOMATIQUE');
    log_step('warning', 'Rollback', "Déclenchement rollback {$rollbackId} — erreur critique détectée");
    $t      = DSMResult::timer();
    $rback  = RollbackManager::restore($rollbackId);
    $steps['rollback'] = $rback;
    log_step($rback['status'], 'Rollback', $rback['message'], DSMResult::elapsed($t));
    write_log('ROLLBACK', "Restored backup {$rollbackId}: {$rback['status']}");
    $exitCode = 1;
}

// ─── PHASE 11 : Log + Résumé ──────────────────────────────────────────────────
$totalMs  = DSMResult::elapsed($globalStart);
$okCount  = count(array_filter($steps, fn($s) => ($s['status'] ?? '') === 'ok'));
$errCount = count(array_filter($steps, fn($s) => ($s['status'] ?? '') === 'error'));
$warnCount= count(array_filter($steps, fn($s) => ($s['status'] ?? '') === 'warning'));

$globalStatus = $hasCritical ? 'error' : ($errCount > 0 ? 'warning' : 'ok');
if ($exitCode === 0 && $hasCritical) $exitCode = 1;

$logEntry = [
    'status'        => $globalStatus,
    'mode'          => $mode,
    'duration_ms'   => round($totalMs, 2),
    'dry_run'       => $dryRun,
    'rollback_id'   => $rollbackId,
    'rollback_done' => $hasCritical && !$noRollback && !$dryRun,
    'base_url'      => $baseUrl,
    'git_commit'    => trim(@shell_exec('git rev-parse --short HEAD 2>/dev/null') ?: 'unknown'),
    'git_branch'    => trim(@shell_exec('git rev-parse --abbrev-ref HEAD 2>/dev/null') ?: 'main'),
    'ok'            => $okCount,
    'warnings'      => $warnCount,
    'errors'        => $errCount,
    'steps'         => $steps,
    'php_version'   => PHP_VERSION,
    'environment'   => ENVIRONMENT,
];

$logId = DeploymentLog::record($logEntry);

echo "\n";
echo "╔════════════════════════════════════════════════════╗\n";
printf("║  RÉSULTAT : %-38s ║\n", strtoupper($globalStatus));
printf("║  Durée    : %-38s ║\n", round($totalMs / 1000, 2) . 's');
printf("║  OK:%-3d  WARN:%-3d  ERR:%-3d  Log: %-13s ║\n", $okCount, $warnCount, $errCount, $logId);
echo "╚════════════════════════════════════════════════════╝\n\n";

write_log('DONE', "mode={$mode} status={$globalStatus} duration={$totalMs}ms log={$logId}");

exit($exitCode);
