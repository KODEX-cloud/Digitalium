<?php
namespace App\System\Migrations;

use App\Services\Database;
use App\System\DSMResult;

class SeoMigration implements MigrationInterface {
    public static function getName(): string { return 'seo'; }
    public static function getDescription(): string { return 'Assure la présence des meta SEO par défaut sur toutes les pages'; }

    public static function run(): array {
        $t       = DSMResult::timer();
        $updated = 0;
        $errors  = [];

        try {
            $pages = Database::fetchAll("SELECT id, slug, title, meta_title, meta_description FROM pages");
            $siteName = 'Digitalium Group';

            // Try to get site name from settings
            $snRow = Database::fetch("SELECT setting_value FROM settings WHERE setting_key = 'site_name'");
            if ($snRow) $siteName = $snRow['setting_value'];

            foreach ($pages as $page) {
                $needsUpdate = false;
                $metaTitle   = $page['meta_title'];
                $metaDesc    = $page['meta_description'];

                if (empty($metaTitle)) {
                    $metaTitle   = $page['title'] . ' — ' . $siteName;
                    $needsUpdate = true;
                }

                if (empty($metaDesc)) {
                    $metaDesc    = $page['title'] . ' — ' . $siteName;
                    $needsUpdate = true;
                }

                if ($needsUpdate) {
                    Database::query(
                        "UPDATE pages SET meta_title = :mt, meta_description = :md WHERE id = :id",
                        ['mt' => $metaTitle, 'md' => $metaDesc, 'id' => $page['id']]
                    );
                    $updated++;
                }
            }

            $total = count($pages);
            $msg   = $updated > 0
                ? "SEO: {$updated}/{$total} page(s) avec meta par défaut ajoutées"
                : "SEO: toutes les pages ont déjà leurs meta ({$total} pages)";

            return DSMResult::ok(self::getName(), $msg, ['total' => $total, 'updated' => $updated], DSMResult::elapsed($t));
        } catch (\Exception $e) {
            return DSMResult::error(self::getName(), $e->getMessage(), [], [], DSMResult::elapsed($t));
        }
    }
}
