<?php
namespace App\System;

use App\System\Migrations\SeoMigration;
use App\System\Migrations\HeroMigration;
use App\System\Migrations\FooterMigration;
use App\System\Migrations\MenuMigration;
use App\System\Migrations\ServicesMigration;
use App\System\Migrations\ProjectsMigration;
use App\System\Migrations\BlogMigration;
use App\System\Migrations\ContactMigration;
use App\System\Migrations\SettingsMigration;

/**
 * DeployPipeline — Orchestrateur Enterprise multi-modes.
 *
 * Chaque mode est une séquence de step-keys.
 * dispatch() résout chaque step-key vers son manager.
 *
 * Compatible CLI / HTTP / Webhook / Cron / GitHub Actions.
 */
class DeployPipeline {

    // ─── Définition des pipelines par mode ───────────────────────────────────
    public static array $modes = [
        'quick' => [
            'label'       => 'Quick Deploy',
            'description' => 'Assets + Cache + Routes + Tests HTTP',
            'steps'       => ['assets', 'cache_clear', 'routes_scan', 'http_tests'],
        ],
        'full' => [
            'label'       => 'Full Deploy',
            'description' => 'Pipeline complet — migration + sync + cache + santé',
            'steps'       => [
                'sql_migrate', 'business_migrate', 'cms_sync_all',
                'assets', 'cache_clear', 'uploads_scan',
                'routes_scan', 'integrity', 'health', 'http_tests', 'report',
            ],
        ],
        'production' => [
            'label'       => 'Production Deploy',
            'description' => 'Pipeline complet + sécurité + SEO + Git + Certification',
            'steps'       => [
                'backup', 'sql_migrate', 'business_migrate', 'cms_sync_all',
                'assets', 'cache_clear', 'cache_warm',
                'routes_scan', 'integrity', 'security', 'seo_sync',
                'uploads_scan', 'health', 'http_tests', 'performance',
                'self_heal', 'certify', 'git_commit', 'report',
            ],
        ],
        'repair' => [
            'label'       => 'Repair Mode',
            'description' => 'Auto-détection et correction des anomalies',
            'steps'       => [
                'self_heal', 'business_migrate', 'integrity',
                'cache_clear', 'routes_scan', 'report',
            ],
        ],
        'audit' => [
            'label'       => 'Audit Mode',
            'description' => 'Audit complet — routes, intégrité, sécurité, SEO, HTTP',
            'steps'       => [
                'routes_scan', 'integrity', 'security',
                'seo_sync', 'health', 'http_tests', 'performance', 'report',
            ],
        ],
        'development' => [
            'label'       => 'Development Mode',
            'description' => 'Migration + sync + routes (pas de cache warm ni backup)',
            'steps'       => [
                'sql_migrate', 'business_migrate', 'settings_sync',
                'cache_clear', 'routes_scan', 'integrity',
            ],
        ],
        'safe' => [
            'label'       => 'Safe Deploy',
            'description' => 'Backup d\'abord, puis migration conservative',
            'steps'       => [
                'health', 'backup',
                'sql_migrate', 'business_migrate', 'settings_sync',
                'cache_clear', 'routes_scan', 'report',
            ],
        ],
        'rollback' => [
            'label'       => 'Rollback',
            'description' => 'Liste les backups disponibles + clear cache',
            'steps'       => ['backup_list', 'cache_clear', 'report'],
        ],
    ];

    // ─── State interne ───────────────────────────────────────────────────────
    private static array $steps   = [];
    private static float $startMs = 0;
    private static string $baseUrl = '';

    // ─── Modes qui déclenchent le pre-deploy check ───────────────────────────
    private const MODES_WITH_PRECHECK = ['production', 'full', 'safe', 'quick'];
    // ─── Modes où un check critique ANNULE le pipeline ───────────────────────
    private const MODES_ABORT_ON_CRITICAL = ['production', 'full'];

    // ─── Point d'entrée public ───────────────────────────────────────────────
    public static function run(string $mode = 'full', array $options = []): array {
        self::$steps   = [];
        self::$startMs = DSMResult::timer();
        self::$baseUrl = $options['base_url'] ?? self::detectBaseUrl();

        $pipelineDef = self::$modes[$mode] ?? self::$modes['full'];
        $stepKeys    = $pipelineDef['steps'];

        // ── Phase 10 : Pre-deploy BootCheck ──────────────────────────────────
        if (in_array($mode, self::MODES_WITH_PRECHECK)) {
            $boot       = \App\Services\BootCheck::run();
            $bootStatus = $boot['critical'] ? 'error' : ($boot['ok'] ? 'ok' : 'warning');
            self::$steps[] = DSMResult::make(
                $bootStatus,
                'Pre-Deploy Check',
                $boot['summary'],
                ['counts' => $boot['counts'], 'checks' => $boot['checks']],
                [],
                0
            );

            // Abort production/full if a CRITICAL check failed
            if ($boot['critical'] && in_array($mode, self::MODES_ABORT_ON_CRITICAL)) {
                $failedKeys = array_keys(array_filter(
                    $boot['checks'],
                    fn($c) => ($c['status'] === 'error') && ($c['critical'] ?? false)
                ));
                self::$steps[] = DSMResult::error(
                    'Pipeline Annulé',
                    'Déploiement annulé — checks critiques échoués : ' . implode(', ', $failedKeys) . '. Corriger avant de relancer.',
                    ['failed' => $failedKeys]
                );
                return self::buildSummary($mode, $pipelineDef['label']);
            }
        }

        foreach ($stepKeys as $key) {
            self::execute($key);
        }

        // Auto-tag si production + succès
        if ($mode === 'production' && empty(array_filter(self::$steps, fn($r) => ($r['status'] ?? '') === 'error'))) {
            self::execute('git_tag');
        }

        return self::buildSummary($mode, $pipelineDef['label']);
    }

    // ─── Exécution d'un step ─────────────────────────────────────────────────
    private static function execute(string $key): void {
        $t = DSMResult::timer();
        try {
            $result = self::dispatch($key);
            // Flatten les résultats agrégés (ex: business_migrate retourne aggregate)
            if (isset($result['data']['results']) && is_array($result['data']['results'])) {
                foreach ($result['data']['results'] as $sub) {
                    self::$steps[] = $sub;
                }
                // Ajouter aussi le résumé agrégé
                self::$steps[] = [
                    'status'      => $result['status'],
                    'label'       => $result['label'] . ' [total]',
                    'message'     => $result['message'],
                    'duration_ms' => DSMResult::elapsed($t),
                    'timestamp'   => date('Y-m-d H:i:s'),
                ];
            } else {
                self::$steps[] = $result;
            }
        } catch (\Throwable $e) {
            self::$steps[] = DSMResult::error("Step: {$key}", $e->getMessage(), [], [], DSMResult::elapsed($t));
        }
    }

    // ─── Dispatch step-key → manager ─────────────────────────────────────────
    private static function dispatch(string $key): array {
        return match ($key) {
            // ── Migrations ──────────────────────────────────────
            'sql_migrate'      => MigrationManager::run(),
            'business_migrate' => BusinessMigrationManager::run(),
            'settings_sync'    => SettingsMigration::run(),
            'seo_sync'         => SeoMigration::run(),

            // ── CMS Sync (toutes migrations métier en séquence) ─
            'cms_sync_all'     => self::syncAll(),

            // ── Assets & Cache ───────────────────────────────────
            'assets'           => AssetManager::check(),
            'cache_clear'      => CacheManager::clear(),
            'cache_warm'       => CacheManager::warmup(self::$baseUrl),

            // ── Health & Integrity ───────────────────────────────
            'health'           => self::runHealth(),
            'integrity'        => IntegrityManager::verify(),
            'uploads_scan'     => HealthManager::checkUploads(),

            // ── Routes & HTTP ────────────────────────────────────
            'routes_scan'      => RouteManager::scan(),
            'http_tests'       => RouteManager::httpTest(self::$baseUrl),

            // ── Security & SEO ───────────────────────────────────
            'security'         => AuditManager::run(),

            // ── Performance ──────────────────────────────────────
            'performance'      => PerformanceManager::runTests(self::$baseUrl),

            // ── Self-Heal ────────────────────────────────────────
            'self_heal'        => SelfHealManager::run(),

            // ── Backup ───────────────────────────────────────────
            'backup'           => BackupManager::create(),
            'backup_list'      => BackupManager::list(),

            // ── Git ──────────────────────────────────────────────
            'git_commit'       => GitManager::commitAndPush('DSM Auto-deploy [' . date('Y-m-d H:i') . ']'),
            'git_tag'          => GitManager::createTag('v' . date('Y.m.d.His'), 'DSM Production Deploy'),

            // ── Rapport ──────────────────────────────────────────
            'report'           => self::generateReport(),

            // ── Certification ─────────────────────────────────────
            'certify'          => self::certify(),

            // ── Pre-deploy (step standalone) ──────────────────────
            'pre_check'        => self::runBootCheck(),

            default            => DSMResult::error($key, "Step '{$key}' inconnu dans le pipeline"),
        };
    }

    // ─── Helpers internes ────────────────────────────────────────────────────
    private static function syncAll(): array {
        $t       = DSMResult::timer();
        $results = [
            HeroMigration::run(),
            FooterMigration::run(),
            MenuMigration::run(),
            ServicesMigration::run(),
            ProjectsMigration::run(),
            BlogMigration::run(),
            SeoMigration::run(),
            ContactMigration::run(),
        ];
        return DSMResult::aggregate('CMS Sync', $results);
    }

    private static function runHealth(): array {
        $checks = HealthManager::check();
        $score  = HealthManager::score($checks);
        $status = $score >= 8 ? 'ok' : ($score >= 5 ? 'warning' : 'error');
        return DSMResult::make($status, 'Health Check', "Score santé: {$score}/10", ['score' => $score, 'checks' => $checks]);
    }

    private static function certify(): array {
        $checks    = HealthManager::check();
        $score     = HealthManager::score($checks);
        $certified = $score >= 7;
        $status    = $certified ? 'ok' : 'warning';
        return DSMResult::make(
            $status, 'Certification',
            $certified ? "CERTIFIÉ — Score {$score}/10" : "NON CERTIFIÉ — Score insuffisant {$score}/10 (min 7)",
            ['score' => $score, 'certified' => $certified, 'threshold' => 7]
        );
    }

    private static function generateReport(): array {
        return ReportManager::generate('Pipeline Deploy', self::$steps, [
            'base_url' => self::$baseUrl,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }

    private static function buildSummary(string $mode, string $label): array {
        $totalMs   = DSMResult::elapsed(self::$startMs);
        $okCount   = count(array_filter(self::$steps, fn($r) => ($r['status'] ?? '') === 'ok'));
        $errCount  = count(array_filter(self::$steps, fn($r) => ($r['status'] ?? '') === 'error'));
        $warnCount = count(array_filter(self::$steps, fn($r) => ($r['status'] ?? '') === 'warning'));
        $total     = count(self::$steps);

        $globalStatus = $errCount > 0 ? 'error' : ($warnCount > 0 ? 'warning' : 'ok');

        return [
            'status'      => $globalStatus,
            'mode'        => $mode,
            'label'       => $label,
            'message'     => "{$okCount} OK — {$warnCount} WARNING — {$errCount} ERROR / {$total} étapes",
            'steps'       => self::$steps,
            'duration_ms' => round($totalMs, 2),
            'timestamp'   => date('Y-m-d H:i:s'),
            'ok'          => $okCount,
            'warning'     => $warnCount,
            'error'       => $errCount,
            'total'       => $total,
        ];
    }

    private static function runBootCheck(): array {
        $boot   = \App\Services\BootCheck::run();
        $status = $boot['critical'] ? 'error' : ($boot['ok'] ? 'ok' : 'warning');
        return DSMResult::make($status, 'Boot Check', $boot['summary'], ['checks' => $boot['checks']]);
    }

    private static function detectBaseUrl(): string {
        $s = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $s . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }
}
