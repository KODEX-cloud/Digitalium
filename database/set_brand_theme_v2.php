<?php
/**
 * Set Brand Theme v2 — Digitalium Group
 * Aligne la couleur de marque sur le bleu du visuel de référence de la
 * Homepage v2, à la place du teal (theme_primary) et de l'indigo
 * (color_primary/color_accent/color_bg_base — panneau admin "Couleurs & Thème",
 * app/Views/admin/settings.php) d'origine. Ce second bloc étant injecté en
 * dernier dans <head> (app/Views/frontend/layout.php), il gagne la cascade
 * CSS sur theme_primary si non aligné — d'où ce fix combiné.
 *
 * Idempotent : ne modifie QUE si la valeur actuelle est encore l'ancien défaut
 * (teal ou indigo) — si un admin a déjà personnalisé la couleur depuis
 * /admin/settings, ce script ne l'écrase pas.
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

use App\Models\Setting;

echo "=== SET BRAND THEME V2 (bleu de référence) ===\n";

try {
    $oldPrimary = Setting::getVal('theme_primary', '#0d9488');
    if ($oldPrimary === '#0d9488') {
        Setting::setVal('theme_primary', '#2563eb');
        echo "theme_primary : #0d9488 -> #2563eb\n";
    } else {
        echo "theme_primary déjà personnalisé ({$oldPrimary}) — non modifié.\n";
    }

    $oldShadow = Setting::getVal('theme_shadow_btn', '0 4px 18px rgba(13,148,136,0.28)');
    if ($oldShadow === '0 4px 18px rgba(13,148,136,0.28)') {
        Setting::setVal('theme_shadow_btn', '0 4px 18px rgba(37,99,235,0.28)');
        echo "theme_shadow_btn : ombre teal -> ombre bleue\n";
    } else {
        echo "theme_shadow_btn déjà personnalisé — non modifié.\n";
    }

    // Panneau admin "Couleurs & Thème" (settings.color_*) — injecté APRÈS le
    // bloc theme_primary ci-dessus dans layout.php, donc prioritaire dans la
    // cascade CSS s'il reste sur ses défauts indigo d'origine.
    $colorFixes = [
        'color_primary' => ['#4f46e5', '#2563eb'],
        'color_accent'  => ['#818cf8', '#f59e0b'],
        'color_bg_base' => ['#f0f4ff', '#ffffff'],
    ];
    foreach ($colorFixes as $key => $pair) {
        [$old, $new] = $pair;
        $current = Setting::getVal($key, $old);
        if ($current === $old) {
            Setting::setVal($key, $new);
            echo "{$key} : {$old} -> {$new}\n";
        } else {
            echo "{$key} déjà personnalisé ({$current}) — non modifié.\n";
        }
    }

    \App\Services\Cache::clear();
    echo "=== TERMINÉ ===\n";
} catch (\Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}
