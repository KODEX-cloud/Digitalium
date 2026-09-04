<?php
/**
 * Build Réalisations — page /realisations et extension du module Réalisations
 *
 * Deux volets :
 *  1. Étend la table `projects` avec les champs d'une étude de cas complète.
 *  2. Crée la page CMS « realisations » et monte ses sections.
 *
 * La page ne contient AUCUNE réalisation en dur : la section `projects_cms`
 * lit la table `projects`. Tant qu'aucune réalisation n'est saisie, la grille
 * affiche le message d'attente administrable — jamais de faux client.
 *
 * Script RÉCONCILIATEUR (leçon BUG-HERO-01) : existence, statut et position des
 * sections sont réalignés à chaque déploiement ; le CONTENU n'est semé que si
 * la section est vide, donc rien de ce qui est modifié en admin n'est écrasé.
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

use App\Models\Page;
use App\Models\Section;
use App\Models\Block;
use App\Services\Database;

echo "=== BUILD REALISATIONS (/realisations) ===\n";

try {
    // ── 1. Colonnes de l'étude de cas ───────────────────────────────────────
    // `context` porte déjà le problème et `impact` les résultats : les
    // dupliquer aurait créé deux colonnes pour une même donnée (Règle #1).
    $pdo = Database::getConnection();

    $existing = [];
    foreach ($pdo->query("SHOW COLUMNS FROM `projects`") as $col) {
        $existing[$col['Field']] = true;
    }

    $newColumns = [
        'status'             => "VARCHAR(20) NULL DEFAULT 'published'",
        'sector'             => "VARCHAR(150) NULL",
        'year'               => "VARCHAR(10) NULL",
        'objectives'         => "TEXT NULL",
        'solution'           => "TEXT NULL",
        'features'           => "TEXT NULL",
        'testimonial_quote'  => "TEXT NULL",
        'testimonial_author' => "VARCHAR(150) NULL",
        'testimonial_role'   => "VARCHAR(150) NULL",
        'meta_title'         => "VARCHAR(255) NULL",
        'meta_description'   => "TEXT NULL",
    ];

    $added = [];
    foreach ($newColumns as $name => $definition) {
        if (isset($existing[$name])) { continue; }
        $pdo->exec("ALTER TABLE `projects` ADD COLUMN `$name` $definition");
        $added[] = $name;
    }
    echo $added
        ? "Colonnes ajoutées à `projects` : " . implode(', ', $added) . "\n"
        : "Colonnes de `projects` déjà à jour.\n";

    // Les lignes antérieures n'ont pas de statut : les laisser NULL les ferait
    // passer pour des brouillons dans un futur filtre strict.
    $pdo->exec("UPDATE `projects` SET status = 'published' WHERE status IS NULL OR status = ''");

    $nbProjects = (int)($pdo->query("SELECT COUNT(*) AS n FROM `projects`")->fetch()['n'] ?? 0);
    echo "Réalisations enregistrées : $nbProjects\n";

    // ── 2. Page CMS ─────────────────────────────────────────────────────────
    $page = Page::findBySlug('realisations');
    if (!$page) {
        $pageId = (int)Page::createPage(
            'Réalisations',
            'realisations',
            'Réalisations et études de cas — Digitalium Group',
            "Découvrez comment Digitalium Group transforme des problématiques métiers en solutions technologiques performantes, évolutives et adaptées aux réalités de ses clients.",
            'published'
        );
        echo "Page 'realisations' créée (#$pageId).\n";
    } else {
        $pageId = (int)$page['id'];
        echo "Page 'realisations' déjà présente (#$pageId).\n";
    }

    // Placée après Secteurs d'activité dans la navigation.
    $sectorsPage = Page::findBySlug('secteurs');
    $navOrder = $sectorsPage ? ((int)($sectorsPage['sort_order'] ?? 3) + 1) : 4;
    Database::query(
        "UPDATE pages SET status = 'published', in_navigation = 1, sort_order = :o, hero_status = 0 WHERE id = :id",
        ['o' => $navOrder, 'id' => $pageId]
    );
    echo "Navigation : publiée, position $navOrder, hero de page désactivé.\n";

    /**
     * Le header lit d'abord le menu 'primary' ; poser in_navigation ne suffit
     * pas quand ce menu existe. Insertion idempotente, sans renuméroter les
     * entrées rangées à la main en admin.
     */
    $primaryMenu = Database::fetch("SELECT id FROM menus WHERE location = 'primary' LIMIT 1");
    if ($primaryMenu) {
        $menuId = (int)$primaryMenu['id'];
        $itemCount = (int)(Database::fetch("SELECT COUNT(*) AS n FROM menu_items WHERE menu_id = :m", ['m' => $menuId])['n'] ?? 0);

        if ($itemCount === 0) {
            echo "Menu 'primary' vide — la page apparaît via in_navigation.\n";
        } else {
            $existingItem = Database::fetch(
                "SELECT id, is_active FROM menu_items
                 WHERE menu_id = :m AND (page_id = :p OR url IN ('/realisations', 'realisations'))
                 LIMIT 1",
                ['m' => $menuId, 'p' => $pageId]
            );

            if ($existingItem) {
                if ((int)$existingItem['is_active'] !== 1) {
                    Database::query(
                        "UPDATE menu_items SET is_active = 1, page_id = :p WHERE id = :id",
                        ['p' => $pageId, 'id' => (int)$existingItem['id']]
                    );
                    echo "Menu 'primary' : entrée Réalisations réactivée.\n";
                } else {
                    echo "Menu 'primary' : entrée Réalisations déjà présente.\n";
                }
            } else {
                $anchor = null;
                if ($sectorsPage) {
                    $anchor = Database::fetch(
                        "SELECT sort_order FROM menu_items
                         WHERE menu_id = :m AND parent_id IS NULL
                           AND (page_id = :p OR url IN ('/secteurs', 'secteurs'))
                         LIMIT 1",
                        ['m' => $menuId, 'p' => (int)$sectorsPage['id']]
                    );
                }
                $itemOrder = $anchor
                    ? (int)$anchor['sort_order']
                    : (int)(Database::fetch(
                        "SELECT COALESCE(MAX(sort_order), 0) AS o FROM menu_items WHERE menu_id = :m AND parent_id IS NULL",
                        ['m' => $menuId]
                    )['o'] ?? 0) + 1;

                Database::query(
                    "INSERT INTO menu_items (menu_id, parent_id, page_id, label, url, target, icon, sort_order, is_active)
                     VALUES (:m, NULL, :p, 'Réalisations', '/realisations', '_self', '', :o, 1)",
                    ['m' => $menuId, 'p' => $pageId, 'o' => $itemOrder]
                );
                echo "Menu 'primary' : entrée Réalisations ajoutée (position $itemOrder).\n";
            }
        }
    } else {
        echo "Aucun menu 'primary' — la page apparaît via in_navigation.\n";
    }

    // ── 3. Sections ─────────────────────────────────────────────────────────
    $reconcile = function (string $type, string $name, int $order) use ($pageId): int {
        foreach (Section::getByPage($pageId) as $s) {
            if (($s['type'] ?? '') === $type) {
                $id = (int)$s['id'];
                if (($s['status'] ?? 'active') !== 'active' || (int)$s['sort_order'] !== $order) {
                    Database::query(
                        "UPDATE sections SET status = 'active', sort_order = :o WHERE id = :id",
                        ['o' => $order, 'id' => $id]
                    );
                }
                return $id;
            }
        }
        $id = (int)Section::addSection($pageId, $name, $type, $order);
        Database::query("UPDATE sections SET status = 'active', sort_order = :o WHERE id = :id", ['o' => $order, 'id' => $id]);
        echo "  Section créée : #$id [$type] $name (position $order)\n";
        return $id;
    };

    $seed = function (int $secId, array $singles, array $groups = []) : bool {
        $content = Block::getStructuredContent($secId);
        if (!empty($content['single']) || !empty($content['groups'])) {
            echo "    contenu déjà présent — non modifié.\n";
            return false;
        }
        foreach ($singles as $key => $value) {
            if ($value === '') { continue; }
            $type = str_contains($key, 'url') || str_contains($key, 'link') ? 'link'
                  : (str_contains($key, 'image') && !str_contains($key, '_ratio') && !str_contains($key, '_radius') && !str_contains($key, '_max_width') ? 'image'
                  : (strlen($value) > 120 ? 'textarea' : 'text'));
            Block::setVal($secId, $key, $type, $value);
        }
        foreach ($groups as $g => $fields) {
            foreach ($fields as $key => $value) {
                if ($value === '') { continue; }
                Block::setVal($secId, $key, 'text', $value, $g + 1, $g);
            }
        }
        echo "    " . count($singles) . " blocs + " . count($groups) . " groupes semés.\n";
        return true;
    };

    // ── HERO ────────────────────────────────────────────────────────────────
    echo "\n[1/4] Hero\n";
    $id = $reconcile('hero_media_cards', 'Hero — réalisations', -1);
    $seed($id, [
        'badge'              => 'Nos réalisations',
        'title'              => "Des solutions numériques",
        'title_accent'       => "qui produisent des résultats concrets.",
        'text'               => "Découvrez comment Digitalium Group transforme des problématiques métiers en solutions technologiques performantes, évolutives et adaptées aux réalités de nos clients.",
        'cta1_text'          => 'Découvrir nos projets',
        'cta1_url'           => '#projets',
        'cta1_icon'          => 'arrow-down',
        'cta2_text'          => 'Parler de mon projet',
        'cta2_url'           => '/contact',
        'cta2_icon'          => 'message-square',
        'image_alt'          => "Des solutions numériques qui produisent des résultats concrets",
        'decor'              => '1',
        // Même composition que /secteurs : texte à gauche par-dessus le visuel.
        'layout'             => 'overlay',
        'image_max_width'    => '1250',
        'image_ratio'        => '1250 / 500',
        'image_ratio_mobile' => '16 / 9',
        'overlay_opacity'    => '62',
        'overlay_min_height' => '460',
        'image_radius'       => '0',
    ]);
    echo "    NOTE : le visuel du hero reste à choisir en admin (champ « Visuel »).\n";

    // ── PROJETS ─────────────────────────────────────────────────────────────
    echo "[2/4] Réalisations\n";
    $id = $reconcile('projects_cms', 'Réalisations — grille filtrable', 0);
    $seed($id, [
        'tag'          => 'Études de cas',
        'title'        => "Nos réalisations",
        'subtitle'     => "Chaque projet répond à un besoin métier précis, avec des choix techniques assumés.",
        'filter_all'   => 'Tous',
        'cta_text'     => "Voir l'étude de cas",
        'show_filters' => '1',
        'empty_text'   => "Les études de cas sont en cours de publication. Contactez-nous pour découvrir nos réalisations en détail.",
    ], [
        // Ordre d'affichage des filtres. Une catégorie qu'aucun projet
        // n'utilise n'est pas affichée : le filtre serait vide.
        ['cat_value' => 'Software',            'cat_label' => 'Software'],
        ['cat_value' => 'IA & Automatisation', 'cat_label' => 'IA & Automatisation'],
        ['cat_value' => 'Data & BI',           'cat_label' => 'Data & BI'],
        ['cat_value' => 'Infrastructure',      'cat_label' => 'Infrastructure'],
        ['cat_value' => 'Web & Digital',       'cat_label' => 'Web & Digital'],
        ['cat_value' => 'Cybersécurité',       'cat_label' => 'Cybersécurité'],
        ['cat_value' => 'Autres',              'cat_label' => 'Autres'],
    ]);

    // ── EXPERTISES ──────────────────────────────────────────────────────────
    echo "[3/4] Expertises\n";
    $id = $reconcile('capabilities_grid', 'Expertises complémentaires', 1);
    $seed($id, [
        'tag'      => 'Nos expertises',
        'title'    => "Des expertises complémentaires pour construire des solutions complètes.",
        'subtitle' => "Un projet mobilise rarement une seule compétence : nos pôles travaillent ensemble sur une même solution.",
    ], [
        ['cap_icon' => 'code-2',       'cap_title' => 'Software & Applications',
         'cap_desc' => "Applications métier, plateformes web et mobiles conçues autour des processus réels de l'organisation."],
        ['cap_icon' => 'bot',          'cap_title' => 'IA & Automatisation',
         'cap_desc' => "Automatisation des tâches répétitives et assistance intelligente au traitement des demandes."],
        ['cap_icon' => 'bar-chart-3',  'cap_title' => 'Data & Business Intelligence',
         'cap_desc' => "Consolidation des données éparses et restitution en indicateurs lisibles et actionnables."],
        ['cap_icon' => 'cloud',        'cap_title' => 'Infrastructure & Cloud',
         'cap_desc' => "Architectures dimensionnées et supervisées, capables de suivre la croissance de l'activité."],
        ['cap_icon' => 'shield-check', 'cap_title' => 'Cybersécurité',
         'cap_desc' => "Protection des accès, des données et de la continuité de service."],
        ['cap_icon' => 'life-buoy',    'cap_title' => 'Managed IT',
         'cap_desc' => "Exploitation, maintenance et accompagnement des équipes dans la durée."],
    ]);

    // ── CTA FINAL ───────────────────────────────────────────────────────────
    echo "[4/4] CTA final\n";
    $id = $reconcile('cta', 'CTA final', 2);
    // Clés exactes attendues par app/Views/frontend/sections/cta.php.
    $seed($id, [
        'eyebrow'   => 'Parlons de votre projet',
        'title'     => "Votre prochain projet peut être notre prochaine réalisation.",
        'subtitle'  => "Parlez-nous de votre besoin et construisons ensemble une solution adaptée à vos objectifs.",
        'cta_text'  => "Parler à un expert",
        'cta_url'   => '/contact',
        'cta2_text' => "Demander un devis",
        'cta2_url'  => '/contact',
    ]);

    // ── 4. Libellés de l'étude de cas ───────────────────────────────────────
    // Posés UNIQUEMENT s'ils manquent : une formulation changée en admin est
    // conservée. Ils rendent administrables les titres de rubriques de
    // /realisations/{slug}, qui seraient sinon codés en dur dans la vue.
    $caseLabels = [
        'case_label_project'    => 'Le projet',
        'case_label_client'     => 'Client',
        'case_label_sector'     => 'Secteur',
        'case_label_category'   => 'Catégorie',
        'case_label_year'       => 'Année',
        'case_label_problem'    => 'Le problème',
        'case_label_objectives' => 'Les objectifs',
        'case_label_solution'   => 'La solution Digitalium',
        'case_label_tech'       => 'Technologies utilisées',
        'case_label_features'   => 'Fonctionnalités principales',
        'case_label_gallery'    => 'Le projet en images',
        'case_label_results'    => 'Résultats obtenus',
        'case_label_related'    => 'Autres réalisations',
        'case_back_text'        => 'Toutes les réalisations',
        'case_visit_text'       => 'Voir le projet en ligne',
        'case_cta_title'        => 'Vous avez un projet similaire ?',
        'case_cta_text'         => "Parlons de votre besoin : nous revenons vers vous avec une lecture de vos enjeux et les options possibles.",
        'case_cta_button'       => 'Parler à Digitalium',
        'case_cta_url'          => '/contact',
    ];

    $addedLabels = [];
    foreach ($caseLabels as $key => $value) {
        $row = Database::fetch("SELECT id FROM settings WHERE setting_key = :k LIMIT 1", ['k' => $key]);
        if (!$row) {
            Database::query(
                "INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v)",
                ['k' => $key, 'v' => $value]
            );
            $addedLabels[] = $key;
        }
    }
    echo "\nLibellés d'étude de cas : " . count($addedLabels) . " ajouté(s), "
       . (count($caseLabels) - count($addedLabels)) . " déjà présent(s).\n";

    \App\Services\Cache::clear();
    echo "\nCache vidé. TERMINÉ.\n";

} catch (\Throwable $e) {
    echo "ERREUR : " . $e->getMessage() . "\n";
    echo "  " . $e->getFile() . ':' . $e->getLine() . "\n";
    exit(1);
}
