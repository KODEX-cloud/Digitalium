<?php
/**
 * Build Hero v4 — Remplacement du hero de la page d'accueil
 *
 * Retire le hero de page (pages.hero_status = 0) et le remplace par une section
 * `hero_media_cards` placée en tête, reproduisant le modèle de référence fourni
 * par la direction (visuel + cartes flottantes).
 *
 * Le CONTENU existant est repris tel quel depuis les champs hero_* de la page :
 * badge, titre, chapô, deux CTA et image. Rien n'est perdu, rien n'est réinventé.
 * Le titre est scindé en deux : la partie soulignée par un <span> devient
 * `title_accent`, affichée en graisse légère et en couleur d'accent comme dans
 * le modèle.
 *
 * Auto-exécuté à chaque déploiement (.github/workflows/deploy.yml).
 * Protégé par storage/hero_v4.lock : construit UNE SEULE FOIS, puis no-op —
 * afin de ne jamais écraser ce que l'admin aura modifié ensuite (Règle #2).
 * Supprimer ce verrou force une reconstruction complète.
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

echo "=== BUILD HERO V4 (hero media cards) ===\n";

$lock = ROOT_PATH . '/storage/hero_v4.lock';
if (file_exists($lock)) {
    echo "Verrou présent (storage/hero_v4.lock) — hero déjà construit, aucune modification.\n";
    echo "=== TERMINÉ (no-op) ===\n";
    exit(0);
}

try {
    $home = Page::findBySlug('home');
    if (!$home) {
        echo "Page 'home' introuvable — abandon.\n";
        exit(0);
    }
    $pageId = (int)$home['id'];

    // ── 1. Récupération du contenu du hero actuel ───────────────────────────
    $rawTitle = (string)($home['hero_title'] ?? '');
    $accent   = '';

    // La partie mise en couleur (<span>) devient l'accent en graisse légère.
    if (preg_match('/<span[^>]*>(.*?)<\/span>/is', $rawTitle, $m)) {
        $accent   = trim(strip_tags($m[1]));
        $rawTitle = str_replace($m[0], '', $rawTitle);
    }
    $titleMain = trim(strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $rawTitle)));
    $titleMain = trim(preg_replace('/\n{2,}/', "\n", $titleMain));

    echo "Titre repris      : " . str_replace("\n", ' / ', $titleMain) . "\n";
    echo "Accent repris     : " . ($accent !== '' ? $accent : '(aucun)') . "\n";

    $badge  = trim((string)($home['hero_badge'] ?? ''));
    $lead   = trim(strip_tags((string)($home['hero_subtitle'] ?? '')));
    $cta1T  = trim((string)($home['hero_cta1_text'] ?? ''));
    $cta1U  = trim((string)($home['hero_cta1_url'] ?? ''));
    $cta2T  = trim((string)($home['hero_cta2_text'] ?? ''));
    $cta2U  = trim((string)($home['hero_cta2_url'] ?? ''));
    $image  = trim((string)($home['hero_image'] ?? ''));

    // ── 2. Désactivation du hero de page (le « supprimer » proprement) ──────
    Database::query("UPDATE pages SET hero_status = 0 WHERE id = :id", ['id' => $pageId]);
    echo "pages.hero_status : 1 -> 0 (hero de page retiré)\n";

    // ── 3. Section hero_media_cards en tête de page ─────────────────────────
    $sections = Section::getByPage($pageId);
    $existing = null;
    foreach ($sections as $s) {
        if (($s['type'] ?? '') === 'hero_media_cards') { $existing = $s; break; }
    }

    if ($existing) {
        $secId = (int)$existing['id'];
        echo "Section hero_media_cards déjà présente (#$secId) — réutilisée.\n";
    } else {
        // On décale les sections existantes pour libérer la première position.
        Database::query("UPDATE sections SET sort_order = sort_order + 1 WHERE page_id = :id", ['id' => $pageId]);
        $secId = (int)Section::addSection($pageId, 'Hero — visuel et cartes', 'hero_media_cards', 0);
        echo "Section hero_media_cards créée (#$secId) en position 0.\n";
    }

    // ── 4. Blocs « single » — contenu repris du hero existant ───────────────
    $singles = [
        ['badge',        'text',     $badge],
        ['title',        'textarea', $titleMain],
        ['title_accent', 'text',     $accent],
        ['text',         'textarea', $lead],
        ['cta1_text',    'text',     $cta1T],
        ['cta1_url',     'link',     $cta1U],
        ['cta1_icon',    'text',     'arrow-right'],
        ['cta2_text',    'text',     $cta2T],
        ['cta2_url',     'link',     $cta2U],
        ['cta2_icon',    'text',     'play'],
        ['image',        'image',    $image],
        ['image_alt',    'text',     str_replace("\n", ' ', $titleMain)],
        ['decor',        'text',     '1'],
    ];
    $n = 0;
    foreach ($singles as [$key, $type, $value]) {
        if ($value === '') { continue; }
        Block::setVal($secId, $key, $type, $value);
        $n++;
    }
    echo "$n blocs 'single' posés.\n";

    // ── 5. Cartes flottantes ────────────────────────────────────────────────
    // Les deux premières reprennent des chiffres DÉJÀ affichés sur le site
    // (section stats_intro) — aucune nouvelle affirmation n'est inventée.
    // Toutes sont modifiables depuis /admin/pages.
    $cards = [
        [
            'card_icon' => 'users', 'card_label' => 'Clients accompagnés',
            'card_value' => '100+', 'card_badge' => 'Actif',
            'card_top' => '4', 'card_left' => '46',
        ],
        [
            'card_icon' => 'gauge', 'card_label' => 'Taux de satisfaction',
            'card_value' => '95', 'card_unit' => '%', 'card_progress' => '95',
            'card_top' => '32', 'card_left' => '30',
        ],
        [
            'card_icon' => 'video', 'card_label' => 'Premier échange',
            'card_title' => 'Audit offert', 'card_meta' => '30 min • en visioconférence',
            'card_top' => '58', 'card_left' => '18',
        ],
        [
            'card_icon' => 'shield-check',
            'card_title' => 'Vos données. Vos règles.', 'card_meta' => 'Sécurisé, hébergé, maîtrisé.',
            'card_top' => '82', 'card_left' => '6',
        ],
    ];

    $content = Block::getStructuredContent($secId);
    if (!empty($content['groups'])) {
        echo count($content['groups']) . " carte(s) déjà présente(s) — non modifiées.\n";
    } else {
        foreach ($cards as $g => $card) {
            foreach ($card as $key => $value) {
                // sort_order = index du groupe : garantit l'ordre d'affichage des cartes.
                Block::setVal($secId, $key, 'text', $value, $g + 1, $g);
            }
        }
        echo count($cards) . " cartes flottantes créées.\n";
    }

    file_put_contents($lock, date('c') . " — hero v4 construit (section #$secId)\n");
    echo "\nVerrou écrit : storage/hero_v4.lock\n";
    echo "Tout le hero est éditable depuis /admin/pages -> Accueil -> section « Hero — visuel et cartes ».\n";

    \App\Services\Cache::clear();
    echo "=== TERMINÉ ===\n";
} catch (\Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}
