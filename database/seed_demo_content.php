<?php
/**
 * Seed Demo Content — remplit les emplacements laissés vides faute de contenu réel
 *
 * ⚠ CONTENU DE DÉMONSTRATION, À RELIRE AVANT TOUTE COMMUNICATION ⚠
 *
 * Les six réalisations semées ici — noms de clients, résultats chiffrés et
 * témoignages — sont INVENTÉES. Elles ont été demandées explicitement pour que
 * les pages ne soient pas vides en attendant les vraies, et sont modifiables
 * depuis /admin/projects sans toucher au code.
 *
 * Ce que cela implique, écrit ici pour que la prochaine personne qui lit ce
 * fichier le sache : tant qu'elles sont en ligne, le site affirme publiquement
 * avoir livré des projets à des clients qui n'existent pas. Les témoignages
 * signés d'un nom et les pourcentages de résultat sont les éléments les plus
 * exposés. Le champ `client` de chaque réalisation commence par « Démo — »
 * pour qu'aucune de ces lignes ne puisse être prise pour une référence réelle
 * par mégarde, ni en admin ni en base.
 *
 * ── Garde-fous ──────────────────────────────────────────────────────────────
 *  1. Drapeau `demo_content_seeded_v1` dans `settings` : le script ne sème
 *     qu'une seule fois. Il vit en base, il est donc visible et réarmable
 *     depuis l'admin, contrairement à un fichier de verrou (leçon BUG-HERO-01).
 *  2. Les réalisations ne sont semées que si la table est VIDE. Une seule
 *     réalisation réelle déjà saisie suffit à tout annuler.
 *  3. Les visuels de hero ne sont posés que sur les sections qui n'en ont pas.
 *     Un choix fait en admin n'est jamais écrasé.
 *
 * ── Suppression ─────────────────────────────────────────────────────────────
 *     /admin/projects → supprimer les six lignes « Démo — … ».
 *     Rien d'autre à défaire : le reste (visuels, activation de section) est du
 *     réglage, pas du contenu inventé.
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
use App\Models\Project;
use App\Models\Setting;
use App\Services\Database;

echo "=== SEED DEMO CONTENT ===\n";

try {
    if (Setting::getVal('demo_content_seeded_v1')) {
        echo "Déjà semé (drapeau demo_content_seeded_v1). Rien à faire.\n";
        echo "  Pour rejouer : supprimer ce réglage depuis l'admin.\n";
        echo "=== TERMINÉ ===\n";
        exit(0);
    }

    /**
     * Visuels disponibles dans la Bibliothèque Média. Aucune image n'est créée
     * ici : on ne fait que réutiliser ce qui a déjà été téléversé. Les trois
     * `proj-*` avaient manifestement été préparées pour des réalisations.
     */
    $IMG_FINANCE   = '/assets/uploads/proj-finance-dashboard-1893001.jpg';
    $IMG_SANTE     = '/assets/uploads/proj-health-tablet-1893001.jpg';
    $IMG_LOGISTIQUE = '/assets/uploads/proj-logistics-map-1893001.jpg';
    $IMG_DASHBOARD = '/assets/uploads/hero-pro-dashboard-1893001.jpg';
    $IMG_EQUIPE    = '/assets/uploads/about-team-meeting-1893001.jpg';

    // ── 1. RÉALISATIONS ─────────────────────────────────────────────────────
    echo "\n[1/3] Réalisations\n";

    $existantes = 0;
    try {
        $existantes = (int)(Database::fetch("SELECT COUNT(*) AS n FROM projects")['n'] ?? 0);
    } catch (\Throwable $e) {
        echo "  table projects illisible : " . $e->getMessage() . "\n";
    }

    if ($existantes > 0) {
        echo "  $existantes réalisation(s) déjà en base — aucune démo ajoutée.\n";
    } else {
        // Catégories reprises telles quelles des filtres déjà déclarés sur
        // /realisations, sinon les boutons de filtre ne correspondraient à rien.
        $demos = [
            [
                'title' => "Plateforme de gestion des sinistres",
                'slug'  => 'plateforme-gestion-sinistres',
                'category' => 'Software',
                'sector' => 'Finance & Assurance',
                'client' => 'Démo — Assurance Horizon',
                'year' => '2025',
                'main_image' => $IMG_FINANCE,
                'description' => "Une application métier qui suit chaque dossier de sinistre de la déclaration au règlement, avec un délai de traitement visible à tout moment.",
                'context' => "Les déclarations arrivaient par téléphone, par courriel et au guichet, puis étaient reprises à la main dans trois classeurs différents. Personne ne savait dire, à un instant donné, combien de dossiers étaient en attente ni depuis combien de temps.",
                'objectives' => "Centraliser les déclarations, quel que soit le canal d'entrée\nRendre visible l'ancienneté de chaque dossier\nSupprimer les ressaisies entre services\nProduire les états réglementaires sans travail manuel",
                'solution' => "Une application web unique où chaque déclaration devient un dossier suivi par étapes. Les gestionnaires voient leur file d'attente triée par ancienneté ; la direction voit les délais moyens par type de sinistre. Les états mensuels se génèrent à date fixe.",
                'technologies' => "PHP 8\nMySQL\nAPI REST\nTableaux de bord",
                'features' => "Saisie unifiée multicanal\nSuivi par étapes avec historique\nAlerte sur les dossiers dormants\nExport réglementaire automatisé",
                'impact' => "Délai moyen de traitement divisé par deux\nPlus aucune ressaisie entre le guichet et la gestion\nÉtats mensuels produits automatiquement",
                'sort_order' => 1, 'is_featured' => 1,
            ],
            [
                'title' => "Dossier patient et prise de rendez-vous",
                'slug'  => 'dossier-patient-rendez-vous',
                'category' => 'Software',
                'sector' => 'Santé',
                'client' => 'Démo — Polyclinique Les Palmiers',
                'year' => '2025',
                'main_image' => $IMG_SANTE,
                'description' => "Un dossier patient consultable au lit du malade et une prise de rendez-vous qui tient compte des disponibilités réelles des praticiens.",
                'context' => "Le dossier papier suivait mal le patient d'un service à l'autre, et les rendez-vous se prenaient sur un registre unique tenu à l'accueil — invisible depuis les cabinets.",
                'objectives' => "Rendre le dossier consultable depuis les services\nOuvrir la prise de rendez-vous aux praticiens\nRéduire les créneaux perdus",
                'solution' => "Une application accessible sur tablette dans les services, avec un dossier patient consolidé et un agenda partagé. Les praticiens ouvrent et ferment leurs créneaux eux-mêmes ; l'accueil voit les disponibilités en temps réel.",
                'technologies' => "PHP 8\nMySQL\nInterface tactile\nAccès par rôles",
                'features' => "Dossier patient consolidé\nAgenda partagé par praticien\nRappel de rendez-vous\nJournal d'accès aux dossiers",
                'impact' => "Créneaux perdus nettement réduits\nDossier disponible dans tous les services\nTraçabilité complète des consultations du dossier",
                'sort_order' => 2, 'is_featured' => 1,
            ],
            [
                'title' => "Suivi de flotte et tournées de livraison",
                'slug'  => 'suivi-flotte-tournees-livraison',
                'category' => 'Infrastructure',
                'sector' => 'Commerce & Distribution',
                'client' => 'Démo — TransLogis',
                'year' => '2024',
                'main_image' => $IMG_LOGISTIQUE,
                'description' => "Une vue unique sur les véhicules, les tournées du jour et les livraisons effectuées, consultable depuis le dépôt comme depuis la route.",
                'context' => "Les tournées se préparaient la veille sur tableur et se corrigeaient par téléphone toute la journée. Le décompte des livraisons effectuées ne se faisait que le lendemain.",
                'objectives' => "Préparer les tournées sans tableur\nConnaître l'avancement en cours de journée\nRapprocher automatiquement livraisons prévues et effectuées",
                'solution' => "Une application de préparation des tournées et une interface mobile pour les chauffeurs, qui fonctionne aussi en connexion instable et se synchronise dès le retour du réseau.",
                'technologies' => "PHP 8\nMySQL\nCartographie\nSynchronisation hors ligne",
                'features' => "Préparation des tournées\nPointage des livraisons sur mobile\nFonctionnement en connexion dégradée\nRapprochement automatique en fin de journée",
                'impact' => "Avancement des tournées connu en temps réel\nDécompte des livraisons disponible le jour même\nPlus de préparation sur tableur",
                'sort_order' => 3, 'is_featured' => 0,
            ],
            [
                'title' => "Portail de scolarité et paiements",
                'slug'  => 'portail-scolarite-paiements',
                'category' => 'Web & Digital',
                'sector' => 'Éducation & Formation',
                'client' => 'Démo — Institut Supérieur Ahoua',
                'year' => '2024',
                'main_image' => $IMG_DASHBOARD,
                'description' => "Un espace où étudiants et familles consultent inscriptions, notes et échéances, et règlent la scolarité sans se déplacer.",
                'context' => "Chaque échéance provoquait une file d'attente au service scolarité, et les demandes de relevé se traitaient une par une au guichet.",
                'objectives' => "Désengorger le guichet aux périodes d'échéance\nDonner aux familles une visibilité sur les échéances\nAutomatiser l'édition des relevés",
                'solution' => "Un portail étudiant relié au système de scolarité, avec paiement en ligne et édition immédiate des documents courants.",
                'technologies' => "PHP 8\nMySQL\nPaiement en ligne\nGénération de documents",
                'features' => "Espace étudiant et espace famille\nPaiement des échéances en ligne\nRelevés éditables à la demande\nNotifications d'échéance",
                'impact' => "Files d'attente fortement réduites aux échéances\nRelevés obtenus sans passage au guichet\nSuivi des règlements consolidé",
                'sort_order' => 4, 'is_featured' => 0,
            ],
            [
                'title' => "Tableau de bord de pilotage financier",
                'slug'  => 'tableau-bord-pilotage-financier',
                'category' => 'Data & BI',
                'sector' => 'Immobilier',
                'client' => 'Démo — Groupe Bâtir',
                'year' => '2025',
                'main_image' => $IMG_FINANCE,
                'description' => "Les données de gestion de plusieurs entités rassemblées dans un tableau de bord unique, actualisé chaque nuit.",
                'context' => "Chaque entité produisait son propre reporting, dans son propre format. La consolidation prenait une semaine et arrivait trop tard pour servir aux arbitrages.",
                'objectives' => "Unifier les indicateurs entre entités\nRaccourcir le délai de consolidation\nLimiter le tableau de bord aux indicateurs qui déclenchent une décision",
                'solution' => "Une collecte automatique des données de chaque entité, un jeu d'indicateurs commun défini avec la direction, et un tableau de bord actualisé chaque nuit.",
                'technologies' => "PHP 8\nMySQL\nETL planifié\nVisualisation de données",
                'features' => "Collecte automatique par entité\nIndicateurs communs\nComparaison entre entités\nExport pour le conseil",
                'impact' => "Consolidation ramenée d'une semaine à une nuit\nIndicateurs identiques pour toutes les entités\nArbitrages appuyés sur des données du jour",
                'sort_order' => 5, 'is_featured' => 1,
            ],
            [
                'title' => "Automatisation du traitement des demandes",
                'slug'  => 'automatisation-traitement-demandes',
                'category' => 'IA & Automatisation',
                'sector' => 'Associations & Organisations',
                'client' => 'Démo — Fondation Espoir Jeunesse',
                'year' => '2023',
                'main_image' => $IMG_EQUIPE,
                'description' => "Le tri et la préparation des dossiers de demande d'aide, automatisés pour rendre du temps aux équipes de terrain.",
                'context' => "Les demandes arrivaient sous toutes les formes et étaient triées à la main. Le temps passé au tri était pris sur l'accompagnement.",
                'objectives' => "Automatiser le tri et la qualification des demandes\nRepérer les dossiers incomplets dès l'arrivée\nRendre du temps aux équipes de terrain",
                'solution' => "Un flux qui reçoit les demandes, extrait les informations utiles, signale les pièces manquantes et oriente le dossier vers le bon interlocuteur. L'humain garde la décision.",
                'technologies' => "PHP 8\nMySQL\nAutomatisation de flux\nExtraction de documents",
                'features' => "Réception multicanal\nQualification automatique\nDétection des pièces manquantes\nOrientation vers le bon interlocuteur",
                'impact' => "Temps de tri fortement réduit\nDossiers incomplets détectés dès la réception\nDécision toujours prise par une personne",
                'sort_order' => 6, 'is_featured' => 0,
            ],
        ];

        $ajoutes = 0;
        foreach ($demos as $d) {
            $d['status'] = 'published';
            $d['meta_title'] = $d['title'] . ' — Digitalium Group';
            // Troncature sûre en UTF-8 sans dépendre de mbstring : le
            // modificateur /u compte des caractères là où substr() compte des
            // octets et couperait un accent en deux.
            $meta = $d['description'];
            if (preg_match('/^.{0,155}/su', $meta, $mm)) { $meta = rtrim($mm[0]); }
            $d['meta_description'] = $meta;
            try {
                Project::add($d);
                $ajoutes++;
                echo "  + {$d['title']}  ({$d['category']} · {$d['client']})\n";
            } catch (\Throwable $e) {
                echo "  ! {$d['title']} : " . $e->getMessage() . "\n";
            }
        }
        echo "  $ajoutes réalisation(s) de démonstration ajoutée(s).\n";
    }

    // ── 2. VISUELS DE HERO ──────────────────────────────────────────────────
    // Les six pages Solutions s'affichaient en aplat bleu faute d'image. On
    // pose un visuel UNIQUEMENT là où le champ est vide.
    echo "\n[2/3] Visuels de hero\n";

    $visuels = [
        'solutions'                   => [$IMG_DASHBOARD,   "Équipe consultant un tableau de bord"],
        'software-platforms'          => [$IMG_DASHBOARD,   "Interface d'une application métier"],
        'ia-automatisation'           => [$IMG_EQUIPE,      "Équipe au travail sur un processus"],
        'data-business-intelligence'  => [$IMG_FINANCE,     "Tableau de bord d'indicateurs"],
        'infrastructure-security'     => [$IMG_LOGISTIQUE,  "Supervision d'une infrastructure"],
        'managed-operations'          => [$IMG_SANTE,       "Poste de travail supervisé"],
        'realisations'                => [$IMG_LOGISTIQUE,  "Projet en cours de déploiement"],
    ];

    $poses = 0;
    foreach ($visuels as $slug => [$img, $alt]) {
        $page = Page::findBySlug($slug);
        if (!$page) { echo "  page '$slug' absente — ignorée.\n"; continue; }

        $heroSection = null;
        foreach (Section::getByPage((int)$page['id']) as $s) {
            if (($s['type'] ?? '') === 'hero_media_cards') { $heroSection = $s; break; }
        }
        if (!$heroSection) { echo "  '$slug' : aucun hero_media_cards — ignorée.\n"; continue; }

        $sid = (int)$heroSection['id'];
        $single = Block::getStructuredContent($sid)['single'] ?? [];
        if (trim((string)($single['image'] ?? '')) !== '') {
            echo "  '$slug' : visuel déjà choisi — conservé.\n";
            continue;
        }
        Block::setVal($sid, 'image', 'image', $img);
        if (trim((string)($single['image_alt'] ?? '')) === '') {
            Block::setVal($sid, 'image_alt', 'text', $alt);
        }
        $poses++;
        echo "  '$slug' : visuel posé (" . basename($img) . ").\n";
    }
    echo "  $poses visuel(s) posé(s).\n";

    // ── 3. SECTION RÉALISATIONS DE /solutions ───────────────────────────────
    // Créée inactive tant que la table était vide. Maintenant qu'elle ne l'est
    // plus, on l'allume — une seule fois, pour ne pas contrarier une extinction
    // décidée plus tard en admin.
    echo "\n[3/3] Section Réalisations de /solutions\n";
    $sol = Page::findBySlug('solutions');
    if (!$sol) {
        echo "  page /solutions absente — ignorée.\n";
    } else {
        $nb = 0;
        try {
            $nb = (int)(Database::fetch(
                "SELECT COUNT(*) AS n FROM projects WHERE status = 'published' OR status IS NULL OR status = ''"
            )['n'] ?? 0);
        } catch (\Throwable $e) { /* table illisible : on n'allume rien */ }

        if ($nb === 0) {
            echo "  aucune réalisation publiée — section laissée inactive.\n";
        } else {
            $fait = false;
            foreach (Section::getByPage((int)$sol['id']) as $s) {
                if (($s['type'] ?? '') === 'projects_cms' && ($s['status'] ?? '') !== 'active') {
                    Database::query("UPDATE sections SET status = 'active' WHERE id = :id", ['id' => (int)$s['id']]);
                    echo "  section #{$s['id']} activée ($nb réalisation(s) publiée(s)).\n";
                    $fait = true;
                }
            }
            if (!$fait) { echo "  section déjà active — rien à faire.\n"; }
        }
    }

    Setting::setVal('demo_content_seeded_v1', '1');
    \App\Services\Cache::clear();

    echo "\n⚠ Les réalisations « Démo — … » sont inventées. À remplacer par de vraies\n";
    echo "  études de cas depuis /admin/projects avant toute communication.\n";
    echo "=== TERMINÉ ===\n";
} catch (\Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    echo "  " . $e->getFile() . ':' . $e->getLine() . "\n";
    exit(1);
}
