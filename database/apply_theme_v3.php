<?php
/**
 * Apply Theme v3 — "Fintech Vert Profond" — Digitalium Group
 *
 * Applique intégralement la palette et les jetons de design issus du modèle de
 * référence fourni par la direction (vert profond #1D6363 / menthe #E0F1DF /
 * charbon #272727 sur fond blanc cassé #FBFBFB).
 *
 * Auto-exécuté à chaque déploiement (.github/workflows/deploy.yml, étape MIGRATIONS).
 * Protégé par storage/theme_v3.lock : la palette est posée UNE SEULE FOIS, puis
 * les exécutions suivantes ne font rien — afin de ne jamais écraser les couleurs
 * que l'admin aurait ensuite ajustées depuis /admin/theme (Règle #2).
 * Supprimer ce verrou force une réapplication complète.
 */

define('SECURE_ACCESS', true);
define('ROOT_PATH', dirname(__DIR__));
require ROOT_PATH . '/config/config.php';
require ROOT_PATH . '/app/Services/Database.php';

// PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $sep = chr(92);
    $prefix = 'App' . $sep;
    $baseDir = ROOT_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $file = $baseDir . str_replace($sep, DIRECTORY_SEPARATOR, substr($class, $len)) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Models\Setting;

echo "=== APPLY THEME V3 — Fintech Vert Profond ===\n";

$lock = ROOT_PATH . '/storage/theme_v3.lock';
if (file_exists($lock)) {
    echo "Verrou présent (storage/theme_v3.lock) — palette déjà appliquée, aucune modification.\n";
    echo "=== TERMINÉ (no-op) ===\n";
    exit(0);
}

try {
    // Palette extraite pixel par pixel du modèle de référence.
    $theme = [
        // ── Couleurs de marque ──
        'theme_primary'            => '#1d6363',  // vert profond — aplats, panneaux, footer
        'theme_secondary'          => '#346a6d',  // teal moyen — pastilles d'icônes
        'theme_accent'             => '#004d3f',  // vert sombre — accents, textes de badge
        // ── Texte ──
        'theme_text_main'          => '#272727',  // charbon (pas noir pur)
        'theme_text_sub'           => '#3b3f4a',
        'theme_text_muted'         => '#4d4f63',  // ardoise
        // ── Fonds ──
        'theme_bg_base'            => '#fbfbfb',  // blanc cassé (pas blanc pur)
        'theme_bg_alt'             => '#e8f3e9',  // menthe douce
        'theme_bg_card'            => '#ffffff',
        // ── Rayons : le modèle utilise des rectangles arrondis, pas des pilules ──
        'theme_radius_pill'        => '8',
        'theme_radius_card'        => '22',
        'theme_radius_btn'         => '12',
        'theme_radius_md'          => '14',
        'theme_radius_sm'          => '10',
        // ── Ombres : très douces, teintées de bleu-nuit ──
        'theme_shadow_card'        => '0 1px 2px rgba(18,32,44,0.04), 0 10px 30px rgba(18,32,44,0.06)',
        'theme_shadow_btn'         => '0 6px 20px rgba(39,39,39,0.18)',
        // ── Jetons de design introduits par le modèle ──
        'theme_btn_primary_bg'     => '#272727',  // CTA charbon — PAS la couleur primaire
        'theme_btn_primary_text'   => '#ffffff',
        'theme_badge_bg'           => '#e0f1df',  // pastille menthe
        'theme_badge_text'         => '#004d3f',
        'theme_footer_bg'          => '#1d6363',  // footer vert profond
        'theme_surface_dark'       => '#12202c',  // panneaux sombres (héros de page, blog)
    ];

    // Seconde couche : les clés color_* sont injectées APRÈS theme_* dans le <head>
    // et gagnent donc la cascade. Elles doivent être cohérentes, sinon elles
    // écrasent silencieusement la palette (incident constaté sur le thème bleu).
    $colors = [
        'color_primary'    => '#1d6363',
        'color_accent'     => '#004d3f',
        'color_text_main'  => '#272727',
        'color_text_muted' => '#4d4f63',
        'color_bg_base'    => '#fbfbfb',
    ];

    $n = 0;
    foreach (array_merge($theme, $colors) as $key => $value) {
        $before = Setting::getVal($key, '');
        Setting::setVal($key, $value);
        $n++;
        printf("  %-26s %-12s -> %s\n", $key, ($before === '' ? '(absent)' : $before), $value);
    }

    file_put_contents($lock, date('c') . " — theme v3 (fintech vert profond) appliqué : $n clés\n");
    echo "\n$n clés de thème posées. Verrou écrit : storage/theme_v3.lock\n";
    echo "La palette reste modifiable depuis /admin/theme — elle ne sera plus jamais écrasée.\n";

    \App\Services\Cache::clear();
    echo "=== TERMINÉ ===\n";
} catch (\Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}
