<?php
namespace App\System\Migrations;

use App\Services\Database;
use App\System\DSMResult;

class MenuMigration implements MigrationInterface {
    public static function getName(): string { return 'menu'; }
    public static function getDescription(): string { return 'Assure l\'existence du menu principal avec items de base'; }

    public static function run(): array {
        $t       = DSMResult::timer();
        $created = 0;
        $errors  = [];

        try {
            // Ensure 'primary' menu exists
            $menu = Database::fetch("SELECT id FROM menus WHERE location = 'primary' LIMIT 1");
            if (!$menu) {
                Database::query(
                    "INSERT INTO menus (name, location) VALUES ('Menu Principal', 'primary')"
                );
                $menuId = Database::getConnection()->lastInsertId();
                $created++;

                /* Semis de secours — utilisé UNIQUEMENT si le menu 'primary'
                   n'existe pas, donc après une perte ou une restauration.

                   Il contenait des adresses qui ne correspondent plus au site :
                   '/services' au pluriel répondait déjà 404, et '/blog' n'est
                   plus qu'une redirection 301 vers /insights. Un secours qui
                   remonte des liens morts aggrave la panne qu'il est censé
                   réparer — et aurait ici ressuscité l'entrée « Blog » tout
                   juste retirée (Règle #8).

                   Aligné sur le menu resserré : Accueil puis quatre rubriques,
                   le contact étant atteint par le bouton d'action de l'en-tête.
                   Le secours reste à plat — les sous-menus se rétablissent
                   depuis /admin/menus. */
                $items = [
                    ['Accueil',      '/',             1],
                    ['Solutions',    '/solutions',    2],
                    ['Réalisations', '/realisations', 3],
                    ['Insights',     '/insights',     4],
                    ['À propos',     '/a-propos',     5],
                ];

                foreach ($items as [$label, $url, $order]) {
                    Database::query(
                        "INSERT INTO menu_items (menu_id, label, url, sort_order) VALUES (:mid, :label, :url, :order)",
                        ['mid' => $menuId, 'label' => $label, 'url' => $url, 'order' => $order]
                    );
                    $created++;
                }
            }

            // Ensure 'footer' menu exists
            $footerMenu = Database::fetch("SELECT id FROM menus WHERE location = 'footer' LIMIT 1");
            if (!$footerMenu) {
                Database::query("INSERT INTO menus (name, location) VALUES ('Menu Footer', 'footer')");
                $created++;
            }

            $msg = $created > 0
                ? "Menu créés: {$created} entrée(s) (menus + items par défaut)"
                : "Menus déjà présents — aucune modification";

            return DSMResult::ok(self::getName(), $msg, ['created' => $created], DSMResult::elapsed($t));
        } catch (\Exception $e) {
            return DSMResult::error(self::getName(), $e->getMessage(), [], [], DSMResult::elapsed($t));
        }
    }
}
