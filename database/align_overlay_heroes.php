<?php
/**
 * Alignement des heros « overlay » sur celui de /secteurs
 *
 * /secteurs sert de référence : son hero est bord à bord et haut de 560px,
 * les six autres pages en overlay étaient plus étroites et plus basses.
 *
 * ── LA RÉFÉRENCE EST LUE EN BASE, PAS RECOPIÉE ──────────────────────────────
 * Les valeurs ne sont pas écrites en dur ici : elles sont relues sur le hero de
 * /secteurs au moment de l'exécution. Si quelqu'un ajuste ce hero en admin
 * avant le déploiement, c'est SA valeur qui se propage — pas une transcription
 * figée qui aurait déjà vieilli.
 *
 * ── CE QUI EST COPIÉ, ET RIEN D'AUTRE ───────────────────────────────────────
 *   overlay_min_height  hauteur du cadre
 *   image_max_width     largeur (« full » = bord à bord)
 *   image_radius        arrondi des angles
 * La demande porte sur la taille et la hauteur. Le voile (`overlay_opacity`),
 * la photo, les titres et les boutons de chaque page restent intacts : ce sont
 * l'identité de la page, pas sa géométrie.
 *
 * ── RESTE ENTIÈREMENT ADMINISTRABLE ─────────────────────────────────────────
 * Ces trois réglages sont des blocs `single` déjà exposés dans l'éditeur
 * (/admin/pages -> la page -> section Hero), avec intitulé et aide de saisie.
 * Ce script pose une valeur de départ, il ne verrouille rien : toute
 * modification faite ensuite en admin tient, puisque l'opération est unique.
 *
 * ── OPÉRATION UNIQUE ────────────────────────────────────────────────────────
 * Sous drapeau `settings`. Sans lui, une hauteur ajustée à la main serait
 * réécrite à chaque déploiement.
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

echo "=== ALIGNEMENT DES HEROS OVERLAY SUR /secteurs ===\n";

const DRAPEAU   = 'hero_overlay_size_aligned_v1';
const REFERENCE = 'secteurs';

/** Les seules clés recopiées : la géométrie du cadre. */
$aCopier = ['overlay_min_height', 'image_max_width', 'image_radius'];

$cibles = ['a-propos', 'solutions', 'realisations', 'insights', 'labs', 'contact'];

/** Retourne la section hero_media_cards d'une page, ou null. */
$heroDe = static function (string $slug): ?array {
    $s = Database::fetch(
        "SELECT s.id
           FROM sections s
           JOIN pages p ON p.id = s.page_id
          WHERE p.slug = :slug AND s.type = 'hero_media_cards'
          ORDER BY s.sort_order ASC, s.id ASC
          LIMIT 1",
        ['slug' => $slug]
    );
    return $s ?: null;
};

// ── Drapeau lu dans son propre try : sa lecture ne doit pas tuer le script.
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
    echo "  Drapeau " . DRAPEAU . " déjà posé — hauteurs laissées telles quelles.\n";
    echo "=== TERMINÉ ===\n";
    exit(0);
}

// ── 1. La référence ─────────────────────────────────────────────────────────
try {
    $secRef = $heroDe(REFERENCE);
    if (!$secRef) {
        echo "  Page « " . REFERENCE . " » sans hero hero_media_cards — abandon.\n";
        echo "=== TERMINÉ ===\n";
        exit(0);
    }
    $refId      = (int)$secRef['id'];
    $refContenu = Block::getStructuredContent($refId);
    $refSingle  = $refContenu['single'] ?? [];
} catch (\Throwable $e) {
    echo "  Référence illisible : " . $e->getMessage() . "\n";
    echo "=== TERMINÉ (avec erreurs) ===\n";
    exit(0);
}

/* Copier la géométrie d'un hero qui ne serait pas en overlay n'aurait aucun
   sens : `overlay_min_height` n'y est même pas lu. */
$refLayout = $refSingle['layout'] ?? 'split';
if ($refLayout !== 'overlay') {
    echo "  Le hero de référence est en mode « $refLayout », pas « overlay » — abandon.\n";
    echo "=== TERMINÉ ===\n";
    exit(0);
}

$modele = [];
foreach ($aCopier as $cle) {
    $v = trim((string)($refSingle[$cle] ?? ''));
    if ($v !== '') { $modele[$cle] = $v; }
}

if (empty($modele)) {
    echo "  La référence ne porte aucune des clés à copier — abandon.\n";
    echo "=== TERMINÉ ===\n";
    exit(0);
}

echo "  Référence /" . REFERENCE . " (hero #$refId) :\n";
foreach ($modele as $cle => $val) { echo "    $cle = $val\n"; }

// ── 2. Les cibles ───────────────────────────────────────────────────────────
$alignees = 0;
$dejaBonnes = 0;
$echec = false;

foreach ($cibles as $slug) {
    try {
        $sec = $heroDe($slug);
        if (!$sec) {
            echo "  [$slug] aucun hero hero_media_cards — page ignorée.\n";
            continue;
        }
        $secId   = (int)$sec['id'];
        $contenu = Block::getStructuredContent($secId);
        $single  = $contenu['single'] ?? [];

        $layout = $single['layout'] ?? 'split';
        if ($layout !== 'overlay') {
            echo "  [$slug] hero #$secId en mode « $layout » — page ignorée.\n";
            continue;
        }

        $changements = [];
        foreach ($modele as $cle => $val) {
            if (trim((string)($single[$cle] ?? '')) === $val) { continue; }
            Block::setVal($secId, $cle, 'text', $val);
            $changements[] = $cle . ' : «' . trim((string)($single[$cle] ?? '')) . '» -> ' . $val;
        }

        if (empty($changements)) {
            echo "  [$slug] hero #$secId déjà conforme.\n";
            $dejaBonnes++;
        } else {
            echo "  [$slug] hero #$secId aligné — " . implode(' | ', $changements) . "\n";
            $alignees++;
        }

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

echo "  Bilan : $alignees page(s) alignée(s), $dejaBonnes déjà conforme(s).\n";
echo "  Hauteur et largeur réglables page par page : /admin/pages -> la page -> Hero.\n";
echo "=== TERMINÉ ===\n";
