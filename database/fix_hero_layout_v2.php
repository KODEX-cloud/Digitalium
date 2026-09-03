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
use App\Models\Setting;
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

    // ─── Règle #2 (zéro hardcode) : textes autrefois écrits en dur dans les
    // gabarits, désormais lus depuis les blocs CMS — on les crée s'ils manquent
    // pour que l'affichage reste identique tout en devenant administrable.
    $missingBlocks = [
        'team'               => ['tag' => 'Notre équipe'],
        'projects_showcase'  => ['result_label' => 'Résultat :'],
    ];
    foreach ($missingBlocks as $type => $keys) {
        if (!isset($sectionByType[$type])) {
            continue;
        }
        $secId = (int)$sectionByType[$type]['id'];
        $content = Block::getStructuredContent($secId);
        foreach ($keys as $key => $value) {
            if (($content['single'][$key] ?? '') === '') {
                Block::setVal($secId, $key, 'text', $value);
                echo "{$type}.{$key} : créé en base (\"{$value}\") — était codé en dur dans le gabarit.\n";
            } else {
                echo "{$type}.{$key} déjà renseigné — non modifié.\n";
            }
        }
    }

    // ─── stats_intro : séparation valeur / libellé / description ─────────────
    // Le gabarit fidèle au modèle affiche trois niveaux par carte (nombre bleu,
    // libellé en gras, description grise). L'ancien seed ne portait que deux
    // champs — on répartit le contenu existant sur le nouveau champ stat_label.
    if (isset($sectionByType['stats_intro'])) {
        $secId = (int)$sectionByType['stats_intro']['id'];
        $content = Block::getStructuredContent($secId);
        // [valeur attendue en base, nouvelle valeur, nouveau libellé, nouvelle description]
        $statSplit = [
            '100+'                 => ['100+', 'Clients accompagnés', "dans divers secteurs d'activité"],
            '95%'                  => ['95%',  'Taux de satisfaction', 'grâce à notre engagement et à la qualité de nos services'],
            'Support réactif'      => ['',     'Support réactif',      'Une équipe disponible et réactive pour vous accompagner au quotidien'],
            'Solutions sur mesure' => ['',     'Solutions sur mesure', 'Des solutions adaptées à vos besoins et à vos objectifs business'],
        ];
        foreach ($content['groups'] as $group) {
            $groupId = (int)$group['_group_id'];
            if (($group['stat_label'] ?? '') !== '') {
                continue; // déjà migré ou personnalisé en admin
            }
            $current = $group['stat_value'] ?? '';
            if (!isset($statSplit[$current])) {
                continue;
            }
            [$newValue, $newLabel, $newDesc] = $statSplit[$current];
            Block::setVal($secId, 'stat_value', 'text', $newValue, $groupId, 1);
            Block::setVal($secId, 'stat_label', 'text', $newLabel, $groupId, 2);
            Block::setVal($secId, 'stat_desc', 'textarea', $newDesc, $groupId, 3);
            echo "stats_intro (groupe {$groupId}) : \"{$current}\" réparti en valeur/libellé/description.\n";
        }
    }

    // ─── Footer : titres de colonnes et bloc Newsletter (Règle #2) ───────────
    // Ces libellés étaient écrits en dur dans layout.php — ils deviennent des
    // réglages modifiables depuis /admin/settings.
    $footerSettings = [
        'footer_nav_title'              => 'Liens utiles',
        'footer_services_title'         => 'Services',
        'footer_contact_title'          => 'Contact',
        'footer_newsletter_title'       => 'Newsletter',
        'footer_newsletter_text'        => 'Recevez nos actualités et conseils digitaux chaque mois.',
        'footer_newsletter_placeholder' => 'Votre email',
        'footer_backtotop_text'         => 'Remonter',
    ];
    foreach ($footerSettings as $key => $value) {
        if (empty(Setting::getVal($key, ''))) {
            Setting::setVal($key, $value);
            echo "{$key} : créé (\"{$value}\") — était codé en dur dans layout.php.\n";
        } else {
            echo "{$key} déjà renseigné — non modifié.\n";
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
        if (!isset($sectionByType[$oldType])) {
            echo "Aucune section de type '{$oldType}' trouvée sur la page 'home' — non modifié.\n";
            continue;
        }
        $secId = (int)$sectionByType[$oldType]['id'];
        if (isset($sectionByType[$newType])) {
            // Une section du nouveau type existe déjà : la renommer créerait un
            // doublon affiché deux fois sur la page. On désactive l'ancienne.
            Database::query("UPDATE sections SET status = 'inactive' WHERE id = :id", ['id' => $secId]);
            echo "Section #{$secId} [{$oldType}] désactivée : une section '{$newType}' existe déjà (pas de doublon).\n";
        } else {
            Database::query("UPDATE sections SET type = :new_type WHERE id = :id", [
                'new_type' => $newType,
                'id' => $secId,
            ]);
            echo "Section #{$secId} : type {$oldType} -> {$newType} (gabarit fidèle à la référence).\n";
        }
    }

    // ─── Dédoublonnage : une seule section active par type sur la page ────────
    // Filet de sécurité auto-réparateur : si un enchaînement de scripts a laissé
    // deux sections actives du même type (la page affichait alors deux fois les
    // blocs "Nos services" et "Notre approche en 6 étapes"), on ne garde que
    // celle qui porte le plus de blocs de contenu — les autres sont désactivées,
    // jamais supprimées (Règle #4), donc réactivables depuis /admin/pages.
    $sections = Section::getByPage((int)$page['id']);
    $activeByType = [];
    foreach ($sections as $s) {
        if (($s['status'] ?? 'active') !== 'active') {
            continue;
        }
        $activeByType[$s['type']][] = $s;
    }
    foreach ($activeByType as $type => $list) {
        if (count($list) < 2) {
            continue;
        }
        // Trier : le plus de blocs d'abord, puis le sort_order le plus bas
        usort($list, function ($a, $b) {
            $ca = count(Block::getBySection((int)$a['id']));
            $cb = count(Block::getBySection((int)$b['id']));
            if ($ca !== $cb) {
                return $cb <=> $ca;
            }
            return ((int)$a['sort_order']) <=> ((int)$b['sort_order']);
        });
        $keep = array_shift($list);
        echo "Doublons [{$type}] : section #{$keep['id']} conservée (" . count(Block::getBySection((int)$keep['id'])) . " blocs).\n";
        foreach ($list as $dup) {
            Database::query("UPDATE sections SET status = 'inactive' WHERE id = :id", ['id' => (int)$dup['id']]);
            echo "  -> section #{$dup['id']} [{$type}] désactivée (doublon affiché en double sur la page).\n";
        }
    }

    // ─── Footer (site entier) : texte vide car settings.footer_slogan /
    // footer_copyright existent en base avec une valeur '' (chaîne vide), ce qui
    // empêchait le texte par défaut de layout.php de s'afficher (?? ne couvre
    // pas les chaînes vides, seulement null) — le footer entier de la Homepage
    // v2 apparaissait donc sans description ni copyright.
    if (empty(Setting::getVal('footer_slogan', ''))) {
        Setting::setVal('footer_slogan', "Nous accompagnons les entreprises et organisations en Afrique dans leur transformation digitale à des solutions innovantes, fiables et durables.");
        echo "footer_slogan : vide -> texte de présentation renseigné.\n";
    } else {
        echo "footer_slogan déjà personnalisé — non modifié.\n";
    }
    if (empty(Setting::getVal('footer_copyright', ''))) {
        Setting::setVal('footer_copyright', '© ' . date('Y') . ' Digitalium Group. Tous droits réservés.');
        echo "footer_copyright : vide -> texte par défaut renseigné.\n";
    } else {
        echo "footer_copyright déjà personnalisé — non modifié.\n";
    }


    // ─── Rythme vertical — 130px produisait 260px de vide entre chaque section ──
    // Idempotent : on ne corrige QUE la valeur d'origine, jamais une valeur
    // que l'admin aurait lui-même choisie depuis /admin/theme.
    if (Setting::getVal('theme_space_section', '130') === '130') {
        Setting::setVal('theme_space_section', '92');
        echo "theme_space_section : 130 -> 92 (rythme vertical resserré).\n";
    } else {
        echo "theme_space_section déjà personnalisé — non modifié.\n";
    }

    \App\Services\Cache::clear();
    echo "=== TERMINÉ ===\n";
} catch (\Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}
