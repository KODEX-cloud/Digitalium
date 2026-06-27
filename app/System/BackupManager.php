<?php
namespace App\System;

class BackupManager {
    private static string $backupDir = '';

    private static function dir(): string {
        if (self::$backupDir === '') {
            self::$backupDir = ROOT_PATH . '/storage/backups';
        }
        if (!is_dir(self::$backupDir)) {
            mkdir(self::$backupDir, 0755, true);
        }
        return self::$backupDir;
    }

    /**
     * Create a full SQL dump backup.
     */
    public static function create(): array {
        $t        = DSMResult::timer();
        $filename = 'backup-' . date('Y-m-d-His') . '.sql';
        $dest     = self::dir() . '/' . $filename;

        $mysqldump = self::findMysqldump();
        if (empty($mysqldump)) {
            // Fallback: PHP-based dump
            return self::phpDump($dest, $filename, $t);
        }

        $cmd = sprintf(
            '%s --user=%s --password=%s --host=%s --port=%s %s > %s 2>&1',
            escapeshellcmd($mysqldump),
            escapeshellarg(DB_USER),
            escapeshellarg(DB_PASS),
            escapeshellarg(DB_HOST),
            escapeshellarg(DB_PORT),
            escapeshellarg(DB_NAME),
            escapeshellarg($dest)
        );

        exec($cmd, $output, $code);

        if ($code !== 0 || !file_exists($dest) || filesize($dest) < 100) {
            return self::phpDump($dest, $filename, $t);
        }

        $size = filesize($dest);
        return DSMResult::ok('Backup',
            "Dump MySQL créé: {$filename} (" . self::formatBytes($size) . ")",
            ['file' => $filename, 'path' => $dest, 'size_bytes' => $size, 'method' => 'mysqldump'],
            DSMResult::elapsed($t)
        );
    }

    /**
     * PHP-based database backup (no mysqldump required).
     */
    private static function phpDump(string $dest, string $filename, float $t): array {
        try {
            $db  = \App\Services\Database::getConnection();
            $sql = "-- Digitalium CMS Backup\n-- Generated: " . date('Y-m-d H:i:s') . "\n-- Database: " . DB_NAME . "\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";

            $tables = $db->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                // Table structure
                $create = $db->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sql .= $create['Create Table'] . ";\n\n";

                // Table data
                $rows = $db->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
                if (!empty($rows)) {
                    $cols   = '`' . implode('`, `', array_keys($rows[0])) . '`';
                    $sql   .= "INSERT INTO `{$table}` ({$cols}) VALUES\n";
                    $values = [];
                    foreach ($rows as $row) {
                        $escaped = array_map(fn($v) => $v === null ? 'NULL' : $db->quote((string)$v), array_values($row));
                        $values[] = '(' . implode(', ', $escaped) . ')';
                    }
                    $sql .= implode(",\n", $values) . ";\n\n";
                }
            }

            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
            file_put_contents($dest, $sql);

            $size = filesize($dest);
            return DSMResult::ok('Backup',
                "Dump PHP créé: {$filename} (" . self::formatBytes($size) . ")",
                ['file' => $filename, 'path' => $dest, 'size_bytes' => $size, 'method' => 'php-pdo', 'tables' => count($tables)],
                DSMResult::elapsed($t)
            );
        } catch (\Exception $e) {
            return DSMResult::error('Backup', "Backup échoué: " . $e->getMessage(), [], [], DSMResult::elapsed($t));
        }
    }

    /**
     * List existing backups.
     */
    public static function list(): array {
        $t     = DSMResult::timer();
        $files = glob(self::dir() . '/*.sql') ?: [];
        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));

        $backups = array_map(fn($f) => [
            'file'    => basename($f),
            'size'    => self::formatBytes(filesize($f)),
            'date'    => date('Y-m-d H:i:s', filemtime($f)),
        ], $files);

        return DSMResult::ok('Backups', count($files) . " backup(s) disponible(s)", ['backups' => $backups], DSMResult::elapsed($t));
    }

    /**
     * Delete backups older than N days.
     */
    public static function purge(int $days = 30): array {
        $t         = DSMResult::timer();
        $threshold = time() - ($days * 86400);
        $files     = glob(self::dir() . '/*.sql') ?: [];
        $deleted   = 0;

        foreach ($files as $f) {
            if (filemtime($f) < $threshold) {
                unlink($f) && $deleted++;
            }
        }

        return DSMResult::ok('Backup Purge', "{$deleted} backup(s) >$days jours supprimé(s)", ['deleted' => $deleted], DSMResult::elapsed($t));
    }

    private static function findMysqldump(): string {
        $candidates = ['mysqldump', 'C:/wamp64/bin/mysql/mysql8.4.7/bin/mysqldump.exe'];
        foreach ($candidates as $c) {
            if (file_exists($c)) return $c;
            $found = @shell_exec('where ' . escapeshellarg($c) . ' 2>NUL')
                  ?: @shell_exec('which ' . escapeshellarg($c) . ' 2>/dev/null');
            if (!empty(trim($found ?? ''))) return trim(explode("\n", $found)[0]);
        }
        return '';
    }

    private static function formatBytes(int $bytes): string {
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 2)    . ' KB';
        return $bytes . ' B';
    }
}
