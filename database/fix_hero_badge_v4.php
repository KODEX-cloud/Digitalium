<?php
/**
 * Fix Hero Badge v4 — pastille du hero d'accueil
 *
 * `build_hero_v4.php` reprend le contenu du hero de page. Le champ `hero_badge`
 * étant vide en base, aucun bloc `badge` n'a été créé et la pastille du modèle
 * ne s'affichait pas.
 *
 * Ce script est volontairement AUTONOME : `fix_hero_layout_v2.php` sort
 * prématurément quand `hero_variant` n'est plus `hero_corporate` (ligne 49), ce
 * qui empêchait le correctif d'y être porté. Aucune garde ici, hors le verrou.
 *
 * Protégé par storage/hero_badge_v4.lock : posé une seule fois, puis no-op —
 * si l'admin vide la pastille, elle ne réapparaîtra pas au déploiement suivant.
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

echo "=== FIX HERO BADGE V4 ===\n";

$lock = ROOT_PATH . '/storage/hero_badge_v4.lock';
if (file_exists($lock)) {
    echo "Verrou présent — pastille déjà traitée, aucune modification.\n";
    echo "=== TERMINÉ (no-op) ===\n";
    exit(0);
}

try {
    $home = Page::findBySlug('home');
    if (!$home) {
        echo "Page 'home' introuvable — abandon.\n";
        exit(0);
    }

    $secId = null;
    foreach (Section::getByPage((int)$home['id']) as $s) {
        if (($s['type'] ?? '') === 'hero_media_cards') { $secId = (int)$s['id']; break; }
    }

    if ($secId === null) {
        echo "Section hero_media_cards absente — rien à faire (verrou non écrit).\n";
        echo "=== TERMINÉ ===\n";
        exit(0);
    }

    echo "Section hero_media_cards trouvée : #$secId\n";
    $content = Block::getStructuredContent($secId);
    $current = $content['single']['badge'] ?? '';

    if ($current === '') {
        Block::setVal($secId, 'badge', 'text', 'Transformation digitale');
        echo "badge : (vide) -> 'Transformation digitale'\n";
    } else {
        echo "badge déjà renseigné ('$current') — non modifié.\n";
    }

    file_put_contents($lock, date('c') . " — badge hero v4 traité (section #$secId)\n");
    echo "Verrou écrit : storage/hero_badge_v4.lock\n";

    \App\Services\Cache::clear();
    echo "=== TERMINÉ ===\n";
} catch (\Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}
