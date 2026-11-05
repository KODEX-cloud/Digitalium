<?php
/**
 * Système de jetons — alignement des réglages du thème
 *
 * Demande (2026-11-05) : rendu haut de gamme reposant sur un système global
 * de jetons — grille d'espacement de 8 px, rayons harmonisés, trois niveaux
 * d'ombre, interligne 1,6, texte courant ≥ 16 px.
 *
 * Les jetons vivent dans public/assets/css/index.css. Une partie d'entre eux
 * reste administrable (panneau Thème) et ces réglages en base les écrasent :
 * leurs valeurs actuelles sortaient de la grille (92 px d'espacement, rayons
 * de 9, 10 et 14 px, interligne 1,55, ombre de carte non superposée). On les
 * aligne une fois ; l'admin peut ensuite les modifier librement.
 *
 * Opération unique (drapeau `design_tokens_v1`) : un réglage changé ensuite
 * dans le panneau Thème n'est jamais réécrit. Valeurs précédentes
 * sauvegardées en JSON dans `design_tokens_backup_v1`.
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

echo "=== JETONS DE DESIGN : REGLAGES DU THEME ===\n";

try {
    if (Setting::getVal('design_tokens_v1')) {
        echo "Déjà appliqué (drapeau design_tokens_v1). Rien à faire.\n";
        echo "=== TERMINÉ ===\n";
        exit(0);
    }

    $cibles = [
        // Rythme vertical : 96 px ; les paliers 64 et 40 en découlent (layout.php).
        'theme_space_section'          => '96',
        // Rayons : 8 / 12 / 20, boutons 12 (échelle de index.css).
        'theme_radius_sm'              => '8',
        'theme_radius_md'              => '12',
        'theme_radius_card'            => '20',
        'theme_radius_btn'             => '12',
        'theme_radius_pill'            => '8',
        // Ombre de niveau 1 (cartes au repos) et ombre des boutons, superposées.
        'theme_shadow_card'            => '0 1px 2px rgba(18,32,44,0.04), 0 2px 6px rgba(18,32,44,0.04), 0 8px 24px -8px rgba(18,32,44,0.10)',
        'theme_shadow_btn'             => '0 1px 2px rgba(18,32,44,0.10), 0 6px 16px -6px rgba(18,32,44,0.28)',
        // Lisibilité : 16 px, interligne 1,6, interlettrage resserré des titres.
        'theme_font_body'              => '1',
        'theme_line_height_body'       => '1.6',
        'theme_letter_spacing_heading' => '-0.025',
    ];

    $sauvegarde = [];
    foreach (array_keys($cibles) as $cle) {
        $sauvegarde[$cle] = Setting::getVal($cle, '');
    }
    Setting::setVal('design_tokens_backup_v1', json_encode($sauvegarde, JSON_UNESCAPED_UNICODE));

    foreach ($cibles as $cle => $valeur) {
        $avant = $sauvegarde[$cle];
        Setting::setVal($cle, $valeur);
        printf("  %-30s %-12s -> %s\n", $cle, $avant === '' ? '(absent)' : mb_strimwidth($avant, 0, 12, '…'), mb_strimwidth($valeur, 0, 40, '…'));
    }

    Setting::setVal('design_tokens_v1', '1');
    \App\Services\Cache::clear();
    echo "  Sauvegarde : design_tokens_backup_v1\n";
    echo "=== TERMINÉ ===\n";
} catch (\Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    echo "  " . $e->getFile() . ':' . $e->getLine() . "\n";
    exit(1);
}
