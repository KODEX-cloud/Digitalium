#!/usr/bin/env php
<?php
/**
 * ═══════════════════════════════════════════════════════════════
 *  DIGITALIUM GROUP — PRODUCTION RECOVERY SCRIPT
 *  Version : 1.4 — Enterprise
 * ═══════════════════════════════════════════════════════════════
 *
 *  Usage SSH :
 *    php bin/recover-production.php
 *    php bin/recover-production.php --dry-run        (simulation)
 *    php bin/recover-production.php --no-maintenance (sans mode maintenance)
 *    php bin/recover-production.php --skip-git       (sauter le git pull)
 *    php bin/recover-production.php --report-only    (diagnostic uniquement)
 *    php bin/recover-production.php --force          (ignorer les erreurs non-critiques)
 *
 *  Ce script :
 *   1. Active le mode maintenance (visitors → 503 + page branded)
 *   2. Diagnostique la configuration complète
 *   3. Vérifie / répare la connexion SQL
 *   4. Exécute les migrations manquantes
 *   5. Synchronise le schéma de production
 *   6. Vide les caches
 *   7. Reconstruit les menus
 *   8. Vérifie les assets + uploads
 *   9. Vérifie les routes
 *  10. Lance le HealthCheck complet
 *  11. Lance les Smoke Tests HTTP
 *  12. Désactive le mode maintenance (si succès)
 *  13. Produit un rapport HTML + JSON
 *
 * ═══════════════════════════════════════════════════════════════
 */

// ─── Sécurité : CLI uniquement ────────────────────────────────────────────────
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Access denied — CLI only');
}

$startTime = microtime(true);

// ─── Options CLI ──────────────────────────────────────────────────────────────
$opts = [
    'dry_run'          => in_array('--dry-run',          $argv),
    'no_maintenance'   => in_array('--no-maintenance',   $argv),
    'skip_git'         => in_array('--skip-git',         $argv),
    'report_only'      => in_array('--report-only',      $argv),
    'force'            => in_array('--force',             $argv),
];

// ─── Bootstrap minimal ────────────────────────────────────────────────────────
$root = dirname(__DIR__);

define('SECURE_ACCESS', true);
define('ROOT_PATH', $root);

// Chargement config
$configFile = $root . '/config/config.php';
if (!file_exists($configFile)) {
    die("FATAL: config/config.php introuvable. Vérifiez le déploiement.\n");
}
require $configFile;

// ─── Helpers CLI ─────────────────────────────────────────────────────────────

$steps   = [];
$summary = ['ok' => 0, 'warning' => 0, 'error' => 0, 'total' => 0];

function step(string $status, string $label, string $message, ?float $ms = null, array $data = []): void {
    global $steps, $summary;
    $ms   ??= 0.0;
    $icon  = match($status) {
        'ok'      => '✓',
        'warning' => '⚠',
        'error'   => '✗',
        'info'    => '›',
        'skip'    => '—',
        default   => '?',
    };
    $color = match($status) {
        'ok'      => "\033[32m",
        'warning' => "\033[33m",
        'error'   => "\033[31m",
        'info'    => "\033[36m",
        default   => "\033[90m",
    };
    $reset = "\033[0m";
    $msStr = $ms > 0 ? sprintf(' [%.0fms]', $ms) : '';
    echo sprintf("%s[%s %s]%s %-30s %s%s\n", $color, $icon, strtoupper(substr($status,0,4)), $reset, $label, $message, $msStr);

    if (in_array($status, ['ok','warning','error'])) {
        $summary[$status]++;
        $summary['total']++;
    }

    $steps[] = [
        'status'      => $status,
        'label'       => $label,
        'message'     => $message,
        'duration_ms' => round($ms),
        'data'        => $data,
        'timestamp'   => date('Y-m-d H:i:s'),
    ];
}

function sep(string $title = ''): void {
    echo "\n" . str_repeat('─', 65) . "\n";
    if ($title) echo "  $title\n" . str_repeat('─', 65) . "\n";
}

function elapsed(float $start): float {
    return (microtime(true) - $start) * 1000;
}

function tryExec(string $cmd): array {
    $output = []; $code = 0;
    exec($cmd . ' 2>&1', $output, $code);
    return ['code' => $code, 'output' => implode("\n", $output)];
}

// ─── Bannière ────────────────────────────────────────────────────────────────
echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  DIGITALIUM GROUP — PRODUCTION RECOVERY                     ║\n";
echo "║  " . date('Y-m-d H:i:s') . "                                    ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

if ($opts['dry_run'])     { echo "\033[33m[DRY RUN — Simulation uniquement, aucune modification]\033[0m\n\n"; }
if ($opts['report_only']) { echo "\033[36m[REPORT ONLY — Diagnostic uniquement]\033[0m\n\n"; }

// ════════════════════════════════════════════════════════════════════════════
// PHASE 1 — MAINTENANCE MODE
// ════════════════════════════════════════════════════════════════════════════
sep('PHASE 1 — MODE MAINTENANCE');

$maintenanceLock = ROOT_PATH . '/storage/maintenance.lock';
$maintenanceActivated = false;

if (!$opts['no_maintenance'] && !$opts['dry_run'] && !$opts['report_only']) {
    $t = microtime(true);
    if (!is_dir(ROOT_PATH . '/storage')) {
        @mkdir(ROOT_PATH . '/storage', 0755, true);
    }
    if (file_put_contents($maintenanceLock, date('Y-m-d H:i:s') . ' — Recovery en cours')) {
        $maintenanceActivated = true;
        step('ok', 'Maintenance Mode', 'Activé — visiteurs redirigés vers page 503', elapsed($t));
    } else {
        step('warning', 'Maintenance Mode', 'Impossible d\'activer — fichier non créé', elapsed($t));
    }
} else {
    step('skip', 'Maintenance Mode', $opts['no_maintenance'] ? 'Désactivé par option' : 'DRY RUN — ignoré');
}

// ════════════════════════════════════════════════════════════════════════════
// PHASE 2 — DIAGNOSTIC CONFIGURATION
// ════════════════════════════════════════════════════════════════════════════
sep('PHASE 2 — DIAGNOSTIC CONFIGURATION');

// 2.1 PHP Version
$t = microtime(true);
$phpVer = PHP_VERSION;
$phpOk  = version_compare($phpVer, '8.0', '>=');
step($phpOk ? 'ok' : 'error', 'PHP Version', "PHP $phpVer" . ($phpOk ? '' : ' — PHP 8.0+ requis'), elapsed($t));

// 2.2 Constantes critiques
$t = microtime(true);
$requiredConstants = ['ROOT_PATH', 'APP_PATH', 'DB_HOST', 'DB_NAME', 'DB_USER', 'ENVIRONMENT'];
$missingConst = array_filter($requiredConstants, fn($c) => !defined($c));
if (empty($missingConst)) {
    step('ok', 'Constantes config', 'Toutes présentes (' . implode(', ', $requiredConstants) . ')', elapsed($t));
} else {
    step('error', 'Constantes config', 'Manquantes : ' . implode(', ', $missingConst), elapsed($t));
}

// 2.3 Environnement
$t = microtime(true);
$env = defined('ENVIRONMENT') ? ENVIRONMENT : 'inconnu';
step('info', 'Environnement', $env, elapsed($t));

// 2.4 .env
$t = microtime(true);
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    $hasDbHost  = str_contains($envContent, 'DB_HOST');
    step($hasDbHost ? 'ok' : 'warning', '.env', $hasDbHost ? 'Présent et configuré' : 'Présent mais DB_HOST manquant', elapsed($t));
} else {
    step('warning', '.env', 'Absent — utilisation des valeurs config.php par défaut', elapsed($t));
}

// 2.5 Autoloader
$t = microtime(true);
$autoloaderFile = ROOT_PATH . '/app/Services/Database.php';
if (file_exists($autoloaderFile)) {
    step('ok', 'Autoloader', 'Classes PHP disponibles', elapsed($t));
} else {
    step('error', 'Autoloader', 'app/Services/Database.php introuvable — déploiement incomplet ?', elapsed($t));
}

// 2.6 Répertoires critiques
$t = microtime(true);
$dirs = [
    ROOT_PATH . '/storage/logs'    => true,
    ROOT_PATH . '/storage/cache'   => true,
    ROOT_PATH . '/storage/backups' => false,
    ROOT_PATH . '/storage/deployments' => false,
    ROOT_PATH . '/public/uploads'  => true,
];
$dirErrors = [];
foreach ($dirs as $dir => $required) {
    if (!is_dir($dir)) {
        if (!$opts['dry_run'] && !$opts['report_only']) {
            @mkdir($dir, 0755, true);
        }
        if (is_dir($dir)) {
            step('ok', 'Répertoire créé', str_replace(ROOT_PATH . '/', '', $dir), elapsed($t));
        } elseif ($required) {
            $dirErrors[] = str_replace(ROOT_PATH . '/', '', $dir);
        }
    }
}
if (empty($dirErrors)) {
    step('ok', 'Répertoires', 'Tous présents / créés', elapsed($t));
} else {
    step('error', 'Répertoires', 'Manquants : ' . implode(', ', $dirErrors), elapsed($t));
}

// ════════════════════════════════════════════════════════════════════════════
// PHASE 3 — CONNEXION BASE DE DONNÉES
// ════════════════════════════════════════════════════════════════════════════
sep('PHASE 3 — BASE DE DONNÉES');

$pdo = null;
$dbHost = defined('DB_HOST') ? DB_HOST : null;
$dbName = defined('DB_NAME') ? DB_NAME : null;
$dbUser = defined('DB_USER') ? DB_USER : null;
$dbPass = defined('DB_PASS') ? DB_PASS : '';

// 3.1 Connexion PDO
$t = microtime(true);
try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser, $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 10]
    );
    step('ok', 'Connexion MySQL', "Connecté à {$dbName}@{$dbHost}", elapsed($t));
} catch (\PDOException $e) {
    step('error', 'Connexion MySQL', 'ÉCHEC : ' . $e->getMessage(), elapsed($t));
    $pdo = null;
}

// 3.2 Tables existantes
$existingTables = [];
if ($pdo) {
    $t = microtime(true);
    try {
        $existingTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        step('ok', 'Tables existantes', count($existingTables) . ' tables trouvées : ' . implode(', ', $existingTables), elapsed($t));
    } catch (\Throwable $e) {
        step('error', 'Tables', 'Erreur SHOW TABLES : ' . $e->getMessage(), elapsed($t));
    }
}

// 3.3 Tables requises
$t = microtime(true);
$requiredTables = [
    'users', 'pages', 'sections', 'blocks', 'settings',
    'media', 'hero_slides', 'projects', 'blog_posts', 'blog_categories',
    'blog_tags', 'blog_post_tags', 'blog_comments', 'contact_messages',
    'menus', 'menu_items'
];
$missingTables = array_diff($requiredTables, $existingTables);
if (empty($missingTables)) {
    step('ok', 'Tables requises', 'Toutes les ' . count($requiredTables) . ' tables présentes', elapsed($t));
} else {
    step('error', 'Tables manquantes', implode(', ', $missingTables), elapsed($t));
}

// 3.4 Colonne critique : menus.location (root cause de l'incident)
if ($pdo && in_array('menus', $existingTables)) {
    $t = microtime(true);
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM menus")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('location', $cols)) {
            step('ok', 'menus.location', 'Colonne présente ✓', elapsed($t));
        } else {
            step('error', 'menus.location', 'COLONNE MANQUANTE — cause de l\'incident production !', elapsed($t));
        }
    } catch (\Throwable $e) {
        step('warning', 'menus.location', 'Vérification impossible : ' . $e->getMessage(), elapsed($t));
    }
}

if ($opts['report_only']) {
    goto produce_report;
}

// ════════════════════════════════════════════════════════════════════════════
// PHASE 4 — GIT PULL
// ════════════════════════════════════════════════════════════════════════════
sep('PHASE 4 — GIT PULL');

if (!$opts['skip_git'] && !$opts['dry_run']) {
    $t = microtime(true);
    $gitStatus = tryExec('git -C ' . escapeshellarg(ROOT_PATH) . ' status --short');
    if ($gitStatus['code'] === 0) {
        step('info', 'Git Status', trim($gitStatus['output']) ?: 'Clean', elapsed($t));

        $t = microtime(true);
        $gitPull = tryExec('git -C ' . escapeshellarg(ROOT_PATH) . ' fetch origin main && git -C ' . escapeshellarg(ROOT_PATH) . ' reset --hard origin/main');
        if ($gitPull['code'] === 0) {
            $commit = tryExec('git -C ' . escapeshellarg(ROOT_PATH) . ' rev-parse --short HEAD');
            step('ok', 'Git Pull', 'Code mis à jour — ' . trim($commit['output']), elapsed($t));
        } else {
            step('error', 'Git Pull', 'Échec : ' . substr($gitPull['output'], 0, 200), elapsed($t));
        }
    } else {
        step('warning', 'Git', 'Git indisponible ou pas de remote configuré', elapsed($t));
    }
} else {
    step('skip', 'Git Pull', $opts['skip_git'] ? 'Sauté par option --skip-git' : 'DRY RUN');
}

// ════════════════════════════════════════════════════════════════════════════
// PHASE 5 — MASTER MIGRATION + SYNC PRODUCTION
// ════════════════════════════════════════════════════════════════════════════
sep('PHASE 5 — MIGRATIONS');

// 5.1 Master Migration
$t = microtime(true);
$masterMig = ROOT_PATH . '/database/master_migration.php';
if (file_exists($masterMig)) {
    if (!$opts['dry_run']) {
        ob_start();
        try {
            include $masterMig;
            $out = ob_get_clean();
            step('ok', 'Master Migration', 'Exécutée — ' . (substr_count($out, 'CREATE') + substr_count($out, 'ALTER')) . ' opérations', elapsed($t));
        } catch (\Throwable $e) {
            ob_end_clean();
            step('warning', 'Master Migration', 'Warning : ' . $e->getMessage(), elapsed($t));
        }
    } else {
        step('skip', 'Master Migration', 'DRY RUN');
    }
} else {
    step('warning', 'Master Migration', 'database/master_migration.php introuvable', elapsed($t));
}

// 5.2 Sync Production (colonne location + colonnes manquantes)
$t = microtime(true);
$syncFile = ROOT_PATH . '/database/sync_production.php';
if (file_exists($syncFile)) {
    if (!$opts['dry_run']) {
        ob_start();
        try {
            include $syncFile;
            $out = ob_get_clean();
            step('ok', 'Sync Production', 'Schéma synchronisé', elapsed($t));
        } catch (\Throwable $e) {
            ob_end_clean();
            step('warning', 'Sync Production', 'Warning : ' . $e->getMessage(), elapsed($t));
        }
    } else {
        step('skip', 'Sync Production', 'DRY RUN');
    }
} else {
    // Inline minimal fix pour menus.location si SyncProductionManager absent
    if ($pdo && in_array('menus', $existingTables)) {
        $t2 = microtime(true);
        try {
            $cols = $pdo->query("SHOW COLUMNS FROM menus")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('location', $cols) && !$opts['dry_run']) {
                $pdo->exec("ALTER TABLE menus ADD COLUMN location VARCHAR(50) DEFAULT 'primary' AFTER name");
                $pdo->exec("UPDATE menus SET location = 'primary' WHERE location IS NULL OR location = ''");
                step('ok', 'Fix menus.location', 'Colonne ajoutée et initialisée — CORRECTIF APPLIQUÉ', elapsed($t2));
            } elseif (in_array('location', $cols)) {
                step('ok', 'menus.location', 'Déjà présente', elapsed($t2));
            }
        } catch (\Throwable $e) {
            step('error', 'Fix menus.location', $e->getMessage(), elapsed($t2));
        }
    }
    step('warning', 'Sync Production', 'database/sync_production.php introuvable — fix inline appliqué', elapsed($t));
}

// 5.3 Re-vérification menus.location après corrections
if ($pdo && in_array('menus', $existingTables)) {
    $t = microtime(true);
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM menus")->fetchAll(PDO::FETCH_COLUMN);
        $status = in_array('location', $cols) ? 'ok' : 'error';
        step($status, 'Vérification menus.location', in_array('location', $cols) ? 'Présente ✓' : 'TOUJOURS MANQUANTE ✗', elapsed($t));
    } catch (\Throwable $e) {
        step('warning', 'Vérification menus.location', $e->getMessage(), elapsed($t));
    }
}

// ════════════════════════════════════════════════════════════════════════════
// PHASE 6 — CACHE CLEAR
// ════════════════════════════════════════════════════════════════════════════
sep('PHASE 6 — CACHE');

$t = microtime(true);
$cacheDir = ROOT_PATH . '/storage/cache';
$cleared = 0;
if (is_dir($cacheDir) && !$opts['dry_run']) {
    foreach (glob($cacheDir . '/*') ?: [] as $f) {
        if (is_file($f)) { @unlink($f); $cleared++; }
    }
    step('ok', 'Cache Clear', "{$cleared} fichier(s) supprimé(s)", elapsed($t));
} elseif ($opts['dry_run']) {
    $count = count(glob($cacheDir . '/*') ?: []);
    step('skip', 'Cache Clear', "DRY RUN — {$count} fichier(s) seraient supprimés");
} else {
    step('info', 'Cache Clear', 'Répertoire cache vide ou inexistant', elapsed($t));
}

// ════════════════════════════════════════════════════════════════════════════
// PHASE 7 — RECONSTRUCTION DES MENUS
// ════════════════════════════════════════════════════════════════════════════
sep('PHASE 7 — MENUS');

if ($pdo) {
    $t = microtime(true);
    try {
        $menuCount = (int) $pdo->query("SELECT COUNT(*) FROM menus")->fetchColumn();
        if ($menuCount === 0) {
            // Seed menu principal depuis les pages publiées
            if (!$opts['dry_run']) {
                $pdo->exec("INSERT IGNORE INTO menus (name, location, is_active) VALUES ('Menu Principal', 'primary', 1)");
                $menuId = $pdo->lastInsertId();
                if ($menuId) {
                    $pages = $pdo->query("SELECT id, title, slug FROM pages WHERE status = 'published' ORDER BY sort_order ASC, id ASC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
                    $pos = 0;
                    foreach ($pages as $page) {
                        $pdo->exec("INSERT IGNORE INTO menu_items (menu_id, label, url, sort_order, is_active) VALUES ({$menuId}, " . $pdo->quote($page['title']) . ", '/" . $page['slug'] . "', {$pos}, 1)");
                        $pos++;
                    }
                    step('ok', 'Menus', "Menu principal créé avec {$pos} items depuis les pages publiées", elapsed($t));
                } else {
                    step('info', 'Menus', 'Menu principal déjà existant', elapsed($t));
                }
            } else {
                step('skip', 'Menus', 'DRY RUN — menu principal serait créé');
            }
        } else {
            step('ok', 'Menus', "{$menuCount} menu(s) existant(s)", elapsed($t));
        }
    } catch (\Throwable $e) {
        step('warning', 'Menus', $e->getMessage(), elapsed($t));
    }

    // Settings requis
    $t = microtime(true);
    $requiredSettings = [
        'site_name'    => 'Digitalium Group',
        'site_email'   => 'contact@digitaliumgroup.com',
        'contact_email'=> 'contact@digitaliumgroup.com',
    ];
    $settingsOk = 0;
    try {
        foreach ($requiredSettings as $key => $default) {
            $exists = $pdo->query("SELECT id FROM settings WHERE `key` = " . $pdo->quote($key))->fetchColumn();
            if (!$exists && !$opts['dry_run']) {
                $pdo->exec("INSERT INTO settings (`key`, `value`) VALUES (" . $pdo->quote($key) . ", " . $pdo->quote($default) . ")");
            }
            $settingsOk++;
        }
        step('ok', 'Settings requis', "{$settingsOk} clés vérifiées/créées", elapsed($t));
    } catch (\Throwable $e) {
        step('warning', 'Settings', $e->getMessage(), elapsed($t));
    }
}

// ════════════════════════════════════════════════════════════════════════════
// PHASE 8 — ASSETS + UPLOADS
// ════════════════════════════════════════════════════════════════════════════
sep('PHASE 8 — ASSETS & UPLOADS');

$t = microtime(true);
$cssFile = ROOT_PATH . '/public/css/style.css';
if (file_exists($cssFile)) {
    $cssSize = filesize($cssFile);
    step('ok', 'CSS Principal', "style.css — " . round($cssSize / 1024, 1) . ' KB', elapsed($t));
} else {
    step('warning', 'CSS Principal', 'public/css/style.css introuvable', elapsed($t));
}

$t = microtime(true);
$uploadsDir = ROOT_PATH . '/public/uploads';
if (is_dir($uploadsDir)) {
    $uploadFiles = count(glob($uploadsDir . '/*') ?: []);
    $writable    = is_writable($uploadsDir);
    step($writable ? 'ok' : 'warning', 'Uploads', "{$uploadFiles} fichier(s) — " . ($writable ? 'Accessible en écriture' : '⚠ Non accessible en écriture'), elapsed($t));
} else {
    if (!$opts['dry_run']) { @mkdir($uploadsDir, 0755, true); }
    step('warning', 'Uploads', 'Répertoire créé (était manquant)', elapsed($t));
}

// Permissions storage
$t = microtime(true);
$permDirs = ['storage/logs', 'storage/cache', 'storage/backups', 'storage/deployments'];
$permIssues = [];
foreach ($permDirs as $d) {
    $fullPath = ROOT_PATH . '/' . $d;
    if (!is_dir($fullPath)) { @mkdir($fullPath, 0755, true); }
    if (!is_writable($fullPath)) { $permIssues[] = $d; }
}
step(empty($permIssues) ? 'ok' : 'warning', 'Permissions', empty($permIssues) ? 'Tous les répertoires accessibles' : 'Problème sur : ' . implode(', ', $permIssues), elapsed($t));

// ════════════════════════════════════════════════════════════════════════════
// PHASE 9 — ROUTES
// ════════════════════════════════════════════════════════════════════════════
sep('PHASE 9 — ROUTES');

$t = microtime(true);
$routesFile = ROOT_PATH . '/routes/web.php';
if (file_exists($routesFile)) {
    $routesContent = file_get_contents($routesFile);
    $routeCount    = substr_count($routesContent, '$router->');
    $hasCatchAll   = str_contains($routesContent, '/{slug}');
    $hasHome       = str_contains($routesContent, "HomeController@index");
    $hasMenu       = str_contains($routesContent, "MenuController");
    step('ok', 'Routes fichier', "{$routeCount} routes — home:" . ($hasHome?'✓':'✗') . " menus:" . ($hasMenu?'✓':'✗') . " catch-all:" . ($hasCatchAll?'✓':'✗'), elapsed($t));
} else {
    step('error', 'Routes fichier', 'routes/web.php introuvable !', elapsed($t));
}

// index.php
$t = microtime(true);
$indexFile = ROOT_PATH . '/public/index.php';
if (file_exists($indexFile)) {
    $indexContent = file_get_contents($indexFile);
    $hasMaintMode  = str_contains($indexContent, 'maintenance.lock');
    $hasExcHandler = str_contains($indexContent, 'set_exception_handler');
    $hasShutdown   = str_contains($indexContent, 'register_shutdown_function');
    step('ok', 'index.php', "maintenance:" . ($hasMaintMode?'✓':'✗') . " exception_handler:" . ($hasExcHandler?'✓':'✗') . " shutdown:" . ($hasShutdown?'✓':'✗'), elapsed($t));
    if (!$hasExcHandler) {
        step('error', 'ALERTE index.php', 'set_exception_handler ABSENT — stack traces visibles en production ! git pull requis.', elapsed($t));
    }
} else {
    step('error', 'index.php', 'INTROUVABLE !', elapsed($t));
}

// ════════════════════════════════════════════════════════════════════════════
// PHASE 10 — HEALTH CHECK
// ════════════════════════════════════════════════════════════════════════════
sep('PHASE 10 — HEALTH CHECK');

$healthScore = 0;
$healthMax   = 0;

// DB
$t = microtime(true);
if ($pdo) {
    try {
        $pdo->query("SELECT 1");
        step('ok', 'Health: Database', 'Connexion active', elapsed($t));
        $healthScore++;
    } catch (\Throwable $e) {
        step('error', 'Health: Database', $e->getMessage(), elapsed($t));
    }
} else {
    step('error', 'Health: Database', 'PDO non initialisé', elapsed($t));
}
$healthMax++;

// Tables critiques
$t = microtime(true);
$criticalTables = ['pages', 'settings', 'menus', 'menu_items'];
$critMissing    = array_diff($criticalTables, $existingTables);
if (empty($critMissing)) {
    step('ok', 'Health: Tables', 'Tables critiques présentes', elapsed($t));
    $healthScore++;
} else {
    step('error', 'Health: Tables', 'Manquantes : ' . implode(', ', $critMissing), elapsed($t));
}
$healthMax++;

// menus.location
if ($pdo && in_array('menus', $existingTables)) {
    $t = microtime(true);
    try {
        $cols  = $pdo->query("SHOW COLUMNS FROM menus")->fetchAll(PDO::FETCH_COLUMN);
        $hasLoc = in_array('location', $cols);
        step($hasLoc ? 'ok' : 'error', 'Health: menus.location', $hasLoc ? 'Présente' : 'MANQUANTE — cause root de l\'incident', elapsed($t));
        if ($hasLoc) $healthScore++;
    } catch (\Throwable $e) {
        step('warning', 'Health: menus.location', $e->getMessage(), elapsed($t));
        $healthScore++;
    }
    $healthMax++;
}

// index.php exception handler
$t = microtime(true);
if (isset($indexContent) && str_contains($indexContent, 'set_exception_handler')) {
    step('ok', 'Health: ErrorHandler', 'Activé dans index.php', elapsed($t));
    $healthScore++;
} else {
    step('error', 'Health: ErrorHandler', 'ABSENT — nécessite git pull', elapsed($t));
}
$healthMax++;

// Storage writable
$t = microtime(true);
$logDir = ROOT_PATH . '/storage/logs';
if (is_dir($logDir) && is_writable($logDir)) {
    step('ok', 'Health: Storage', 'Accessible en écriture', elapsed($t));
    $healthScore++;
} else {
    step('warning', 'Health: Storage', 'storage/logs non accessible', elapsed($t));
}
$healthMax++;

$healthPct = $healthMax > 0 ? round(($healthScore / $healthMax) * 10) : 0;
echo "\n  SCORE SANTÉ : {$healthScore}/{$healthMax} — " . $healthPct . "/10\n";

// ════════════════════════════════════════════════════════════════════════════
// PHASE 11 — SMOKE TESTS HTTP
// ════════════════════════════════════════════════════════════════════════════
sep('PHASE 11 — SMOKE TESTS HTTP');

$appUrl = defined('APP_URL') ? APP_URL : 'https://digitaliumgroup.com';

$smokeUrls = [
    '/'            => 'Homepage',
    '/blog'        => 'Blog',
    '/realisations'=> 'Réalisations',
    '/sitemap.xml' => 'Sitemap',
];

$smokeOk = 0;
foreach ($smokeUrls as $path => $label) {
    $t   = microtime(true);
    $url = rtrim($appUrl, '/') . $path;
    $result = tryExec("curl -s -o /dev/null -w \"%{http_code}\" --max-time 10 --location " . escapeshellarg($url));
    $code   = trim($result['output']);
    $ok     = in_array($code, ['200', '301', '302']);
    step($ok ? 'ok' : 'error', "Smoke: {$label}", "HTTP {$code} — {$url}", elapsed($t));
    if ($ok) $smokeOk++;
}

echo "  SMOKE TESTS : {$smokeOk}/" . count($smokeUrls) . " OK\n";

// ════════════════════════════════════════════════════════════════════════════
// PHASE 12 — DÉSACTIVER MAINTENANCE
// ════════════════════════════════════════════════════════════════════════════
sep('PHASE 12 — FIN MAINTENANCE');

$hasCriticalError = $summary['error'] > 0;

if ($maintenanceActivated && !$opts['dry_run']) {
    $t = microtime(true);
    if (!$hasCriticalError || $opts['force']) {
        if (@unlink($maintenanceLock)) {
            step('ok', 'Maintenance Mode', 'Désactivé — site en ligne', elapsed($t));
        } else {
            step('warning', 'Maintenance Mode', 'Impossible de supprimer le fichier lock — suppression manuelle requise : rm storage/maintenance.lock', elapsed($t));
        }
    } else {
        step('warning', 'Maintenance Mode', "MAINTENU — {$summary['error']} erreur(s) critique(s) détectée(s). Utilisez --force pour forcer la désactivation.", elapsed($t));
    }
} else {
    step('skip', 'Maintenance Mode', $opts['dry_run'] ? 'DRY RUN' : 'Non activé');
}

// ════════════════════════════════════════════════════════════════════════════
// RAPPORT FINAL
// ════════════════════════════════════════════════════════════════════════════
produce_report:
$totalTime = round((microtime(true) - $startTime) * 1000);

sep('RAPPORT FINAL');

$statusGlobal = $summary['error'] > 0 ? 'ERREUR' : ($summary['warning'] > 0 ? 'WARNING' : 'OK');
$statusColor  = $summary['error'] > 0 ? "\033[31m" : ($summary['warning'] > 0 ? "\033[33m" : "\033[32m");
$reset        = "\033[0m";

echo "\n";
echo "{$statusColor}  STATUT GLOBAL : {$statusGlobal}{$reset}\n";
echo "  OK      : {$summary['ok']}\n";
echo "  WARNING : {$summary['warning']}\n";
echo "  ERREUR  : {$summary['error']}\n";
echo "  TOTAL   : {$summary['total']}\n";
echo "  DURÉE   : {$totalTime}ms\n";
echo "\n";

// Résumé des actions à effectuer si erreurs
if ($summary['error'] > 0) {
    echo "\033[31m  ACTIONS REQUISES :\033[0m\n";
    foreach ($steps as $s) {
        if ($s['status'] === 'error') {
            echo "  ✗ [{$s['label']}] {$s['message']}\n";
        }
    }
    echo "\n";
}

// Rapport JSON
$reportDir  = ROOT_PATH . '/storage/logs';
$reportJson = $reportDir . '/recovery_' . date('Y-m-d_H-i-s') . '.json';
if (!$opts['dry_run']) {
    @mkdir($reportDir, 0755, true);
    $reportData = [
        'status'      => strtolower($statusGlobal),
        'started_at'  => date('Y-m-d H:i:s', (int)$startTime),
        'finished_at' => date('Y-m-d H:i:s'),
        'duration_ms' => $totalTime,
        'summary'     => $summary,
        'health_score'=> ($healthScore ?? 0) . '/' . ($healthMax ?? 0),
        'smoke_ok'    => ($smokeOk ?? 0) . '/' . count($smokeUrls),
        'options'     => $opts,
        'steps'       => $steps,
        'php_version' => PHP_VERSION,
        'environment' => defined('ENVIRONMENT') ? ENVIRONMENT : 'inconnu',
    ];
    file_put_contents($reportJson, json_encode($reportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "  Rapport JSON : {$reportJson}\n";
}

// Rapport HTML
$reportHtml = str_replace('.json', '.html', $reportJson ?? '/dev/null');
if (!$opts['dry_run'] && isset($reportJson)) {
    $rows = '';
    foreach ($steps as $s) {
        $bg  = match($s['status']) { 'ok' => '#d1fae5', 'error' => '#fee2e2', 'warning' => '#fef3c7', default => '#f8fafc' };
        $clr = match($s['status']) { 'ok' => '#065f46', 'error' => '#991b1b', 'warning' => '#92400e', default => '#64748b' };
        $ico = match($s['status']) { 'ok' => '✓', 'error' => '✗', 'warning' => '⚠', default => '›' };
        $rows .= "<tr><td style='background:{$bg};color:{$clr};font-weight:700;padding:.4rem .75rem;'>{$ico} " . htmlspecialchars($s['status']) . "</td><td style='padding:.4rem .75rem;font-weight:600;'>" . htmlspecialchars($s['label']) . "</td><td style='padding:.4rem .75rem;color:#64748b;'>" . htmlspecialchars($s['message']) . "</td><td style='padding:.4rem .75rem;color:#94a3b8;font-family:monospace;'>" . $s['duration_ms'] . "ms</td></tr>";
    }
    $globalBg  = $summary['error'] > 0 ? '#fee2e2' : ($summary['warning'] > 0 ? '#fef3c7' : '#d1fae5');
    $globalClr = $summary['error'] > 0 ? '#991b1b' : ($summary['warning'] > 0 ? '#92400e' : '#065f46');
    $html = "<!DOCTYPE html><html lang='fr'><head><meta charset='UTF-8'><title>Recovery Report — Digitalium</title><style>body{font-family:system-ui,sans-serif;background:#f1f5f9;margin:0;padding:2rem;}h1{color:#1e293b;margin-bottom:.5rem;}table{width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 1px 8px rgba(0,0,0,.06);}th{background:#1e293b;color:#fff;padding:.6rem .75rem;text-align:left;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;}tr:nth-child(even){background:#f8fafc;}.banner{padding:1.5rem;border-radius:12px;margin-bottom:1.5rem;background:{$globalBg};color:{$globalClr};}pre{background:#0f172a;color:#e2e8f0;padding:1rem;border-radius:8px;font-size:.8rem;overflow-x:auto;}</style></head><body>";
    $html .= "<h1>Recovery Report — Digitalium Group</h1><p style='color:#64748b;margin-bottom:1.5rem;'>" . date('Y-m-d H:i:s') . " — Durée : {$totalTime}ms</p>";
    $html .= "<div class='banner'><strong>Statut : {$statusGlobal}</strong> — OK: {$summary['ok']} | Warning: {$summary['warning']} | Erreur: {$summary['error']}</div>";
    $html .= "<table><thead><tr><th>Statut</th><th>Étape</th><th>Message</th><th>Durée</th></tr></thead><tbody>{$rows}</tbody></table>";
    $html .= "</body></html>";
    file_put_contents($reportHtml, $html);
    echo "  Rapport HTML : {$reportHtml}\n";
}

echo "\n";
if (!$hasCriticalError) {
    echo "\033[32m✅ RECOVERY TERMINÉE AVEC SUCCÈS\033[0m\n";
    echo "   Le site est opérationnel.\n\n";
    exit(0);
} else {
    echo "\033[31m❌ RECOVERY INCOMPLÈTE — {$summary['error']} erreur(s) critique(s)\033[0m\n";
    echo "   Le mode maintenance reste ACTIF si activé.\n";
    echo "   Consultez le rapport pour les actions requises.\n\n";
    exit(1);
}
