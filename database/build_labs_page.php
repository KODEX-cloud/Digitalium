<?php
/**
 * Build Labs Page — Digitalium Labs (/labs)
 *
 * Monte la table des produits, la page, sa navigation et ses huit sections.
 * Aucun texte n'est écrit dans les gabarits : tout passe par les blocs CMS et
 * reste modifiable depuis /admin/pages (Règle #2).
 *
 * ── Script RÉCONCILIATEUR ───────────────────────────────────────────────────
 * Existence et position de chaque section sont réalignées à CHAQUE déploiement.
 * Le CONTENU n'est semé que si la section est vide, et le STATUT n'est posé
 * qu'à la création : rien de ce qui est décidé en admin ne peut être écrasé.
 *
 * ── Aucun produit n'est semé ────────────────────────────────────────────────
 * La section « Nos produits » lit la table `lab_products`, alimentée depuis
 * /admin/labs. Tant qu'aucun produit réel n'y est saisi, elle affiche le
 * message d'attente — le cahier des charges interdit d'inventer un produit.
 *
 * ── Isolation des étapes non essentielles ───────────────────────────────────
 * Le CREATE TABLE, l'accent et tout le bloc de navigation ont chacun leur
 * try/catch. Au premier déploiement d'Insights, une erreur sur `settings` avait
 * remonté jusqu'au try extérieur et tué la création de la page elle-même. Une
 * entrée de menu se corrige en deux clics ; une page absente, non.
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

echo "=== BUILD LABS PAGE (/labs) ===\n";

try {

    // ══════════════════════════════════════════════════════════════════════
    //  1. SCHÉMA — produits Digitalium Labs
    // ══════════════════════════════════════════════════════════════════════
    //
    // Table dédiée, et non `projects` : /realisations publie TOUS les projets,
    // un produit Labs y apparaîtrait comme du travail livré pour un client sur
    // une page que le cahier des charges interdit de modifier. `stage` porte le
    // cycle de vie (idée → disponible), `status` la publication (draft/published)
    // comme partout ailleurs dans ce projet.

    try {
        Database::query("CREATE TABLE IF NOT EXISTS `lab_products` (
            `id`               INT AUTO_INCREMENT PRIMARY KEY,
            `name`             VARCHAR(190) NOT NULL,
            `slug`             VARCHAR(190) NOT NULL UNIQUE,
            `tagline`          VARCHAR(255) NULL,
            `description`      TEXT NULL,
            `sector`           VARCHAR(120) NULL,
            `stage`            VARCHAR(30) NOT NULL DEFAULT 'idee',
            `logo`             VARCHAR(255) NULL,
            `main_image`       VARCHAR(255) NULL,
            `technologies`     VARCHAR(500) NULL,
            `external_link`    VARCHAR(255) NULL,
            `availability`     VARCHAR(190) NULL,
            `sort_order`       INT NOT NULL DEFAULT 0,
            `is_featured`      TINYINT(1) NOT NULL DEFAULT 0,
            `status`           VARCHAR(20) NOT NULL DEFAULT 'draft',
            `meta_title`       VARCHAR(255) NULL,
            `meta_description` TEXT NULL,
            `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_labs_public` (`status`, `sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "  Table lab_products : OK.\n";
    } catch (\Throwable $e) {
        echo "  ATTENTION lab_products : " . $e->getMessage() . "\n";
    }

    // ══════════════════════════════════════════════════════════════════════
    //  2. PAGE
    // ══════════════════════════════════════════════════════════════════════

    $metaDesc = "Digitalium Labs conçoit des logiciels, SaaS et prototypes issus des besoins "
              . "récurrents rencontrés sur le terrain, pour les entreprises et les organisations "
              . "africaines.";

    $page = Page::findBySlug('labs');
    if (!$page) {
        $pageId = (int)Page::createPage(
            'Digitalium Labs',
            'labs',
            'Digitalium Labs — Innovation, R&D et produits propriétaires',
            $metaDesc,
            'published'
        );
        echo "\nPage 'labs' créée (#$pageId).\n";
        $navOrder = 7;
    } else {
        $pageId = (int)$page['id'];
        $navOrder = (int)($page['sort_order'] ?? 7);
        echo "\nPage 'labs' déjà présente (#$pageId).\n";
    }

    // Le hero est une SECTION (hero_media_cards), pas le hero moteur de `pages` :
    // laisser les deux actifs empilerait deux bannières.
    $aParentSlug = (bool)Database::fetch("SHOW COLUMNS FROM `pages` LIKE 'parent_slug'");
    Database::query(
        "UPDATE pages SET status = 'published', in_navigation = 1, sort_order = :o,
                          hero_status = 0" . ($aParentSlug ? ", parent_slug = NULL" : "") . "
         WHERE id = :id",
        ['o' => $navOrder, 'id' => $pageId]
    );
    echo "Navigation : publiée, in_navigation = 1, position $navOrder.\n";

    // Accent — posé UNE SEULE FOIS, comme sur les autres pages.
    try {
        $aAccent = (bool)Database::fetch("SHOW COLUMNS FROM `pages` LIKE 'accent_color'");
        if ($aAccent) {
            $actuel = Database::fetch("SELECT accent_color FROM pages WHERE id = :id", ['id' => $pageId]);
            if (trim((string)($actuel['accent_color'] ?? '')) === '') {
                Database::query("UPDATE pages SET accent_color = '#0868B0' WHERE id = :id", ['id' => $pageId]);
                echo "Accent : #0868B0 (bleu clair du logo).\n";
            } else {
                echo "Accent : {$actuel['accent_color']} — choix conservé.\n";
            }
        }
    } catch (\Throwable $e) {
        echo "  ATTENTION accent non posé : " . $e->getMessage() . "\n";
    }

    // ── Entrée de menu — opération à faire UNE FOIS ─────────────────────────
    //
    // Bloc entièrement isolé, drapeau compris : au premier déploiement
    // d'Insights, la lecture du drapeau elle-même avait interrompu le script
    // avant la création des sections.
    try {
        $drapeau  = 'labs_nav_added_v1';
        $dejaFait = Database::fetch(
            "SELECT id FROM settings WHERE setting_key = :k LIMIT 1", ['k' => $drapeau]
        );

        $menu = Database::fetch("SELECT id FROM menus WHERE location = 'primary' LIMIT 1");
        if (!$menu) {
            echo "Aucun menu 'primary' — la page apparaît via in_navigation.\n";
        } elseif ($dejaFait) {
            echo "Navigation : entrée déjà ajoutée — laissée telle quelle.\n";
        } else {
            $menuId  = (int)$menu['id'];
            $nbItems = (int)(Database::fetch(
                "SELECT COUNT(*) AS n FROM menu_items WHERE menu_id = :m", ['m' => $menuId]
            )['n'] ?? 0);

            if ($nbItems === 0) {
                echo "Menu 'primary' vide — la page apparaît via in_navigation.\n";
            } else {
                $entree = Database::fetch(
                    "SELECT id FROM menu_items
                     WHERE menu_id = :m AND (url IN ('/labs', 'labs') OR page_id = :p) LIMIT 1",
                    ['m' => $menuId, 'p' => $pageId]
                );
                if ($entree) {
                    echo "Menu 'primary' : entrée Labs déjà présente (#{$entree['id']}).\n";
                } else {
                    $ordre = (int)(Database::fetch(
                        "SELECT COALESCE(MAX(sort_order), 0) AS o FROM menu_items
                         WHERE menu_id = :m AND parent_id IS NULL", ['m' => $menuId]
                    )['o'] ?? 0) + 1;
                    Database::query(
                        "INSERT INTO menu_items (menu_id, parent_id, page_id, label, url, target, icon, sort_order, is_active)
                         VALUES (:m, NULL, :p, 'Labs', '/labs', '_self', '', :o, 1)",
                        ['m' => $menuId, 'p' => $pageId, 'o' => $ordre]
                    );
                    echo "Menu 'primary' : entrée Labs ajoutée (position $ordre).\n";
                }
            }
        }

        if (!$dejaFait) {
            Database::query(
                "INSERT INTO settings (setting_key, setting_value) VALUES (:k, '1')",
                ['k' => $drapeau]
            );
        }
    } catch (\Throwable $e) {
        echo "ATTENTION navigation non posée : " . $e->getMessage() . "\n";
        echo "  -> la page existe ; l'entrée de menu est à vérifier dans /admin/menus.\n";
    }

    // ══════════════════════════════════════════════════════════════════════
    //  3. OUTILS DE RÉCONCILIATION
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Réaligne une section sur le couple (type, nom).
     *
     * Le NOM fait partie de la clé : cette page porte DEUX sections
     * `capabilities_grid` (domaines d'innovation, partenariats). Sans le nom,
     * la seconde écraserait la première à chaque passage.
     */
    $reconcile = function (string $type, string $nom, int $ordre) use ($pageId): int {
        foreach (Section::getByPage($pageId) as $s) {
            if (($s['type'] ?? '') === $type && ($s['name'] ?? '') === $nom) {
                $id = (int)$s['id'];
                Database::query("UPDATE sections SET sort_order = :o WHERE id = :id",
                    ['o' => $ordre, 'id' => $id]);
                return $id;
            }
        }
        $id = (int)Section::addSection($pageId, $nom, $type, $ordre);
        Database::query("UPDATE sections SET status = 'active', sort_order = :o WHERE id = :id",
            ['o' => $ordre, 'id' => $id]);
        echo "  section créée : #$id [$type] $nom (position $ordre)\n";
        return $id;
    };

    /** Sème les blocs UNIQUEMENT si la section est encore vide. */
    $seed = function (int $secId, array $singles, array $groups = []): bool {
        $contenu = Block::getStructuredContent($secId);
        if (!empty($contenu['single']) || !empty($contenu['groups'])) {
            echo "    contenu déjà présent — non modifié.\n";
            return false;
        }
        foreach ($singles as $cle => $valeur) {
            if ($valeur === '') { continue; }
            $type = \App\Helpers\BlockFieldHelper::type($cle);
            // BlockFieldHelper décrit comment ÉDITER le champ ; le stockage ne
            // connaît que text/textarea/image/link. « select » se range en text.
            if ($type === 'select') { $type = 'text'; }
            Block::setVal($secId, $cle, $type, $valeur);
        }
        foreach ($groups as $g => $champs) {
            foreach ($champs as $cle => $valeur) {
                if ($valeur === '') { continue; }
                Block::setVal($secId, $cle, 'text', $valeur, $g + 1, $g);
            }
        }
        echo "    " . count($singles) . " blocs + " . count($groups) . " groupes semés.\n";
        return true;
    };

    /** Pose les blocs MANQUANTS d'une section déjà remplie. */
    $ensure = function (int $secId, array $defauts): void {
        $contenu = Block::getStructuredContent($secId);
        $poses = 0;
        foreach ($defauts as $cle => $valeur) {
            if (array_key_exists($cle, $contenu['single'] ?? [])) { continue; }
            $type = \App\Helpers\BlockFieldHelper::type($cle);
            if ($type === 'select') { $type = 'text'; }
            Block::setVal($secId, $cle, $type, $valeur);
            $poses++;
        }
        if ($poses > 0) { echo "    $poses bloc(s) manquant(s) posé(s).\n"; }
    };

    // ══════════════════════════════════════════════════════════════════════
    //  4. SECTIONS
    // ══════════════════════════════════════════════════════════════════════

    echo "\n[1/8] Hero\n";
    $id = $reconcile('hero_media_cards', 'Hero — Labs', -1);
    $seed($id, [
        'badge'              => 'Digitalium Labs',
        'title'              => "Nous ne construisons pas seulement pour nos clients.",
        'title_accent'       => "Nous construisons aussi pour demain.",
        'text'               => "Digitalium Labs est notre espace d'innovation dédié à la conception de "
                              . "logiciels, SaaS, prototypes et technologies capables de répondre aux "
                              . "besoins émergents des entreprises et des populations africaines.",
        'cta1_text'          => 'Découvrir nos innovations',
        'cta1_url'           => '#produits',
        'cta1_icon'          => 'arrow-down',
        'cta2_text'          => 'Proposer un partenariat',
        'cta2_url'           => '/contact',
        'cta2_icon'          => 'handshake',
        'decor'              => '1',
        'layout'             => 'overlay',
        'image_max_width'    => '1250',
        'image_ratio'        => '1250 / 500',
        'image_ratio_mobile' => '16 / 9',
        'overlay_opacity'    => '64',
        'overlay_min_height' => '500',
        'image_radius'       => '0',
    ]);

    echo "[2/8] Pourquoi Digitalium Labs\n";
    $id = $reconcile('process_strip', 'Pourquoi Digitalium Labs', 1);
    $seed($id, [
        'tag'      => 'Pourquoi Digitalium Labs',
        'title'    => 'Transformer les problèmes récurrents en produits technologiques.',
        'subtitle' => "Les missions réalisées auprès des entreprises permettent à Digitalium "
                    . "d'identifier des besoins récurrents. Lorsque ces besoins peuvent bénéficier "
                    . "à plusieurs organisations, Digitalium Labs les transforme progressivement "
                    . "en produits réutilisables.",
    ], [
        ['proc_num' => '01', 'proc_icon' => 'eye',         'proc_title' => 'Observer',
         'proc_desc' => "Sur le terrain, au contact des organisations."],
        ['proc_num' => '02', 'proc_icon' => 'search',      'proc_title' => 'Identifier un problème',
         'proc_desc' => "Un besoin qui revient chez plusieurs clients."],
        ['proc_num' => '03', 'proc_icon' => 'pencil-ruler','proc_title' => 'Prototyper',
         'proc_desc' => "Une première version, volontairement réduite."],
        ['proc_num' => '04', 'proc_icon' => 'flask-conical','proc_title' => 'Tester',
         'proc_desc' => "En conditions réelles, avec des utilisateurs."],
        ['proc_num' => '05', 'proc_icon' => 'rocket',      'proc_title' => 'Lancer',
         'proc_desc' => "Mise à disposition et accompagnement des premiers usages."],
        ['proc_num' => '06', 'proc_icon' => 'refresh-cw',  'proc_title' => 'Faire évoluer',
         'proc_desc' => "Le produit se corrige et s'étend avec ses utilisateurs."],
    ]);

    echo "[3/8] Domaines d'innovation\n";
    $id = $reconcile('capabilities_grid', "Domaines d'innovation", 2);
    $seed($id, [
        'tag'      => "Nos domaines d'innovation",
        'title'    => "Là où nous cherchons, testons et construisons.",
        'subtitle' => "Six terrains sur lesquels les besoins rencontrés se répètent assez pour "
                    . "justifier un produit plutôt qu'un développement sur mesure.",
    ], [
        ['cap_icon' => 'brain-circuit',   'cap_title' => 'Intelligence Artificielle',
         'cap_desc' => "Agents IA, assistants métiers, automatisation."],
        ['cap_icon' => 'layout-grid',     'cap_title' => 'SaaS B2B',
         'cap_desc' => "Logiciels métiers accessibles par abonnement."],
        ['cap_icon' => 'credit-card',     'cap_title' => 'FinTech & Paiements',
         'cap_desc' => "Solutions numériques adaptées aux usages africains."],
        ['cap_icon' => 'landmark',        'cap_title' => 'GovTech & CivicTech',
         'cap_desc' => "Outils pour collectivités, organisations et services publics."],
        ['cap_icon' => 'graduation-cap',  'cap_title' => 'Education Technology',
         'cap_desc' => "Gestion, formation, apprentissage et certification."],
        ['cap_icon' => 'gauge',           'cap_title' => 'Business Productivity',
         'cap_desc' => "Outils pour améliorer les opérations des PME et organisations."],
    ]);

    echo "[4/8] Nos produits\n";
    $id = $reconcile('lab_products', 'Nos produits', 3);
    $seed($id, [
        'tag'                => 'Nos produits',
        'title'              => 'Ce que Digitalium Labs construit.',
        'subtitle'           => "Chaque produit est né d'un besoin rencontré en mission, puis rendu "
                              . "réutilisable par d'autres organisations.",
        'show_filters'       => '1',
        'filter_all'         => 'Tous',
        'featured_only'      => '0',
        'cta_text'           => 'Découvrir',
        'tech_label'         => 'Technologies',
        'availability_label' => 'Disponibilité',
        'empty_text'         => "Nos premiers produits sont en cours de conception. "
                              . "Ils seront publiés ici dès qu'ils seront exploitables.",
    ]);
    $ensure($id, [
        'cta_text'           => 'Découvrir',
        'tech_label'         => 'Technologies',
        'availability_label' => 'Disponibilité',
        'filter_all'         => 'Tous',
    ]);

    echo "[5/8] Du service au produit\n";
    $id = $reconcile('flow_chain', 'Du service au produit', 4);
    $seed($id, [
        'tag'      => 'Du service au produit',
        'title'    => 'Notre terrain nourrit notre innovation.',
        'subtitle' => "Un produit Labs ne part jamais d'une idée abstraite : il part d'un problème "
                    . "déjà rencontré, plusieurs fois, chez de vrais clients.",
    ], [
        ['flow_label' => 'Services Digitalium', 'flow_icon' => 'briefcase', 'flow_accent' => '1',
         'flow_note'  => "Les missions menées auprès des entreprises et des institutions."],
        ['flow_label' => 'Problèmes rencontrés chez plusieurs clients', 'flow_icon' => 'alert-circle',
         'flow_accent' => '0', 'flow_note' => ''],
        ['flow_label' => 'Recherche & validation', 'flow_icon' => 'microscope',
         'flow_accent' => '0', 'flow_note' => "Le besoin est-il assez large pour justifier un produit ?"],
        ['flow_label' => 'Digitalium Labs', 'flow_icon' => 'flask-conical', 'flow_accent' => '1',
         'flow_note'  => "Conception, prototypage et test de la solution."],
        ['flow_label' => 'Produit / SaaS', 'flow_icon' => 'package',
         'flow_accent' => '0', 'flow_note' => ''],
        ['flow_label' => "Déploiement en Côte d'Ivoire", 'flow_icon' => 'map-pin',
         'flow_accent' => '0', 'flow_note' => "Premiers usages réels, sur notre marché."],
        ['flow_label' => 'Expansion Afrique', 'flow_icon' => 'globe', 'flow_accent' => '1',
         'flow_note'  => "Adaptation du produit aux autres marchés du continent."],
    ]);

    echo "[6/8] Innovation africaine\n";
    $id = $reconcile('values', 'Innovation africaine', 5);
    $seed($id, [
        'tag'   => 'Innovation africaine',
        'title' => "Construire depuis l'Afrique pour résoudre des problèmes réels.",
    ], [
        ['val_icon' => 'target',      'val_title' => 'Utilité',
         'val_text' => "Résoudre un besoin réel avant de rechercher l'effet technologique."],
        ['val_icon' => 'hand-coins',  'val_title' => 'Accessibilité',
         'val_text' => "Construire des solutions adaptées aux réalités économiques locales."],
        ['val_icon' => 'trending-up', 'val_title' => 'Scalabilité',
         'val_text' => "Créer des produits pouvant évoluer vers plusieurs marchés africains."],
        ['val_icon' => 'shuffle',     'val_title' => 'Souplesse',
         'val_text' => "Intégrer mobile, web, paiement local, WhatsApp et infrastructures existantes."],
    ]);

    echo "[7/8] Partenariats\n";
    $id = $reconcile('capabilities_grid', 'Partenariats', 6);
    $seed($id, [
        'tag'      => 'Partenariats',
        'title'    => 'Construisons ensemble.',
        'subtitle' => "Digitalium Labs travaille avec celles et ceux qui connaissent un problème "
                    . "mieux que nous, ou qui peuvent aider un produit à exister plus vite.",
        'cta_text' => 'Proposer un partenariat',
        'cta_url'  => '/contact',
    ], [
        ['cap_icon' => 'building-2',    'cap_title' => 'Entreprises',
         'cap_desc' => "Co-construire un produit à partir d'un besoin métier précis."],
        ['cap_icon' => 'trending-up',   'cap_title' => 'Investisseurs',
         'cap_desc' => "Accompagner le passage du prototype au produit exploitable."],
        ['cap_icon' => 'landmark',      'cap_title' => 'Institutions',
         'cap_desc' => "Adresser des enjeux publics avec des outils numériques adaptés."],
        ['cap_icon' => 'sprout',        'cap_title' => 'Incubateurs',
         'cap_desc' => "Mutualiser accompagnement, réseau et mise à l'épreuve du marché."],
        ['cap_icon' => 'graduation-cap','cap_title' => 'Universités',
         'cap_desc' => "Recherche appliquée, expérimentation et formation."],
        ['cap_icon' => 'users',         'cap_title' => 'Experts métiers',
         'cap_desc' => "Apporter la connaissance d'un secteur que la technologie seule n'a pas."],
        ['cap_icon' => 'plug',          'cap_title' => 'Partenaires technologiques',
         'cap_desc' => "Intégrations, infrastructures et briques techniques complémentaires."],
    ]);
    $ensure($id, ['cta_text' => 'Proposer un partenariat', 'cta_url' => '/contact']);

    echo "[8/8] CTA final\n";
    $id = $reconcile('cta', 'CTA final — Labs', 7);
    $seed($id, [
        'eyebrow'   => 'Digitalium Labs',
        'title'     => "Une idée peut devenir la prochaine solution Digitalium Labs.",
        'subtitle'  => "Vous avez identifié un problème important dans votre secteur ? "
                     . "Explorons ensemble son potentiel technologique.",
        'cta_text'  => 'Présenter une idée',
        'cta_url'   => '/contact',
        'cta2_text' => 'Parler avec Digitalium',
        'cta2_url'  => '/contact',
    ]);

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
