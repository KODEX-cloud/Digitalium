<?php
namespace App\System\Migrations;

use App\Services\Database;
use App\System\DSMResult;

class HeroMigration implements MigrationInterface {
    public static function getName(): string { return 'hero'; }
    public static function getDescription(): string { return 'Assure la présence d\'une section hero sur la page d\'accueil'; }

    public static function run(): array {
        $t       = DSMResult::timer();
        $created = 0;
        $errors  = [];

        try {
            // Find the home page
            $home = Database::fetch("SELECT id FROM pages WHERE slug = 'home' OR slug = 'accueil' LIMIT 1");
            if (!$home) {
                return DSMResult::warning(self::getName(), 'Page d\'accueil introuvable — hero non créé', [], [], DSMResult::elapsed($t));
            }

            $pageId = $home['id'];

            // Check if a hero section already exists
            $heroSection = Database::fetch(
                "SELECT id FROM sections WHERE page_id = :pid AND type LIKE '%hero%' LIMIT 1",
                ['pid' => $pageId]
            );

            if (!$heroSection) {
                // Create hero section
                Database::query(
                    "INSERT INTO sections (page_id, type, name, is_active, sort_order) VALUES (:pid, 'hero', 'Hero Principal', 1, 1)",
                    ['pid' => $pageId]
                );
                $sectionId = Database::getConnection()->lastInsertId();
                $created++;

                // Default hero blocks
                $blocks = [
                    ['hero_badge',    'text',  'Agence Digitale Premium'],
                    ['hero_title',    'text',  'Transformez votre présence digitale'],
                    ['hero_subtitle', 'text',  'Nous créons des solutions digitales sur mesure pour votre entreprise'],
                    ['cta1_label',    'text',  'Démarrer un projet'],
                    ['cta1_url',      'url',   '/contact'],
                    ['cta2_label',    'text',  'Nos réalisations'],
                    ['cta2_url',      'url',   '/realisations'],
                    ['stat1_number',  'text',  '150+'],
                    ['stat1_label',   'text',  'Projets réalisés'],
                    ['stat2_number',  'text',  '98%'],
                    ['stat2_label',   'text',  'Clients satisfaits'],
                    ['stat3_number',  'text',  '5+'],
                    ['stat3_label',   'text',  'Ans d\'expérience'],
                ];

                foreach ($blocks as [$key, $type, $value]) {
                    Database::query(
                        "INSERT INTO blocks (section_id, block_key, type, value) VALUES (:sid, :k, :t, :v)",
                        ['sid' => $sectionId, 'k' => $key, 't' => $type, 'v' => $value]
                    );
                    $created++;
                }
            }

            $msg = $created > 0
                ? "Hero créé avec {$created} blocs sur la page d'accueil"
                : "Section hero déjà présente — aucune modification";

            return DSMResult::ok(self::getName(), $msg, ['created' => $created, 'page_id' => $pageId], DSMResult::elapsed($t));
        } catch (\Exception $e) {
            return DSMResult::error(self::getName(), $e->getMessage(), [], [], DSMResult::elapsed($t));
        }
    }
}
