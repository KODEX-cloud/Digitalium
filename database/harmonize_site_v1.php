<?php
/**
 * Harmonisation du site — menu resserré, palette unique, heros alignés
 *
 * Trois opérations indépendantes, chacune sous son propre drapeau `settings` :
 * un échec de l'une n'empêche pas les autres, et chacune ne s'exécute qu'une
 * fois. Ce qui est réglé ensuite en admin n'est donc jamais réécrit.
 *
 * ── 1. MENU PRINCIPAL : 8 liens -> Accueil + 4 rubriques ────────────────────
 *   Accueil
 *   Solutions ▾     Toutes nos solutions · les 5 sous-pages · Par secteur d'activité
 *   Réalisations
 *   Insights
 *   À propos ▾      Digitalium Group · Digitalium Labs · Contact
 * « Accueil » reste en tête (demande utilisateur) ; « Contact » quitte le premier
 * niveau (le bouton d'action de l'en-tête y mène déjà). Un lien n'est créé que
 * si sa page existe et est publiée : jamais de lien vers une page absente.
 * L'ancien menu est sauvegardé en JSON dans `primary_menu_backup_v2`.
 *
 * ── 2. PALETTE UNIQUE : le bleu du logo ─────────────────────────────────────
 * Le site affichait trois couleurs principales selon la page — vert #1d6363
 * (accueil, /secteurs, /contact), bleu #0868B0 (Solutions, Insights, Labs),
 * bleus foncés #0B4F8A et #003060 (/a-propos, /realisations) — via le thème
 * global et des accents par page (`pages.accent_color`). En-tête et pied de
 * page changeaient donc de couleur d'une page à l'autre. Décision : le bleu du
 * logo partout. Le thème global passe au bleu et les accents par page sont
 * vidés. Valeurs précédentes sauvegardées dans `brand_palette_backup_v1`.
 *
 * ── 3. HEROS : géométrie de /secteurs sur TOUTES les pages en overlay ───────
 * `align_overlay_heroes.php` (v1) a aligné six pages nommées ; les cinq
 * sous-pages de /solutions, créées avec 1250px de large et une hauteur
 * moindre, n'en faisaient pas partie. La référence est relue en base, comme
 * en v1. Seules les clés de géométrie sont copiées.
 *
 * Tout reste administrable : /admin/menus, /admin/settings (Couleurs & Thème),
 * /admin/pages -> la page -> Hero.
 *
 * Récupération : le pipeline crée une sauvegarde SQL (RollbackManager) avant
 * chaque déploiement, en plus des deux sauvegardes JSON ci-dessus.
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

use App\Models\Block;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Setting;
use App\Services\Database;

echo "=== HARMONISATION DU SITE (v1) ===\n";

/** Lecture d'un drapeau. null = illisible : l'étape est alors abandonnée. */
$drapeau = static function (string $cle): ?bool {
    try {
        return (bool)Database::fetch(
            "SELECT id FROM settings WHERE setting_key = :k LIMIT 1", ['k' => $cle]
        );
    } catch (\Throwable $e) {
        echo "  ATTENTION drapeau $cle illisible : " . $e->getMessage() . "\n";
        return null;
    }
};

$poserDrapeau = static function (string $cle): void {
    try {
        Setting::setVal($cle, '1');
        echo "  Drapeau $cle posé — opération non rejouable.\n";
    } catch (\Throwable $e) {
        echo "  ATTENTION drapeau $cle non posé : " . $e->getMessage() . "\n";
    }
};

$modifie = false;

// ════════════════════════════════════════════════════════════════════════════
//  1. MENU PRINCIPAL
// ════════════════════════════════════════════════════════════════════════════
echo "\n[1/3] Menu principal\n";
const DRAPEAU_MENU = 'primary_menu_v3_applied';

$etat = $drapeau(DRAPEAU_MENU);
if ($etat === true) {
    echo "  Drapeau " . DRAPEAU_MENU . " déjà posé — menu laissé tel quel.\n";
} elseif ($etat === null) {
    echo "  Étape abandonnée.\n";
} else {
    try {
        $menu = Menu::findByLocation('primary');
        if (!$menu) {
            throw new \RuntimeException("aucun menu à l'emplacement « primary »");
        }
        $menuId = (int)$menu['id'];

        /** Page publiée par chemin (« solutions/ia-automatisation » ou « labs »), sinon null. */
        $page = static function (string $chemin): ?array {
            $parts  = explode('/', trim($chemin, '/'));
            $slug   = array_pop($parts);
            $parent = $parts[0] ?? '';
            $p = $parent !== ''
                ? Database::fetch(
                    "SELECT id, title, status FROM pages WHERE slug = :s AND parent_slug = :p LIMIT 1",
                    ['s' => $slug, 'p' => $parent])
                : Database::fetch(
                    "SELECT id, title, status FROM pages WHERE slug = :s LIMIT 1", ['s' => $slug]);
            return ($p && ($p['status'] ?? '') === 'published') ? $p : null;
        };

        // [référence, chemin, libellé (null = titre de la page), parent]
        $plan = [
            ['home',     'home',                                       'Accueil',                null],
            ['sol',      'solutions',                                  'Solutions',              null],
            ['sol-all',  'solutions',                                  'Toutes nos solutions',   'sol'],
            ['sol-sw',   'solutions/software-platforms',               null,                     'sol'],
            ['sol-ia',   'solutions/ia-automatisation',                null,                     'sol'],
            ['sol-data', 'solutions/data-business-intelligence',       null,                     'sol'],
            ['sol-infra','solutions/infrastructure-security',          null,                     'sol'],
            ['sol-ops',  'solutions/managed-operations',               null,                     'sol'],
            ['sol-sect', 'secteurs',                                   "Par secteur d'activité", 'sol'],
            ['real',     'realisations',                               'Réalisations',           null],
            ['ins',      'insights',                                   'Insights',               null],
            ['apr',      'a-propos',                                   'À propos',               null],
            ['apr-grp',  'a-propos',                                   'Digitalium Group',       'apr'],
            ['apr-labs', 'labs',                                       'Digitalium Labs',        'apr'],
            ['apr-ct',   'contact',                                    'Contact',                'apr'],
        ];

        $items   = [];
        $presents = [];
        $rang    = 0;
        foreach ($plan as [$ref, $chemin, $libelle, $parent]) {
            $p = $page($chemin);
            if (!$p) {
                echo "  [$chemin] page absente ou non publiée — lien non créé.\n";
                continue;
            }
            // Un sous-lien dont le parent n'a pas pu être créé remonte à la racine
            // plutôt que de disparaître.
            $parentRef = ($parent !== null && isset($presents[$parent])) ? 'new_' . $parent : '';
            $items[] = [
                'id'         => 'new_' . $ref,
                'label'      => $libelle ?? $p['title'],
                'url'        => $chemin === 'home' ? '/' : '/' . $chemin,
                'page_id'    => (int)$p['id'],
                'target'     => '_self',
                'icon'       => '',
                'parent_id'  => $parentRef,
                'sort_order' => $rang++,
                'is_active'  => 1,
            ];
            $presents[$ref] = true;
        }

        if (count($items) < 5) {
            throw new \RuntimeException('moins de 5 liens constructibles — menu actuel conservé');
        }

        // Sauvegarde de l'existant AVANT toute écriture.
        $avant = Database::fetchAll(
            "SELECT id, parent_id, page_id, label, url, target, icon, sort_order, is_active
               FROM menu_items WHERE menu_id = :m ORDER BY parent_id, sort_order, id",
            ['m' => $menuId]
        );
        Setting::setVal('primary_menu_backup_v2', json_encode($avant, JSON_UNESCAPED_UNICODE));
        echo "  Menu actuel sauvegardé (" . count($avant) . " lien(s)) dans primary_menu_backup_v2.\n";

        MenuItem::saveForMenu($menuId, $items);

        $racines = count(array_filter($items, fn($i) => $i['parent_id'] === ''));
        echo "  Menu reconstruit : $racines rubrique(s), " . (count($items) - $racines) . " sous-lien(s).\n";
        foreach ($items as $i) {
            echo '    ' . ($i['parent_id'] === '' ? '' : '  └ ') . $i['label'] . ' -> ' . $i['url'] . "\n";
        }
        $poserDrapeau(DRAPEAU_MENU);
        $modifie = true;
    } catch (\Throwable $e) {
        echo "  ERREUR menu : " . $e->getMessage() . "\n";
        echo "  Drapeau NON posé — l'étape sera rejouée au prochain déploiement.\n";
    }
}

// ════════════════════════════════════════════════════════════════════════════
//  2. PALETTE UNIQUE
// ════════════════════════════════════════════════════════════════════════════
echo "\n[2/3] Palette\n";
const DRAPEAU_PALETTE = 'brand_palette_blue_v1';

$etat = $drapeau(DRAPEAU_PALETTE);
if ($etat === true) {
    echo "  Drapeau " . DRAPEAU_PALETTE . " déjà posé — couleurs laissées telles quelles.\n";
} elseif ($etat === null) {
    echo "  Étape abandonnée.\n";
} else {
    try {
        // Couleurs du logo : #0868B0 (bleu), #003060 (bleu nuit). Les teintes
        // claires sont des dérivés du bleu pour les fonds et les pastilles.
        $palette = [
            'theme_primary'    => '#0868B0',
            'theme_secondary'  => '#003060',
            'theme_accent'     => '#003060',
            'theme_bg_alt'     => '#eef5fb',
            'theme_badge_bg'   => '#e3effa',
            'theme_badge_text' => '#0B4F8A',
            // Seconde couche, injectée APRÈS theme_* dans le <head> : elle
            // l'emporte et doit donc porter la même valeur.
            'color_primary'    => '#0868B0',
            'color_accent'     => '#003060',
        ];

        $sauvegarde = ['settings' => [], 'accents' => []];
        foreach ($palette as $cle => $valeur) {
            $sauvegarde['settings'][$cle] = Setting::getVal($cle);
        }
        $aAccent = (bool)Database::fetch("SHOW COLUMNS FROM `pages` LIKE 'accent_color'");
        if ($aAccent) {
            $sauvegarde['accents'] = Database::fetchAll(
                "SELECT id, slug, accent_color FROM pages
                  WHERE accent_color IS NOT NULL AND accent_color <> ''"
            );
        }
        Setting::setVal('brand_palette_backup_v1', json_encode($sauvegarde, JSON_UNESCAPED_UNICODE));
        echo "  Valeurs précédentes sauvegardées dans brand_palette_backup_v1.\n";

        foreach ($palette as $cle => $valeur) {
            $ancien = (string)($sauvegarde['settings'][$cle] ?? '');
            if ($ancien === $valeur) { continue; }
            Setting::setVal($cle, $valeur);
            echo "    $cle : " . ($ancien !== '' ? $ancien : '(vide)') . " -> $valeur\n";
        }

        if ($aAccent) {
            foreach ($sauvegarde['accents'] as $p) {
                echo "    accent de /{$p['slug']} retiré ({$p['accent_color']}).\n";
            }
            Database::query("UPDATE pages SET accent_color = NULL WHERE accent_color IS NOT NULL");
        }

        $poserDrapeau(DRAPEAU_PALETTE);
        $modifie = true;
    } catch (\Throwable $e) {
        echo "  ERREUR palette : " . $e->getMessage() . "\n";
        echo "  Drapeau NON posé — l'étape sera rejouée au prochain déploiement.\n";
    }
}

// ════════════════════════════════════════════════════════════════════════════
//  3. GÉOMÉTRIE DES HEROS OVERLAY
// ════════════════════════════════════════════════════════════════════════════
echo "\n[3/3] Heros overlay\n";
const DRAPEAU_HEROS = 'hero_overlay_size_aligned_v2';
const REFERENCE     = 'secteurs';

$etat = $drapeau(DRAPEAU_HEROS);
if ($etat === true) {
    echo "  Drapeau " . DRAPEAU_HEROS . " déjà posé — heros laissés tels quels.\n";
} elseif ($etat === null) {
    echo "  Étape abandonnée.\n";
} else {
    try {
        $aCopier = ['overlay_min_height', 'image_max_width', 'image_radius'];

        // Premier hero_media_cards de chaque page (même règle qu'en v1).
        $heros = Database::fetchAll(
            "SELECT p.slug, p.parent_slug, MIN(s.id) AS section_id
               FROM sections s
               JOIN pages p ON p.id = s.page_id
              WHERE s.type = 'hero_media_cards'
              GROUP BY p.id, p.slug, p.parent_slug"
        );

        $refId = null;
        foreach ($heros as $h) {
            if ($h['slug'] === REFERENCE && trim((string)$h['parent_slug']) === '') {
                $refId = (int)$h['section_id'];
            }
        }
        if (!$refId) {
            throw new \RuntimeException('page « ' . REFERENCE . ' » sans hero hero_media_cards');
        }

        $refSingle = Block::getStructuredContent($refId)['single'] ?? [];
        if (($refSingle['layout'] ?? 'split') !== 'overlay') {
            throw new \RuntimeException('le hero de référence n\'est pas en mode overlay');
        }
        $modele = [];
        foreach ($aCopier as $cle) {
            $v = trim((string)($refSingle[$cle] ?? ''));
            if ($v !== '') { $modele[$cle] = $v; }
        }
        if (empty($modele)) {
            throw new \RuntimeException('la référence ne porte aucune clé de géométrie');
        }
        echo "  Référence /" . REFERENCE . " : "
           . implode(' · ', array_map(fn($k, $v) => "$k=$v", array_keys($modele), $modele)) . "\n";

        $alignees = 0;
        foreach ($heros as $h) {
            $secId = (int)$h['section_id'];
            if ($secId === $refId) { continue; }
            $chemin = '/' . (trim((string)$h['parent_slug']) !== '' ? $h['parent_slug'] . '/' : '') . $h['slug'];

            $single = Block::getStructuredContent($secId)['single'] ?? [];
            // L'accueil est en mode « split » : la géométrie overlay n'y a aucun sens.
            if (($single['layout'] ?? 'split') !== 'overlay') {
                echo "  [$chemin] mode « " . ($single['layout'] ?? 'split') . " » — ignoré.\n";
                continue;
            }
            $changements = [];
            foreach ($modele as $cle => $val) {
                $actuel = trim((string)($single[$cle] ?? ''));
                if ($actuel === $val) { continue; }
                Block::setVal($secId, $cle, 'text', $val);
                $changements[] = "$cle «{$actuel}» -> $val";
            }
            if ($changements) {
                echo "  [$chemin] " . implode(' | ', $changements) . "\n";
                $alignees++;
            }
        }
        echo "  $alignees hero(s) aligné(s).\n";
        $poserDrapeau(DRAPEAU_HEROS);
        $modifie = true;
    } catch (\Throwable $e) {
        echo "  ERREUR heros : " . $e->getMessage() . "\n";
        echo "  Drapeau NON posé — l'étape sera rejouée au prochain déploiement.\n";
    }
}

// ════════════════════════════════════════════════════════════════════════════
//  4. « ACCUEIL » EN TÊTE DU MENU — ajout, sans rien retirer
// ════════════════════════════════════════════════════════════════════════════
// Demande utilisateur après la mise en ligne : « Accueil » ne doit pas
// disparaître. Il est AJOUTÉ devant les rubriques existantes ; aucun autre
// lien n'est modifié, déplacé ou supprimé. Si un lien vers l'accueil existe
// déjà (étape 1 jouée avec le plan à jour, ou ajout manuel), rien n'est fait.
echo "\n[4/4] Lien « Accueil »\n";
const DRAPEAU_ACCUEIL = 'primary_menu_home_v1';

$etat = $drapeau(DRAPEAU_ACCUEIL);
if ($etat === true) {
    echo "  Drapeau " . DRAPEAU_ACCUEIL . " déjà posé — menu laissé tel quel.\n";
} elseif ($etat === null) {
    echo "  Étape abandonnée.\n";
} else {
    try {
        $menu = Menu::findByLocation('primary');
        if (!$menu) {
            throw new \RuntimeException("aucun menu à l'emplacement « primary »");
        }
        $menuId = (int)$menu['id'];
        $home   = Database::fetch("SELECT id FROM pages WHERE slug = 'home' LIMIT 1");
        $homeId = $home ? (int)$home['id'] : 0;

        $existe = Database::fetch(
            "SELECT id FROM menu_items
              WHERE menu_id = :m AND (url = '/' OR (page_id = :p AND (url IS NULL OR url = '')))
              LIMIT 1",
            ['m' => $menuId, 'p' => $homeId]
        );
        if ($existe) {
            echo "  Un lien vers l'accueil existe déjà (#{$existe['id']}) — rien à ajouter.\n";
        } else {
            // Juste avant la première rubrique : aucun renumérotage des autres liens.
            $premier = (int)(Database::fetch(
                "SELECT COALESCE(MIN(sort_order), 0) AS o FROM menu_items
                  WHERE menu_id = :m AND parent_id IS NULL", ['m' => $menuId]
            )['o'] ?? 0);
            $id = Database::insert(
                "INSERT INTO menu_items (menu_id, parent_id, page_id, label, url, target, icon, sort_order, is_active)
                 VALUES (:m, NULL, :p, 'Accueil', '/', '_self', '', :o, 1)",
                ['m' => $menuId, 'p' => $homeId ?: null, 'o' => $premier - 1]
            );
            echo "  « Accueil » ajouté en tête du menu (#$id, position " . ($premier - 1) . ").\n";
            $modifie = true;
        }
        $poserDrapeau(DRAPEAU_ACCUEIL);
    } catch (\Throwable $e) {
        echo "  ERREUR accueil : " . $e->getMessage() . "\n";
        echo "  Drapeau NON posé — l'étape sera rejouée au prochain déploiement.\n";
    }
}

if ($modifie) {
    try { \App\Services\Cache::clear(); echo "\nCache vidé.\n"; } catch (\Throwable $e) {}
}

echo "=== TERMINÉ ===\n";
