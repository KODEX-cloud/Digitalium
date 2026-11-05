<?php
/**
 * Heroes Solutions & Secteurs — valeurs d'origine administrables
 *
 * Constat (2026-11-05) : les bandeaux de /solutions, de ses cinq pages filles
 * et de /secteurs sont des sections `hero_media_cards` déjà stockées en base,
 * mais l'admin les rendait introuvables (champs mêlés aux réglages de mise en
 * page, gestionnaire « Hero » de la page désactivé sur ces pages). L'admin est
 * corrigé dans le code ; ce script prépare les données :
 *
 *   1. relève les textes, liens, image et alt ACTUELS de chaque hero et les
 *      range dans `settings.hero_defaults_v1` (JSON indexé par id de section).
 *      Ce sont les valeurs reprises quand un champ est vidé en admin
 *      (App\Helpers\HeroContentHelper::withDefaults) ;
 *   2. inscrit dans la Bibliothèque Média les images de ces heroes qui n'y
 *      figurent pas encore, pour qu'elles restent sélectionnables.
 *
 * Non destructif : aucun bloc n'est modifié, le rendu public est identique.
 * Idempotent : drapeau `hero_content_v1`.
 * Réversible : php database/hero_content_v1.php --rollback
 *   retire hero_defaults_v1, le drapeau, et les seules lignes média créées ici
 *   (les fichiers sur disque ne sont jamais supprimés).
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

use App\Helpers\HeroContentHelper;
use App\Models\Block;
use App\Models\Media;
use App\Models\Page;
use App\Models\Section;
use App\Models\Setting;
use App\Services\Database;

const DRAPEAU = 'hero_content_v1';
const JOURNAL = 'hero_content_v1_log';

$PAGES = ['solutions', 'software-platforms', 'ia-automatisation', 'data-business-intelligence',
          'infrastructure-security', 'managed-operations', 'secteurs'];

$rollback = in_array('--rollback', $argv ?? [], true);
echo "=== HEROES SOLUTIONS & SECTEURS" . ($rollback ? " — RETOUR ARRIÈRE" : "") . " ===\n";

try {
    // ── RETOUR ARRIÈRE ──────────────────────────────────────────────────────
    if ($rollback) {
        $journal = json_decode((string)Setting::getVal(JOURNAL, ''), true) ?: [];
        foreach (($journal['media_ids'] ?? []) as $mid) {
            Database::query("DELETE FROM media WHERE id = :id", ['id' => (int)$mid]);
            echo "  média #$mid retiré de la bibliothèque (fichier conservé).\n";
        }
        Database::query(
            "DELETE FROM settings WHERE setting_key IN (:a, :b, :c)",
            ['a' => HeroContentHelper::DEFAULTS_SETTING, 'b' => DRAPEAU, 'c' => JOURNAL]
        );
        echo "  hero_defaults_v1, drapeau et journal supprimés.\n";
        \App\Services\Cache::clear();
        echo "=== TERMINÉ ===\n";
        exit(0);
    }

    if (Setting::getVal(DRAPEAU)) {
        echo "Déjà appliqué (drapeau " . DRAPEAU . "). Rien à faire.\n";
        echo "=== TERMINÉ ===\n";
        exit(0);
    }

    // ── 1. VALEURS D'ORIGINE ────────────────────────────────────────────────
    echo "\n[1/2] Relevé des valeurs actuelles\n";
    $defaults = json_decode((string)Setting::getVal(HeroContentHelper::DEFAULTS_SETTING, ''), true) ?: [];
    $images = [];
    foreach ($PAGES as $slug) {
        $page = Page::findBySlug($slug);
        if (!$page) { echo "  '$slug' : page absente — ignorée.\n"; continue; }
        $hero = null;
        foreach (Section::getByPage((int)$page['id']) as $s) {
            if (($s['type'] ?? '') !== 'hero_media_cards') { continue; }
            if ($hero === null || (($s['status'] ?? '') === 'active' && ($hero['status'] ?? '') !== 'active')) { $hero = $s; }
        }
        if (!$hero) { echo "  '$slug' : aucun hero_media_cards — ignorée.\n"; continue; }
        $sid = (int)$hero['id'];
        $single = Block::getStructuredContent($sid)['single'] ?? [];
        $values = ['_page' => $slug];
        foreach (array_keys(HeroContentHelper::CONTENT_KEYS) as $k) {
            $v = trim((string)($single[$k] ?? ''));
            if ($v !== '') { $values[$k] = $v; }
        }
        $defaults[(string)$sid] = $values;
        if (!empty($values['image'])) { $images[$values['image']] = true; }
        echo "  '$slug' (section #$sid) : " . (count($values) - 1) . " champs relevés.\n";
    }
    Setting::setVal(HeroContentHelper::DEFAULTS_SETTING, json_encode($defaults, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    // ── 2. BIBLIOTHÈQUE MÉDIA ───────────────────────────────────────────────
    echo "\n[2/2] Bibliothèque Média\n";
    $created = [];
    foreach (array_keys($images) as $path) {
        $rel = '/' . ltrim($path, '/');
        $file = PUBLIC_PATH . $rel;
        $deja = Database::fetch("SELECT id FROM media WHERE filepath = :a OR filepath = :b LIMIT 1", ['a' => $rel, 'b' => ltrim($rel, '/')]);
        if ($deja) { echo "  " . basename($rel) . " déjà inscrit (#{$deja['id']}).\n"; continue; }
        if (!is_file($file)) { echo "  ! " . basename($rel) . " absent du disque — non inscrit.\n"; continue; }
        $mime = function_exists('mime_content_type') ? (mime_content_type($file) ?: 'image/jpeg') : 'image/jpeg';
        $id = Media::add([
            'filename' => basename($rel), 'filepath' => $rel, 'original_name' => basename($rel),
            'file_size' => filesize($file), 'mime_type' => $mime,
        ]);
        $created[] = (int)$id;
        echo "  " . basename($rel) . " inscrit (#$id).\n";
    }

    Setting::setVal(JOURNAL, json_encode(['media_ids' => $created, 'date' => date('c')]));
    Setting::setVal(DRAPEAU, '1');
    \App\Services\Cache::clear();
    echo "=== TERMINÉ ===\n";
} catch (\Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    echo "  " . $e->getFile() . ':' . $e->getLine() . "\n";
    exit(1);
}
