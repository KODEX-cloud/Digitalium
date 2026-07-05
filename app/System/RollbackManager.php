<?php
namespace App\System;

use App\Services\Database;

/**
 * RollbackManager — Backup SQL + config avant déploiement.
 * Restore sur échec critique.
 *
 * Backup : storage/backups/{id}/  → database.sql + manifest.json
 * Restore : TRUNCATE + INSERT dans le bon ordre FK
 * Idempotent : chaque backup a un ID unique timestamp
 */
class RollbackManager {

    private const BACKUP_DIR   = ROOT_PATH . '/storage/backups';
    private const MAX_BACKUPS  = 10;

    // Tables dans l'ordre d'insertion (respect des FK)
    private const TABLE_ORDER = [
        'users', 'pages', 'settings', 'media',
        'sections', 'blocks', 'hero_slides',
        'projects', 'blog_categories', 'blog_posts',
        'blog_tags', 'blog_post_tags', 'blog_comments',
        'contact_messages', 'menus', 'menu_items',
    ];

    // ─── Création d'un point de rollback ─────────────────────────────────────

    public static function create(): array {
        $t   = DSMResult::timer();
        $id  = 'backup_' . date('Y-m-d_H-i-s');
        $dir = self::BACKUP_DIR . '/' . $id;

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $tables  = [];
        $rows    = [];
        $errors  = [];

        try {
            $pdo = Database::getConnection();
            $sql = self::generateSqlDump($pdo, $tables, $rows);
            file_put_contents($dir . '/database.sql', $sql);
        } catch (\Throwable $e) {
            $errors[] = 'SQL dump: ' . $e->getMessage();
        }

        // Config backup
        $configSrc = ROOT_PATH . '/config/config.php';
        if (file_exists($configSrc)) {
            @copy($configSrc, $dir . '/config.php.bak');
        }

        $manifest = [
            'id'         => $id,
            'created_at' => date('Y-m-d H:i:s'),
            'tables'     => $tables,
            'rows'       => $rows,
            'size_bytes' => file_exists($dir . '/database.sql') ? filesize($dir . '/database.sql') : 0,
            'errors'     => $errors,
        ];
        file_put_contents($dir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));

        // Purge old backups (keep MAX_BACKUPS)
        self::purgeOld();

        $status = empty($errors) ? 'ok' : 'warning';
        return DSMResult::make($status, 'Rollback Point', "Backup {$id} — " . array_sum($rows) . ' lignes', $manifest, $errors, DSMResult::elapsed($t));
    }

    // ─── Restore depuis un backup ─────────────────────────────────────────────

    public static function restore(string $id): array {
        $t   = DSMResult::timer();
        $dir = self::BACKUP_DIR . '/' . $id;
        $sql = $dir . '/database.sql';

        if (!is_dir($dir) || !file_exists($sql)) {
            return DSMResult::error('Rollback', "Backup {$id} introuvable ou incomplet");
        }

        try {
            $pdo     = Database::getConnection();
            $content = file_get_contents($sql);
            $stmts   = self::parseSql($content);
            $executed = 0;

            $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
            $pdo->beginTransaction();
            foreach ($stmts as $stmt) {
                $stmt = trim($stmt);
                if ($stmt === '' || str_starts_with($stmt, '--')) continue;
                $pdo->exec($stmt);
                $executed++;
            }
            $pdo->commit();
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

            // Restore config backup if present
            $configBak = $dir . '/config.php.bak';
            if (file_exists($configBak)) {
                @copy($configBak, ROOT_PATH . '/config/config.php');
            }

            return DSMResult::make('ok', 'Rollback', "Backup {$id} restauré — {$executed} statements", ['id' => $id, 'statements' => $executed], [], DSMResult::elapsed($t));
        } catch (\Throwable $e) {
            try { $pdo->rollBack(); } catch (\Throwable $ignored) {}
            try { $pdo->exec('SET FOREIGN_KEY_CHECKS=1'); } catch (\Throwable $ignored) {}
            return DSMResult::error('Rollback', $e->getMessage(), ['id' => $id], [], DSMResult::elapsed($t));
        }
    }

    // ─── Liste des backups disponibles ────────────────────────────────────────

    public static function list(): array {
        $t       = DSMResult::timer();
        $dir     = self::BACKUP_DIR;
        $backups = [];

        if (!is_dir($dir)) {
            return DSMResult::make('ok', 'Backup List', 'Aucun backup disponible', [], [], DSMResult::elapsed($t));
        }

        $dirs = glob($dir . '/backup_*', GLOB_ONLYDIR);
        if ($dirs) {
            rsort($dirs);
            foreach ($dirs as $d) {
                $manifest = $d . '/manifest.json';
                if (file_exists($manifest)) {
                    $data = json_decode(file_get_contents($manifest), true);
                    if ($data) $backups[] = $data;
                }
            }
        }

        return DSMResult::make('ok', 'Backup List', count($backups) . ' backup(s) disponible(s)', $backups, [], DSMResult::elapsed($t));
    }

    /**
     * Retourne l'ID du backup le plus récent, null si aucun.
     */
    public static function getLatestId(): ?string {
        $dir  = self::BACKUP_DIR;
        $dirs = glob($dir . '/backup_*', GLOB_ONLYDIR);
        if (!$dirs) return null;
        rsort($dirs);
        return basename($dirs[0]);
    }

    // ─── Helpers SQL ─────────────────────────────────────────────────────────

    private static function generateSqlDump(\PDO $pdo, array &$tables, array &$rows): string {
        $sql = "-- Digitalium CMS Backup\n";
        $sql .= "-- Created: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Encoding: UTF-8\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $sql .= "SET NAMES utf8mb4;\n\n";

        // Use fixed order to respect FK dependencies
        $existingTables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
        $orderedTables  = array_filter(self::TABLE_ORDER, fn($t) => in_array($t, $existingTables));
        // Add any tables not in our order (new tables)
        $extra = array_diff($existingTables, self::TABLE_ORDER);
        $orderedTables = array_merge($orderedTables, $extra);

        foreach ($orderedTables as $table) {
            $tables[] = $table;
            $count = (int) $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
            $rows[$table] = $count;

            $sql .= "-- Table: {$table} ({$count} rows)\n";
            $sql .= "TRUNCATE TABLE `{$table}`;\n";

            if ($count > 0) {
                $allRows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
                if ($allRows) {
                    $cols  = '`' . implode('`, `', array_keys($allRows[0])) . '`';
                    $chunk = [];
                    foreach ($allRows as $row) {
                        $vals = array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote((string)$v), $row);
                        $chunk[] = '(' . implode(', ', $vals) . ')';
                        if (count($chunk) >= 100) {
                            $sql  .= "INSERT INTO `{$table}` ({$cols}) VALUES\n" . implode(",\n", $chunk) . ";\n";
                            $chunk = [];
                        }
                    }
                    if ($chunk) {
                        $sql .= "INSERT INTO `{$table}` ({$cols}) VALUES\n" . implode(",\n", $chunk) . ";\n";
                    }
                }
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        return $sql;
    }

    private static function parseSql(string $content): array {
        return array_filter(
            array_map('trim', explode(";\n", $content)),
            fn($s) => $s !== '' && !str_starts_with(trim($s), '--')
        );
    }

    private static function purgeOld(): void {
        $dirs = glob(self::BACKUP_DIR . '/backup_*', GLOB_ONLYDIR);
        if (!$dirs || count($dirs) <= self::MAX_BACKUPS) return;
        rsort($dirs);
        foreach (array_slice($dirs, self::MAX_BACKUPS) as $old) {
            foreach (glob($old . '/*') as $f) { @unlink($f); }
            @rmdir($old);
        }
    }
}
