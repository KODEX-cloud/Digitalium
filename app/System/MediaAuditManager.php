<?php
namespace App\System;

use App\Services\Database;

class MediaAuditManager {
    public static function audit(): array {
        $t = DSMResult::timer();
        $checks = [
            self::countMedia(),
            self::findOrphans(),
            self::checkLargeFiles(),
        ];

        return DSMResult::aggregate('Audit Médias', $checks);
    }

    public static function countMedia(): array {
        $t = DSMResult::timer();

        try {
            $total  = Database::fetch("SELECT COUNT(*) as n FROM media")['n']                        ?? 0;
            $images = Database::fetch("SELECT COUNT(*) as n FROM media WHERE mime_type LIKE 'image/%'")['n'] ?? 0;
            $size   = Database::fetch("SELECT SUM(file_size) as s FROM media")['s']                 ?? 0;

            return DSMResult::ok('Médias BDD',
                "{$total} fichiers en BDD ({$images} images) — " . self::formatBytes((int)$size),
                ['total' => $total, 'images' => $images, 'size_bytes' => (int)$size],
                DSMResult::elapsed($t)
            );
        } catch (\Exception $e) {
            return DSMResult::error('Médias BDD', $e->getMessage(), [], [], DSMResult::elapsed($t));
        }
    }

    public static function findOrphans(): array {
        $t = DSMResult::timer();

        try {
            $dbFiles   = array_column(Database::fetchAll("SELECT filename FROM media"), 'filename');
            $diskFiles = array_map('basename', glob(UPLOAD_PATH . '/*') ?: []);
            $diskFiles = array_filter($diskFiles, fn($f) => !str_starts_with($f, '.') && is_file(UPLOAD_PATH . '/' . $f));

            $orphans = array_values(array_diff($diskFiles, $dbFiles));
            $missing = array_values(array_diff($dbFiles, $diskFiles));

            $data = [
                'db_count'     => count($dbFiles),
                'disk_count'   => count($diskFiles),
                'orphaned'     => $orphans,
                'missing_disk' => $missing,
            ];

            if (!empty($orphans) || !empty($missing)) {
                $errors = [];
                if (!empty($orphans)) $errors[] = count($orphans) . " fichier(s) orphelin(s) sur disque";
                if (!empty($missing)) $errors[] = count($missing) . " référence(s) BDD sans fichier disque";
                return DSMResult::warning('Médias Orphelins', implode(' — ', $errors), $data, $errors, DSMResult::elapsed($t));
            }

            return DSMResult::ok('Médias Orphelins', "Cohérence parfaite disque/BDD — " . count($dbFiles) . " fichiers", $data, DSMResult::elapsed($t));
        } catch (\Exception $e) {
            return DSMResult::warning('Médias Orphelins', 'Vérification partielle: ' . $e->getMessage(), [], [], DSMResult::elapsed($t));
        }
    }

    public static function checkLargeFiles(): array {
        $t     = DSMResult::timer();
        $files = glob(UPLOAD_PATH . '/*') ?: [];
        $large = [];
        $threshold = 5 * 1024 * 1024; // 5MB

        foreach ($files as $f) {
            if (!is_file($f)) continue;
            $size = filesize($f);
            if ($size > $threshold) {
                $large[] = ['file' => basename($f), 'size' => self::formatBytes($size)];
            }
        }

        if (!empty($large)) {
            return DSMResult::warning('Gros Fichiers',
                count($large) . " fichier(s) >5MB détecté(s)",
                ['large_files' => $large],
                array_map(fn($l) => "{$l['file']} ({$l['size']})", $large),
                DSMResult::elapsed($t)
            );
        }

        return DSMResult::ok('Gros Fichiers', "Aucun fichier >5MB détecté", ['checked' => count($files)], DSMResult::elapsed($t));
    }

    private static function formatBytes(int $bytes): string {
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 2)    . ' KB';
        return $bytes . ' B';
    }
}
