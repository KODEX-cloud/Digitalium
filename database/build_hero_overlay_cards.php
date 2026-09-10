<?php
/**
 * Cartes d'information sur les heros « overlay »
 *
 * L'accueil portait des cartes flottantes sur son hero ; les autres pages n'en
 * avaient aucune et leur bandeau restait muet à droite du titre. Ce script pose
 * deux cartes par page, dans la colonne claire du dégradé.
 *
 * ── AUCUN CHIFFRE INVENTÉ ───────────────────────────────────────────────────
 * Chaque valeur numérique est le DÉCOMPTE de ce que la page affiche déjà :
 *   /solutions  → 5 : Software & Platforms, AI & Automation, Data & BI,
 *                     Infrastructure & Security, Managed Operations
 *   /secteurs   → 8 : Finance, Éducation, Commerce, Immobilier, Santé,
 *                     Entreprises & PME, Associations, Événementiel
 *   /a-propos   → 3 : Digitalium Solutions, AI & Operations, Labs
 * Les autres cartes ne portent aucun nombre : /realisations, /insights et
 * /labs ont un contenu qui grandit, un décompte figé y deviendrait faux.
 * /contact reprend mot pour mot deux cartes déjà publiées sur l'accueil —
 * aucune affirmation nouvelle n'est introduite.
 *
 * ── COMMENT LES CARTES SONT RECONNUES ───────────────────────────────────────
 * En mode overlay, les groupes d'une section hero servent aux diapositives
 * (préfixe `slide_`) ET aux cartes (préfixe `card_`). Le gabarit tranche sur le
 * préfixe : un groupe `card_*` n'est jamais pris pour une diapositive.
 *
 * ── OPÉRATION UNIQUE ────────────────────────────────────────────────────────
 * Sous drapeau `settings`. Sans lui, une carte supprimée volontairement en
 * admin réapparaîtrait au déploiement suivant.
 *
 * ── RÉCUPÉRATION ────────────────────────────────────────────────────────────
 * Le pipeline crée une sauvegarde SQL (RollbackManager) AVANT chaque
 * déploiement.
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
use App\Services\Database;

echo "=== CARTES DES HEROS OVERLAY ===\n";

const DRAPEAU = 'hero_overlay_cards_v1';

/* Deux cartes maximum par page : au-delà, la colonne concurrence le titre. */
$parPage = [
    'a-propos' => [
        ['card_icon' => 'layers', 'card_label' => 'Notre structure',
         'card_value' => '3', 'card_unit' => 'pôles',
         'card_meta'  => 'Solutions • AI & Operations • Labs'],
        ['card_icon' => 'map-pin',
         'card_title' => 'Abidjan, Côte d\'Ivoire',
         'card_meta'  => 'Une ambition régionale et internationale'],
    ],
    'solutions' => [
        ['card_icon' => 'layers', 'card_label' => 'Domaines d\'expertise',
         'card_value' => '5',
         'card_meta'  => 'Du logiciel aux opérations gérées'],
        ['card_icon' => 'git-merge',
         'card_title' => 'Une chaîne complète',
         'card_meta'  => 'Concevoir, construire, intégrer, opérer'],
    ],
    'secteurs' => [
        ['card_icon' => 'building-2', 'card_label' => 'Secteurs couverts',
         'card_value' => '8',
         'card_meta'  => 'Finance, santé, éducation, commerce…'],
        ['card_icon' => 'target',
         'card_title' => 'La technologie adaptée',
         'card_meta'  => 'À chaque métier, ses contraintes'],
    ],
    'realisations' => [
        ['card_icon' => 'folder-check',
         'card_title' => 'Études de cas',
         'card_meta'  => 'Contexte, solution, résultat'],
        ['card_icon' => 'route',
         'card_title' => 'Du cadrage à la production',
         'card_meta'  => 'Des projets suivis de bout en bout'],
    ],
    'insights' => [
        ['card_icon' => 'book-open',
         'card_title' => 'Analyses et guides',
         'card_meta'  => 'Retours d\'expérience de nos équipes'],
        ['card_icon' => 'search',
         'card_title' => 'Veille technologique',
         'card_meta'  => 'IA, données, transformation numérique'],
    ],
    'labs' => [
        ['card_icon' => 'flask-conical',
         'card_title' => 'Recherche appliquée',
         'card_meta'  => 'Prototypes et produits internes'],
        ['card_icon' => 'handshake',
         'card_title' => 'Partenariats ouverts',
         'card_meta'  => 'Co-construire de nouveaux produits'],
    ],
    'contact' => [
        // Reprises telles quelles du hero de l'accueil : même promesse, même mots.
        ['card_icon' => 'video', 'card_label' => 'Premier échange',
         'card_title' => 'Audit offert',
         'card_meta'  => '30 min • en visioconférence'],
        ['card_icon' => 'shield-check',
         'card_title' => 'Vos données. Vos règles.',
         'card_meta'  => 'Sécurisé, hébergé, maîtrisé.'],
    ],
];

// ── Drapeau lu dans son propre try : sa lecture ne doit pas tuer le script.
try {
    $deja = Database::fetch(
        "SELECT id FROM settings WHERE setting_key = :k LIMIT 1",
        ['k' => DRAPEAU]
    );
} catch (\Throwable $e) {
    echo "  ATTENTION drapeau illisible : " . $e->getMessage() . "\n";
    echo "  Abandon — mieux vaut aucune carte que des cartes en double.\n";
    echo "=== TERMINÉ ===\n";
    exit(0);
}

if ($deja) {
    echo "  Drapeau " . DRAPEAU . " déjà posé — contenu laissé tel quel.\n";
    echo "=== TERMINÉ ===\n";
    exit(0);
}

$pagesTraitees = 0;
$cartesPosees  = 0;
$echec         = false;

foreach ($parPage as $slug => $cartes) {
    try {
        /* Le hero est la section hero_media_cards la plus haute de la page. */
        $section = Database::fetch(
            "SELECT s.id
               FROM sections s
               JOIN pages p ON p.id = s.page_id
              WHERE p.slug = :slug AND s.type = 'hero_media_cards'
              ORDER BY s.sort_order ASC, s.id ASC
              LIMIT 1",
            ['slug' => $slug]
        );

        if (!$section) {
            echo "  [$slug] aucun hero hero_media_cards — page ignorée.\n";
            continue;
        }

        $secId   = (int)$section['id'];
        $contenu = Block::getStructuredContent($secId);

        /* Le gabarit ne rend ces cartes qu'en mode overlay. Sur un hero
           « split » elles deviendraient des cartes flottantes surnuméraires,
           par-dessus celles déjà en place. */
        $layout = $contenu['single']['layout'] ?? 'split';
        if ($layout !== 'overlay') {
            echo "  [$slug] hero #$secId en mode « $layout » — page ignorée.\n";
            continue;
        }

        /* Une carte déjà présente = quelqu'un est passé par là. On n'y touche pas. */
        $dejaDesCartes = false;
        $maxGroupe     = 0;
        foreach (($contenu['groups'] ?? []) as $groupe) {
            $maxGroupe = max($maxGroupe, (int)$groupe['_group_id']);
            foreach ($groupe as $cle => $valeur) {
                if (strncmp((string)$cle, 'card_', 5) === 0 && trim((string)$valeur) !== '') {
                    $dejaDesCartes = true;
                }
            }
        }
        if ($dejaDesCartes) {
            echo "  [$slug] hero #$secId porte déjà des cartes — page ignorée.\n";
            continue;
        }

        $n = 0;
        foreach ($cartes as $i => $carte) {
            $groupId = $maxGroupe + 1 + $i;
            foreach ($carte as $cle => $valeur) {
                Block::setVal($secId, $cle, 'text', $valeur, $groupId, $groupId);
            }
            $n++;
        }

        echo "  [$slug] hero #$secId : $n carte(s) posée(s).\n";
        $pagesTraitees++;
        $cartesPosees += $n;

    } catch (\Throwable $e) {
        echo "  [$slug] ERREUR : " . $e->getMessage() . "\n";
        $echec = true;
    }
}

/* Drapeau posé seulement si tout a réussi : un échec doit pouvoir être rejoué. */
if ($echec) {
    echo "  Drapeau NON posé — le script sera rejoué au prochain déploiement.\n";
    echo "=== TERMINÉ (avec erreurs) ===\n";
    exit(0);
}

try {
    Database::query(
        "INSERT INTO settings (setting_key, setting_value) VALUES (:k, '1')",
        ['k' => DRAPEAU]
    );
    echo "  Drapeau " . DRAPEAU . " posé — opération non rejouable.\n";
} catch (\Throwable $e) {
    echo "  ATTENTION drapeau non posé : " . $e->getMessage() . "\n";
}

try { \App\Services\Cache::clear(); echo "  Cache vidé.\n"; } catch (\Throwable $e) {}

echo "  Bilan : $pagesTraitees page(s), $cartesPosees carte(s).\n";
echo "  Chaque carte est éditable : /admin/pages -> la page -> section Hero.\n";
echo "=== TERMINÉ ===\n";
