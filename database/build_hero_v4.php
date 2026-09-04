<?php
/**
 * Build Hero v4 — Hero de la page d'accueil (visuel + cartes flottantes)
 *
 * Retire le hero de page (pages.hero_status = 0) et le remplace par une section
 * `hero_media_cards` placée en tête, reproduisant le modèle de référence fourni
 * par la direction.
 *
 * ── Script RÉCONCILIATEUR, pas one-shot ────────────────────────────────────
 * Incident constaté : la première version sortait immédiatement quand le verrou
 * existait. Or `build_home_v2.php` désactive toute section dont le type n'est pas
 * dans sa liste `$targetTypes` ; `hero_media_cards` n'y figurait pas, la section
 * a donc été désactivée au déploiement suivant et la page s'est retrouvée SANS
 * aucun hero (l'ancien étant désactivé, le nouveau coupé).
 *
 * Deux garde-fous désormais :
 *   1. `hero_media_cards` est déclaré dans `$targetTypes` de build_home_v2.php ;
 *   2. ce script réaligne la section (existence, statut actif, position) à CHAQUE
 *      déploiement — il se répare tout seul si un autre script la désactive.
 *
 * Le contenu, lui, reste protégé : les blocs ne sont semés que si la section est
 * vide, et le verrou storage/hero_v4.lock empêche de retoucher pages.hero_status
 * une fois le basculement effectué. Rien de ce que l'admin modifie n'est écrasé.
 *
 * Position : sort_order = -1, en amont des sections 0..N réordonnées par
 * build_home_v2.php — évite toute égalité de tri avec logos_strip.
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

echo "=== BUILD HERO V4 (réconciliation) ===\n";

try {
    $home = Page::findBySlug('home');
    if (!$home) {
        echo "Page 'home' introuvable — abandon.\n";
        exit(0);
    }
    $pageId = (int)$home['id'];
    $lock   = ROOT_PATH . '/storage/hero_v4.lock';
    $first  = !file_exists($lock);

    // ── 1. Section : existence, statut, position — réaligné à chaque passage ──
    $secId = null;
    foreach (Section::getByPage($pageId) as $s) {
        if (($s['type'] ?? '') === 'hero_media_cards') {
            $secId  = (int)$s['id'];
            $status = $s['status'] ?? 'active';
            $order  = (int)($s['sort_order'] ?? 0);
            break;
        }
    }

    if ($secId === null) {
        $secId = (int)Section::addSection($pageId, 'Hero — visuel et cartes', 'hero_media_cards', -1);
        Database::query("UPDATE sections SET status = 'active', sort_order = -1 WHERE id = :id", ['id' => $secId]);
        echo "Section hero_media_cards créée (#$secId), active, position -1.\n";
    } else {
        if ($status !== 'active' || $order !== -1) {
            Database::query("UPDATE sections SET status = 'active', sort_order = -1 WHERE id = :id", ['id' => $secId]);
            echo "Section #$secId réalignée : statut '$status' -> 'active', position $order -> -1.\n";
        } else {
            echo "Section #$secId déjà active en position -1 — inchangée.\n";
        }
    }

    // ── 2. Hero de page : désactivé une seule fois (l'admin garde la main) ────
    if ($first) {
        Database::query("UPDATE pages SET hero_status = 0 WHERE id = :id", ['id' => $pageId]);
        echo "pages.hero_status -> 0 (hero de page retiré).\n";
    } else {
        echo "pages.hero_status non touché (bascule déjà effectuée).\n";
    }

    // ── 3. Contenu : semé UNIQUEMENT si la section est vide ───────────────────
    $content = Block::getStructuredContent($secId);
    if (!empty($content['single']) || !empty($content['groups'])) {
        echo "Contenu déjà présent ("
            . count($content['single']) . " blocs, "
            . count($content['groups']) . " cartes) — non modifié.\n";
    } else {
        // Reprise intégrale du contenu du hero de page existant.
        $rawTitle = (string)($home['hero_title'] ?? '');
        $accent   = '';
        if (preg_match('/<span[^>]*>(.*?)<\/span>/is', $rawTitle, $m)) {
            $accent   = trim(strip_tags($m[1]));
            $rawTitle = str_replace($m[0], '', $rawTitle);
        }
        $titleMain = trim(strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $rawTitle)));
        $titleMain = trim(preg_replace('/\n{2,}/', "\n", $titleMain));

        $badge = trim((string)($home['hero_badge'] ?? ''));
        if ($badge === '') {
            // Le champ hero_badge est vide en base : sans valeur, la pastille du
            // modèle ne s'afficherait pas. Valeur de départ, éditable en admin.
            $badge = 'Transformation digitale';
        }

        $singles = [
            ['badge',        'text',     $badge],
            ['title',        'textarea', $titleMain],
            ['title_accent', 'text',     $accent],
            ['text',         'textarea', trim(strip_tags((string)($home['hero_subtitle'] ?? '')))],
            ['cta1_text',    'text',     trim((string)($home['hero_cta1_text'] ?? ''))],
            ['cta1_url',     'link',     trim((string)($home['hero_cta1_url'] ?? ''))],
            ['cta1_icon',    'text',     'arrow-right'],
            ['cta2_text',    'text',     trim((string)($home['hero_cta2_text'] ?? ''))],
            ['cta2_url',     'link',     trim((string)($home['hero_cta2_url'] ?? ''))],
            ['cta2_icon',    'text',     'play'],
            ['image',        'image',    trim((string)($home['hero_image'] ?? ''))],
            ['image_alt',    'text',     str_replace("\n", ' ', $titleMain)],
            ['decor',        'text',     '1'],
        ];
        $n = 0;
        foreach ($singles as [$key, $type, $value]) {
            if ($value === '') { continue; }
            Block::setVal($secId, $key, $type, $value);
            $n++;
        }
        echo "$n blocs 'single' posés (titre : " . str_replace("\n", ' / ', $titleMain)
            . " | accent : " . ($accent !== '' ? $accent : '(aucun)') . ").\n";

        // Cartes flottantes. Les deux premières reprennent des chiffres DÉJÀ
        // affichés sur le site (section stats_intro) — aucune nouvelle
        // affirmation n'est introduite. Toutes éditables depuis /admin/pages.
        $cards = [
            ['card_icon' => 'users', 'card_label' => 'Clients accompagnés',
             'card_value' => '100+', 'card_badge' => 'Actif',
             'card_top' => '4', 'card_left' => '46'],
            ['card_icon' => 'gauge', 'card_label' => 'Taux de satisfaction',
             'card_value' => '95', 'card_unit' => '%', 'card_progress' => '95',
             'card_top' => '32', 'card_left' => '30'],
            ['card_icon' => 'video', 'card_label' => 'Premier échange',
             'card_title' => 'Audit offert', 'card_meta' => '30 min • en visioconférence',
             'card_top' => '58', 'card_left' => '18'],
            ['card_icon' => 'shield-check',
             'card_title' => 'Vos données. Vos règles.', 'card_meta' => 'Sécurisé, hébergé, maîtrisé.',
             'card_top' => '82', 'card_left' => '6'],
        ];
        foreach ($cards as $g => $card) {
            foreach ($card as $key => $value) {
                // sort_order = index du groupe : fixe l'ordre d'affichage des cartes.
                Block::setVal($secId, $key, 'text', $value, $g + 1, $g);
            }
        }
        echo count($cards) . " cartes flottantes créées.\n";
    }

    if ($first) {
        file_put_contents($lock, date('c') . " — hero v4 construit (section #$secId)\n");
        echo "Verrou écrit : storage/hero_v4.lock\n";
    }

    \App\Services\Cache::clear();
    echo "Tout le hero est éditable depuis /admin/pages -> Accueil -> « Hero — visuel et cartes ».\n";
    echo "=== TERMINÉ ===\n";
} catch (\Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}
