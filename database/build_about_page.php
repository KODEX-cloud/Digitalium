<?php
/**
 * Build About Page — À propos / Notre vision (/a-propos)
 *
 * Monte la table des collaborateurs, la page, sa navigation et ses dix sections.
 * Aucun texte n'est écrit dans les gabarits : tout passe par les blocs CMS et
 * reste modifiable depuis /admin/pages (Règle #2).
 *
 * ── Script RÉCONCILIATEUR ───────────────────────────────────────────────────
 * Existence et position de chaque section sont réalignées à CHAQUE déploiement.
 * Le CONTENU n'est semé que si la section est vide, et le STATUT n'est posé
 * qu'à la création : rien de ce qui est décidé en admin ne peut être écrasé.
 *
 * ── Aucun collaborateur n'est semé ──────────────────────────────────────────
 * La section « Notre équipe » lit la table `team_members`, alimentée depuis
 * /admin/team. Tant qu'aucun collaborateur réel n'y est publié, elle affiche
 * les PÔLES D'EXPERTISE — le cahier des charges interdit d'inventer un membre
 * d'équipe. Le repli est dans la section : le jour du premier collaborateur
 * publié, la page bascule seule.
 *
 * ── Ni dates ni chiffres ────────────────────────────────────────────────────
 * « Notre trajectoire » décrit l'ORDRE dans lequel les métiers se sont ajoutés,
 * sans aucune année : inventer un calendrier d'entreprise est interdit. De même
 * `badge_years` n'est pas utilisé et aucune statistique n'est semée.
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

echo "=== BUILD ABOUT PAGE (/a-propos) ===\n";

try {

    // ══════════════════════════════════════════════════════════════════════
    //  1. SCHÉMA — collaborateurs
    // ══════════════════════════════════════════════════════════════════════
    //
    // Table dédiée, et non des groupes de blocs : un groupe répétable porte
    // `sort_order` mais AUCUN indicateur de publication. Retirer un membre du
    // site obligerait à supprimer ses données, alors que le cahier des charges
    // demande explicitement « publié / dépublié ».

    try {
        Database::query("CREATE TABLE IF NOT EXISTS `team_members` (
            `id`          INT AUTO_INCREMENT PRIMARY KEY,
            `name`        VARCHAR(190) NOT NULL,
            `role`        VARCHAR(190) NULL,
            `department`  VARCHAR(60) NULL,
            `bio`         TEXT NULL,
            `photo`       VARCHAR(255) NULL,
            `linkedin`    VARCHAR(255) NULL,
            `email`       VARCHAR(190) NULL,
            `sort_order`  INT NOT NULL DEFAULT 0,
            `status`      VARCHAR(20) NOT NULL DEFAULT 'draft',
            `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_team_public` (`status`, `sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "  Table team_members : OK.\n";
    } catch (\Throwable $e) {
        echo "  ATTENTION team_members : " . $e->getMessage() . "\n";
        echo "  -> la section Équipe affichera les pôles d'expertise.\n";
    }

    // ══════════════════════════════════════════════════════════════════════
    //  2. PAGE
    // ══════════════════════════════════════════════════════════════════════

    $metaDesc = "Digitalium Group est une entreprise technologique basée en Côte d'Ivoire : "
              . "logiciel, intelligence artificielle, données et infrastructures numériques "
              . "au service des entreprises et des organisations.";

    $page = Page::findBySlug('a-propos');
    if (!$page) {
        $pageId = (int)Page::createPage(
            'À propos',
            'a-propos',
            'À propos de Digitalium Group — Notre vision',
            $metaDesc,
            'published'
        );
        echo "\nPage 'a-propos' créée (#$pageId).\n";
        $navOrder = 2;
    } else {
        $pageId = (int)$page['id'];
        $navOrder = (int)($page['sort_order'] ?? 2);
        echo "\nPage 'a-propos' déjà présente (#$pageId).\n";
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
                Database::query("UPDATE pages SET accent_color = '#0B4F8A' WHERE id = :id", ['id' => $pageId]);
                echo "Accent : #0B4F8A (bleu institutionnel).\n";
            } else {
                echo "Accent : {$actuel['accent_color']} — choix conservé.\n";
            }
        }
    } catch (\Throwable $e) {
        echo "  ATTENTION accent non posé : " . $e->getMessage() . "\n";
    }

    // ── Entrée de menu — opération à faire UNE FOIS ─────────────────────────
    //
    // Bloc entièrement isolé, drapeau compris. La recherche porte sur l'URL ET
    // sur `page_id` : un lien « À Propos » a existé par le passé, et le
    // dupliquer serait pire que de ne rien ajouter.
    try {
        $drapeau  = 'about_nav_added_v1';
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
                     WHERE menu_id = :m AND (url IN ('/a-propos', 'a-propos', '/about', 'about')
                                             OR page_id = :p) LIMIT 1",
                    ['m' => $menuId, 'p' => $pageId]
                );
                if ($entree) {
                    echo "Menu 'primary' : entrée À propos déjà présente (#{$entree['id']}).\n";
                } else {
                    // Placée en 2e position : juste après l'accueil, comme
                    // l'attend une page institutionnelle.
                    Database::query(
                        "UPDATE menu_items SET sort_order = sort_order + 1
                         WHERE menu_id = :m AND parent_id IS NULL AND sort_order >= 2",
                        ['m' => $menuId]
                    );
                    Database::query(
                        "INSERT INTO menu_items (menu_id, parent_id, page_id, label, url, target, icon, sort_order, is_active)
                         VALUES (:m, NULL, :p, 'À propos', '/a-propos', '_self', '', 2, 1)",
                        ['m' => $menuId, 'p' => $pageId]
                    );
                    echo "Menu 'primary' : entrée À propos ajoutée (position 2).\n";
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
     * Le NOM fait partie de la clé. Sans lui, deux sections de même type sur la
     * page se écraseraient l'une l'autre à chaque passage.
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

    // ══════════════════════════════════════════════════════════════════════
    //  4. SECTIONS
    // ══════════════════════════════════════════════════════════════════════

    echo "\n[1/10] Hero\n";
    $id = $reconcile('hero_media_cards', 'Hero — À propos', -1);
    $seed($id, [
        'badge'              => 'À propos de Digitalium Group',
        'title'              => "Construire aujourd'hui les systèmes numériques",
        'title_accent'       => "qui feront avancer demain.",
        'text'               => "Digitalium Group est une entreprise technologique basée en Côte d'Ivoire, "
                              . "qui accompagne les entreprises et les organisations dans leur transformation "
                              . "grâce au logiciel, à l'intelligence artificielle, à la donnée et aux "
                              . "infrastructures numériques.",
        'cta1_text'          => 'Découvrir notre vision',
        'cta1_url'           => '#mission',
        'cta1_icon'          => 'arrow-down',
        'cta2_text'          => 'Parler à notre équipe',
        'cta2_url'           => '/contact',
        'cta2_icon'          => 'send',
        // Seul visuel de la bibliothèque conforme au cahier des charges :
        // représentation professionnelle africaine, tonalité bleu/blanc, sans
        // tableau de bord envahissant. À remplacer par un visuel dédié depuis
        // la Bibliothèque Média — il est aujourd'hui partagé avec l'accueil.
        'image'              => 'assets/images/digitalium-hero-team.png',
        'image_alt'          => "L'équipe Digitalium Group au travail",
        'decor'              => '1',
        'layout'             => 'overlay',
        'image_max_width'    => '1250',
        'image_ratio'        => '1250 / 500',
        'image_ratio_mobile' => '16 / 9',
        'overlay_opacity'    => '62',
        'overlay_min_height' => '500',
        'image_radius'       => '0',
    ]);

    echo "[2/10] Qui sommes-nous\n";
    // Type `about` (texte + cartes) et non `about_visual` : ce dernier réclame
    // une image, et la bibliothèque n'en contient aucune autre qui convienne.
    // Une section institutionnelle avec un cadre d'image vide ferait moins bien
    // que la même section sans image du tout.
    $id = $reconcile('about', 'Qui sommes-nous', 1);
    $seed($id, [
        'tag'         => 'Qui sommes-nous',
        'title'       => "La technologie n'a de valeur que lorsqu'elle résout un problème réel.",
        'description' => "Digitalium Group ne se contente pas de livrer des outils. Nous commençons par "
                       . "comprendre un besoin dans son contexte réel, nous concevons ensuite le système "
                       . "qui y répond, puis nous accompagnons son évolution à mesure que l'organisation "
                       . "grandit. C'est cette continuité — comprendre, construire, faire durer — qui "
                       . "sépare un système réellement utilisé d'un logiciel abandonné quelques mois "
                       . "après sa mise en ligne.",
    ], [
        ['val_icon' => 'code-2', 'val_title' => 'Expertise technologique',
         'val_text' => "Logiciel, intelligence artificielle, données et infrastructures : les compétences sont réunies au sein du groupe."],
        ['val_icon' => 'map-pin', 'val_title' => 'Compréhension du terrain africain',
         'val_text' => "Contraintes de connectivité, réalités d'usage, moyens de paiement, langues : un système qui les ignore ne sera pas adopté."],
        ['val_icon' => 'shield-check', 'val_title' => 'Standards professionnels internationaux',
         'val_text' => "Méthodes de travail, sécurité et qualité de code alignées sur les pratiques attendues à l'international."],
        ['val_icon' => 'lightbulb', 'val_title' => 'Innovation utile',
         'val_text' => "Une technologie n'est retenue que si elle résout un problème identifié — jamais pour elle-même."],
        ['val_icon' => 'handshake', 'val_title' => 'Accompagnement dans la durée',
         'val_text' => "La mise en ligne est un début : exploitation, évolutions et support prennent le relais."],
    ]);

    echo "[3/10] Mission et vision\n";
    $id = $reconcile('mission', 'Mission et vision', 2);
    $seed($id, [
        'tag'         => 'Mission & Vision',
        'title'       => "Ce que nous faisons, et où nous allons.",
        'description' => "Deux engagements distincts : le premier décrit notre métier au quotidien, "
                       . "le second la direction que nous donnons au groupe.",
    ], [
        ['card_icon' => 'target', 'card_title' => 'Mission',
         'card_description' => "Concevoir, déployer et exploiter des solutions numériques permettant aux "
                             . "organisations d'améliorer leur performance, leurs opérations et leur "
                             . "capacité à évoluer."],
        ['card_icon' => 'compass', 'card_title' => 'Vision',
         'card_description' => "Faire émerger depuis l'Afrique des solutions technologiques solides, "
                             . "compétitives et capables de répondre aussi bien aux réalités locales "
                             . "qu'aux standards internationaux."],
    ]);

    echo "[4/10] Notre modèle\n";
    $id = $reconcile('process_timeline', 'Notre modèle', 3);
    $seed($id, [
        'tag'   => 'Notre modèle',
        'title' => "De la compréhension du problème à l'évolution de la solution.",
    ], [
        ['proc_num' => '01', 'proc_icon' => 'search', 'proc_title' => 'Comprendre',
         'proc_desc' => "Analyser le besoin réel, ses contraintes et ses utilisateurs avant toute proposition technique."],
        ['proc_num' => '02', 'proc_icon' => 'pen-tool', 'proc_title' => 'Concevoir',
         'proc_desc' => "Définir l'architecture, les parcours et le périmètre qui répondent à ce besoin."],
        ['proc_num' => '03', 'proc_icon' => 'code-2', 'proc_title' => 'Construire',
         'proc_desc' => "Développer la solution avec des méthodes et un niveau de qualité vérifiables."],
        ['proc_num' => '04', 'proc_icon' => 'plug', 'proc_title' => 'Intégrer',
         'proc_desc' => "Connecter la solution aux systèmes, aux données et aux usages déjà en place."],
        ['proc_num' => '05', 'proc_icon' => 'workflow', 'proc_title' => 'Automatiser',
         'proc_desc' => "Retirer des tâches répétitives du quotidien des équipes, là où c'est fiable et utile."],
        ['proc_num' => '06', 'proc_icon' => 'server', 'proc_title' => 'Opérer',
         'proc_desc' => "Exploiter, surveiller et maintenir le système une fois en production."],
        ['proc_num' => '07', 'proc_icon' => 'trending-up', 'proc_title' => 'Améliorer',
         'proc_desc' => "Faire évoluer la solution à mesure que l'organisation et ses besoins changent."],
    ]);

    echo "[5/10] Nos piliers\n";
    $id = $reconcile('capabilities_grid', 'Nos piliers', 4);
    $seed($id, [
        'tag'      => 'Nos piliers',
        'title'    => "Trois pôles, une même chaîne de valeur.",
        'subtitle' => "Le groupe est organisé en trois pôles complémentaires : construire les systèmes, "
                    . "les faire fonctionner et les rendre intelligents, puis transformer les besoins "
                    . "récurrents en produits.",
        'cta_text' => 'Découvrir Digitalium Labs',
        'cta_url'  => '/labs',
    ], [
        ['cap_icon' => 'layers', 'cap_title' => '01 — Digitalium Solutions',
         'cap_desc' => "Software, applications, infrastructure, cloud et systèmes numériques."],
        ['cap_icon' => 'cpu', 'cap_title' => '02 — Digitalium AI & Operations',
         'cap_desc' => "Intelligence artificielle, automatisation, données, support et opérations numériques."],
        ['cap_icon' => 'flask-conical', 'cap_title' => '03 — Digitalium Labs',
         'cap_desc' => "Recherche, innovation, SaaS et produits technologiques propriétaires."],
    ]);

    echo "[6/10] Nos valeurs\n";
    $id = $reconcile('values', 'Nos valeurs', 5);
    $seed($id, [
        'tag'   => 'Nos valeurs',
        'title' => "Ce qui guide nos décisions.",
    ], [
        ['val_icon' => 'award', 'val_title' => 'Excellence',
         'val_text' => "Construire des solutions professionnelles et durables."],
        ['val_icon' => 'lightbulb', 'val_title' => 'Innovation utile',
         'val_text' => "Innover pour résoudre des problèmes concrets."],
        ['val_icon' => 'shield', 'val_title' => 'Intégrité',
         'val_text' => "Travailler avec transparence, responsabilité et confidentialité."],
        ['val_icon' => 'target', 'val_title' => 'Impact',
         'val_text' => "Mesurer la technologie par la valeur qu'elle crée."],
        ['val_icon' => 'handshake', 'val_title' => 'Engagement client',
         'val_text' => "Construire des relations durables au-delà de la livraison."],
    ]);

    echo "[7/10] Notre trajectoire\n";
    // AUCUNE année. Le cahier des charges interdit d'inventer une date ou un
    // événement : cette chaîne décrit l'ordre dans lequel les métiers se sont
    // ajoutés, ce qui est vérifiable, pas un calendrier qui ne le serait pas.
    $id = $reconcile('flow_chain', 'Notre trajectoire', 6);
    $seed($id, [
        'tag'      => 'Notre trajectoire',
        'title'    => "Un métier qui s'est élargi, étape après étape.",
        'subtitle' => "L'ordre dans lequel les activités du groupe se sont ajoutées les unes aux autres. "
                    . "Chaque étape s'appuie sur la précédente plutôt qu'elle ne la remplace.",
    ], [
        ['flow_label' => 'Services numériques', 'flow_icon' => 'globe', 'flow_accent' => '1',
         'flow_note'  => "Les premiers accompagnements numériques auprès des entreprises."],
        ['flow_label' => 'Développement logiciel', 'flow_icon' => 'code-2', 'flow_accent' => '0',
         'flow_note'  => "Conception d'applications et de plateformes sur mesure."],
        ['flow_label' => 'Infrastructure IT', 'flow_icon' => 'server', 'flow_accent' => '0',
         'flow_note'  => "Socles techniques, cloud et sécurité pour faire tenir les systèmes livrés."],
        ['flow_label' => 'IA & Automatisation', 'flow_icon' => 'cpu', 'flow_accent' => '0',
         'flow_note'  => "Retirer du travail répétitif et rendre les systèmes capables de décider."],
        ['flow_label' => 'Data & Business Intelligence', 'flow_icon' => 'bar-chart-3', 'flow_accent' => '0',
         'flow_note'  => "Transformer les données produites par ces systèmes en décisions."],
        ['flow_label' => 'Produits propriétaires', 'flow_icon' => 'flask-conical', 'flow_accent' => '0',
         'flow_note'  => "Les besoins rencontrés plusieurs fois deviennent des produits réutilisables."],
        ['flow_label' => 'Expansion africaine', 'flow_icon' => 'map', 'flow_accent' => '1',
         'flow_note'  => "Porter ces solutions au-delà du marché ivoirien."],
    ]);

    echo "[8/10] Notre équipe\n";
    $id = $reconcile('team_members', 'Notre équipe', 7);
    $seed($id, [
        'tag'         => 'Notre équipe',
        'title'       => "Les expertises derrière Digitalium.",
        'subtitle'    => "Le groupe réunit des compétences complémentaires, de la conception à "
                       . "l'exploitation des systèmes.",
        'poles_title' => "Les profils individuels ne sont pas publiés. Voici les pôles d'expertise "
                       . "qui composent le groupe.",
    ], [
        ['pole_icon' => 'code-2', 'pole_title' => 'Engineering',
         'pole_desc' => "Conception et développement des applications et des plateformes."],
        ['pole_icon' => 'cpu', 'pole_title' => 'AI & Data',
         'pole_desc' => "Intelligence artificielle, automatisation, données et décisionnel."],
        ['pole_icon' => 'server', 'pole_title' => 'Infrastructure',
         'pole_desc' => "Cloud, réseaux, sécurité et exploitation des systèmes."],
        ['pole_icon' => 'pen-tool', 'pole_title' => 'Design',
         'pole_desc' => "Parcours utilisateurs, interfaces et identité visuelle."],
        ['pole_icon' => 'briefcase', 'pole_title' => 'Business',
         'pole_desc' => "Compréhension des métiers, cadrage et relation client."],
        ['pole_icon' => 'life-buoy', 'pole_title' => 'Support',
         'pole_desc' => "Assistance, suivi et continuité de service après la mise en ligne."],
    ]);

    echo "[9/10] Notre ambition\n";
    $id = $reconcile('process_strip', 'Notre ambition', 8);
    $seed($id, [
        'tag'      => 'Notre ambition',
        'title'    => "Construire en Afrique. Penser à l'échelle du monde.",
        'subtitle' => "Digitalium Group ambitionne de développer depuis la Côte d'Ivoire des services, "
                    . "plateformes et produits technologiques capables d'accompagner les organisations "
                    . "africaines tout en répondant aux exigences des marchés internationaux.",
    ], [
        ['proc_num' => '01', 'proc_icon' => 'map-pin', 'proc_title' => "Côte d'Ivoire",
         'proc_desc' => "Le marché où le groupe est implanté et où ses solutions sont éprouvées."],
        ['proc_num' => '02', 'proc_icon' => 'map', 'proc_title' => "Afrique de l'Ouest",
         'proc_desc' => "Des réalités économiques et réglementaires proches, où les mêmes systèmes s'appliquent."],
        ['proc_num' => '03', 'proc_icon' => 'globe-2', 'proc_title' => 'Afrique',
         'proc_desc' => "Un continent dont les besoins numériques appellent des réponses conçues sur place."],
        ['proc_num' => '04', 'proc_icon' => 'globe', 'proc_title' => 'International',
         'proc_desc' => "Des standards de qualité qui permettent d'adresser aussi les marchés hors du continent."],
    ]);

    echo "[10/10] CTA final\n";
    $id = $reconcile('cta', 'CTA final — À propos', 9);
    $seed($id, [
        'eyebrow'   => 'Digitalium Group',
        'title'     => "Construisons la prochaine étape de votre transformation.",
        'subtitle'  => "Vous avez une ambition, un problème métier ou un projet technologique ? "
                     . "Digitalium peut vous accompagner de l'idée jusqu'à la mise en œuvre.",
        'cta_text'  => 'Parler à notre équipe',
        'cta_url'   => '/contact',
        'cta2_text' => 'Découvrir nos solutions',
        'cta2_url'  => '/solutions',
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
