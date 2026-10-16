<?php
/**
 * Visuels générés — combler les emplacements d'image restés vides
 *
 * Relevé en production le 2026-10-16 (parcours complet du site, 53 adresses) :
 *   - heros de /insights, /labs et /contact sans visuel (fond composé de repli) ;
 *   - les 3 articles Insights sans image de couverture : vignette « À la une »,
 *     grille, figure de l'article et « Articles similaires » vides ;
 *   - carte d'adresse de /contact : dégradé uni.
 *
 * Sept illustrations aux couleurs du logo (#0868B0 / #003060), composées puis
 * rendues en WebP, versionnées dans public/assets/uploads/visuel-*.webp. Ce
 * sont des illustrations, pas des photos : aucune personne, aucun client, aucun
 * chiffre présenté comme un résultat.
 *
 * Garde-fous :
 *   - drapeau `generated_visuals_v1` : une seule exécution ;
 *   - un visuel n'est posé QUE si le champ est vide — un choix fait en admin
 *     n'est jamais écrasé ;
 *   - chaque fichier est inscrit dans la Bibliothèque Média (une fois) pour
 *     rester sélectionnable et remplaçable depuis /admin/media.
 *
 * Tout reste administrable : /admin/pages -> la page -> Hero (Visuel),
 * /admin/blog -> l'article (Image à la une), /admin/pages -> Contact ->
 * Coordonnées (Fond de la carte).
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

use App\Models\Block;
use App\Models\Media;
use App\Models\Page;
use App\Models\Section;
use App\Models\Setting;
use App\Services\Database;

echo "=== VISUELS GÉNÉRÉS ===\n";

try {
    if (Setting::getVal('generated_visuals_v1')) {
        echo "Déjà appliqué (drapeau generated_visuals_v1). Rien à faire.\n";
        echo "=== TERMINÉ ===\n";
        exit(0);
    }

    $DIR = '/assets/uploads/';
    $HEROS = [
        'insights' => ['visuel-hero-insights.webp', "Illustration : analyses, tendances et guides"],
        'labs'     => ['visuel-hero-labs.webp',     "Illustration : prototype et modules en construction"],
        'contact'  => ['visuel-hero-contact.webp',  "Illustration : carte de l'Afrique centrée sur Abidjan"],
    ];
    $COUVERTURES = [
        'solutions-ia-automatiser-processus-croissance' => 'visuel-insight-ia-automatisation.webp',
        'seo-reseaux-sociaux-ia-visibilite'             => 'visuel-insight-seo-visibilite.webp',
        'transformation-numerique-experience-client'    => 'visuel-insight-experience-client.webp',
    ];
    $CARTE = 'visuel-plan-abidjan.webp';

    // ── 1. BIBLIOTHÈQUE MÉDIA ───────────────────────────────────────────────
    echo "\n[1/4] Bibliothèque Média\n";
    $fichiers = array_merge(array_column($HEROS, 0), array_values($COUVERTURES), [$CARTE]);
    $disponibles = [];
    foreach ($fichiers as $f) {
        $physique = PUBLIC_PATH . $DIR . $f;
        if (!is_file($physique)) { echo "  ! $f absent du disque — ignoré partout.\n"; continue; }
        $disponibles[$f] = true;
        $deja = Database::fetch("SELECT id FROM media WHERE filepath = :p LIMIT 1", ['p' => $DIR . $f]);
        if ($deja) { echo "  $f déjà inscrit (#{$deja['id']}).\n"; continue; }
        $id = Media::add([
            'filename' => $f, 'filepath' => $DIR . $f, 'original_name' => $f,
            'file_size' => filesize($physique), 'mime_type' => 'image/webp',
        ]);
        echo "  $f inscrit (#$id).\n";
    }

    // ── 2. HEROS ────────────────────────────────────────────────────────────
    echo "\n[2/4] Visuels de hero\n";
    foreach ($HEROS as $slug => [$f, $alt]) {
        if (!isset($disponibles[$f])) { continue; }
        $page = Page::findBySlug($slug);
        if (!$page) { echo "  page '$slug' absente — ignorée.\n"; continue; }
        $hero = null;
        foreach (Section::getByPage((int)$page['id']) as $s) {
            if (($s['type'] ?? '') === 'hero_media_cards') { $hero = $s; break; }
        }
        if (!$hero) { echo "  '$slug' : aucun hero_media_cards — ignorée.\n"; continue; }
        $sid = (int)$hero['id'];
        $single = Block::getStructuredContent($sid)['single'] ?? [];
        if (trim((string)($single['image'] ?? '')) !== '') { echo "  '$slug' : visuel déjà choisi — conservé.\n"; continue; }
        Block::setVal($sid, 'image', 'image', $DIR . $f);
        if (trim((string)($single['image_alt'] ?? '')) === '') { Block::setVal($sid, 'image_alt', 'text', $alt); }
        echo "  '$slug' : visuel posé ($f).\n";
    }

    // ── 3. COUVERTURES DES ARTICLES ─────────────────────────────────────────
    echo "\n[3/4] Couvertures des articles\n";
    foreach ($COUVERTURES as $slug => $f) {
        if (!isset($disponibles[$f])) { continue; }
        $post = Database::fetch("SELECT id, featured_image FROM blog_posts WHERE slug = :s LIMIT 1", ['s' => $slug]);
        if (!$post) { echo "  article '$slug' absent — ignoré.\n"; continue; }
        if (trim((string)($post['featured_image'] ?? '')) !== '') { echo "  '$slug' : couverture déjà choisie — conservée.\n"; continue; }
        Database::query("UPDATE blog_posts SET featured_image = :i WHERE id = :id", ['i' => $DIR . $f, 'id' => (int)$post['id']]);
        echo "  '$slug' : couverture posée ($f).\n";
    }

    // ── 4. FOND DE LA CARTE (/contact) ──────────────────────────────────────
    echo "\n[4/4] Fond de la carte\n";
    $contact = Page::findBySlug('contact');
    if (!$contact || !isset($disponibles[$CARTE])) {
        echo "  page /contact ou fichier absent — ignoré.\n";
    } else {
        $n = 0;
        foreach (Section::getByPage((int)$contact['id']) as $s) {
            if (($s['type'] ?? '') !== 'contact_details') { continue; }
            $sid = (int)$s['id'];
            $single = Block::getStructuredContent($sid)['single'] ?? [];
            if (trim((string)($single['map_image'] ?? '')) !== '') { echo "  section #$sid : fond déjà choisi — conservé.\n"; continue; }
            Block::setVal($sid, 'map_image', 'image', $DIR . $CARTE);
            $n++;
            echo "  section #$sid : fond posé.\n";
        }
        if ($n === 0) { echo "  aucune section contact_details à compléter.\n"; }
    }

    Setting::setVal('generated_visuals_v1', '1');
    \App\Services\Cache::clear();
    echo "=== TERMINÉ ===\n";
} catch (\Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    echo "  " . $e->getFile() . ':' . $e->getLine() . "\n";
    exit(1);
}
