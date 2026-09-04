<?php
/**
 * Build Sectors Page — page « Secteurs d'activité » (/secteurs)
 *
 * Crée la page, l'ajoute à la navigation et monte ses 8 sections avec leur
 * contenu. Aucune ligne de contenu n'est écrite dans les gabarits : tout passe
 * par les blocs CMS et reste modifiable depuis /admin/pages (Règle #2).
 *
 * ── Script RÉCONCILIATEUR (leçon de BUG-HERO-01) ────────────────────────────
 * Existence, statut et position de chaque section sont réalignés à CHAQUE
 * déploiement. Le CONTENU, lui, n'est semé que si la section est vide : rien de
 * ce qui est modifié en admin ne peut être écrasé.
 *
 * ── Section « Réalisations » ────────────────────────────────────────────────
 * La table `projects` est vide. Plutôt que d'inventer des références clients,
 * la section reprend par COPIE les projets déjà saisis dans la section
 * `projects_showcase` de la page d'accueil. Si cette source est vide, la
 * section est créée puis laissée INACTIVE : jamais de bloc vide en ligne.
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
use App\Models\Setting;
use App\Services\Database;

echo "=== BUILD SECTORS PAGE (/secteurs) ===\n";

try {
    // ── 1. Page ─────────────────────────────────────────────────────────────
    $page = Page::findBySlug('secteurs');
    if (!$page) {
        $pageId = (int)Page::createPage(
            "Secteurs d'activité",
            'secteurs',
            "Secteurs d'activité — Digitalium Group",
            "Digitalium Group conçoit des solutions numériques adaptées aux réalités opérationnelles de chaque secteur : finance, éducation, commerce, immobilier, santé, PME, associations et événementiel.",
            'published'
        );
        echo "Page 'secteurs' créée (#$pageId).\n";
        $page = Page::findBySlug('secteurs');
    } else {
        $pageId = (int)$page['id'];
        echo "Page 'secteurs' déjà présente (#$pageId).\n";
    }

    // Navigation : publiée, visible au menu, placée après Services.
    // Ne touche que ce qui est nécessaire — le reste de la ligne est conservé.
    $servicePage = Page::findBySlug('service');
    $navOrder = $servicePage ? ((int)($servicePage['sort_order'] ?? 2) + 1) : 3;
    Database::query(
        "UPDATE pages SET status = 'published', in_navigation = 1, sort_order = :o, hero_status = 0 WHERE id = :id",
        ['o' => $navOrder, 'id' => $pageId]
    );
    echo "Navigation : publiée, in_navigation = 1, position $navOrder, hero de page désactivé.\n";

    /**
     * Le header (layout.php) lit d'ABORD le menu 'primary' de la table `menus`
     * et ne retombe sur les pages `in_navigation = 1` que si ce menu est vide.
     * Poser in_navigation ne suffit donc pas quand un menu primaire existe :
     * il faut y déclarer l'entrée. Réconciliateur et idempotent.
     */
    $primaryMenu = Database::fetch("SELECT id FROM menus WHERE location = 'primary' LIMIT 1");
    if ($primaryMenu) {
        $menuId = (int)$primaryMenu['id'];
        $itemCount = (int)(Database::fetch(
            "SELECT COUNT(*) AS n FROM menu_items WHERE menu_id = :m",
            ['m' => $menuId]
        )['n'] ?? 0);

        if ($itemCount === 0) {
            // Menu vide : le header utilise la retombée sur in_navigation.
            echo "Menu 'primary' vide — la page apparaît via in_navigation.\n";
        } else {
            $existing = Database::fetch(
                "SELECT id, is_active FROM menu_items
                 WHERE menu_id = :m AND (page_id = :p OR url IN ('/secteurs', 'secteurs'))
                 LIMIT 1",
                ['m' => $menuId, 'p' => $pageId]
            );

            if ($existing) {
                if ((int)$existing['is_active'] !== 1) {
                    Database::query(
                        "UPDATE menu_items SET is_active = 1, page_id = :p WHERE id = :id",
                        ['p' => $pageId, 'id' => (int)$existing['id']]
                    );
                    echo "Menu 'primary' : entrée Secteurs réactivée (#{$existing['id']}).\n";
                } else {
                    echo "Menu 'primary' : entrée Secteurs déjà présente (#{$existing['id']}).\n";
                }
            } else {
                // Position : juste après Services. Égalité de sort_order → l'ordre
                // secondaire (id ASC) place la nouvelle entrée derrière, sans
                // renuméroter les entrées rangées à la main en admin.
                $svcItem = null;
                if ($servicePage) {
                    $svcItem = Database::fetch(
                        "SELECT sort_order FROM menu_items
                         WHERE menu_id = :m AND parent_id IS NULL
                           AND (page_id = :p OR url IN ('/service', 'service'))
                         LIMIT 1",
                        ['m' => $menuId, 'p' => (int)$servicePage['id']]
                    );
                }
                $itemOrder = $svcItem
                    ? (int)$svcItem['sort_order']
                    : (int)(Database::fetch(
                        "SELECT COALESCE(MAX(sort_order), 0) AS o FROM menu_items WHERE menu_id = :m AND parent_id IS NULL",
                        ['m' => $menuId]
                    )['o'] ?? 0) + 1;

                Database::query(
                    "INSERT INTO menu_items (menu_id, parent_id, page_id, label, url, target, icon, sort_order, is_active)
                     VALUES (:m, NULL, :p, :l, :u, '_self', '', :o, 1)",
                    [
                        'm' => $menuId,
                        'p' => $pageId,
                        'l' => "Secteurs d'activité",
                        'u' => '/secteurs',
                        'o' => $itemOrder,
                    ]
                );
                echo "Menu 'primary' : entrée Secteurs ajoutée (position $itemOrder).\n";
            }
        }
    } else {
        echo "Aucun menu 'primary' — la page apparaît via in_navigation.\n";
    }

    /**
     * Réaligne une section (création, statut, position) sans toucher au contenu.
     * Retourne son identifiant.
     */
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

    /** Sème les blocs UNIQUEMENT si la section est encore vide. */
    $seed = function (int $secId, array $singles, array $groups = []) : bool {
        $content = Block::getStructuredContent($secId);
        if (!empty($content['single']) || !empty($content['groups'])) {
            echo "    contenu déjà présent — non modifié.\n";
            return false;
        }
        foreach ($singles as $key => $value) {
            if ($value === '') { continue; }
            $type = str_contains($key, 'url') || str_contains($key, 'link') ? 'link'
                  : (str_contains($key, 'image') ? 'image'
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

    // ── 2. HERO ─────────────────────────────────────────────────────────────
    echo "\n[1/8] Hero\n";
    $id = $reconcile('hero_media_cards', 'Hero — visuel et cartes', -1);
    $seed($id, [
        'badge'        => "Nos secteurs d'activité",
        'title'        => "La technologie adaptée",
        'title_accent' => "aux réalités de votre métier.",
        'text'         => "Chaque secteur possède ses contraintes, ses processus et ses enjeux. Digitalium Group conçoit des solutions numériques adaptées aux réalités opérationnelles des organisations africaines afin d'améliorer leur performance, leur productivité et leur capacité à évoluer.",
        'cta1_text'    => 'Découvrir les secteurs',
        'cta1_url'     => '#secteurs',
        'cta1_icon'    => 'arrow-down',
        'cta2_text'    => 'Parler de mon projet',
        'cta2_url'     => '/contact',
        'cta2_icon'    => 'message-square',
        'image'            => '/assets/uploads/hero-pro-dashboard-1893001.jpg',
        'image_alt'        => "La technologie adaptée aux réalités de votre métier",
        'decor'            => '1',
        // Bandeau large plutôt que visuel latéral, et aucune carte flottante.
        'layout'           => 'banner',
        'image_max_width'  => '1300',
        'image_ratio'      => '1300 / 400',
        'image_ratio_mobile' => '16 / 9',
    ]);

    /**
     * Reprise du hero déjà en ligne — la section n'est plus vide, donc $seed
     * ne s'applique plus. Deux opérations ciblées, toutes deux idempotentes :
     *
     *  a) poser les clés de mise en page SI ELLES MANQUENT. Jamais d'écrasement :
     *     une valeur changée en admin est conservée.
     *  b) retirer UNE SEULE FOIS les cartes flottantes semées par la version
     *     précédente. Le drapeau vit dans `settings` : il reste visible et
     *     réarmable, contrairement à un fichier de verrou (leçon BUG-HERO-01).
     */
    $heroContent = Block::getStructuredContent($id);
    $heroSingle  = $heroContent['single'] ?? [];

    $heroDefaults = [
        'layout'             => ['text', 'banner'],
        'image_max_width'    => ['text', '1300'],
        'image_ratio'        => ['text', '1300 / 400'],
        'image_ratio_mobile' => ['text', '16 / 9'],
    ];
    $added = [];
    foreach ($heroDefaults as $key => [$type, $value]) {
        if (!array_key_exists($key, $heroSingle)) {
            Block::setVal($id, $key, $type, $value);
            $added[] = $key;
        }
    }
    if ($added) {
        echo "    mise en page bandeau : " . implode(', ', $added) . " ajouté(s).\n";
    }

    if (!Setting::getVal('sectors_hero_cards_removed')) {
        $removed = 0;
        foreach ($heroContent['groups'] ?? [] as $group) {
            $gid = (int)($group['_group_id'] ?? 0);
            if ($gid > 0 && Block::deleteGroup($id, $gid)) { $removed++; }
        }
        Setting::setVal('sectors_hero_cards_removed', '1');
        echo "    cartes flottantes du hero retirées : $removed groupe(s).\n";
    }

    // ── 3. NOTRE APPROCHE ───────────────────────────────────────────────────
    echo "[2/8] Notre approche\n";
    $id = $reconcile('process_strip', 'Notre approche', 0);
    $seed($id, [
        'tag'   => 'Notre approche',
        'title' => "Nous ne proposons pas la même solution à toutes les organisations.",
    ], [
        ['proc_num' => '01', 'proc_icon' => 'search',      'proc_title' => 'Comprendre',
         'proc_desc' => "Analyser le métier, les processus réels et les contraintes du terrain avant toute proposition."],
        ['proc_num' => '02', 'proc_icon' => 'pen-tool',    'proc_title' => 'Concevoir',
         'proc_desc' => "Définir une architecture et des fonctionnalités qui répondent aux besoins identifiés, sans surcouche inutile."],
        ['proc_num' => '03', 'proc_icon' => 'plug',        'proc_title' => 'Intégrer',
         'proc_desc' => "Connecter la solution aux outils déjà en place et accompagner la prise en main par les équipes."],
        ['proc_num' => '04', 'proc_icon' => 'trending-up', 'proc_title' => 'Faire évoluer',
         'proc_desc' => "Mesurer l'usage, ajuster et étendre la solution au rythme de l'organisation."],
    ]);

    // ── 4. SECTEURS ─────────────────────────────────────────────────────────
    echo "[3/8] Secteurs\n";
    $id = $reconcile('sectors_grid', "Secteurs d'activité", 1);
    $sectors = [
        ['01', 'landmark',    'Finance & Assurance',          "Automatiser le traitement des dossiers, fiabiliser les données et sécuriser les échanges.",
         "Traitement automatisé des demandes|Tableaux de bord de pilotage|Sécurité et traçabilité"],
        ['02', 'graduation-cap', 'Éducation & Formation',     "Gérer les parcours, les inscriptions et le suivi pédagogique sur une plateforme unique.",
         "Plateforme de gestion des apprenants|Contenus et évaluations en ligne|Suivi des parcours"],
        ['03', 'shopping-cart', 'Commerce & Distribution',    "Relier la vente, le stock et la logistique pour piloter l'activité en temps réel.",
         "Boutique en ligne et paiement|Gestion des stocks|Suivi des commandes"],
        ['04', 'building-2',  'Immobilier',                   "Centraliser les biens, les mandats et la relation client sur un même outil.",
         "Catalogue de biens en ligne|Gestion des mandats et contrats|Relation client et relances"],
        ['05', 'heart-pulse', 'Santé',                        "Structurer l'information patient et fluidifier la coordination entre services.",
         "Dossier patient structuré|Prise de rendez-vous|Coordination entre services"],
        ['06', 'briefcase',   'Entreprises & PME',            "Outiller les fonctions clés sans alourdir l'organisation ni les coûts.",
         "Automatisation des tâches répétitives|Outils métier sur mesure|Infrastructure et support"],
        ['07', 'users',       'Associations & Organisations', "Gérer les membres, les adhésions et la communication avec des moyens maîtrisés.",
         "Gestion des membres et adhésions|Collecte de dons en ligne|Communication et newsletters"],
        ['08', 'calendar',    'Événementiel',                 "Piloter l'inscription, l'accueil et la logistique d'un événement de bout en bout.",
         "Billetterie et inscriptions|Gestion des participants|Accueil et contrôle d'accès"],
    ];
    $sectorGroups = [];
    foreach ($sectors as [$num, $icon, $title, $desc, $needs]) {
        $sectorGroups[] = [
            'sec_num' => $num, 'sec_icon' => $icon, 'sec_title' => $title,
            'sec_desc' => $desc, 'sec_needs' => $needs,
            'sec_link' => '/contact', 'sec_link_text' => 'Explorer',
        ];
    }
    $seed($id, [
        'tag'      => 'Secteurs',
        'title'    => "Des solutions pensées pour votre secteur",
        'subtitle' => "Huit domaines dans lesquels nous intervenons, avec les besoins que nous y rencontrons le plus souvent.",
    ], $sectorGroups);

    // ── 5. PROBLÈMES → SOLUTIONS ────────────────────────────────────────────
    echo "[4/8] Problèmes et solutions\n";
    $id = $reconcile('problems_solutions', 'Problèmes et solutions', 2);
    $seed($id, [
        'tag'            => 'Cas concrets',
        'title'          => "Des situations que nous rencontrons, et ce que nous y opposons",
        'problem_label'  => 'Situation',
        'solution_label' => 'Réponse',
    ], [
        ['ps_icon' => 'bot',        'ps_problem' => "Traitement manuel des demandes",
         'ps_solution' => "IA, automatisation et CRM",
         'ps_detail'  => "Les demandes sont qualifiées, routées et suivies automatiquement ; les équipes se concentrent sur les cas à valeur ajoutée."],
        ['ps_icon' => 'bar-chart-3','ps_problem' => "Beaucoup de données, peu d'indicateurs",
         'ps_solution' => "Data et Business Intelligence",
         'ps_detail'  => "Les données éparses sont consolidées puis restituées en indicateurs lisibles et actionnables."],
        ['ps_icon' => 'plug-zap',   'ps_problem' => "Logiciels non connectés entre eux",
         'ps_solution' => "API et intégrations",
         'ps_detail'  => "Les outils existants communiquent, la double saisie disparaît et l'information circule."],
        ['ps_icon' => 'server',     'ps_problem' => "Infrastructure devenue insuffisante",
         'ps_solution' => "Cloud, sécurité et Managed IT",
         'ps_detail'  => "L'infrastructure est dimensionnée, sécurisée et supervisée pour suivre la croissance."],
    ]);

    // ── 6. EXPERTISES TRANSVERSALES ─────────────────────────────────────────
    echo "[5/8] Expertises transversales\n";
    $id = $reconcile('capabilities_grid', 'Expertises transversales', 3);
    $seed($id, [
        'tag'      => 'Expertises',
        'title'    => "Les compétences mobilisées, quel que soit le secteur",
        'subtitle' => "Six domaines techniques que nous combinons selon les besoins du projet.",
    ], [
        ['cap_icon' => 'code-2',       'cap_title' => 'Software & Applications',
         'cap_desc' => "Applications web et mobiles, outils métier sur mesure."],
        ['cap_icon' => 'brain-circuit','cap_title' => 'Intelligence Artificielle',
         'cap_desc' => "Traitement automatisé, assistance à la décision, analyse de documents."],
        ['cap_icon' => 'bar-chart-3',  'cap_title' => 'Data & Business Intelligence',
         'cap_desc' => "Consolidation des données et tableaux de bord de pilotage."],
        ['cap_icon' => 'workflow',     'cap_title' => 'Automatisation',
         'cap_desc' => "Suppression des tâches répétitives et fiabilisation des processus."],
        ['cap_icon' => 'cloud',        'cap_title' => 'Cloud & Infrastructure',
         'cap_desc' => "Hébergement, dimensionnement et haute disponibilité."],
        ['cap_icon' => 'shield-check', 'cap_title' => 'Cybersécurité & Managed IT',
         'cap_desc' => "Protection, supervision et support des systèmes en production."],
    ]);

    // ── 7. MÉTHODE ──────────────────────────────────────────────────────────
    echo "[6/8] Notre méthode\n";
    $id = $reconcile('process_timeline', 'Notre méthode', 4);
    $seed($id, [
        'tag'   => 'Méthode',
        'title' => "De la compréhension du métier à l'accompagnement dans la durée",
    ], [
        ['proc_num' => '01', 'proc_icon' => 'search',       'proc_title' => 'Comprendre',
         'proc_desc' => "Immersion dans le métier, les processus et les contraintes réelles."],
        ['proc_num' => '02', 'proc_icon' => 'clipboard-list','proc_title' => 'Analyser',
         'proc_desc' => "Identification des points de friction et des gains atteignables."],
        ['proc_num' => '03', 'proc_icon' => 'pen-tool',     'proc_title' => 'Concevoir',
         'proc_desc' => "Architecture, parcours et fonctionnalités validés avant développement."],
        ['proc_num' => '04', 'proc_icon' => 'rocket',       'proc_title' => 'Déployer',
         'proc_desc' => "Mise en production progressive, avec reprise des données et formation."],
        ['proc_num' => '05', 'proc_icon' => 'workflow',     'proc_title' => 'Automatiser',
         'proc_desc' => "Suppression des tâches manuelles restantes et connexion des outils."],
        ['proc_num' => '06', 'proc_icon' => 'life-buoy',    'proc_title' => 'Accompagner',
         'proc_desc' => "Support, supervision et évolutions au rythme de l'organisation."],
    ]);

    // ── 8. RÉALISATIONS — copiées depuis l'accueil, jamais inventées ────────
    echo "[7/8] Réalisations\n";
    $id = $reconcile('projects_showcase', 'Réalisations', 5);
    $existing = Block::getStructuredContent($id);
    if (!empty($existing['single']) || !empty($existing['groups'])) {
        echo "    contenu déjà présent — non modifié.\n";
    } else {
        // Source : la section projects_showcase de la page d'accueil.
        $home = Page::findBySlug('home');
        $source = null;
        if ($home) {
            foreach (Section::getByPage((int)$home['id']) as $s) {
                if (($s['type'] ?? '') === 'projects_showcase') { $source = (int)$s['id']; break; }
            }
        }
        $copied = 0;
        if ($source !== null) {
            $src = Block::getStructuredContent($source);
            foreach (($src['single'] ?? []) as $k => $v) {
                if ($v !== '' && $v !== null) { Block::setVal($id, $k, 'text', $v); }
            }
            foreach (($src['groups'] ?? []) as $g => $fields) {
                foreach ($fields as $k => $v) {
                    if (str_starts_with($k, '_') || $v === '' || $v === null) { continue; }
                    Block::setVal($id, $k, 'text', $v, $g + 1, $g);
                    $copied++;
                }
            }
            echo "    " . count($src['groups'] ?? []) . " réalisation(s) copiée(s) depuis l'accueil ($copied blocs).\n";
        }
        if ($copied === 0) {
            // Aucune réalisation réelle disponible : on n'affiche pas une section vide.
            Database::query("UPDATE sections SET status = 'inactive' WHERE id = :id", ['id' => $id]);
            echo "    aucune réalisation réelle en base — section laissée INACTIVE (activable depuis /admin/pages).\n";
        }
    }

    // ── 9. CTA FINAL ────────────────────────────────────────────────────────
    echo "[8/8] CTA final\n";
    $id = $reconcile('cta', 'CTA final', 6);
    $seed($id, [
        'eyebrow'   => "Votre secteur a ses défis.",
        'title'     => "Construisons la solution qui lui correspond.",
        'subtitle'  => "Décrivez-nous votre contexte : nous revenons vers vous avec une lecture de vos enjeux et les options possibles.",
        'cta_text'  => 'Parler à un expert',
        'cta_url'   => '/contact',
        'cta2_text' => 'Demander un devis',
        'cta2_url'  => '/contact',
    ]);

    \App\Services\Cache::clear();
    echo "\nPage /secteurs éditable depuis /admin/pages -> Secteurs d'activité.\n";
    echo "=== TERMINÉ ===\n";
} catch (\Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    echo "  " . $e->getFile() . ':' . $e->getLine() . "\n";
    exit(1);
}
