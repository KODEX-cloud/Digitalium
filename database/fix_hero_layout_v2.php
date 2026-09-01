<?php
/**
 * Fix Hero Layout v2 — Digitalium Group
 * Corrige la mise en page du hero "hero_corporate" de la Homepage v2 pour la
 * rendre fidèle au visuel de référence : titre empilé sur plusieurs lignes,
 * alignement à gauche (au lieu du centrage par défaut de hero.php quand
 * hero_text_alignment est vide) — et remplace les images d'illustration
 * (hero, "8+ années d'expérience", 3 cartes Réalisations) qui pointaient vers
 * des visuels IA génériques sans rapport avec le sujet (l'une d'elles était
 * même la capture d'écran d'un site tiers, ivoirekita.com, présentée à tort
 * comme un projet Digitalium) par des photos réelles et thématiquement
 * cohérentes (licence Pexels, usage commercial libre).
 *
 * build_home_v2.php ne peut pas rejouer ce correctif car il est verrouillé par
 * storage/homepage_v2.lock une fois la page construite (pour ne jamais écraser
 * les modifications d'un admin) — ce script séparé applique donc le correctif
 * une seule fois, sans toucher au lock.
 *
 * Idempotent : ne modifie QUE si les valeurs sont encore celles du premier
 * build — si un admin a déjà personnalisé le hero ou les images depuis
 * /admin/pages, ce script ne les écrase pas.
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
use App\Models\Section;
use App\Models\Block;
use App\Services\Database;

echo "=== FIX HERO LAYOUT V2 (alignement gauche + titre empilé + images) ===\n";

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

    $oldHeroImage = '/assets/images/digitalium-hero-team.png';
    if (($page['hero_image'] ?? '') === $oldHeroImage) {
        $data['hero_image'] = '/assets/uploads/hero-pro-dashboard-1893001.jpg';
        $changed = true;
        echo "hero_image : photo générique (étudiants) -> photo professionnelle (homme, tableau de bord).\n";
    } else {
        echo "hero_image déjà personnalisé — non modifié.\n";
    }

    if ($changed) {
        Page::updatePage((int)$page['id'], $data);
        echo "Page 'home' mise à jour.\n";
    } else {
        echo "Aucun changement nécessaire sur le hero.\n";
    }

    // ─── Sections : about_visual (image "8+ années") + projects_showcase ─────
    $sections = Section::getByPage((int)$page['id']);
    $sectionByType = [];
    foreach ($sections as $s) {
        if (!isset($sectionByType[$s['type']])) {
            $sectionByType[$s['type']] = $s;
        }
    }

    if (isset($sectionByType['about_visual'])) {
        $secId = (int)$sectionByType['about_visual']['id'];
        $content = Block::getStructuredContent($secId);
        $oldAboutImage = '/assets/uploads/digitalium-pic-3-1780069686.webp';
        if (($content['single']['image'] ?? '') === $oldAboutImage) {
            Block::setVal($secId, 'image', 'image', '/assets/uploads/about-team-meeting-1893001.jpg');
            echo "about_visual.image : homme + hologramme ville -> équipe réunie autour d'un ordinateur portable.\n";
        } else {
            echo "about_visual.image déjà personnalisé — non modifié.\n";
        }
    }

    if (isset($sectionByType['projects_showcase'])) {
        $secId = (int)$sectionByType['projects_showcase']['id'];
        $content = Block::getStructuredContent($secId);
        $imageFixes = [
            '/assets/uploads/website-design-featuring-user-interface-elements-displaying-the-latest-trends-in-web-design-interfa-1780069994.webp' => '/assets/uploads/proj-finance-dashboard-1893001.jpg',
            '/assets/uploads/digitalium-pic-8-1780069994.webp' => '/assets/uploads/proj-logistics-map-1893001.jpg',
            '/assets/uploads/ivoire-kita-1780071304.webp' => '/assets/uploads/proj-health-tablet-1893001.jpg',
        ];
        foreach ($content['groups'] as $group) {
            $groupId = (int)$group['_group_id'];
            $current = $group['proj_image'] ?? '';
            if (isset($imageFixes[$current])) {
                Block::setVal($secId, 'proj_image', 'image', $imageFixes[$current], $groupId, 0);
                echo "projects_showcase (groupe {$groupId}) : image incorrecte -> photo cohérente ({$group['proj_category']}).\n";
            }
        }
    }

    // ─── Sections partagées avec d'autres pages : bascule vers un type dédié ──
    // "services_grid" (carte bannière/tag/liste à puces) et "process" (grille de
    // cartes) sont réutilisés sur d'autres pages du site — les muter directement
    // régresserait leur rendu ailleurs. On bascule donc uniquement les sections
    // de CETTE page vers les nouveaux types dédiés services_grid_v2 /
    // process_timeline, qui lisent les mêmes clés de blocs (aucune perte de
    // contenu, juste un changement de gabarit visuel).
    $typeSwaps = [
        'services_grid' => 'services_grid_v2',
        'process'       => 'process_timeline',
    ];
    foreach ($typeSwaps as $oldType => $newType) {
        if (isset($sectionByType[$oldType])) {
            $secId = (int)$sectionByType[$oldType]['id'];
            Database::query("UPDATE sections SET type = :new_type WHERE id = :id", [
                'new_type' => $newType,
                'id' => $secId,
            ]);
            echo "Section #{$secId} : type {$oldType} -> {$newType} (gabarit fidèle à la référence).\n";
        } else {
            echo "Aucune section de type '{$oldType}' trouvée sur la page 'home' — non modifié.\n";
        }
    }

    \App\Services\Cache::clear();
    echo "=== TERMINÉ ===\n";
} catch (\Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}
