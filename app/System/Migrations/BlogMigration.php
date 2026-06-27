<?php
namespace App\System\Migrations;

use App\Services\Database;
use App\System\DSMResult;

class BlogMigration implements MigrationInterface {
    public static function getName(): string { return 'blog'; }
    public static function getDescription(): string { return 'Assure la présence d\'une catégorie blog par défaut'; }

    public static function run(): array {
        $t       = DSMResult::timer();
        $created = 0;

        try {
            $cats = Database::fetch("SELECT COUNT(*) as n FROM blog_categories")['n'] ?? 0;
            if ($cats == 0) {
                Database::query(
                    "INSERT INTO blog_categories (name, slug, description) VALUES (:n, :s, :d)",
                    ['n' => 'Actualités', 's' => 'actualites', 'd' => 'Dernières actualités Digitalium Group']
                );
                Database::query(
                    "INSERT INTO blog_categories (name, slug, description) VALUES (:n, :s, :d)",
                    ['n' => 'Tech & Digital', 's' => 'tech-digital', 'd' => 'Articles sur le digital et la technologie']
                );
                $created = 2;
            }

            $msg = $created > 0 ? "Blog: {$created} catégorie(s) créée(s)" : "Blog: catégories déjà présentes ({$cats})";
            return DSMResult::ok(self::getName(), $msg, ['created' => $created, 'existing' => $cats], DSMResult::elapsed($t));
        } catch (\Exception $e) {
            return DSMResult::error(self::getName(), $e->getMessage(), [], [], DSMResult::elapsed($t));
        }
    }
}
