<?php
namespace App\System;

use App\Services\Database;
use App\System\Migrations\SettingsMigration;
use App\System\Migrations\MenuMigration;
use App\System\Migrations\SeoMigration;

/**
 * SelfHealManager — Moteur d'auto-réparation du CMS.
 *
 * Détecte automatiquement les anomalies et tente de les corriger.
 * Si la correction est impossible → rapport détaillé.
 */
class SelfHealManager {

    public static function run(): array {
        $t       = DSMResult::timer();
        $results = [];

        $results[] = self::healStorageDirs();
        $results[] = self::healLogFile();
        $results[] = self::healCache();
        $results[] = self::healMissingSettings();
        $results[] = self::healMissingMenus();
        $results[] = self::healMissingSeo();
        $results[] = self::healOrphanedUploads();
        $results[] = self::healBrokenRoutes();
        $results[] = self::healPermissions();

        $aggregate = DSMResult::aggregate('Self-Heal', $results);

        // Résumé des réparations
        $fixed    = count(array_filter($results, fn($r) => ($r['status'] ?? '') === 'ok' && str_contains($r['message'] ?? '', 'répar')));
        $reported = count(array_filter($results, fn($r) => ($r['status'] ?? '') === 'warning'));
        $errors   = count(array_filter($results, fn($r) => ($r['status'] ?? '') === 'error'));

        $aggregate['label']   = 'Self-Heal';
        $aggregate['data']['fixed']    = $fixed;
        $aggregate['data']['reported'] = $reported;
        $aggregate['data']['errors']   = $errors;
        $aggregate['duration_ms']      = DSMResult::elapsed($t);

        return $aggregate;
    }

    // ─── Réparations ─────────────────────────────────────────────────────────

    private static function healStorageDirs(): array {
        $t    = DSMResult::timer();
        $dirs = [
            ROOT_PATH . '/storage/cache',
            ROOT_PATH . '/storage/logs',
            ROOT_PATH . '/storage/reports',
            ROOT_PATH . '/storage/backups',
            ROOT_PATH . '/storage/sessions',
        ];
        $created = 0;
        $errors  = [];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                if (!@mkdir($dir, 0755, true)) {
                    $errors[] = "Impossible de créer: " . basename($dir);
                } else {
                    $created++;
                }
            } elseif (!is_writable($dir)) {
                @chmod($dir, 0755);
                if (!is_writable($dir)) {
                    $errors[] = "Non accessible en écriture: " . basename($dir);
                }
            }
        }

        if (!empty($errors)) {
            return DSMResult::warning('Heal:StorageDirs', implode(', ', $errors), ['dirs' => $dirs], $errors, DSMResult::elapsed($t));
        }

        $msg = $created > 0 ? "réparé — {$created} répertoire(s) créé(s)" : "Répertoires storage OK";
        return DSMResult::ok('Heal:StorageDirs', $msg, ['created' => $created], DSMResult::elapsed($t));
    }

    private static function healLogFile(): array {
        $t       = DSMResult::timer();
        $logPath = ROOT_PATH . '/storage/logs/app.log';

        if (!file_exists($logPath)) {
            $created = @file_put_contents($logPath, "# Log initialisé par SelfHeal — " . date('Y-m-d H:i:s') . "\n");
            if ($created === false) {
                return DSMResult::error('Heal:LogFile', 'Impossible de créer storage/logs/app.log', [], [], DSMResult::elapsed($t));
            }
            return DSMResult::ok('Heal:LogFile', 'réparé — app.log créé', [], DSMResult::elapsed($t));
        }

        // Tronquer si > 20 MB
        $size = filesize($logPath);
        if ($size > 20 * 1024 * 1024) {
            $last = self::tailFile($logPath, 500);
            @file_put_contents($logPath, "# Log tronqué par SelfHeal — " . date('Y-m-d H:i:s') . "\n" . $last);
            return DSMResult::warning('Heal:LogFile', 'réparé — log tronqué (était ' . round($size / 1024 / 1024, 1) . ' MB)', [], [], DSMResult::elapsed($t));
        }

        return DSMResult::ok('Heal:LogFile', 'Log OK — ' . round($size / 1024, 1) . ' KB', [], DSMResult::elapsed($t));
    }

    private static function healCache(): array {
        $t        = DSMResult::timer();
        $cacheDir = ROOT_PATH . '/storage/cache';
        $removed  = 0;

        if (!is_dir($cacheDir)) {
            return DSMResult::ok('Heal:Cache', 'Répertoire cache absent — rien à réparer', [], DSMResult::elapsed($t));
        }

        $files = glob($cacheDir . '/*.cache') ?: [];
        foreach ($files as $file) {
            $size    = filesize($file);
            $content = $size > 0 ? @file_get_contents($file) : false;
            // Fichier vide ou contenu non-sérialisable
            $corrupt = ($size === 0) || ($content !== false && $content !== '' && @unserialize($content) === false && @json_decode($content) === null);
            if ($corrupt) {
                @unlink($file);
                $removed++;
            }
        }

        $msg = $removed > 0 ? "réparé — {$removed} fichier(s) cache corrompu(s) supprimé(s)" : "Cache OK — " . count($files) . " fichier(s)";
        return DSMResult::ok('Heal:Cache', $msg, ['removed' => $removed, 'total' => count($files)], DSMResult::elapsed($t));
    }

    private static function healMissingSettings(): array {
        $t      = DSMResult::timer();
        try {
            $result = SettingsMigration::run();
            $created = $result['data']['created'] ?? 0;
            $msg    = $created > 0 ? "réparé — {$created} setting(s) manquant(s) créé(s)" : "Settings complets";
            return DSMResult::ok('Heal:Settings', $msg, ['created' => $created], DSMResult::elapsed($t));
        } catch (\Exception $e) {
            return DSMResult::error('Heal:Settings', $e->getMessage(), [], [], DSMResult::elapsed($t));
        }
    }

    private static function healMissingMenus(): array {
        $t = DSMResult::timer();
        try {
            $result  = MenuMigration::run();
            $created = $result['data']['created'] ?? 0;
            $msg     = $created > 0 ? "réparé — {$created} élément(s) de menu créé(s)" : "Menus OK";
            return DSMResult::ok('Heal:Menus', $msg, ['created' => $created], DSMResult::elapsed($t));
        } catch (\Exception $e) {
            return DSMResult::error('Heal:Menus', $e->getMessage(), [], [], DSMResult::elapsed($t));
        }
    }

    private static function healMissingSeo(): array {
        $t = DSMResult::timer();
        try {
            $result  = SeoMigration::run();
            $updated = $result['data']['updated'] ?? 0;
            $msg     = $updated > 0 ? "réparé — {$updated} page(s) sans meta corrigée(s)" : "SEO OK sur toutes les pages";
            return DSMResult::ok('Heal:SEO', $msg, ['updated' => $updated], DSMResult::elapsed($t));
        } catch (\Exception $e) {
            return DSMResult::error('Heal:SEO', $e->getMessage(), [], [], DSMResult::elapsed($t));
        }
    }

    private static function healOrphanedUploads(): array {
        $t          = DSMResult::timer();
        $uploadPath = defined('UPLOAD_PATH') ? UPLOAD_PATH : ROOT_PATH . '/public/assets/uploads';
        $orphans    = 0;
        $total      = 0;

        if (!is_dir($uploadPath)) {
            return DSMResult::warning('Heal:Uploads', 'Répertoire uploads introuvable', [], ['Chemin: ' . $uploadPath], DSMResult::elapsed($t));
        }

        try {
            $dbFiles = Database::fetchAll("SELECT file_path FROM media");
            $dbSet   = array_flip(array_column($dbFiles, 'file_path'));
            $disk    = glob($uploadPath . '/*.*') ?: [];
            $total   = count($disk);

            foreach ($disk as $f) {
                $rel = '/assets/uploads/' . basename($f);
                if (!isset($dbSet[$rel]) && !isset($dbSet[basename($f)])) {
                    $orphans++;
                }
            }

            $msg = $orphans > 0
                ? "{$orphans}/{$total} fichier(s) upload sans entrée DB — rapport seulement"
                : "Uploads OK — {$total} fichier(s) tous référencés";

            $status = $orphans > 0 ? 'warning' : 'ok';
            return DSMResult::make($status, 'Heal:Uploads', $msg, ['orphans' => $orphans, 'total' => $total], [], DSMResult::elapsed($t));
        } catch (\Exception $e) {
            return DSMResult::error('Heal:Uploads', $e->getMessage(), [], [], DSMResult::elapsed($t));
        }
    }

    private static function healBrokenRoutes(): array {
        $t      = DSMResult::timer();
        $result = RouteManager::scan();
        $broken = $result['data']['invalid'] ?? 0;

        if ($broken > 0) {
            return DSMResult::warning(
                'Heal:Routes',
                "{$broken} route(s) avec handler manquant — correction manuelle requise",
                ['broken' => $broken],
                $result['errors'] ?? [],
                DSMResult::elapsed($t)
            );
        }

        return DSMResult::ok('Heal:Routes', 'Toutes les routes ont un handler valide', [], DSMResult::elapsed($t));
    }

    private static function healPermissions(): array {
        $t       = DSMResult::timer();
        $issues  = [];

        // Windows : chmod est ignoré, on vérifie juste la lisibilité/écriture
        $checks = [
            ROOT_PATH . '/storage/cache'   => true,
            ROOT_PATH . '/storage/logs'    => true,
            ROOT_PATH . '/storage/reports' => true,
            ROOT_PATH . '/storage/backups' => true,
            ROOT_PATH . '/public/assets/uploads' => true,
        ];

        foreach ($checks as $path => $needsWrite) {
            if (!is_dir($path)) {
                $issues[] = basename($path) . ' manquant';
                continue;
            }
            if ($needsWrite && !is_writable($path)) {
                $issues[] = basename($path) . ' non-writable';
            }
        }

        if (!empty($issues)) {
            return DSMResult::warning('Heal:Permissions', implode(', ', $issues), [], $issues, DSMResult::elapsed($t));
        }

        return DSMResult::ok('Heal:Permissions', 'Permissions OK sur tous les répertoires système', [], DSMResult::elapsed($t));
    }

    // ─── Utilitaire ──────────────────────────────────────────────────────────
    private static function tailFile(string $path, int $lines): string {
        $file    = new \SplFileObject($path, 'r');
        $file->seek(PHP_INT_MAX);
        $total   = $file->key();
        $start   = max(0, $total - $lines);
        $content = '';
        $file->seek($start);
        while (!$file->eof()) {
            $content .= $file->fgets();
        }
        return $content;
    }
}
