<?php
/**
 * Couleurs de texte : du gris au noir
 *
 * Demande utilisateur (2026-10-16) : « certaines écritures sont de couleur grise,
 * mets-les en noir ». Les trois couleurs de texte du thème étaient un charbon
 * (#272727) et deux ardoises (#3b3f4a, #4d4f63) ; le texte atténué — labels,
 * descriptions, métadonnées, 162 usages — se lisait gris.
 *
 * Nouvelles valeurs, toutes perçues comme noires, avec une hiérarchie à peine
 * marquée : principal #111111 · secondaire #1a1a1a · atténué #262626.
 *
 * Les clés color_* sont injectées APRÈS theme_* dans le <head> et gagnent la
 * cascade : elles sont réglées ensemble, sinon elles rétabliraient le gris.
 *
 * Opération unique (drapeau `text_colors_black_v1`) : une couleur choisie
 * ensuite dans /admin/settings (Couleurs & Thème) n'est jamais réécrite.
 * Valeurs précédentes sauvegardées en JSON dans `text_colors_backup_v1`.
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

use App\Models\Setting;

echo "=== COULEURS DE TEXTE : NOIR ===\n";

try {
    if (Setting::getVal('text_colors_black_v1')) {
        echo "Déjà appliqué (drapeau text_colors_black_v1). Rien à faire.\n";
        echo "=== TERMINÉ ===\n";
        exit(0);
    }

    $cibles = [
        'theme_text_main'  => '#111111',
        'theme_text_sub'   => '#1a1a1a',
        'theme_text_muted' => '#262626',
        'color_text_main'  => '#111111',
        'color_text_muted' => '#262626',
    ];

    $sauvegarde = [];
    foreach (array_keys($cibles) as $cle) {
        $sauvegarde[$cle] = Setting::getVal($cle, '');
    }
    Setting::setVal('text_colors_backup_v1', json_encode($sauvegarde, JSON_UNESCAPED_UNICODE));

    foreach ($cibles as $cle => $valeur) {
        $avant = $sauvegarde[$cle];
        Setting::setVal($cle, $valeur);
        printf("  %-18s %-10s -> %s\n", $cle, $avant === '' ? '(absent)' : $avant, $valeur);
    }

    Setting::setVal('text_colors_black_v1', '1');
    \App\Services\Cache::clear();
    echo "  Sauvegarde : text_colors_backup_v1\n";
    echo "=== TERMINÉ ===\n";
} catch (\Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    echo "  " . $e->getFile() . ':' . $e->getLine() . "\n";
    exit(1);
}
