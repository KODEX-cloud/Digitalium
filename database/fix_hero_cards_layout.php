<?php
/**
 * Repositionnement des cartes flottantes du hero « split »
 *
 * ── LE DÉFAUT ───────────────────────────────────────────────────────────────
 * Sur l'accueil, les quatre cartes du hero étaient disposées en cascade
 * (4/46, 32/30, 58/18, 82/6) et masquaient les visages de la photo d'équipe.
 *
 * Le vrai problème n'était pas leur position mais leur encombrement : chaque
 * carte mesurait de 232 à 300 px de large dans une colonne d'environ 600 px,
 * et le cadrage vertical de la photo place des visages sur TOUTE la largeur,
 * de 8 % à 62 % de la hauteur. Aucune disposition de quatre cartes de cette
 * taille ne pouvait laisser les visages dégagés.
 *
 * La correction est double :
 *   - côté feuille de style (hero_media_cards.php) : largeur en pourcentage,
 *     visuel plus haut, donc une bande basse plus profonde ;
 *   - côté contenu (ce script) : les positions ENREGISTRÉES sont réécrites en
 *     grille 2x2 dans cette bande basse, seule zone sans visage.
 *
 * Le second est indispensable : les positions vivent dans `blocks`, elles
 * l'emportent sur les valeurs par défaut du gabarit. Modifier le code seul
 * n'aurait rien changé sur le site en production.
 *
 * ── OPÉRATION UNIQUE ────────────────────────────────────────────────────────
 * Sous drapeau `settings`. Les positions restent modifiables carte par carte
 * depuis /admin/pages : sans ce drapeau, un réglage fait à la main serait
 * écrasé à chaque déploiement.
 *
 * ── PÉRIMÈTRE ───────────────────────────────────────────────────────────────
 * Uniquement les heros en mode « split ». Les modes « bandeau » et « overlay »
 * n'affichent pas de carte flottante — en overlay les groupes sont des
 * diapositives de carrousel, les toucher casserait le hero.
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

echo "=== REPOSITIONNEMENT DES CARTES DU HERO ===\n";

const DRAPEAU = 'hero_cards_grid_v1';

/* Grille 2x2 dans le tiers bas. Identique aux valeurs par défaut du gabarit
   (hero_media_cards.php) : un hero reconstruit et un hero migré se ressemblent.
   Au-delà de quatre cartes, le motif se répète — les cartes suivantes se
   superposeraient, mais quatre est déjà le maximum lisible sur ce cadre. */
$positions = [
    ['top' => '66', 'left' => '3'],
    ['top' => '66', 'left' => '52'],
    ['top' => '83', 'left' => '3'],
    ['top' => '83', 'left' => '52'],
];

// ── Le drapeau, lu dans son propre try : sa lecture ne doit pas pouvoir
//    tuer le script entier (incident constaté sur le retrait des doublons).
try {
    $deja = Database::fetch(
        "SELECT id FROM settings WHERE setting_key = :k LIMIT 1",
        ['k' => DRAPEAU]
    );
} catch (\Throwable $e) {
    echo "  ATTENTION drapeau illisible : " . $e->getMessage() . "\n";
    echo "  Abandon — mieux vaut ne rien faire que réécrire un réglage manuel.\n";
    echo "=== TERMINÉ ===\n";
    exit(0);
}

if ($deja) {
    echo "  Drapeau " . DRAPEAU . " déjà posé — positions laissées telles quelles.\n";
    echo "=== TERMINÉ ===\n";
    exit(0);
}

$sectionsTraitees = 0;
$cartesDeplacees  = 0;
$echec            = false;

try {
    $sections = Database::fetchAll(
        "SELECT s.id, s.name, p.slug
           FROM sections s
           JOIN pages p ON p.id = s.page_id
          WHERE s.type = 'hero_media_cards'
          ORDER BY s.id"
    ) ?: [];

    echo "  " . count($sections) . " section(s) de type hero_media_cards trouvée(s).\n";

    foreach ($sections as $sec) {
        $secId = (int)$sec['id'];
        $slug  = (string)$sec['slug'];

        try {
            $contenu = Block::getStructuredContent($secId);
        } catch (\Throwable $e) {
            echo "  [$slug] section #$secId illisible : " . $e->getMessage() . "\n";
            $echec = true;
            continue;
        }

        /* Même règle que le gabarit : champ absent ou valeur inattendue = split. */
        $layout = $contenu['single']['layout'] ?? 'split';
        if (!in_array($layout, ['split', 'banner', 'overlay'], true)) { $layout = 'split'; }

        if ($layout !== 'split') {
            echo "  [$slug] section #$secId en mode « $layout » — ignorée.\n";
            continue;
        }

        $groupes = $contenu['groups'] ?? [];
        if (empty($groupes)) {
            echo "  [$slug] section #$secId : aucune carte.\n";
            continue;
        }

        $n = 0;
        foreach ($groupes as $i => $groupe) {
            $groupId = (int)$groupe['_group_id'];
            $ordre   = (int)($groupe['_sort_order'] ?? $i);
            $pos     = $positions[$i % count($positions)];

            try {
                Block::setVal($secId, 'card_top',  'text', $pos['top'],  $groupId, $ordre);
                Block::setVal($secId, 'card_left', 'text', $pos['left'], $groupId, $ordre);
                $n++;
            } catch (\Throwable $e) {
                echo "  [$slug] carte #$groupId non déplacée : " . $e->getMessage() . "\n";
                $echec = true;
            }
        }

        echo "  [$slug] section #$secId : $n carte(s) rangée(s) en grille basse.\n";
        $sectionsTraitees++;
        $cartesDeplacees += $n;
    }
} catch (\Throwable $e) {
    echo "  ERREUR : " . $e->getMessage() . "\n";
    $echec = true;
}

/* Le drapeau n'est posé QUE si tout s'est bien passé : un échec doit pouvoir
   être rejoué au déploiement suivant plutôt que d'être figé définitivement. */
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

echo "  Bilan : $sectionsTraitees section(s), $cartesDeplacees carte(s).\n";
echo "  Positions modifiables carte par carte : /admin/pages -> Accueil -> Hero.\n";
echo "=== TERMINÉ ===\n";
