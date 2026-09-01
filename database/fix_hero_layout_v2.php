<?php
/**
 * Fix Hero Layout v2 — Digitalium Group
 * Corrige la mise en page du hero "hero_corporate" de la Homepage v2 pour la
 * rendre fidèle au visuel de référence : titre empilé sur plusieurs lignes,
 * alignement à gauche (au lieu du centrage par défaut de hero.php quand
 * hero_text_alignment est vide).
 *
 * build_home_v2.php ne peut pas rejouer ce correctif car il est verrouillé par
 * storage/homepage_v2.lock une fois la page construite (pour ne jamais écraser
 * les modifications d'un admin) — ce script séparé applique donc le correctif
 * une seule fois, sans toucher au lock.
 *
 * Idempotent : ne modifie QUE si les valeurs sont encore celles du premier
 * build (titre inline sans <br>, alignement vide/centré) — si un admin a déjà
 * personnalisé le hero depuis /admin/pages, ce script ne l'écrase pas.
 * Auto-exécuté au déploiement (voir .github/workflows/deploy.yml).
 */

define('SECURE_ACCESS', true);
define('ROOT_PATH', dirname(__DIR__));
require ROOT_PATH . '/config/config.php';
require ROOT_PATH . '/app/Services/Database.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = ROOT_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, $len)) . '.php';
    if (file_exists($file)) require_once $file;
});

use App\Models\Page;

echo "=== FIX HERO LAYOUT V2 (alignement gauche + titre empilé) ===\n";

try {
    $page = Page::findBySlug('home');
    if (!$page || (($page['hero_variant'] ?? '') !== 'hero_corporate')) {
        echo "Page 'home' introuvable ou hero_variant différent de hero_corporate — script ignoré.\n";
        exit(0);
    }

    $data = $page;
    $changed = false;

    $oldTitle = 'Digitaliser. Automatiser. <span style="color:var(--primary);">Faire avancer votre entreprise.</span>';
    if (($page['hero_title'] ?? '') === $oldTitle) {
        $data['hero_title'] = 'Digitaliser.<br>Automatiser.<br><span style="color:var(--primary);">Faire avancer votre entreprise.</span>';
        $changed = true;
        echo "hero_title : titre inline -> titre empilé (3 lignes).\n";
    } else {
        echo "hero_title déjà personnalisé — non modifié.\n";
    }

    $currentAlignment = $page['hero_text_alignment'] ?? '';
    if ($currentAlignment === '' || $currentAlignment === 'center' || $currentAlignment === 'centre') {
        $data['hero_text_alignment'] = 'left';
        $changed = true;
        echo "hero_text_alignment : {$currentAlignment} -> left.\n";
    } else {
        echo "hero_text_alignment déjà personnalisé ({$currentAlignment}) — non modifié.\n";
    }

    if ($changed) {
        Page::updatePage((int)$page['id'], $data);
        \App\Services\Cache::clear();
        echo "Page 'home' mise à jour.\n";
    } else {
        echo "Aucun changement nécessaire.\n";
    }

    echo "=== TERMINÉ ===\n";
} catch (\Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}
