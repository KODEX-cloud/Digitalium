<?php
/**
 * Set Brand Theme v2 — Digitalium Group
 * Aligne la couleur de marque (Settings → theme_primary/theme_shadow_btn) sur le
 * bleu du visuel de référence de la Homepage v2, à la place du teal d'origine.
 *
 * Idempotent : ne modifie QUE si la valeur actuelle est encore l'ancien défaut
 * teal (#0d9488) — si un admin a déjà personnalisé la couleur depuis
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

    \App\Services\Cache::clear();
    echo "=== TERMINÉ ===\n";
} catch (\Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}
