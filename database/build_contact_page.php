<?php
/**
 * Build Contact Page — page « Contact » (/contact) et pipeline commercial
 *
 * Deux travaux distincts, volontairement dans le même script car l'un ne sert
 * à rien sans l'autre :
 *
 *  1. SCHÉMA — `contact_messages` passe d'une boîte de réception à un pipeline :
 *     colonnes de qualification, statuts élargis, table d'historique.
 *  2. PAGE — les sept sections décrites au cahier des charges.
 *
 * ── Précaution sur les ALTER (leçon du déploiement 041cc52) ─────────────────
 * Un ALTER verrouille sa table. `contact_messages` n'est lue par aucune des
 * quatre pages contrôlées par les smoke tests du déploiement, le risque est
 * donc faible ; chaque ALTER est malgré tout isolé, et son échec ne fait pas
 * perdre le reste du script.
 *
 * ── Ce qui n'est pas cassé ──────────────────────────────────────────────────
 * Les demandes déjà en base sont conservées et leurs anciens statuts sont
 * traduits (`lu` → « à qualifier », `archivé` → « archivé »). L'ancien
 * formulaire de contact simple et sa route POST /contact continuent de
 * fonctionner : seule sa SECTION est désactivée sur la page, et elle se
 * réactive d'un clic depuis l'admin.
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

echo "=== BUILD CONTACT PAGE (/contact) ===\n";

try {
    // ══════════════════════════════════════════════════════════════════════
    //  1. SCHÉMA
    // ══════════════════════════════════════════════════════════════════════
    echo "\n[schéma] contact_messages\n";

    $colonnes = [];
    try {
        $colonnes = array_column(Database::fetchAll("SHOW COLUMNS FROM contact_messages"), 'Field');
    } catch (\Throwable $e) {
        echo "  table illisible : " . $e->getMessage() . "\n";
    }

    $aAjouter = [
        'entreprise'       => "VARCHAR(150) NULL",
        'secteur'          => "VARCHAR(100) NULL",
        'pays'             => "VARCHAR(100) NULL",
        'besoin'           => "VARCHAR(100) NULL",
        'objectif'         => "VARCHAR(255) NULL",
        'urgence'          => "VARCHAR(50) NULL",
        'budget'           => "VARCHAR(80) NULL",
        'piece_jointe'     => "VARCHAR(255) NULL",
        'piece_jointe_nom' => "VARCHAR(255) NULL",
        'source'           => "VARCHAR(80) NULL",
    ];
    $ajoutees = 0;
    foreach ($aAjouter as $col => $def) {
        if (in_array($col, $colonnes, true)) { continue; }
        try {
            Database::query("ALTER TABLE `contact_messages` ADD COLUMN `$col` $def");
            $ajoutees++;
        } catch (\Throwable $e) {
            echo "  ATTENTION colonne $col : " . $e->getMessage() . "\n";
        }
    }
    echo $ajoutees > 0 ? "  $ajoutees colonne(s) ajoutée(s).\n" : "  colonnes déjà présentes.\n";

    /* `statut` était un ENUM de trois valeurs. Il devient un VARCHAR : les sept
       étapes du pipeline ne tiennent pas dans l'ancien jeu, et un ENUM se
       modifie mal à chaque évolution. */
    try {
        $t = Database::fetch("SHOW COLUMNS FROM contact_messages LIKE 'statut'");
        $type = strtolower((string)($t['Type'] ?? ''));
        if (str_starts_with($type, 'enum')) {
            Database::query(
                "ALTER TABLE `contact_messages` MODIFY `statut` VARCHAR(30) NOT NULL DEFAULT 'nouveau'"
            );
            echo "  statut : ENUM → VARCHAR(30).\n";
            // Traduction des anciennes valeurs, une seule fois par valeur.
            Database::query("UPDATE contact_messages SET statut = 'a_qualifier' WHERE statut = 'lu'");
            Database::query("UPDATE contact_messages SET statut = 'archive'    WHERE statut = 'archivé'");
            echo "  anciens statuts traduits (lu → à qualifier, archivé → archivé).\n";
        } else {
            echo "  statut déjà en VARCHAR — inchangé.\n";
        }
    } catch (\Throwable $e) {
        echo "  ATTENTION statut : " . $e->getMessage() . "\n";
    }

    try {
        Database::query(
            "CREATE TABLE IF NOT EXISTS `message_events` (
                `id`         INT AUTO_INCREMENT PRIMARY KEY,
                `message_id` INT NOT NULL,
                `type`       VARCHAR(30) NOT NULL,
                `ancien`     VARCHAR(30) NULL,
                `nouveau`    VARCHAR(30) NULL,
                `note`       TEXT NULL,
                `auteur`     VARCHAR(100) NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_message_events_message` (`message_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        echo "  message_events : OK.\n";
    } catch (\Throwable $e) {
        echo "  ATTENTION message_events : " . $e->getMessage() . "\n";
    }

    // Dossier des pièces jointes, hors racine web (le .htaccess refuse `storage`).
    $dossier = ROOT_PATH . '/storage/uploads/leads';
    if (!is_dir($dossier)) {
        @mkdir($dossier, 0755, true);
        echo is_dir($dossier) ? "  storage/uploads/leads créé.\n" : "  ATTENTION storage/uploads/leads non créé.\n";
    } else {
        echo "  storage/uploads/leads déjà présent.\n";
    }

    // Plafond d'envois par IP et par heure — réglable sans toucher au code.
    if (Setting::getVal('lead_rate_limit') === null || Setting::getVal('lead_rate_limit') === '') {
        Setting::setVal('lead_rate_limit', '5');
        echo "  réglage lead_rate_limit = 5 (demandes par heure et par IP).\n";
    }

    // ══════════════════════════════════════════════════════════════════════
    //  2. PAGE
    // ══════════════════════════════════════════════════════════════════════
    echo "\n[page] /contact\n";

    $page = Page::findBySlug('contact');
    if (!$page) {
        $pageId = (int)Page::createPage(
            'Contact', 'contact',
            'Parlons de votre projet — Digitalium Group',
            "Expliquez-nous votre contexte, vos objectifs ou les difficultés à résoudre. "
            . "Digitalium Group vous accompagne pour identifier et construire la solution adaptée.",
            'published'
        );
        echo "  page créée (#$pageId).\n";
    } else {
        $pageId = (int)$page['id'];
        echo "  page déjà présente (#$pageId).\n";
    }
    // Le hero de page historique laisse la place à la section `hero_media_cards`,
    // comme sur les autres pages refondues.
    Database::query("UPDATE pages SET status = 'published', hero_status = 0 WHERE id = :id", ['id' => $pageId]);

    /**
     * Réaligne une section sur le couple (type, nom). Position réalignée à
     * chaque passage ; statut posé à la création seulement, pour qu'une section
     * éteinte depuis l'admin ne se rallume pas au déploiement suivant.
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

    $seed = function (int $secId, array $singles, array $groups = []) : bool {
        $contenu = Block::getStructuredContent($secId);
        if (!empty($contenu['single']) || !empty($contenu['groups'])) {
            echo "    contenu déjà présent — non modifié.\n";
            return false;
        }
        foreach ($singles as $cle => $valeur) {
            if ($valeur === '') { continue; }
            $type = \App\Helpers\BlockFieldHelper::type($cle);
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

    // ── Hero ────────────────────────────────────────────────────────────────
    echo "\n[1/7] Hero\n";
    $id = $reconcile('hero_media_cards', 'Hero — Contact', -1);
    $seed($id, [
        'badge'              => 'Parlons de votre projet',
        'title'              => 'Un besoin, un projet ou un défi ?',
        'title_accent'       => "Parlons-en.",
        'text'               => "Expliquez-nous votre contexte, vos objectifs ou les difficultés que vous "
                              . "souhaitez résoudre. Digitalium Group vous accompagne pour identifier et "
                              . "construire la solution technologique la plus adaptée.",
        'cta1_text'          => 'Démarrer ma demande',
        'cta1_url'           => '#demande',
        'cta1_icon'          => 'arrow-down',
        'decor'              => '1',
        'layout'             => 'overlay',
        'image_max_width'    => '1250',
        'image_ratio'        => '1250 / 500',
        'image_ratio_mobile' => '16 / 9',
        'overlay_opacity'    => '64',
        'overlay_min_height' => '500',
        'image_radius'       => '0',
    ]);

    // ── Formulaire ──────────────────────────────────────────────────────────
    echo "[2/7] Formulaire de demande\n";
    $id = $reconcile('lead_form', 'Formulaire de demande', 0);
    $seed($id, [
        'tag'           => 'Votre demande',
        'title'         => "Quelques questions, et nous revenons vers vous.",
        'subtitle'      => "Quatre étapes courtes. Seuls votre nom, votre email et la description "
                         . "de votre besoin sont obligatoires.",
        'step1_title'   => 'Votre besoin',
        'step2_title'   => 'Votre organisation',
        'step3_title'   => 'Le projet',
        'step4_title'   => 'Validation',
        'submit_text'   => 'Envoyer ma demande',
        'back_text'     => 'Retour',
        'next_text'     => 'Continuer',
        'success_title' => 'Votre demande est bien enregistrée.',
        'success_text'  => "Nous l'étudions et un membre de l'équipe revient vers vous. "
                         . "Si votre demande est urgente, écrivez-nous directement sur WhatsApp.",
        'error_title'   => "Votre demande n'a pas pu être envoyée.",
        'file_note'     => "PDF, Word, ODT, TXT ou image. 5 Mo maximum.",
        'privacy_note'  => "Ces informations servent uniquement à traiter votre demande. "
                         . "Elles ne sont ni revendues ni transmises à des tiers.",
    ], [
        ['besoin_label' => 'Développer une application ou plateforme', 'besoin_icon' => 'code'],
        ['besoin_label' => "Automatiser un processus avec l'IA",       'besoin_icon' => 'bot'],
        ['besoin_label' => 'Data & Business Intelligence',             'besoin_icon' => 'bar-chart-3'],
        ['besoin_label' => 'Infrastructure & cybersécurité',           'besoin_icon' => 'shield-check'],
        ['besoin_label' => 'Maintenance / Managed IT',                 'besoin_icon' => 'life-buoy'],
        ['besoin_label' => 'Transformation digitale',                  'besoin_icon' => 'refresh-cw'],
        ['besoin_label' => 'Autre besoin',                             'besoin_icon' => 'message-square'],

        ['secteur_label' => 'Finance & Assurance'],
        ['secteur_label' => 'Éducation & Formation'],
        ['secteur_label' => 'Commerce & Distribution'],
        ['secteur_label' => 'Immobilier'],
        ['secteur_label' => 'Santé'],
        ['secteur_label' => 'Entreprises & PME'],
        ['secteur_label' => 'Associations & Organisations'],
        ['secteur_label' => 'Événementiel'],
        ['secteur_label' => 'Autre secteur'],

        ['urgence_label' => "Immédiat — le sujet bloque aujourd'hui"],
        ['urgence_label' => 'Sous 1 à 3 mois'],
        ['urgence_label' => 'Sous 3 à 6 mois'],
        ['urgence_label' => 'Pas de date arrêtée'],

        ['budget_label' => 'Pas encore défini'],
        ['budget_label' => 'Moins de 5 000 000 FCFA'],
        ['budget_label' => '5 à 15 000 000 FCFA'],
        ['budget_label' => '15 à 50 000 000 FCFA'],
        ['budget_label' => 'Plus de 50 000 000 FCFA'],
    ]);

    // ── Contact direct ──────────────────────────────────────────────────────
    // Les coordonnées viennent de la Configuration du site (réglages
    // contact_address, contact_phone, contact_email, site_whatsapp) : la
    // section ne les duplique pas, elle affiche seulement ses libellés.
    echo "[3/7] Contact direct\n";
    $id = $reconcile('contact_details', 'Contact direct', 1);
    $seed($id, [
        'title'              => 'Nous joindre directement',
        'subtitle'           => "Si vous préférez parler avant d'écrire, ces canaux sont ouverts.",
        'coordonnees_title'  => 'Coordonnées',
        'cta_label'          => 'Envoyer un email',
        'whatsapp_btn_label' => 'Écrire sur WhatsApp',
    ]);

    // ── Types de projets ────────────────────────────────────────────────────
    echo "[4/7] Types de projets\n";
    $id = $reconcile('sectors_grid', 'Types de projets', 2);
    $seed($id, [
        'tag'       => 'Nos domaines',
        'title'     => 'Comment pouvons-nous vous accompagner ?',
        'subtitle'  => "La plupart des projets combinent plusieurs de ces familles. "
                     . "Choisissez celle qui vous parle le plus, nous affinerons ensemble.",
        'more_text' => 'Voir toutes les solutions',
        'more_url'  => '/solutions',
    ], [
        ['sec_num' => '01', 'sec_icon' => 'layout-grid', 'sec_title' => 'Software & Platforms',
         'sec_desc' => "Applications métiers, plateformes web et mobiles conçues autour de vos processus réels.",
         'sec_link' => '/solutions/software-platforms', 'sec_link_text' => 'Explorer'],
        ['sec_num' => '02', 'sec_icon' => 'bot', 'sec_title' => 'AI & Automation',
         'sec_desc' => "Agents, assistants et automatisations qui prennent en charge le travail répétitif.",
         'sec_link' => '/solutions/ia-automatisation', 'sec_link_text' => 'Explorer'],
        ['sec_num' => '03', 'sec_icon' => 'bar-chart-3', 'sec_title' => 'Data & Business Intelligence',
         'sec_desc' => "Vos données de gestion transformées en indicateurs lisibles et en décisions.",
         'sec_link' => '/solutions/data-business-intelligence', 'sec_link_text' => 'Explorer'],
        ['sec_num' => '04', 'sec_icon' => 'shield-check', 'sec_title' => 'Infrastructure & Security',
         'sec_desc' => "Réseaux, cloud et cybersécurité : une informatique qui tient, se sauvegarde et redémarre.",
         'sec_link' => '/solutions/infrastructure-security', 'sec_link_text' => 'Explorer'],
        ['sec_num' => '05', 'sec_icon' => 'life-buoy', 'sec_title' => 'Managed Operations',
         'sec_desc' => "Support, supervision et maintenance : votre informatique gérée dans la durée.",
         'sec_link' => '/solutions/managed-operations', 'sec_link_text' => 'Explorer'],
    ]);

    // ── Ce qui se passe ensuite ─────────────────────────────────────────────
    echo "[5/7] Ce qui se passe ensuite\n";
    $id = $reconcile('process_strip', 'Ce qui se passe ensuite', 3);
    $seed($id, [
        'tag'      => 'Après votre envoi',
        'title'    => 'Ce qui se passe ensuite.',
        'subtitle' => "Aucune de ces étapes ne vous engage. Vous décidez à la fin, pas au début.",
    ], [
        ['proc_num' => '01', 'proc_icon' => 'edit-3',    'proc_title' => 'Vous expliquez votre besoin',
         'proc_desc' => "Le formulaire suffit : quelques phrases valent mieux qu'un cahier des charges complet."],
        ['proc_num' => '02', 'proc_icon' => 'search',    'proc_title' => 'Nous analysons votre contexte',
         'proc_desc' => "Nous relisons votre demande et identifions ce qui manque pour vous répondre utilement."],
        ['proc_num' => '03', 'proc_icon' => 'phone',     'proc_title' => 'Un expert vous contacte',
         'proc_desc' => "Un échange court pour comprendre le métier et les contraintes derrière la demande."],
        ['proc_num' => '04', 'proc_icon' => 'file-text', 'proc_title' => 'Nous proposons une approche',
         'proc_desc' => "Périmètre, étapes et options, formulés en clair plutôt qu'en jargon technique."],
        ['proc_num' => '05', 'proc_icon' => 'rocket',    'proc_title' => 'Nous lançons le projet',
         'proc_desc' => "Uniquement après votre validation, et par étapes livrables."],
    ]);

    // ── FAQ ─────────────────────────────────────────────────────────────────
    echo "[6/7] Questions fréquentes\n";
    $id = $reconcile('faq', 'Questions fréquentes', 4);
    $seed($id, [
        'title'    => 'Questions fréquentes',
        'subtitle' => "Ce qu'on nous demande le plus souvent avant un premier échange.",
    ], [
        ['faq_question' => "Travaillez-vous uniquement en Côte d'Ivoire ?",
         'faq_answer'   => "Non. Nous sommes basés à Abidjan et y intervenons sur site, mais nous travaillons "
                         . "aussi à distance avec des organisations situées ailleurs. La distance change "
                         . "l'organisation du projet, pas sa faisabilité."],
        ['faq_question' => "Pouvez-vous intervenir sur un projet déjà existant ?",
         'faq_answer'   => "Oui. Reprendre une application en service, corriger une intégration ou faire évoluer "
                         . "un outil développé par quelqu'un d'autre fait partie de notre travail courant. "
                         . "Nous commençons alors par un état des lieux du code et de l'infrastructure."],
        ['faq_question' => "Proposez-vous un accompagnement mensuel ?",
         'faq_answer'   => "Oui, c'est notre offre Managed Operations : support, maintenance et supervision "
                         . "dans la durée. Un projet livré puis laissé sans suivi se dégrade en quelques mois."],
        ['faq_question' => "Comment se déroule un premier échange ?",
         'faq_answer'   => "Une conversation d'une trentaine de minutes, sans engagement, pour comprendre votre "
                         . "contexte. Nous en ressortons avec une lecture de votre besoin et les options "
                         . "possibles — y compris celle de ne rien développer si ce n'est pas la bonne réponse."],
        ['faq_question' => "Travaillez-vous avec les PME et les grandes organisations ?",
         'faq_answer'   => "Les deux. Le périmètre et le rythme diffèrent, la méthode non : comprendre le métier "
                         . "avant de proposer une technologie."],
    ]);

    // ── CTA final ───────────────────────────────────────────────────────────
    echo "[7/7] CTA final\n";
    $id = $reconcile('cta', 'CTA final — Contact', 5);
    $seed($id, [
        'eyebrow'   => 'Un dernier mot',
        'title'     => "Construisons quelque chose d'utile.",
        'subtitle'  => "Digitalium transforme les problématiques métiers en solutions numériques "
                     . "performantes, sécurisées et évolutives.",
        'cta_text'  => 'Parler de mon projet',
        'cta_url'   => '#demande',
    ]);

    /**
     * L'ancienne section de contact (formulaire simple) fait doublon avec le
     * nouveau formulaire. Elle est éteinte UNE SEULE FOIS, pas supprimée : son
     * contenu reste là et un clic en admin la rallume. Le drapeau évite de la
     * ré-éteindre si vous choisissez de la remettre.
     */
    if (!Setting::getVal('contact_legacy_form_off_v1')) {
        $eteintes = 0;
        foreach (Section::getByPage($pageId) as $s) {
            if (($s['type'] ?? '') === 'contact' && ($s['status'] ?? '') === 'active') {
                Database::query("UPDATE sections SET status = 'inactive' WHERE id = :id",
                    ['id' => (int)$s['id']]);
                $eteintes++;
            }
        }
        Setting::setVal('contact_legacy_form_off_v1', '1');
        echo "\n  ancien formulaire simple : $eteintes section(s) désactivée(s) — réactivables en admin.\n";
    }

    \App\Services\Cache::clear();
    echo "\nPage /contact éditable depuis /admin/pages -> Contact.\n";
    echo "Demandes reçues : /admin/messages.\n";
    echo "=== TERMINÉ ===\n";
} catch (\Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    echo "  " . $e->getFile() . ':' . $e->getLine() . "\n";
    exit(1);
}
