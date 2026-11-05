<?php
/**
 * Visuels du site public — champs d'administration et variantes responsives
 *
 * Constat (2026-11-05, audit des emplacements d'images) : plusieurs visuels
 * n'avaient aucun texte alternatif éditable, et toutes les images partaient en
 * pleine taille, y compris sur mobile (jusqu'à 1,5 Mo pour le hero d'accueil).
 *
 *   1. UNE FOIS (drapeau `images_v1`) : ajoute les champs vides que l'admin
 *      doit pouvoir remplir —
 *        · `image_alt`      sur chaque section about_visual ;
 *        · `proj_image_alt` sur chaque vignette de projects_showcase ;
 *        · `image_mobile`   sur chaque hero hero_media_cards.
 *      Vides, ces champs ne changent RIEN au rendu (le gabarit garde son
 *      texte alternatif actuel, et la même image sur mobile).
 *   2. À CHAQUE EXÉCUTION : crée les variantes WebP manquantes
 *      (public/assets/uploads/_rs/) de toutes les images référencées par le
 *      site — médiathèque, blocs, heroes de page, réalisations, articles.
 *      Rien n'est recréé si tout existe déjà.
 *
 * Non destructif : les originaux ne sont jamais modifiés ni supprimés.
 * Réversible : php database/images_v1.php --rollback
 *   retire les seuls champs ajoutés ici (journal `images_v1_log`) et les
 *   variantes. Les colonnes *_alt (pages, projects, blog_posts) sont ajoutées
 *   par master_migration.php ; les retirer se fait par ALTER TABLE … DROP.
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

use App\Helpers\ResponsiveImage;
use App\Models\Setting;
use App\Services\Database;

const DRAPEAU = 'images_v1';
const JOURNAL = 'images_v1_log';

@ini_set('memory_limit', '512M');
@set_time_limit(0);

$rollback = in_array('--rollback', $argv ?? [], true);
echo "=== VISUELS — CHAMPS ADMIN ET VARIANTES" . ($rollback ? " — RETOUR ARRIÈRE" : "") . " ===\n";

try {
    // ── RETOUR ARRIÈRE ──────────────────────────────────────────────────────
    if ($rollback) {
        $journal = json_decode((string)Setting::getVal(JOURNAL, ''), true) ?: [];
        $ids = array_map('intval', $journal['block_ids'] ?? []);
        foreach (array_chunk($ids, 200) as $chunk) {
            Database::query("DELETE FROM blocks WHERE id IN (" . implode(',', $chunk) . ")");
        }
        echo "  " . count($ids) . " champ(s) ajouté(s) retiré(s).\n";
        $n = 0;
        foreach (glob(PUBLIC_PATH . ResponsiveImage::DIR . '/*.webp') ?: [] as $f) { if (@unlink($f)) { $n++; } }
        echo "  $n variante(s) supprimée(s) (originaux intacts).\n";
        Database::query("DELETE FROM settings WHERE setting_key IN (:a, :b)", ['a' => DRAPEAU, 'b' => JOURNAL]);
        \App\Services\Cache::clear();
        echo "=== TERMINÉ ===\n";
        exit(0);
    }

    // ── 1. CHAMPS D'ADMINISTRATION (une fois) ───────────────────────────────
    echo "\n[1/2] Champs d'administration\n";
    if (Setting::getVal(DRAPEAU)) {
        echo "  Déjà ajoutés (drapeau " . DRAPEAU . ").\n";
    } else {
        $added = [];
        $addField = function (int $sid, string $key, string $type, ?int $groupId) use (&$added): void {
            $exists = $groupId === null
                ? Database::fetch("SELECT id FROM blocks WHERE section_id = :s AND block_key = :k AND group_id IS NULL LIMIT 1", ['s' => $sid, 'k' => $key])
                : Database::fetch("SELECT id FROM blocks WHERE section_id = :s AND block_key = :k AND group_id = :g LIMIT 1", ['s' => $sid, 'k' => $key, 'g' => $groupId]);
            if ($exists) { return; }
            $order = Database::fetch(
                "SELECT COALESCE(MAX(sort_order), 0) + 1 AS o FROM blocks WHERE section_id = :s AND " . ($groupId === null ? "group_id IS NULL" : "group_id = :g"),
                $groupId === null ? ['s' => $sid] : ['s' => $sid, 'g' => $groupId]
            );
            $added[] = (int)Database::insert(
                "INSERT INTO blocks (section_id, block_key, type, value, group_id, sort_order) VALUES (:s, :k, :t, '', :g, :o)",
                ['s' => $sid, 'k' => $key, 't' => $type, 'g' => $groupId, 'o' => (int)($order['o'] ?? 0)]
            );
        };

        foreach (Database::fetchAll("SELECT id FROM sections WHERE type = 'about_visual'") as $s) {
            $addField((int)$s['id'], 'image_alt', 'text', null);
        }
        foreach (Database::fetchAll("SELECT id FROM sections WHERE type = 'hero_media_cards'") as $s) {
            $addField((int)$s['id'], 'image_mobile', 'image', null);
        }
        foreach (Database::fetchAll(
            "SELECT DISTINCT b.section_id, b.group_id FROM blocks b JOIN sections s ON s.id = b.section_id
             WHERE s.type = 'projects_showcase' AND b.group_id IS NOT NULL AND b.block_key = 'proj_image'"
        ) as $g) {
            $addField((int)$g['section_id'], 'proj_image_alt', 'text', (int)$g['group_id']);
        }

        Setting::setVal(JOURNAL, json_encode(['block_ids' => $added, 'date' => date('c')]));
        Setting::setVal(DRAPEAU, '1');
        echo "  " . count($added) . " champ(s) vide(s) ajouté(s) (rendu public inchangé).\n";
    }

    // ── 2. VARIANTES RESPONSIVES (chaque exécution) ─────────────────────────
    echo "\n[2/2] Variantes WebP\n";
    $refs = [];
    $collect = function (?string $v) use (&$refs): void {
        $v = trim((string)$v);
        if ($v !== '' && ResponsiveImage::localFile($v)) { $refs[$v] = true; }
    };
    $queries = [
        "SELECT filepath AS v FROM media",
        "SELECT value AS v FROM blocks WHERE value LIKE '%/assets/%' AND value NOT LIKE '%<%' AND CHAR_LENGTH(value) < 300",
        "SELECT hero_image AS v FROM pages",
        "SELECT hero_image_mobile AS v FROM pages",
        "SELECT main_image AS v FROM projects",
        "SELECT featured_image AS v FROM blog_posts",
    ];
    foreach ($queries as $sql) {
        try { foreach (Database::fetchAll($sql) as $r) { $collect($r['v'] ?? ''); } }
        catch (\Throwable $e) { echo "  (requête ignorée : " . $e->getMessage() . ")\n"; }
    }
    try {
        foreach (Database::fetchAll("SELECT gallery FROM projects WHERE gallery IS NOT NULL AND gallery <> ''") as $r) {
            foreach (preg_split('/\r?\n/', (string)$r['gallery']) as $line) { $collect($line); }
        }
    } catch (\Throwable $e) {}

    // Budget de temps : le déploiement ne doit pas s'éterniser au premier
    // passage. Ce qui reste sera fait au déploiement suivant ; d'ici là, le
    // site sert simplement l'original pour les images sans variantes.
    $budget = 150.0;
    $t0 = microtime(true);
    $created = 0; $done = 0;
    foreach (array_keys($refs) as $src) {
        if (microtime(true) - $t0 > $budget) {
            echo "  Budget de " . (int)$budget . " s atteint : " . (count($refs) - $done) . " image(s) reportée(s) au prochain déploiement.\n";
            break;
        }
        $n = ResponsiveImage::generate($src);
        $created += $n;
        $done++;
        if ($n > 0) { echo "  " . basename($src) . " : $n variante(s).\n"; }
    }
    echo "  $done image(s) examinée(s), $created variante(s) créée(s).\n";

    \App\Services\Cache::clear();
    echo "=== TERMINÉ ===\n";
} catch (\Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    echo "  " . $e->getFile() . ':' . $e->getLine() . "\n";
    exit(1);
}
