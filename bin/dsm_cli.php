#!/usr/bin/env php
<?php
/**
 * DSM CLI — Digitalium System Manager Command Line Interface
 *
 * Usage:
 *   php bin/dsm_cli.php deploy [mode]
 *   php bin/dsm_cli.php health
 *   php bin/dsm_cli.php migrate
 *   php bin/dsm_cli.php heal
 *   php bin/dsm_cli.php backup
 *   php bin/dsm_cli.php git:info
 *   php bin/dsm_cli.php git:commit [message]
 *   php bin/dsm_cli.php git:tag [tag]
 *   php bin/dsm_cli.php modes
 *   php bin/dsm_cli.php help
 *
 * Compatible : CLI, Cron, SSH, GitHub Actions, Webhook POST body
 */

// ─── Bootstrap ───────────────────────────────────────────────────────────────
define('CLI_MODE', true);

$root = dirname(__DIR__);
if (!file_exists($root . '/public/index.php')) {
    echo "Erreur: Exécuter depuis " . $root . "\n";
    exit(1);
}

require_once $root . '/vendor/autoload.php'  ?: null;
// Bootstrap manuel — même chemin que public/index.php
$bootstrapFile = $root . '/public/index.php';
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', $root);
}

// Charger config
if (file_exists($root . '/config/app.php')) {
    require_once $root . '/config/app.php';
}
if (file_exists($root . '/.env')) {
    $lines = file($root . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        putenv(trim($k) . '=' . trim($v));
        if (!defined(trim($k))) define(trim($k), trim($v));
    }
}

if (!defined('APP_PATH'))    define('APP_PATH',    $root . '/app');
if (!defined('PUBLIC_PATH')) define('PUBLIC_PATH', $root . '/public');
if (!defined('UPLOAD_PATH')) define('UPLOAD_PATH', $root . '/public/assets/uploads');
if (!defined('ENVIRONMENT')) define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'production');

// Autoloader PSR-4
spl_autoload_register(function (string $class) use ($root) {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;
    $file = $root . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (file_exists($file)) require_once $file;
});

// DB si disponible
if (defined('DB_HOST') && defined('DB_NAME')) {
    try { \App\Services\Database::connect(); } catch (\Throwable $e) {}
}

use App\System\DeployPipeline;
use App\System\HealthManager;
use App\System\MigrationManager;
use App\System\BusinessMigrationManager;
use App\System\SelfHealManager;
use App\System\BackupManager;
use App\System\GitManager;
use App\System\DSMResult;

// ─── CLI Parser ───────────────────────────────────────────────────────────────
$command = $argv[1] ?? 'help';
$arg1    = $argv[2] ?? '';
$arg2    = $argv[3] ?? '';

// ─── Output helpers ──────────────────────────────────────────────────────────
function cliColor(string $status): string {
    return match ($status) {
        'ok'      => "\033[32m", // green
        'warning' => "\033[33m", // yellow
        'error'   => "\033[31m", // red
        default   => "\033[37m",
    };
}
function ansiReset(): string { return "\033[0m"; }

function printResult(array $result, int $indent = 0): void {
    $pad    = str_repeat('  ', $indent);
    $status = $result['status'] ?? 'ok';
    $label  = $result['label'] ?? '';
    $msg    = $result['message'] ?? '';
    $ms     = isset($result['duration_ms']) ? round($result['duration_ms']) . 'ms' : '';

    $icon = match ($status) { 'ok' => '✓', 'warning' => '⚠', 'error' => '✗', default => '·' };
    $col  = cliColor($status);
    $rst  = ansiReset();

    echo "{$pad}{$col}{$icon}{$rst} {$label} — {$msg}" . ($ms ? " [{$ms}]" : "") . "\n";

    if (!empty($result['errors'])) {
        foreach ($result['errors'] as $e) {
            echo "{$pad}  {$col}  → {$e}{$rst}\n";
        }
    }
}

function printSummary(array $result): void {
    $s   = $result['status'] ?? 'ok';
    $lbl = $result['label']   ?? '';
    $msg = $result['message'] ?? '';
    $col = cliColor($s);
    $rst = ansiReset();
    echo "\n{$col}══════════════════════════════════════{$rst}\n";
    echo $col . strtoupper($s) . $rst . " — " . $lbl . "\n";
    echo $msg . "\n";
    if (isset($result['duration_ms'])) echo "Durée: " . round($result['duration_ms']) . "ms\n";
    echo "{$col}══════════════════════════════════════{$rst}\n\n";
}

// ─── JSON output (webhook/GitHub Actions) ─────────────────────────────────────
$jsonOutput = in_array('--json', $argv);

// ─── Commands ─────────────────────────────────────────────────────────────────
switch ($command) {

    case 'deploy':
        $mode = $arg1 ?: 'full';
        if (!array_key_exists($mode, DeployPipeline::$modes)) {
            echo "Mode inconnu: {$mode}\nModes disponibles: " . implode(', ', array_keys(DeployPipeline::$modes)) . "\n";
            exit(1);
        }
        echo "🚀 DSM Deploy — Mode: {$mode}\n\n";
        $result = DeployPipeline::run($mode, ['base_url' => getenv('APP_URL') ?: 'http://localhost']);
        if ($jsonOutput) { echo json_encode($result, JSON_PRETTY_PRINT); break; }
        foreach ($result['steps'] ?? [] as $step) { printResult($step, 1); }
        printSummary($result);
        exit($result['status'] === 'error' ? 1 : 0);

    case 'health':
        $checks = HealthManager::check();
        $score  = HealthManager::score($checks);
        $git    = GitManager::healthCheck();
        $checks[] = $git;
        echo "🏥 Health Check — Score: {$score}/10\n\n";
        if ($jsonOutput) { echo json_encode(['score' => $score, 'checks' => $checks], JSON_PRETTY_PRINT); break; }
        foreach ($checks as $c) { printResult($c, 1); }
        echo "\nScore: {$score}/10\n";
        exit($score >= 7 ? 0 : 1);

    case 'migrate':
        echo "🗄  Migration SQL\n\n";
        $result = MigrationManager::run();
        if ($jsonOutput) { echo json_encode($result, JSON_PRETTY_PRINT); break; }
        printResult($result);
        exit($result['status'] === 'error' ? 1 : 0);

    case 'business-migrate':
        $name = $arg1 ?: null;
        echo "🗂  Migrations Métier" . ($name ? " — {$name}" : " (toutes)") . "\n\n";
        $result = BusinessMigrationManager::run($name);
        if ($jsonOutput) { echo json_encode($result, JSON_PRETTY_PRINT); break; }
        printResult($result);
        exit($result['status'] === 'error' ? 1 : 0);

    case 'heal':
        echo "🔧 Self-Heal\n\n";
        $result = SelfHealManager::run();
        if ($jsonOutput) { echo json_encode($result, JSON_PRETTY_PRINT); break; }
        foreach ($result['data']['results'] ?? [] as $r) { printResult($r, 1); }
        printSummary($result);
        exit($result['status'] === 'error' ? 1 : 0);

    case 'backup':
        echo "💾 Backup DB\n\n";
        $result = BackupManager::create();
        if ($jsonOutput) { echo json_encode($result, JSON_PRETTY_PRINT); break; }
        printResult($result);
        exit($result['status'] === 'error' ? 1 : 0);

    case 'git:info':
        $info = GitManager::getInfo();
        if ($jsonOutput) { echo json_encode($info, JSON_PRETTY_PRINT); break; }
        echo "Branch:  {$info['branch']}\n";
        echo "Commit:  {$info['commit']}\n";
        echo "Message: {$info['message']}\n";
        echo "Date:    {$info['date']}\n";
        echo "Tag:     {$info['tag']}\n";
        echo "Status:  {$info['status']}\n";
        echo "Remote:  {$info['remote']}\n";
        break;

    case 'git:commit':
        $msg    = $arg1 ?: 'DSM CLI commit — ' . date('Y-m-d H:i:s');
        $result = GitManager::commitAndPush($msg);
        if ($jsonOutput) { echo json_encode($result, JSON_PRETTY_PRINT); break; }
        printResult($result);
        exit($result['status'] === 'error' ? 1 : 0);

    case 'git:tag':
        $tag    = $arg1 ?: 'v' . date('Y.m.d.His');
        $msg    = $arg2 ?: "DSM CLI Tag — {$tag}";
        $result = GitManager::createTag($tag, $msg);
        if ($jsonOutput) { echo json_encode($result, JSON_PRETTY_PRINT); break; }
        printResult($result);
        exit($result['status'] === 'error' ? 1 : 0);

    case 'modes':
        echo "Pipeline Modes disponibles:\n\n";
        foreach (DeployPipeline::$modes as $key => $def) {
            echo "  \033[36m{$key}\033[0m — {$def['label']}\n";
            echo "    {$def['description']}\n";
            echo "    Steps: " . implode(' → ', $def['steps']) . "\n\n";
        }
        break;

    case 'help':
    default:
        echo <<<HELP
Digitalium System Manager CLI — DSM v1.1

Usage: php bin/dsm_cli.php <command> [args] [--json]

Commands:
  deploy [mode]           Lance le pipeline en mode: quick|full|production|repair|audit|development|safe|rollback
  health                  Vérifie la santé du système
  migrate                 Exécute la migration SQL
  business-migrate [name] Exécute les migrations métier (toutes ou une seule)
  heal                    Lance le moteur self-heal
  backup                  Crée un backup de la base de données
  git:info                Affiche les informations Git
  git:commit [message]    Commit + push les changements
  git:tag [tag] [msg]     Crée et push un tag Git
  modes                   Liste tous les modes de déploiement
  help                    Affiche cette aide

Options:
  --json                  Sortie JSON (pour GitHub Actions, webhooks, etc.)

Examples:
  php bin/dsm_cli.php deploy full
  php bin/dsm_cli.php deploy production --json
  php bin/dsm_cli.php health
  php bin/dsm_cli.php git:tag v1.2.0 "Release 1.2.0"

HELP;
        break;
}
