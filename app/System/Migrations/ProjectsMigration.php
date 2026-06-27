<?php
namespace App\System\Migrations;

use App\Services\Database;
use App\System\DSMResult;

class ProjectsMigration implements MigrationInterface {
    public static function getName(): string { return 'projects'; }
    public static function getDescription(): string { return 'Vérifie la cohérence de la table projects'; }

    public static function run(): array {
        $t = DSMResult::timer();

        try {
            $count     = Database::fetch("SELECT COUNT(*) as n FROM projects")['n'] ?? 0;
            $published = Database::fetch("SELECT COUNT(*) as n FROM projects WHERE is_active = 1")['n'] ?? 0;
            $orphaned  = 0;

            // Check for projects with missing images referenced
            $withImages = Database::fetchAll("SELECT id, featured_image FROM projects WHERE featured_image != '' AND featured_image IS NOT NULL");
            foreach ($withImages as $p) {
                $imgPath = PUBLIC_PATH . $p['featured_image'];
                if (!file_exists($imgPath)) $orphaned++;
            }

            $data = ['total' => $count, 'published' => $published, 'images_orphaned' => $orphaned];

            if ($orphaned > 0) {
                return DSMResult::warning(self::getName(),
                    "{$count} réalisation(s) — {$orphaned} image(s) manquante(s)",
                    $data, ["{$orphaned} image(s) de projet introuvable(s) sur disque"], DSMResult::elapsed($t));
            }

            return DSMResult::ok(self::getName(),
                "{$count} réalisation(s) ({$published} publiée(s)) — images OK",
                $data, DSMResult::elapsed($t));
        } catch (\Exception $e) {
            return DSMResult::error(self::getName(), $e->getMessage(), [], [], DSMResult::elapsed($t));
        }
    }
}
