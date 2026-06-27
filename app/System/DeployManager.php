<?php
namespace App\System;

/**
 * DeployManager — Orchestrateur principal du DSM.
 * Exécute les 22 étapes du cycle de déploiement complet.
 */
class DeployManager {
    private static array $log     = [];
    private static float $startMs = 0;

    public static function run(array $options = []): array {
        self::$log     = [];
        self::$startMs = DSMResult::timer();

        $baseUrl = $options['base_url'] ?? self::detectBaseUrl();
        $full    = $options['full'] ?? true;

        // ──────────────────────────────────────────────
        // ÉTAPE 1 — Migration SQL
        // ──────────────────────────────────────────────
        self::step('1/22', 'Migration SQL', function () {
            return MigrationManager::run();
        });

        // ──────────────────────────────────────────────
        // ÉTAPE 2 — Migrations métier
        // ──────────────────────────────────────────────
        self::step('2/22', 'Migrations Métier', function () {
            return BusinessMigrationManager::run();
        });

        // ──────────────────────────────────────────────
        // ÉTAPES 3–11 — Synchronisations CMS
        // ──────────────────────────────────────────────
        self::step('3/22', 'Sync Pages',       fn() => self::syncEntity('pages'));
        self::step('4/22', 'Sync Hero',        fn() => self::syncEntity('hero'));
        self::step('5/22', 'Sync Footer',      fn() => self::syncEntity('footer'));
        self::step('6/22', 'Sync Menus',       fn() => self::syncEntity('menus'));
        self::step('7/22', 'Sync Services',    fn() => self::syncEntity('services'));
        self::step('8/22', 'Sync Réalisations',fn() => self::syncEntity('projects'));
        self::step('9/22', 'Sync Blog',        fn() => self::syncEntity('blog'));
        self::step('10/22','Sync SEO',         fn() => \App\System\Migrations\SeoMigration::run());
        self::step('11/22','Sync Médias',      fn() => MediaAuditManager::audit());

        // ──────────────────────────────────────────────
        // ÉTAPE 12 — Assets
        // ──────────────────────────────────────────────
        self::step('12/22', 'Assets', function () {
            return AssetManager::check();
        });

        // ──────────────────────────────────────────────
        // ÉTAPE 13 — Vérification uploads
        // ──────────────────────────────────────────────
        self::step('13/22', 'Vérification Uploads', function () {
            return HealthManager::checkUploads();
        });

        // ──────────────────────────────────────────────
        // ÉTAPE 14 — Nettoyage cache
        // ──────────────────────────────────────────────
        self::step('14/22', 'Cache Clear', function () {
            return CacheManager::clear();
        });

        // ──────────────────────────────────────────────
        // ÉTAPE 15 — Warm cache
        // ──────────────────────────────────────────────
        self::step('15/22', 'Cache Warmup', function () use ($baseUrl) {
            return CacheManager::warmup($baseUrl);
        });

        // ──────────────────────────────────────────────
        // ÉTAPE 16 — Scan routes
        // ──────────────────────────────────────────────
        self::step('16/22', 'Scan Routes', function () {
            return RouteManager::scan();
        });

        // ──────────────────────────────────────────────
        // ÉTAPE 17 — Audit intégrité
        // ──────────────────────────────────────────────
        self::step('17/22', 'Audit Intégrité', function () {
            return IntegrityManager::verify();
        });

        // ──────────────────────────────────────────────
        // ÉTAPE 18 — Audit sécurité
        // ──────────────────────────────────────────────
        self::step('18/22', 'Audit Sécurité', function () {
            return AuditManager::run();
        });

        // ──────────────────────────────────────────────
        // ÉTAPE 19 — Tests HTTP
        // ──────────────────────────────────────────────
        self::step('19/22', 'Tests HTTP', function () use ($baseUrl) {
            return RouteManager::httpTest($baseUrl);
        });

        // ──────────────────────────────────────────────
        // ÉTAPE 20 — Health check complet
        // ──────────────────────────────────────────────
        self::step('20/22', 'Health Check', function () {
            $checks = HealthManager::check();
            $score  = HealthManager::score($checks);
            return DSMResult::ok('Health', "Score santé: {$score}/10",
                ['score' => $score, 'checks' => $checks]
            );
        });

        // ──────────────────────────────────────────────
        // ÉTAPE 21 — Génération rapport
        // ──────────────────────────────────────────────
        $report = ReportManager::generate('Deploy Complet', self::$log, [
            'base_url' => $baseUrl,
            'options'  => $options,
        ]);
        self::$log[] = $report;

        // ──────────────────────────────────────────────
        // ÉTAPE 22 — Mise à jour PROJECT_STATE
        // ──────────────────────────────────────────────
        self::step('22/22', 'ProjectState Update', function () {
            return ProjectStateManager::update(self::$log);
        });

        // ──────────────────────────────────────────────
        // RÉSUMÉ FINAL
        // ──────────────────────────────────────────────
        $totalMs  = DSMResult::elapsed(self::$startMs);
        $okCount  = count(array_filter(self::$log, fn($r) => ($r['status'] ?? '') === 'ok'));
        $errCount = count(array_filter(self::$log, fn($r) => ($r['status'] ?? '') === 'error'));
        $warnCount= count(array_filter(self::$log, fn($r) => ($r['status'] ?? '') === 'warning'));
        $total    = count(self::$log);

        $globalStatus = $errCount > 0 ? 'error' : ($warnCount > 0 ? 'warning' : 'ok');

        return [
            'status'       => $globalStatus,
            'label'        => 'Deploy Complet',
            'message'      => "{$okCount} OK — {$warnCount} WARNING — {$errCount} ERROR / {$total} étapes",
            'steps'        => self::$log,
            'duration_ms'  => round($totalMs, 2),
            'timestamp'    => date('Y-m-d H:i:s'),
            'ok'           => $okCount,
            'warning'      => $warnCount,
            'error'        => $errCount,
            'total'        => $total,
        ];
    }

    private static function step(string $num, string $label, callable $fn): void {
        try {
            $result = $fn();
            // Flatten aggregate results
            if (isset($result['data']['results'])) {
                foreach ($result['data']['results'] as $sub) {
                    self::$log[] = $sub;
                }
            } else {
                self::$log[] = $result;
            }
        } catch (\Exception $e) {
            self::$log[] = DSMResult::error($label, "Exception étape {$num}: " . $e->getMessage());
        }
    }

    private static function syncEntity(string $entity): array {
        $t = DSMResult::timer();
        try {
            $count = match ($entity) {
                'pages'    => \App\Services\Database::fetch("SELECT COUNT(*) as n FROM pages")['n'] ?? 0,
                'hero'     => \App\Services\Database::fetch("SELECT COUNT(*) as n FROM sections WHERE type LIKE '%hero%'")['n'] ?? 0,
                'footer'   => \App\Services\Database::fetch("SELECT COUNT(*) as n FROM settings WHERE setting_key LIKE 'footer%'")['n'] ?? 0,
                'menus'    => \App\Services\Database::fetch("SELECT COUNT(*) as n FROM menu_items")['n'] ?? 0,
                'services' => \App\Services\Database::fetch("SELECT COUNT(*) as n FROM sections WHERE type LIKE '%service%'")['n'] ?? 0,
                'projects' => \App\Services\Database::fetch("SELECT COUNT(*) as n FROM projects")['n'] ?? 0,
                'blog'     => \App\Services\Database::fetch("SELECT COUNT(*) as n FROM blog_posts")['n'] ?? 0,
                default    => 0,
            };
            return DSMResult::ok("Sync " . ucfirst($entity), "Synchronisé — {$count} entrée(s)", ['count' => $count], DSMResult::elapsed($t));
        } catch (\Exception $e) {
            return DSMResult::error("Sync " . ucfirst($entity), $e->getMessage(), [], [], DSMResult::elapsed($t));
        }
    }

    private static function detectBaseUrl(): string {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host;
    }
}
