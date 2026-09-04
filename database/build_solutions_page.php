<?php
/**
 * Build Solutions Page — page « Solutions » (/solutions) et ses 5 sous-pages
 *
 * Monte la page, sa navigation et ses 8 sections, puis les 5 pages enfants
 * /solutions/{famille}. Aucune ligne de contenu n'est écrite dans les gabarits :
 * tout passe par les blocs CMS et reste modifiable depuis /admin/pages (Règle #2).
 *
 * ── Script RÉCONCILIATEUR (leçon de BUG-HERO-01) ────────────────────────────
 * Existence et position de chaque section sont réalignées à CHAQUE déploiement.
 * Le CONTENU n'est semé que si la section est vide, et le STATUT n'est posé qu'à
 * la création : rien de ce qui est décidé en admin ne peut être écrasé.
 *
 * ── Deux sections du même type sur une même page ────────────────────────────
 * La page porte DEUX grilles `sectors_grid` : les 5 piliers, puis les secteurs
 * d'activité. Le réconciliateur des scripts précédents identifiait une section
 * par son seul type — il aurait confondu les deux et écrasé la première. Ici il
 * apparie sur le couple (type, nom).
 *
 * ── Sous-pages ──────────────────────────────────────────────────────────────
 * Une page enfant porte `pages.parent_slug = 'solutions'` et un slug court.
 * Elle n'est servie qu'à l'adresse imbriquée : HomeController redirige en 301
 * toute tentative d'accès à l'URL courte, pour qu'un contenu n'ait jamais deux
 * adresses (leçon DT-05).
 *
 * ── Réalisations ────────────────────────────────────────────────────────────
 * La section lit le module Réalisations. Si aucune réalisation publiée n'existe,
 * elle est créée puis laissée INACTIVE : pas de bloc creux en ligne, et surtout
 * aucun client ni résultat inventé.
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

echo "=== BUILD SOLUTIONS PAGE (/solutions) ===\n";

try {
    // ── 0. Schéma : rattachement parent/enfant ──────────────────────────────
    //
    // Isolé dans son propre try/catch, et volontairement SANS index.
    //
    // Un ALTER TABLE verrouille `pages`. Or les quatre pages contrôlées par les
    // smoke tests du déploiement (/, /blog, /realisations, /sitemap.xml) lisent
    // toutes cette table : un ALTER lent bloque le site le temps qu'il dure et
    // fait échouer le déploiement entier. `pages` compte une dizaine de lignes,
    // un index n'apporte rien de mesurable et double le nombre d'ALTER.
    //
    // Un échec ici ne doit pas empêcher le reste : la page se construit quand
    // même, seules les sous-pages resteront inaccessibles, et le message dit
    // exactement quoi regarder.
    $hasParentSlug = false;
    try {
        $col = Database::fetch("SHOW COLUMNS FROM `pages` LIKE 'parent_slug'");
        if (!$col) {
            Database::query("ALTER TABLE `pages` ADD COLUMN `parent_slug` VARCHAR(150) NULL DEFAULT NULL");
            echo "pages.parent_slug ajoutée.\n";
        } else {
            echo "pages.parent_slug déjà présente.\n";
        }
        $hasParentSlug = true;
    } catch (\Throwable $e) {
        echo "ATTENTION pages.parent_slug indisponible : " . $e->getMessage() . "\n";
        echo "  -> la page /solutions sera construite, les sous-pages seront ignorées.\n";
    }

    // ── 1. Page ─────────────────────────────────────────────────────────────
    $metaDesc = "Digitalium Group conçoit, développe, automatise et exploite des solutions "
              . "numériques : applications métiers, IA et automatisation, data et business "
              . "intelligence, infrastructure et cybersécurité, opérations managées.";

    $page = Page::findBySlug('solutions');
    if (!$page) {
        $pageId = (int)Page::createPage(
            'Solutions',
            'solutions',
            'Nos solutions technologiques — Digitalium Group',
            $metaDesc,
            'published'
        );
        echo "Page 'solutions' créée (#$pageId).\n";
    } else {
        $pageId = (int)$page['id'];
        echo "Page 'solutions' déjà présente (#$pageId).\n";
    }

    // Navigation : juste après Services, avant Secteurs d'activité.
    $servicePage = Page::findBySlug('service');
    $navOrder = $servicePage ? ((int)($servicePage['sort_order'] ?? 2) + 1) : 3;
    // `parent_slug` n est remis a NULL que si la colonne existe : /solutions
    // est une page parente, jamais une enfant.
    Database::query(
        "UPDATE pages SET status = 'published', in_navigation = 1, sort_order = :o,
                          hero_status = 0" . ($hasParentSlug ? ", parent_slug = NULL" : "") . "
         WHERE id = :id",
        ['o' => $navOrder, 'id' => $pageId]
    );
    echo "Navigation : publiée, in_navigation = 1, position $navOrder.\n";

    /**
     * Couleur d'accent de la page — bleu clair du logo Digitalium.
     * Posée UNE SEULE FOIS : si la valeur a été changée en admin, on n'y touche
     * plus. Le champ existe depuis la page Réalisations (colonne accent_color).
     */
    $accentCol = Database::fetch("SHOW COLUMNS FROM `pages` LIKE 'accent_color'");
    if (!$accentCol) {
        Database::query("ALTER TABLE `pages` ADD COLUMN `accent_color` VARCHAR(20) NULL DEFAULT NULL");
        echo "pages.accent_color ajoutée.\n";
    }
    $current = Database::fetch("SELECT accent_color FROM pages WHERE id = :id", ['id' => $pageId]);
    if (trim((string)($current['accent_color'] ?? '')) === '') {
        Database::query("UPDATE pages SET accent_color = '#0868B0' WHERE id = :id", ['id' => $pageId]);
        echo "Accent : #0868B0 (bleu clair du logo).\n";
    } else {
        echo "Accent : {$current['accent_color']} — choix conservé.\n";
    }

    /**
     * Le header (layout.php) lit d'ABORD le menu 'primary' et ne retombe sur
     * `in_navigation` que si ce menu est vide. Poser in_navigation ne suffit
     * donc pas : il faut déclarer l'entrée. Réconciliateur et idempotent.
     */
    $primaryMenu = Database::fetch("SELECT id FROM menus WHERE location = 'primary' LIMIT 1");
    if ($primaryMenu) {
        $menuId = (int)$primaryMenu['id'];
        $itemCount = (int)(Database::fetch(
            "SELECT COUNT(*) AS n FROM menu_items WHERE menu_id = :m", ['m' => $menuId]
        )['n'] ?? 0);

        if ($itemCount === 0) {
            echo "Menu 'primary' vide — la page apparaît via in_navigation.\n";
        } else {
            $existing = Database::fetch(
                "SELECT id, is_active FROM menu_items
                 WHERE menu_id = :m AND (page_id = :p OR url IN ('/solutions', 'solutions'))
                 LIMIT 1",
                ['m' => $menuId, 'p' => $pageId]
            );
            if ($existing) {
                if ((int)$existing['is_active'] !== 1) {
                    Database::query(
                        "UPDATE menu_items SET is_active = 1, page_id = :p WHERE id = :id",
                        ['p' => $pageId, 'id' => (int)$existing['id']]
                    );
                    echo "Menu 'primary' : entrée Solutions réactivée (#{$existing['id']}).\n";
                } else {
                    echo "Menu 'primary' : entrée Solutions déjà présente (#{$existing['id']}).\n";
                }
            } else {
                // Position : juste après Services. À sort_order égal, l'ordre
                // secondaire (id ASC) place la nouvelle entrée derrière, sans
                // renuméroter un menu rangé à la main en admin.
                $svcItem = $servicePage ? Database::fetch(
                    "SELECT sort_order FROM menu_items
                     WHERE menu_id = :m AND parent_id IS NULL
                       AND (page_id = :p OR url IN ('/service', 'service')) LIMIT 1",
                    ['m' => $menuId, 'p' => (int)$servicePage['id']]
                ) : null;

                $itemOrder = $svcItem
                    ? (int)$svcItem['sort_order']
                    : (int)(Database::fetch(
                        "SELECT COALESCE(MAX(sort_order), 0) AS o FROM menu_items
                         WHERE menu_id = :m AND parent_id IS NULL", ['m' => $menuId]
                    )['o'] ?? 0) + 1;

                Database::query(
                    "INSERT INTO menu_items (menu_id, parent_id, page_id, label, url, target, icon, sort_order, is_active)
                     VALUES (:m, NULL, :p, 'Solutions', '/solutions', '_self', '', :o, 1)",
                    ['m' => $menuId, 'p' => $pageId, 'o' => $itemOrder]
                );
                echo "Menu 'primary' : entrée Solutions ajoutée (position $itemOrder).\n";
            }
        }
    } else {
        echo "Aucun menu 'primary' — la page apparaît via in_navigation.\n";
    }

    // ── Outils de réconciliation ────────────────────────────────────────────

    /**
     * Réaligne une section sans toucher à son contenu.
     *
     * L'appariement porte sur le couple (type, nom) : la page a deux grilles
     * `sectors_grid`, un appariement par type seul les confondrait.
     *
     * La POSITION est réalignée à chaque déploiement, le STATUT ne l'est qu'à
     * la création. Le brief demande de pouvoir activer et désactiver chaque
     * section depuis l'admin : un déploiement qui rallumerait ce qui vient
     * d'être éteint rendrait ce réglage inopérant.
     */
    $reconcile = function (int $pid, string $type, string $name, int $order, string $status = 'active') : int {
        foreach (Section::getByPage($pid) as $s) {
            if (($s['type'] ?? '') === $type && ($s['name'] ?? '') === $name) {
                $id = (int)$s['id'];
                Database::query(
                    "UPDATE sections SET sort_order = :o WHERE id = :id",
                    ['o' => $order, 'id' => $id]
                );
                return $id;
            }
        }
        $id = (int)Section::addSection($pid, $name, $type, $order);
        Database::query(
            "UPDATE sections SET status = :st, sort_order = :o WHERE id = :id",
            ['st' => $status, 'o' => $order, 'id' => $id]
        );
        echo "  Section créée : #$id [$type] $name (position $order, $status)\n";
        return $id;
    };

    /** Sème les blocs UNIQUEMENT si la section est encore vide. */
    $seed = function (int $secId, array $singles, array $groups = []) : bool {
        $content = Block::getStructuredContent($secId);
        if (!empty($content['single']) || !empty($content['groups'])) {
            echo "    contenu déjà présent — non modifié.\n";
            return false;
        }
        foreach ($singles as $key => $value) {
            if ($value === '') { continue; }
            $type = \App\Helpers\BlockFieldHelper::type($key);
            // BlockFieldHelper décrit comment ÉDITER le champ ; le stockage ne
            // connaît que text/textarea/image/link. « select » se range en text.
            if ($type === 'select') { $type = 'text'; }
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

    // ══════════════════════════════════════════════════════════════════════
    //  SECTIONS DE /solutions
    // ══════════════════════════════════════════════════════════════════════

    // ── 2. HERO ─────────────────────────────────────────────────────────────
    echo "\n[1/8] Hero\n";
    $id = $reconcile($pageId, 'hero_media_cards', 'Hero — Solutions', -1);
    $seed($id, [
        'badge'              => 'Nos solutions',
        'title'              => 'Des solutions technologiques',
        'title_accent'       => 'pensées pour vos enjeux réels.',
        'text'               => "Digitalium Group conçoit, développe, automatise et exploite des solutions "
                              . "numériques adaptées aux entreprises et organisations qui veulent gagner en "
                              . "efficacité, en performance et en capacité d'évolution.",
        'cta1_text'          => 'Découvrir nos solutions',
        'cta1_url'           => '#piliers',
        'cta1_icon'          => 'arrow-down',
        'cta2_text'          => 'Parler à un expert',
        'cta2_url'           => '/contact',
        'cta2_icon'          => 'message-square',
        'decor'              => '1',
        // Format demandé au brief : environ 1250 x 500, texte par-dessus le visuel.
        'layout'             => 'overlay',
        'image_max_width'    => '1250',
        'image_ratio'        => '1250 / 500',
        'image_ratio_mobile' => '16 / 9',
        'overlay_opacity'    => '64',
        'overlay_min_height' => '500',
        'image_radius'       => '0',
    ]);

    // ── 3. LES 5 PILIERS ────────────────────────────────────────────────────
    echo "[2/8] Les 5 piliers\n";
    $id = $reconcile($pageId, 'sectors_grid', 'Nos 5 piliers', 0);
    $seed($id, [
        'tag'      => 'Nos familles de solutions',
        'title'    => "Cinq domaines, un même point de départ : votre besoin.",
        'subtitle' => "Chaque famille répond à un type de problème. La plupart des projets en "
                    . "combinent plusieurs — c'est l'assemblage qui fait la solution.",
    ], [
        ['sec_num' => '01', 'sec_icon' => 'layout-grid', 'sec_title' => 'Software & Platforms',
         'sec_desc' => "Applications métiers, plateformes web et mobiles conçues autour de vos processus réels plutôt que l'inverse.",
         'sec_needs' => "Applications métiers | Plateformes web | Applications mobiles | SaaS | Portails clients | API & intégrations",
         'sec_link' => '/solutions/software-platforms', 'sec_link_text' => 'Explorer'],

        ['sec_num' => '02', 'sec_icon' => 'bot', 'sec_title' => 'AI & Automation',
         'sec_desc' => "Agents, assistants et automatisations qui prennent en charge le travail répétitif et libèrent vos équipes.",
         'sec_needs' => "Agents IA | Assistants métiers | Automatisation de processus | Workflows | Chatbots | RPA",
         'sec_link' => '/solutions/ia-automatisation', 'sec_link_text' => 'Explorer'],

        ['sec_num' => '03', 'sec_icon' => 'bar-chart-3', 'sec_title' => 'Data & Business Intelligence',
         'sec_desc' => "Vos données de gestion transformées en indicateurs lisibles, puis en décisions prises au bon moment.",
         'sec_needs' => "Dashboards | Reporting | Analyse de données | Indicateurs de performance | Data visualisation | Aide à la décision",
         'sec_link' => '/solutions/data-business-intelligence', 'sec_link_text' => 'Explorer'],

        ['sec_num' => '04', 'sec_icon' => 'shield-check', 'sec_title' => 'Infrastructure & Security',
         'sec_desc' => "Réseaux, cloud et cybersécurité : une informatique qui tient, se sauvegarde et redémarre.",
         'sec_needs' => "Réseaux | Cloud | Serveurs | Sauvegarde | Cybersécurité | Continuité d'activité",
         'sec_link' => '/solutions/infrastructure-security', 'sec_link_text' => 'Explorer'],

        ['sec_num' => '05', 'sec_icon' => 'life-buoy', 'sec_title' => 'Managed Operations',
         'sec_desc' => "Support, supervision et maintenance : votre informatique gérée dans la durée, pas seulement livrée.",
         'sec_needs' => "Support informatique | Maintenance | Supervision | Managed IT | Business Operations | Accompagnement continu",
         'sec_link' => '/solutions/managed-operations', 'sec_link_text' => 'Explorer'],
    ]);

    // ── 4. NOTRE APPROCHE ───────────────────────────────────────────────────
    echo "[3/8] Notre approche\n";
    $id = $reconcile($pageId, 'process_strip', 'Notre approche', 1);
    $seed($id, [
        'tag'      => 'Notre approche',
        'title'    => "Une solution utile commence par une bonne compréhension du besoin.",
        'subtitle' => "Digitalium ne pousse pas une technologie simplement parce qu'elle existe. "
                    . "Nous sélectionnons et assemblons les solutions qui répondent réellement aux "
                    . "contraintes de l'organisation.",
    ], [
        ['proc_num' => '01', 'proc_icon' => 'search',      'proc_title' => 'Comprendre',
         'proc_desc' => "Analyser le métier, les processus réels et les contraintes du terrain."],
        ['proc_num' => '02', 'proc_icon' => 'pen-tool',    'proc_title' => 'Concevoir',
         'proc_desc' => "Définir l'architecture et le périmètre qui répondent au besoin, sans surcouche inutile."],
        ['proc_num' => '03', 'proc_icon' => 'hammer',      'proc_title' => 'Construire',
         'proc_desc' => "Développer la solution par étapes livrables, vérifiables à chaque palier."],
        ['proc_num' => '04', 'proc_icon' => 'plug',        'proc_title' => 'Intégrer',
         'proc_desc' => "Connecter la solution aux outils déjà en place et aux habitudes des équipes."],
        ['proc_num' => '05', 'proc_icon' => 'workflow',    'proc_title' => 'Automatiser',
         'proc_desc' => "Retirer les tâches répétitives une fois le processus stabilisé."],
        ['proc_num' => '06', 'proc_icon' => 'trending-up', 'proc_title' => 'Accompagner',
         'proc_desc' => "Mesurer l'usage, ajuster et faire évoluer au rythme de l'organisation."],
    ]);

    // ── 5. CAS D'USAGE ──────────────────────────────────────────────────────
    echo "[4/8] Cas d'usage\n";
    $id = $reconcile($pageId, 'capabilities_grid', "Cas d'usage", 2);
    $seed($id, [
        'tag'      => "Cas d'usage",
        'title'    => "À quoi ressemble une solution assemblée.",
        'subtitle' => "Quatre situations fréquentes et les briques qu'elles mobilisent.",
    ], [
        ['cap_icon' => 'workflow',         'cap_title' => 'Automatiser des tâches répétitives',
         'cap_desc' => 'IA + workflow + CRM'],
        ['cap_icon' => 'layout-dashboard', 'cap_title' => "Centraliser les opérations d'une entreprise",
         'cap_desc' => 'Application métier + API + dashboard'],
        ['cap_icon' => 'line-chart',       'cap_title' => 'Exploiter les données de gestion',
         'cap_desc' => 'Data + BI + reporting'],
        ['cap_icon' => 'shield',           'cap_title' => "Sécuriser et fiabiliser l'infrastructure",
         'cap_desc' => 'Cloud + cybersécurité + Managed IT'],
    ]);

    // ── 6. SOLUTIONS PAR BESOIN ─────────────────────────────────────────────
    echo "[5/8] Solutions par besoin\n";
    $id = $reconcile($pageId, 'needs_router', 'Solutions par besoin', 3);
    $seed($id, [
        'tag'         => 'Par besoin',
        'title'       => "Dites-nous ce que vous voulez faire.",
        'subtitle'    => "Vous n'avez pas à connaître le nom de la technologie : partez de votre besoin, "
                       . "nous vous orientons vers la famille de solutions concernée.",
        'intro_label' => 'Je veux',
    ], [
        ['need_icon' => 'git-branch',   'need_text' => 'Digitaliser un processus',
         'need_solution' => 'Software & Platforms',           'need_link' => '/solutions/software-platforms'],
        ['need_icon' => 'app-window',   'need_text' => 'Construire une application',
         'need_solution' => 'Software & Platforms',           'need_link' => '/solutions/software-platforms'],
        ['need_icon' => 'bot',          'need_text' => 'Automatiser des tâches',
         'need_solution' => 'AI & Automation',                'need_link' => '/solutions/ia-automatisation'],
        ['need_icon' => 'bar-chart-3',  'need_text' => 'Exploiter mes données',
         'need_solution' => 'Data & Business Intelligence',   'need_link' => '/solutions/data-business-intelligence'],
        ['need_icon' => 'shield-check', 'need_text' => 'Sécuriser mon infrastructure',
         'need_solution' => 'Infrastructure & Security',      'need_link' => '/solutions/infrastructure-security'],
        ['need_icon' => 'life-buoy',    'need_text' => 'Améliorer mon support informatique',
         'need_solution' => 'Managed Operations',             'need_link' => '/solutions/managed-operations'],
    ]);

    // ── 7. SOLUTIONS PAR SECTEUR ────────────────────────────────────────────
    // Les secteurs sont ceux de la page /secteurs : mêmes intitulés, un seul
    // discours. Le bouton renvoie vers la page qui les détaille.
    echo "[6/8] Solutions par secteur\n";
    $id = $reconcile($pageId, 'sectors_grid', 'Solutions par secteur', 4);
    $seed($id, [
        'tag'       => 'Par secteur',
        'title'     => "Les mêmes familles, appliquées aux contraintes de votre métier.",
        'subtitle'  => "Un tableau de bord n'a pas la même forme dans une mutuelle et dans un commerce. "
                     . "Le socle technique est commun, l'application ne l'est jamais.",
        'more_text' => 'Voir tous les secteurs',
        'more_url'  => '/secteurs',
    ], [
        ['sec_num' => '01', 'sec_icon' => 'landmark',    'sec_title' => 'Finance & Assurance',    'sec_link' => '/secteurs'],
        ['sec_num' => '02', 'sec_icon' => 'graduation-cap', 'sec_title' => 'Éducation & Formation', 'sec_link' => '/secteurs'],
        ['sec_num' => '03', 'sec_icon' => 'shopping-cart', 'sec_title' => 'Commerce & Distribution', 'sec_link' => '/secteurs'],
        ['sec_num' => '04', 'sec_icon' => 'building-2',  'sec_title' => 'Immobilier',             'sec_link' => '/secteurs'],
        ['sec_num' => '05', 'sec_icon' => 'heart-pulse', 'sec_title' => 'Santé',                  'sec_link' => '/secteurs'],
        ['sec_num' => '06', 'sec_icon' => 'briefcase',   'sec_title' => 'Entreprises & PME',      'sec_link' => '/secteurs'],
        ['sec_num' => '07', 'sec_icon' => 'users',       'sec_title' => 'Associations & Organisations', 'sec_link' => '/secteurs'],
        ['sec_num' => '08', 'sec_icon' => 'calendar',    'sec_title' => 'Événementiel',           'sec_link' => '/secteurs'],
    ]);

    // ── 8. RÉALISATIONS ─────────────────────────────────────────────────────
    // Lit le module Réalisations. Aucun projet inventé : sans réalisation
    // publiée, la section reste inactive plutôt que d'afficher un bloc creux.
    echo "[7/8] Réalisations\n";
    $realCount = (int)(Database::fetch(
        "SELECT COUNT(*) AS n FROM projects WHERE status = 'published' OR status IS NULL OR status = ''"
    )['n'] ?? 0);
    $realStatus = $realCount > 0 ? 'active' : 'inactive';
    $id = $reconcile($pageId, 'projects_cms', 'Réalisations — aperçu', 5, $realStatus);
    $seed($id, [
        'tag'          => 'Réalisations',
        'title'        => "Des solutions déjà en service.",
        'subtitle'     => "Quelques projets menés par Digitalium Group.",
        'show_filters' => '0',
        'limit'        => '3',
        'more_text'    => 'Découvrir nos réalisations',
        'more_url'     => '/realisations',
        'empty_text'   => "Les études de cas sont en cours de publication.",
    ]);
    echo $realCount > 0
        ? "    $realCount réalisation(s) publiée(s) — section active.\n"
        : "    aucune réalisation publiée — section créée mais INACTIVE (activable depuis /admin/pages).\n";

    // ── 9. CTA FINAL ────────────────────────────────────────────────────────
    echo "[8/8] CTA final\n";
    $id = $reconcile($pageId, 'cta', 'CTA final', 6);
    $seed($id, [
        'eyebrow'   => 'Un problème métier ?',
        'title'     => "Construisons la solution adaptée.",
        'subtitle'  => "Expliquez-nous votre besoin, votre processus ou votre objectif. Digitalium vous "
                     . "aide à identifier et mettre en œuvre la meilleure réponse technologique.",
        'cta_text'  => 'Parler à un expert',
        'cta_url'   => '/contact',
        'cta2_text' => 'Demander un devis',
        'cta2_url'  => '/contact',
    ]);

    // ══════════════════════════════════════════════════════════════════════
    //  SOUS-PAGES /solutions/{famille}
    // ══════════════════════════════════════════════════════════════════════
    echo "\n=== SOUS-PAGES ===\n";

    $children = [
        [
            'slug'  => 'software-platforms',
            'title' => 'Software & Platforms',
            'badge' => 'Software & Platforms',
            'h1'    => 'Des applications construites',
            'accent' => 'autour de vos processus réels.',
            'text'  => "Plutôt que d'adapter votre organisation à un outil générique, nous concevons "
                     . "l'outil qui épouse la façon dont vous travaillez déjà — et qui laisse la place "
                     . "aux évolutions à venir.",
            'meta'  => "Applications métiers, plateformes web et mobiles, SaaS, portails clients et intégrations API — Digitalium Group.",
            'caps'  => [
                ['code',          'Applications métiers',   "Un outil taillé pour vos processus, pas un logiciel standard qu'il faut contourner."],
                ['globe',         'Plateformes web',        "Sites, extranets et espaces de travail accessibles depuis n'importe quel poste."],
                ['smartphone',    'Applications mobiles',   "L'accès au métier sur le terrain, y compris en connexion instable."],
                ['cloud',         'SaaS',                   "Une solution hébergée, mise à jour et supervisée, facturée à l'usage."],
                ['user-check',    'Portails clients',       "Donner à vos clients un accès direct à leurs dossiers, documents et demandes."],
                ['plug',          'API & intégrations',     "Faire parler entre eux les outils que vous utilisez déjà."],
            ],
        ],
        [
            'slug'  => 'ia-automatisation',
            'title' => 'IA & Automatisation',
            'badge' => 'AI & Automation',
            'h1'    => "Rendre à vos équipes le temps",
            'accent' => "que les tâches répétitives leur prennent.",
            'text'  => "L'automatisation n'a d'intérêt que sur un processus déjà compris et stable. "
                     . "Nous commençons par le cartographier, puis nous retirons ce qui peut l'être.",
            'meta'  => "Agents IA, assistants métiers, automatisation de processus, workflows, chatbots et RPA — Digitalium Group.",
            'caps'  => [
                ['bot',           'Agents IA',                    "Des agents qui traitent une tâche de bout en bout et rendent la main quand il faut décider."],
                ['message-circle','Assistants métiers',           "Un accès en langage naturel à vos procédures, documents et données internes."],
                ['workflow',      'Automatisation de processus',  "Supprimer les ressaisies et les allers-retours entre outils."],
                ['git-branch',    'Workflows',                    "Des enchaînements de validation explicites, traçables et modifiables."],
                ['message-square','Chatbots',                     "Répondre aux demandes courantes sans mobiliser une personne."],
                ['repeat',        'RPA',                          "Faire exécuter par un robot les manipulations qu'aucune API ne couvre."],
            ],
        ],
        [
            'slug'  => 'data-business-intelligence',
            'title' => 'Data & Business Intelligence',
            'badge' => 'Data & Business Intelligence',
            'h1'    => 'Vos données de gestion,',
            'accent' => 'lisibles et actionnables.',
            'text'  => "Beaucoup d'organisations produisent déjà les données dont elles ont besoin, sans "
                     . "pouvoir les lire. Nous les rassemblons, les fiabilisons et les rendons "
                     . "consultables par ceux qui décident.",
            'meta'  => "Dashboards, reporting, analyse de données, indicateurs de performance et data visualisation — Digitalium Group.",
            'caps'  => [
                ['layout-dashboard','Dashboards',                  "Un écran unique qui répond aux questions que vous vous posez chaque semaine."],
                ['file-text',      'Reporting',                    "Des rapports produits automatiquement, à date fixe, sans travail manuel."],
                ['search',         'Analyse de données',           "Comprendre ce que disent vos chiffres avant d'en tirer des conclusions."],
                ['target',         'Indicateurs de performance',   "Choisir peu d'indicateurs, mais ceux qui déclenchent une action."],
                ['pie-chart',      'Data visualisation',           "Des représentations qui se lisent d'un coup d'œil, sans notice."],
                ['compass',        'Aide à la décision',           "Relier les indicateurs aux arbitrages qu'ils doivent éclairer."],
            ],
        ],
        [
            'slug'  => 'infrastructure-security',
            'title' => 'Infrastructure & Sécurité',
            'badge' => 'Infrastructure & Security',
            'h1'    => 'Une informatique qui tient,',
            'accent' => 'se sauvegarde et redémarre.',
            'text'  => "La fiabilité ne se voit que le jour où elle manque. Nous construisons des "
                     . "infrastructures dont la panne est prévue, et dont le retour à la normale est "
                     . "documenté et testé.",
            'meta'  => "Réseaux, cloud, serveurs, sauvegarde, cybersécurité et continuité d'activité — Digitalium Group.",
            'caps'  => [
                ['network',      'Réseaux',                 "Un réseau segmenté, supervisé et dimensionné pour vos usages réels."],
                ['cloud',        'Cloud',                   "Héberger ce qui gagne à l'être, garder chez vous ce qui doit y rester."],
                ['server',       'Serveurs',                "Des machines dimensionnées, mises à jour et surveillées."],
                ['hard-drive',   'Sauvegarde',              "Des sauvegardes vérifiées par restauration, pas seulement planifiées."],
                ['shield-check', 'Cybersécurité',           "Réduire la surface d'attaque et savoir quoi faire en cas d'incident."],
                ['refresh-cw',   "Continuité d'activité",   "Un plan de reprise écrit, chiffré en temps et déjà éprouvé."],
            ],
        ],
        [
            'slug'  => 'managed-operations',
            'title' => 'Managed Operations',
            'badge' => 'Managed Operations',
            'h1'    => 'Votre informatique gérée',
            'accent' => 'dans la durée, pas seulement livrée.',
            'text'  => "Un projet livré puis abandonné se dégrade en quelques mois. Nous restons "
                     . "responsables du maintien en condition opérationnelle et de l'évolution de ce "
                     . "que nous mettons en place.",
            'meta'  => "Support informatique, maintenance, supervision, Managed IT et accompagnement continu — Digitalium Group.",
            'caps'  => [
                ['life-buoy',    'Support informatique',      "Un interlocuteur joignable, qui connaît votre installation."],
                ['wrench',       'Maintenance',               "Mises à jour, correctifs et remplacements planifiés plutôt que subis."],
                ['activity',     'Supervision',               "Détecter la dégradation avant l'interruption."],
                ['settings',     'Managed IT',                "Confier l'exploitation quotidienne pour vous concentrer sur votre métier."],
                ['briefcase',    'Business Operations',       "Prendre en charge des opérations récurrentes au-delà de la seule technique."],
                ['trending-up',  'Accompagnement continu',    "Faire évoluer la solution au rythme de l'organisation."],
            ],
        ],
    ];

    if (!$hasParentSlug) {
        // Sans colonne de rattachement, une sous-page serait créée mais
        // injoignable à son URL imbriquée : mieux vaut ne pas la créer du tout
        // que de publier du contenu sans chemin pour y accéder.
        echo "pages.parent_slug absente — sous-pages ignorées pour ce passage.
";
        $children = [];
    }

    foreach ($children as $c) {
        echo "\n— /solutions/{$c['slug']}\n";
        $child = Page::findBySlug($c['slug']);
        if (!$child) {
            $childId = (int)Page::createPage(
                $c['title'], $c['slug'],
                $c['title'] . ' — Digitalium Group',
                $c['meta'], 'published'
            );
            echo "  page créée (#$childId).\n";
        } else {
            $childId = (int)$child['id'];
            echo "  page déjà présente (#$childId).\n";
        }

        // Rattachement au parent + hors navigation principale : une sous-page se
        // rejoint depuis /solutions, elle n'encombre pas le menu.
        Database::query(
            "UPDATE pages SET status = 'published', parent_slug = 'solutions',
                              in_navigation = 0, hero_status = 0, accent_color = '#0868B0'
             WHERE id = :id",
            ['id' => $childId]
        );

        $sid = $reconcile($childId, 'hero_media_cards', 'Hero — ' . $c['title'], -1);
        $seed($sid, [
            'badge'              => $c['badge'],
            'title'              => $c['h1'],
            'title_accent'       => $c['accent'],
            'text'               => $c['text'],
            'cta1_text'          => 'Parler à un expert',
            'cta1_url'           => '/contact',
            'cta1_icon'          => 'message-square',
            'cta2_text'          => 'Voir toutes les solutions',
            'cta2_url'           => '/solutions',
            'cta2_icon'          => 'arrow-left',
            'decor'              => '1',
            'layout'             => 'overlay',
            'image_max_width'    => '1250',
            'image_ratio'        => '1250 / 420',
            'image_ratio_mobile' => '16 / 9',
            'overlay_opacity'    => '64',
            'overlay_min_height' => '420',
            'image_radius'       => '0',
        ]);

        $sid = $reconcile($childId, 'capabilities_grid', 'Capacités', 0);
        $groupsCaps = [];
        foreach ($c['caps'] as [$icon, $title, $desc]) {
            $groupsCaps[] = ['cap_icon' => $icon, 'cap_title' => $title, 'cap_desc' => $desc];
        }
        $seed($sid, [
            'tag'      => 'Ce que cela recouvre',
            'title'    => 'Les capacités de cette famille.',
            'subtitle' => "Rarement toutes mobilisées à la fois : le périmètre se définit à partir de votre besoin.",
        ], $groupsCaps);

        $sid = $reconcile($childId, 'cta', 'CTA final', 1);
        $seed($sid, [
            'eyebrow'   => 'Un besoin dans ce domaine ?',
            'title'     => "Parlons-en avant de parler technologie.",
            'subtitle'  => "Décrivez-nous votre situation : nous revenons vers vous avec une lecture de vos "
                         . "enjeux et les options possibles.",
            'cta_text'  => 'Parler à un expert',
            'cta_url'   => '/contact',
            'cta2_text' => 'Demander un devis',
            'cta2_url'  => '/contact',
        ]);
    }

    \App\Services\Cache::clear();
    echo "\nPage /solutions éditable depuis /admin/pages -> Solutions.\n";
    echo "Sous-pages éditables de la même façon (hors menu principal).\n";
    echo "=== TERMINÉ ===\n";
} catch (\Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    echo "  " . $e->getFile() . ':' . $e->getLine() . "\n";
    exit(1);
}
