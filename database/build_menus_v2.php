<?php
/**
 * Build Menus v2 — navigation entièrement administrable
 *
 * Crée les menus d'emplacement manquants et les sème depuis les sources
 * actuelles, pour que RIEN NE BOUGE VISUELLEMENT le jour du déploiement : le
 * pied de page affiche exactement les mêmes liens qu'avant, à ceci près qu'ils
 * deviennent modifiables dans /admin/menus.
 *
 * ── Script RÉCONCILIATEUR ───────────────────────────────────────────────────
 * L'EXISTENCE des menus est réalignée à chaque déploiement. Leur CONTENU n'est
 * semé que si le menu est vide : un menu réorganisé en administration n'est
 * jamais réécrit. C'est la même doctrine que les scripts de pages.
 *
 * ── Isolation ───────────────────────────────────────────────────────────────
 * Chaque étape a son try/catch. Au premier déploiement d'/insights, une
 * exception sur une étape secondaire avait tué la construction entière.
 */

define('SECURE_ACCESS', true);
define('ROOT_PATH', dirname(__DIR__));
require ROOT_PATH . '/config/config.php';
require ROOT_PATH . '/app/Services/Database.php';

spl_autoload_register(function ($class) {
    $sep = chr(92);
    $prefix = 'App' . $sep;
    $baseDir = ROOT_PATH . '/app/';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) { return; }
    $file = $baseDir . str_replace($sep, DIRECTORY_SEPARATOR, substr($class, strlen($prefix))) . '.php';
    if (file_exists($file)) { require_once $file; }
});

use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\Database;

echo "=== BUILD MENUS v2 (navigation administrable) ===\n";

try {

    // ══════════════════════════════════════════════════════════════════════
    //  1. SCHÉMA — la mémoire du « déjà proposé une fois »
    // ══════════════════════════════════════════════════════════════════════
    //
    // Sans cette colonne, l'ajout automatique d'une page ressusciterait un lien
    // volontairement supprimé à chaque enregistrement de la page.

    $aNavSeeded = false;
    try {
        $colonnes = array_column(Database::fetchAll("SHOW COLUMNS FROM `pages`"), 'Field');
        if (in_array('nav_seeded', $colonnes, true)) {
            echo "  pages.nav_seeded déjà présente.\n";
            $aNavSeeded = true;
        } else {
            Database::query("ALTER TABLE `pages` ADD COLUMN `nav_seeded` TINYINT NOT NULL DEFAULT 0");
            echo "  pages.nav_seeded ajoutée.\n";
            $aNavSeeded = true;
        }
    } catch (\Throwable $e) {
        echo "  ATTENTION pages.nav_seeded : " . $e->getMessage() . "\n";
    }

    // ══════════════════════════════════════════════════════════════════════
    //  2. MENUS D'EMPLACEMENT
    // ══════════════════════════════════════════════════════════════════════

    $menus = [];
    foreach ([
        'primary'         => 'Menu principal',
        'footer'          => 'Pied de page — Navigation',
        'footer_services' => 'Pied de page — Services',
    ] as $emplacement => $nom) {
        try {
            $avant = Menu::findByLocation($emplacement);
            $menu  = Menu::ensure($emplacement, $nom);
            $menus[$emplacement] = $menu;
            echo "  Menu « $emplacement » : " . ($avant ? 'déjà présent' : 'créé')
               . " (#" . ($menu['id'] ?? '?') . ").\n";
        } catch (\Throwable $e) {
            echo "  ATTENTION menu $emplacement : " . $e->getMessage() . "\n";
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  3. SEMIS — uniquement si le menu est VIDE
    // ══════════════════════════════════════════════════════════════════════

    /** Ajoute une série de liens à un menu encore vide. */
    $semer = function (?array $menu, string $etiquette, callable $source): void {
        if (!$menu || empty($menu['id'])) {
            echo "  $etiquette : menu absent — semis ignoré.\n";
            return;
        }
        $menuId = (int)$menu['id'];
        try {
            if (MenuItem::compter($menuId) > 0) {
                echo "  $etiquette : déjà " . MenuItem::compter($menuId) . " lien(s) — non modifié.\n";
                return;
            }
            $liens = $source();
            if (!$liens) {
                echo "  $etiquette : aucune source à reprendre — menu laissé vide (repli automatique).\n";
                return;
            }
            MenuItem::saveForMenu($menuId, $liens);
            echo "  $etiquette : " . count($liens) . " lien(s) semé(s).\n";
        } catch (\Throwable $e) {
            echo "  ATTENTION $etiquette : " . $e->getMessage() . "\n";
        }
    };

    // ── Pied de page : les pages actuellement en navigation ─────────────────
    $semer($menus['footer'] ?? null, 'Pied — Navigation', function (): array {
        $pages = Database::fetchAll(
            "SELECT id, title, slug FROM pages
             WHERE status = 'published' AND in_navigation = 1
             ORDER BY sort_order ASC, id ASC"
        );
        $liens = [];
        foreach ($pages as $i => $p) {
            $liens[] = [
                'id'         => '',
                'page_id'    => (int)$p['id'],
                'label'      => $p['title'],
                'url'        => '',
                'target'     => '_self',
                'icon'       => '',
                'parent_id'  => '',
                'sort_order' => $i,
                'is_active'  => 1,
            ];
        }
        return $liens;
    });

    // ── Pied de page : la section Services de l'accueil, à l'identique ──────
    $semer($menus['footer_services'] ?? null, 'Pied — Services', function (): array {
        $sec = Database::fetch(
            "SELECT id FROM sections
             WHERE type IN ('services_grid_v2', 'services_grid') AND status = 'active'
             ORDER BY FIELD(type, 'services_grid_v2', 'services_grid') LIMIT 1"
        );
        if (!$sec) { return []; }

        $contenu = \App\Models\Block::getStructuredContent((int)$sec['id']);
        $liens = [];
        foreach (array_slice($contenu['groups'] ?? [], 0, 6) as $i => $svc) {
            $titre = trim((string)($svc['svc_title'] ?? ''));
            if ($titre === '') { continue; }
            $liens[] = [
                'id'         => '',
                'page_id'    => null,
                'label'      => $titre,
                'url'        => trim((string)($svc['svc_link'] ?? '')),
                'target'     => '_self',
                'icon'       => '',
                'parent_id'  => '',
                'sort_order' => $i,
                'is_active'  => 1,
            ];
        }
        return $liens;
    });

    // ══════════════════════════════════════════════════════════════════════
    //  4. MARQUAGE DES PAGES EXISTANTES — opération UNIQUE
    // ══════════════════════════════════════════════════════════════════════
    //
    // Toutes les pages déjà en ligne figurent déjà dans le menu principal.
    // Sans ce marquage, le PREMIER enregistrement de chacune d'elles ajouterait
    // un second lien : le menu doublerait entièrement.
    //
    // Opération unique, et non « à chaque déploiement » : une page créée en
    // brouillon puis publiée plus tard doit encore pouvoir être proposée.

    if ($aNavSeeded) {
        try {
            $drapeau = 'menus_v2_seeded_v1';
            $dejaFait = Database::fetch(
                "SELECT id FROM settings WHERE setting_key = :k LIMIT 1", ['k' => $drapeau]
            );

            if ($dejaFait) {
                echo "  Marquage des pages : déjà effectué.\n";
            } else {
                Database::query("UPDATE pages SET nav_seeded = 1");
                $n = (int)(Database::fetch("SELECT COUNT(*) AS n FROM pages")['n'] ?? 0);
                echo "  Marquage des pages : $n page(s) marquée(s) comme déjà proposées.\n";

                Database::query(
                    "INSERT INTO settings (setting_key, setting_value) VALUES (:k, '1')",
                    ['k' => $drapeau]
                );
            }
        } catch (\Throwable $e) {
            echo "  ATTENTION marquage des pages : " . $e->getMessage() . "\n";
            echo "     -> à surveiller : un menu principal pourrait se dédoubler au premier enregistrement d'une page.\n";
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  5. CACHE
    // ══════════════════════════════════════════════════════════════════════
    \App\Services\Cache::clear();
    echo "\nCache vidé.\n";
    echo "=== TERMINÉ ===\n";

} catch (\Throwable $e) {
    echo "ÉCHEC : " . $e->getMessage() . "\n";
    echo "  " . $e->getFile() . ':' . $e->getLine() . "\n";
    exit(1);
}
