<?php
/**
 * Apply Typography v3 — mesures relevées sur le modèle de référence
 *
 * Les valeurs ci-dessous ne sont pas estimées : elles proviennent d'une analyse
 * pixel du visuel fourni (hauteurs de capitales, interlignes, hauteurs de boutons),
 * ramenées en pixels CSS via l'échelle de la maquette (2.008 px image = 1 px CSS).
 *
 * Auto-exécuté à chaque déploiement. Protégé par storage/typography_v3.lock :
 * appliqué UNE SEULE FOIS, puis jamais réécrit — les réglages faits ensuite depuis
 * /admin/theme sont préservés (Règle #2).
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
use App\Models\Page;
use App\Models\Section;
use App\Models\Block;
use App\Services\Database;

echo "=== APPLY TYPOGRAPHY V3 (mesures du modèle) ===\n";

$lock = ROOT_PATH . '/storage/typography_v3.lock';
if (file_exists($lock)) {
    echo "Verrou présent — typographie déjà appliquée, aucune modification.\n";
    echo "=== TERMINÉ (no-op) ===\n";
    exit(0);
}

try {
    $typo = [
        // H1 : hauteur de capitale 49.8 px CSS -> corps ~69 px = 4.3 rem
        'theme_font_h1'                => '4.3',
        // H2 : capitale ~35 px CSS -> corps ~48 px = 3.0 rem
        'theme_font_h2'                => '3',
        // Titres de carte : 20 px = 1.25 rem
        'theme_font_h3'                => '1.25',
        'theme_font_body'              => '1',
        'theme_font_weight_heading'    => '800',
        'theme_font_weight_body'       => '400',
        // Interligne du corps : 1.78 était très au-dessus du modèle (~1.40)
        'theme_line_height_body'       => '1.55',
        'theme_letter_spacing_heading' => '-0.02',
        // Rayons relevés : boutons ~14 px, badges ~9 px, cartes ~20 px
        'theme_radius_btn'             => '14',
        'theme_radius_pill'            => '9',
        'theme_radius_card'            => '20',
    ];

    foreach ($typo as $key => $value) {
        $before = Setting::getVal($key, '(absent)');
        Setting::setVal($key, $value);
        printf("  %-30s %-10s -> %s\n", $key, $before, $value);
    }

    // ── Carte de service mise en avant + libellé de son bouton ──────────────
    $home = Page::findBySlug('home');
    if ($home) {
        $sections = Section::getByPage((int)$home['id']);
        foreach ($sections as $sec) {
            if (($sec['type'] ?? '') !== 'services_grid_v2') { continue; }
            $secId = (int)$sec['id'];
            $content = Block::getStructuredContent($secId);

            if (empty($content['single']['card_link_text'])) {
                Block::setVal($secId, 'card_link_text', 'text', 'En savoir plus');
                echo "  services_grid_v2.card_link_text -> 'En savoir plus'\n";
            }

            $groups = $content['groups'] ?? [];
            $already = false;
            foreach ($groups as $g) {
                if (!empty($g['svc_featured']) && $g['svc_featured'] !== '0') { $already = true; break; }
            }
            if (!$already && isset($groups[1]['_group_id'])) {
                Block::setVal($secId, 'svc_featured', 'text', '1', $groups[1]['_group_id'], 0);
                echo "  services_grid_v2 : carte 2 mise en avant (modifiable en admin)\n";
            } elseif ($already) {
                echo "  services_grid_v2 : une carte est déjà mise en avant — inchangé\n";
            }
            break;
        }
    }

    file_put_contents($lock, date('c') . " — typographie v3 appliquée\n");
    echo "\nVerrou écrit : storage/typography_v3.lock\n";
    echo "Tout reste modifiable depuis /admin/theme et /admin/pages.\n";

    \App\Services\Cache::clear();
    echo "=== TERMINÉ ===\n";
} catch (\Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}
