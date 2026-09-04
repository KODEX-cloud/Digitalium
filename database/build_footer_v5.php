<?php
/**
 * Build Footer v5 — footer clair, d'après le modèle de référence
 *
 * Le footer passe d'un aplat vert foncé à un fond BLANC avec texte sombre, et
 * gagne un panneau newsletter en aplat de couleur primaire posé en tête.
 *
 * Ce script ne fait que poser les valeurs manquantes :
 *   - `theme_footer_bg` n'est basculé que s'il vaut encore la valeur d'origine
 *     (#1d6363), jamais une couleur choisie depuis /admin/theme ;
 *   - chaque réglage de texte n'est écrit que s'il est absent ou vide.
 *
 * Il est donc rejouable à chaque déploiement sans rien écraser (Règle #2).
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

echo "=== BUILD FOOTER V5 (footer clair) ===\n";

try {
    // ── Fond du footer : vert foncé -> blanc ────────────────────────────────
    // Garde d'idempotence : on ne touche QUE la valeur d'origine.
    $currentBg = Setting::getVal('theme_footer_bg', '#1d6363');
    if (strtolower($currentBg) === '#1d6363') {
        Setting::setVal('theme_footer_bg', '#ffffff');
        echo "theme_footer_bg : #1d6363 -> #ffffff (footer clair)\n";
    } else {
        echo "theme_footer_bg déjà personnalisé ($currentBg) — non modifié.\n";
    }

    // ── Textes du footer : posés uniquement si absents ou vides ─────────────
    $defaults = [
        // Panneau newsletter
        'footer_newsletter_title'         => 'Restez informé des tendances du digital',
        'footer_newsletter_text'          => 'Recevez nos actualités et conseils digitaux chaque mois, directement dans votre boîte mail.',
        'footer_newsletter_placeholder'   => 'Votre adresse email',
        'footer_newsletter_button'        => "S'inscrire",
        'footer_newsletter_note'          => 'Vous pouvez vous désabonner à tout moment. Consultez notre',
        'footer_newsletter_privacy_text'  => 'politique de confidentialité',
        'footer_newsletter_privacy_url'   => '/mentions-legales',
        // Titres de colonnes
        'footer_nav_title'                => 'Liens utiles',
        'footer_services_title'           => 'Services',
        'footer_contact_title'            => 'Contact',
        // Barre du bas
        'footer_sitemap_text'             => 'Plan du site',
        'footer_sitemap_url'              => '/sitemap.xml',
        'footer_backtotop_text'           => 'Remonter',
    ];

    $posed = 0;
    foreach ($defaults as $key => $value) {
        $current = trim((string)Setting::getVal($key, ''));
        if ($current === '') {
            Setting::setVal($key, $value);
            printf("  %-32s (vide) -> %s\n", $key, $value);
            $posed++;
        }
    }
    echo "$posed réglage(s) posé(s), " . (count($defaults) - $posed) . " déjà renseigné(s) et laissé(s) intacts.\n";

    \App\Services\Cache::clear();
    echo "Footer éditable depuis /admin/settings.\n";
    echo "=== TERMINÉ ===\n";
} catch (\Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}

// Relance de déploiement — voir PROJECT_STATE (incident déploiement footer v5).
