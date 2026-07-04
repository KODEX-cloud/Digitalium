<?php
namespace App\Services;

/**
 * BootCheck — Vérification pré-boot au démarrage de l'application.
 *
 * Chaque check est isolé : un échec retourne un statut dégradé,
 * jamais une exception fatale. Utilisé par DSM HealthCheck et le
 * pipeline DeployPipeline pour valider l'état avant déploiement.
 */
class BootCheck {

    private static array $results = [];
    private static bool  $ran     = false;

    // ─── Tables minimales requises pour le frontend ───────────────────────────
    private const REQUIRED_TABLES = [
        'pages', 'sections', 'blocks', 'settings',
        'menus', 'menu_items', 'media',
    ];

    // ─── Dossiers qui doivent exister et être accessibles en écriture ─────────
    private const REQUIRED_DIRS = [
        'storage/logs',
        'storage/cache',
        'public/uploads',
    ];

    // ─── Point d'entrée principal ─────────────────────────────────────────────

    /**
     * Exécute tous les checks et retourne un résumé.
     * Idempotent dans la même requête (résultats mis en cache statique).
     *
     * @return array{ok: bool, critical: bool, checks: array, summary: string}
     */
    public static function run(): array {
        if (self::$ran) {
            return self::buildSummary();
        }

        self::$results = [];
        self::$ran = true;

        self::checkPhp();
        self::checkConstants();
        self::checkDatabase();
        self::checkTables();
        self::checkDirectories();
        self::checkConfig();
        self::checkSession();

        return self::buildSummary();
    }

    /**
     * Retourne true uniquement si tous les checks critiques passent.
     * Non-critique = warning, n'empêche pas le boot.
     */
    public static function isCriticalOk(): bool {
        if (!self::$ran) self::run();
        foreach (self::$results as $check) {
            if (($check['critical'] ?? false) && ($check['status'] ?? '') === 'error') {
                return false;
            }
        }
        return true;
    }

    // ─── Checks individuels ───────────────────────────────────────────────────

    private static function checkPhp(): void {
        $version = PHP_VERSION;
        $ok = version_compare($version, '8.0.0', '>=');
        self::add('php_version', $ok ? 'ok' : 'error', 'PHP Version',
            $ok ? "PHP {$version} ✓" : "PHP {$version} — requis 8.0+",
            critical: true
        );
    }

    private static function checkConstants(): void {
        $required = ['ROOT_PATH', 'APP_PATH', 'DB_HOST', 'DB_NAME', 'DB_USER', 'ENVIRONMENT'];
        $missing  = [];
        foreach ($required as $const) {
            if (!defined($const)) $missing[] = $const;
        }
        $ok = empty($missing);
        self::add('constants', $ok ? 'ok' : 'error', 'Config Constants',
            $ok ? 'Toutes les constantes définies' : 'Manquantes : ' . implode(', ', $missing),
            critical: true
        );
    }

    private static function checkDatabase(): void {
        try {
            $pdo = Database::getConnection();
            $pdo->query('SELECT 1');
            self::add('db_connection', 'ok', 'Connexion DB', 'Connexion MySQL établie', critical: true);
        } catch (\Throwable $e) {
            self::add('db_connection', 'error', 'Connexion DB',
                ENVIRONMENT === 'development' ? $e->getMessage() : 'Impossible de se connecter à la base de données',
                critical: true
            );
        }
    }

    private static function checkTables(): void {
        try {
            $pdo    = Database::getConnection();
            $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
            $missing = array_diff(self::REQUIRED_TABLES, $tables);
            $ok = empty($missing);
            self::add('db_tables', $ok ? 'ok' : 'warning', 'Tables DB',
                $ok
                    ? count(self::REQUIRED_TABLES) . ' tables requises présentes'
                    : 'Tables manquantes : ' . implode(', ', $missing),
                critical: false
            );
        } catch (\Throwable $e) {
            self::add('db_tables', 'warning', 'Tables DB', 'Vérification impossible (DB hors ligne)', critical: false);
        }
    }

    private static function checkDirectories(): void {
        $root    = defined('ROOT_PATH') ? ROOT_PATH : dirname(dirname(__DIR__));
        $errors  = [];
        $created = [];

        foreach (self::REQUIRED_DIRS as $rel) {
            $path = $root . '/' . $rel;
            if (!is_dir($path)) {
                if (@mkdir($path, 0755, true)) {
                    $created[] = $rel;
                } else {
                    $errors[] = $rel . ' (création impossible)';
                }
            } elseif (!is_writable($path)) {
                $errors[] = $rel . ' (non accessible en écriture)';
            }
        }

        $status = empty($errors) ? 'ok' : 'warning';
        $msg = empty($errors)
            ? (empty($created) ? 'Tous les dossiers OK' : 'Créés : ' . implode(', ', $created))
            : 'Problèmes : ' . implode('; ', $errors);

        self::add('directories', $status, 'Dossiers storage', $msg, critical: false);
    }

    private static function checkConfig(): void {
        try {
            $configPath = defined('ROOT_PATH') ? ROOT_PATH . '/config/config.php' : null;
            $ok = $configPath && file_exists($configPath);
            self::add('config_file', $ok ? 'ok' : 'warning', 'Fichier config',
                $ok ? 'config/config.php chargé' : 'config/config.php introuvable',
                critical: false
            );
        } catch (\Throwable $e) {
            self::add('config_file', 'warning', 'Fichier config', 'Vérification échouée', critical: false);
        }
    }

    private static function checkSession(): void {
        try {
            $status = session_status();
            $ok     = in_array($status, [PHP_SESSION_ACTIVE, PHP_SESSION_NONE]);
            self::add('session', $ok ? 'ok' : 'warning', 'Sessions PHP',
                $ok ? 'Sessions PHP opérationnelles' : 'Session non démarrée',
                critical: false
            );
        } catch (\Throwable $e) {
            self::add('session', 'warning', 'Sessions PHP', 'Vérification échouée', critical: false);
        }
    }

    // ─── Helpers internes ─────────────────────────────────────────────────────

    private static function add(
        string $key,
        string $status,
        string $label,
        string $message,
        bool   $critical = false
    ): void {
        self::$results[$key] = compact('status', 'label', 'message', 'critical');
    }

    private static function buildSummary(): array {
        $errors   = 0;
        $warnings = 0;
        $critical = false;

        foreach (self::$results as $c) {
            if ($c['status'] === 'error') {
                $errors++;
                if ($c['critical']) $critical = true;
            } elseif ($c['status'] === 'warning') {
                $warnings++;
            }
        }

        $ok = $errors === 0;
        $summary = $ok
            ? ($warnings > 0 ? "OK avec {$warnings} avertissement(s)" : 'Tous les checks OK')
            : "{$errors} erreur(s) critique(s)";

        return [
            'ok'       => $ok,
            'critical' => $critical,
            'checks'   => self::$results,
            'summary'  => $summary,
            'counts'   => ['ok' => count(self::$results) - $errors - $warnings, 'warnings' => $warnings, 'errors' => $errors],
        ];
    }

    /**
     * Retourne les résultats du dernier run (sans relancer les checks).
     */
    public static function getResults(): array {
        if (!self::$ran) return self::run();
        return self::buildSummary();
    }
}
