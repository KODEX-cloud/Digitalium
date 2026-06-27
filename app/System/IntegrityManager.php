<?php
namespace App\System;

use App\Services\Database;

class IntegrityManager {
    /**
     * Run all integrity checks.
     */
    public static function verify(): array {
        $t       = DSMResult::timer();
        $checks  = [
            self::checkRequiredTables(),
            self::checkOrphanedMedia(),
            self::checkOrphanedUploads(),
            self::checkPagesIntegrity(),
            self::checkSettingsIntegrity(),
        ];

        return DSMResult::aggregate('Intégrité', $checks);
    }

    public static function checkRequiredTables(): array {
        $t        = DSMResult::timer();
        $required = [
            'users','pages','sections','blocks','settings','media','hero_slides',
            'projects','blog_posts','blog_categories','blog_tags','blog_post_tags',
            'blog_comments','contact_messages','menus','menu_items',
        ];

        $existing = array_column(Database::fetchAll("SHOW TABLES"), null);
        $existing = array_map(fn($row) => array_values($row)[0], Database::fetchAll("SHOW TABLES"));

        $missing = array_diff($required, $existing);

        if (!empty($missing)) {
            return DSMResult::error('Tables SQL', count($missing) . " table(s) manquante(s)",
                array_values($missing), ['existing' => count($existing), 'required' => count($required)],
                DSMResult::elapsed($t)
            );
        }

        return DSMResult::ok('Tables SQL', count($required) . "/" . count($required) . " tables présentes",
            ['count' => count($existing)], DSMResult::elapsed($t));
    }

    public static function checkOrphanedMedia(): array {
        $t = DSMResult::timer();

        try {
            $dbFiles  = Database::fetchAll("SELECT filename FROM media");
            $dbNames  = array_column($dbFiles, 'filename');
            $diskFiles = array_map('basename', glob(UPLOAD_PATH . '/*') ?: []);
            $diskFiles = array_filter($diskFiles, fn($f) => !str_starts_with($f, '.'));

            $orphaned = array_diff($diskFiles, $dbNames);
            $missing  = array_diff($dbNames, $diskFiles);

            $errors = [];
            if (!empty($orphaned)) $errors[] = count($orphaned) . " fichier(s) sur disque sans entrée BDD";
            if (!empty($missing))  $errors[] = count($missing)  . " entrée(s) BDD sans fichier disque";

            $status = empty($errors) ? 'ok' : 'warning';
            $msg    = empty($errors)
                ? count($diskFiles) . " fichiers cohérents entre disque et BDD"
                : implode(' — ', $errors);

            return DSMResult::make($status, 'Médias Intégrité', $msg,
                ['db_count' => count($dbNames), 'disk_count' => count($diskFiles),
                 'orphaned_disk' => array_values($orphaned), 'missing_disk' => array_values($missing)],
                $errors,
                DSMResult::elapsed($t)
            );
        } catch (\Exception $e) {
            return DSMResult::warning('Médias Intégrité', 'Vérification partielle: ' . $e->getMessage(), [], [], DSMResult::elapsed($t));
        }
    }

    public static function checkOrphanedUploads(): array {
        $t     = DSMResult::timer();
        $files = glob(UPLOAD_PATH . '/*') ?: [];
        $files = array_filter($files, 'is_file');

        $old      = [];
        $threshold = time() - (30 * 24 * 3600); // 30 days

        foreach ($files as $f) {
            $mtime = filemtime($f);
            if ($mtime && $mtime < $threshold) {
                $old[] = basename($f);
            }
        }

        $msg = count($files) . " fichiers dans uploads";
        if (!empty($old)) {
            $msg .= " — " . count($old) . " fichier(s) >30 jours";
            return DSMResult::warning('Uploads Anciens', $msg, ['old_files' => $old], [], DSMResult::elapsed($t));
        }

        return DSMResult::ok('Uploads Anciens', $msg, ['total' => count($files)], DSMResult::elapsed($t));
    }

    public static function checkPagesIntegrity(): array {
        $t = DSMResult::timer();

        try {
            $pages = Database::fetchAll("SELECT id, slug, title FROM pages WHERE is_active = 1");

            if (empty($pages)) {
                return DSMResult::warning('Pages CMS', 'Aucune page active trouvée en BDD', [], [], DSMResult::elapsed($t));
            }

            $noSections = 0;
            foreach ($pages as $page) {
                $count = Database::fetch("SELECT COUNT(*) as n FROM sections WHERE page_id = :pid", ['pid' => $page['id']]);
                if (($count['n'] ?? 0) === 0) $noSections++;
            }

            $total = count($pages);
            $msg   = "{$total} pages actives";
            if ($noSections > 0) {
                $msg .= " — {$noSections} sans section(s)";
                return DSMResult::warning('Pages CMS', $msg, ['total' => $total, 'no_sections' => $noSections], [], DSMResult::elapsed($t));
            }

            return DSMResult::ok('Pages CMS', $msg, ['total' => $total], DSMResult::elapsed($t));
        } catch (\Exception $e) {
            return DSMResult::error('Pages CMS', $e->getMessage(), [], [], DSMResult::elapsed($t));
        }
    }

    public static function checkSettingsIntegrity(): array {
        $t = DSMResult::timer();

        try {
            $required = ['site_name', 'site_email', 'logo_desktop', 'footer_copyright'];
            $settings = Database::fetchAll("SELECT setting_key FROM settings");
            $existing = array_column($settings, 'setting_key');

            $missing = array_diff($required, $existing);
            $total   = count($existing);

            if (!empty($missing)) {
                return DSMResult::warning('Settings', count($missing) . " clé(s) requise(s) absente(s)",
                    ['total' => $total], array_values($missing), DSMResult::elapsed($t));
            }

            return DSMResult::ok('Settings', "{$total} paramètres configurés — clés requises présentes",
                ['total' => $total], DSMResult::elapsed($t));
        } catch (\Exception $e) {
            return DSMResult::error('Settings', $e->getMessage(), [], [], DSMResult::elapsed($t));
        }
    }
}
