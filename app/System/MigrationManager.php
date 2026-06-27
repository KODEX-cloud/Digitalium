<?php
namespace App\System;

class MigrationManager {
    /**
     * Run the master SQL migration via PHP CLI.
     */
    public static function run(): array {
        $t       = DSMResult::timer();
        $script  = ROOT_PATH . '/database/master_migration.php';
        $phpBin  = self::findPhpBinary();

        if (!file_exists($script)) {
            return DSMResult::error('Migration SQL', 'master_migration.php introuvable', [$script], [], DSMResult::elapsed($t));
        }

        if (empty($phpBin)) {
            return DSMResult::error('Migration SQL', 'PHP CLI introuvable — exécution manuelle requise', [], [], DSMResult::elapsed($t));
        }

        $cmd    = escapeshellcmd($phpBin) . ' ' . escapeshellarg($script) . ' 2>&1';
        $output = [];
        $code   = 0;

        exec($cmd, $output, $code);

        $outputStr = implode("\n", $output);
        $lines     = count($output);

        if ($code !== 0) {
            return DSMResult::error('Migration SQL', "Migration échouée (exit: {$code})",
                [$outputStr],
                ['output_lines' => $lines, 'exit_code' => $code],
                DSMResult::elapsed($t)
            );
        }

        // Count "CREATE TABLE" or "OK" mentions
        $tables = substr_count(strtolower($outputStr), 'table');
        $msg    = "Migration SQL exécutée — {$lines} lignes de sortie";
        if ($tables > 0) $msg .= " — {$tables} tables vérifiées/créées";

        return DSMResult::ok('Migration SQL', $msg,
            ['output' => $output, 'exit_code' => $code, 'tables_mentioned' => $tables],
            DSMResult::elapsed($t)
        );
    }

    private static function findPhpBinary(): string {
        $candidates = [
            'C:/wamp64/bin/php/php8.3.28/php.exe',
            '/usr/bin/php8.3',
            '/usr/bin/php',
            'php',
        ];

        foreach ($candidates as $c) {
            if (file_exists($c)) return $c;
            // Try which/where for non-absolute paths
            if (!str_contains($c, '/') && !str_contains($c, '\\')) {
                $found = @shell_exec('where ' . escapeshellarg($c) . ' 2>NUL')
                      ?: @shell_exec('which ' . escapeshellarg($c) . ' 2>/dev/null');
                if (!empty(trim($found ?? ''))) return trim(explode("\n", $found)[0]);
            }
        }

        return defined('PHP_BINARY') ? PHP_BINARY : '';
    }
}
