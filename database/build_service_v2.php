<?php
/**
 * Build Service v2 — Reconstruction de la page /service
 *
 * Aligne la page Services sur le langage visuel de la page d'accueil :
 * hero « visuel + cartes flottantes » puis sections aux gabarits v2.
 *
 * ── Principe : ÉCHANGE DE TYPE, pas réécriture ─────────────────────────────
 * Les nouveaux gabarits lisent exactement les mêmes clés de blocs que les
 * anciens. Changer `sections.type` suffit donc à basculer le rendu SANS TOUCHER
 * AU CONTENU. Rien n'est supprimé, rien n'est réécrit :
 *
 *   services_grid     -> services_grid_v2        clés identiques (+ svc_tag)
 *   process_strip     -> process_timeline        proc_num/icon/title/desc
 *   testimonials_grid -> testimonials_carousel   client_company -> client_role
 *   cta                                          inchangé (déjà au nouveau style)
 *
 * ── Script RÉCONCILIATEUR (leçon de BUG-HERO-01) ───────────────────────────
 * `build_home_v2.php` avait désactivé la section hero de l'accueil parce qu'un
 * script one-shot verrouillé ne la réalignait jamais. Ici, l'existence, le
 * statut et la position de la section hero sont réalignés à CHAQUE déploiement.
 * Le CONTENU, lui, n'est semé que si la section est vide — les modifications
 * faites depuis /admin/pages ne peuvent pas être écrasées (Règle #2).
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

use App\Models\Page;
use App\Models\Section;
use App\Models\Block;
use App\Services\Database;

echo "=== BUILD SERVICE V2 ===\n";

/**
 * Scinde un titre en deux pour le rendu bicolore du hero.
 * La partie encadrée par un <span> prime ; à défaut, les ~40 % de mots finaux
 * deviennent l'accent, ce qui reproduit l'effet du modèle sur un titre simple.
 */
function splitHeroTitle(string $raw): array
{
    if (preg_match('/<span[^>]*>(.*?)<\/span>/is', $raw, $m)) {
        $accent = trim(strip_tags($m[1]));
        $main   = trim(strip_tags(preg_replace('/<br\s*\/?>/i', "\n", str_replace($m[0], '', $raw))));
        return [$main, $accent];
    }
    $plain = trim(strip_tags(preg_replace('/<br\s*\/?>/i', ' ', $raw)));
    $words = preg_split('/\s+/', $plain, -1, PREG_SPLIT_NO_EMPTY);
    if (count($words) < 4) {
        return [$plain, ''];
    }
    $cut = (int)ceil(count($words) * 0.6);
    return [
        implode(' ', array_slice($words, 0, $cut)),
        implode(' ', array_slice($words, $cut)),
    ];
}

try {
    $page = Page::findBySlug('service');
    if (!$page) {
        echo "Page 'service' introuvable — abandon.\n";
        exit(0);
    }
    $pageId = (int)$page['id'];
    $lock   = ROOT_PATH . '/storage/service_v2.lock';
    $first  = !file_exists($lock);
    echo "Page 'service' #$pageId — " . ($first ? "première construction" : "réconciliation") . ".\n";

    // ─── 1. Échanges de type — le contenu suit, il n'est jamais réécrit ──────
    $typeSwaps = [
        'services_grid'     => 'services_grid_v2',
        'process_strip'     => 'process_timeline',
        'testimonials_grid' => 'testimonials_carousel',
    ];
    $sections = Section::getByPage($pageId);
    $byType = [];
    foreach ($sections as $s) { $byType[$s['type']][] = $s; }

    foreach ($typeSwaps as $oldType => $newType) {
        if (!isset($byType[$oldType])) {
            echo "  [$oldType] absent — rien à échanger.\n";
            continue;
        }
        foreach ($byType[$oldType] as $sec) {
            $secId = (int)$sec['id'];
            if (isset($byType[$newType])) {
                // Une section du type cible existe déjà : renommer créerait un
                // doublon affiché deux fois. On désactive l'ancienne (Règle #4 :
                // jamais de suppression, elle reste réactivable en admin).
                Database::query("UPDATE sections SET status = 'inactive' WHERE id = :id", ['id' => $secId]);
                echo "  Section #$secId [$oldType] désactivée : un '$newType' existe déjà.\n";
            } else {
                Database::query("UPDATE sections SET type = :t WHERE id = :id", ['t' => $newType, 'id' => $secId]);
                echo "  Section #$secId : $oldType -> $newType (contenu conservé).\n";
                $byType[$newType][] = $sec;
            }
        }
    }

    // ─── 2. Témoignages : client_company -> client_role ──────────────────────
    // Le gabarit carrousel affiche `client_role` là où la grille affichait
    // `client_company`. Sans ce report, la fonction des clients disparaîtrait.
    foreach (Section::getByPage($pageId) as $s) {
        if (($s['type'] ?? '') !== 'testimonials_carousel') { continue; }
        $secId = (int)$s['id'];
        $content = Block::getStructuredContent($secId);
        $moved = 0;
        foreach (($content['groups'] ?? []) as $g) {
            if (empty($g['client_role']) && !empty($g['client_company'])) {
                Block::setVal($secId, 'client_role', 'text', $g['client_company'], (int)$g['_group_id'], (int)($g['_sort_order'] ?? 0));
                $moved++;
            }
        }
        echo "  Témoignages : $moved fonction(s) reportée(s) de client_company vers client_role.\n";
        break;
    }

    // ─── 3. Carte de service mise en avant ───────────────────────────────────
    foreach (Section::getByPage($pageId) as $s) {
        if (($s['type'] ?? '') !== 'services_grid_v2') { continue; }
        $secId = (int)$s['id'];
        $content = Block::getStructuredContent($secId);
        if (empty($content['single']['card_link_text'])) {
            Block::setVal($secId, 'card_link_text', 'text', 'Découvrir');
            echo "  services_grid_v2.card_link_text -> 'Découvrir'\n";
        }
        $groups = $content['groups'] ?? [];
        $already = false;
        foreach ($groups as $g) {
            if (!empty($g['svc_featured']) && $g['svc_featured'] !== '0') { $already = true; break; }
        }
        if (!$already && isset($groups[1]['_group_id'])) {
            Block::setVal($secId, 'svc_featured', 'text', '1', (int)$groups[1]['_group_id'], (int)($groups[1]['_sort_order'] ?? 0));
            echo "  services_grid_v2 : carte 2 mise en avant (modifiable en admin).\n";
        }
        break;
    }

    // ─── 4. Section hero — réalignée à chaque passage ────────────────────────
    $heroId = null; $status = null; $order = null;
    foreach (Section::getByPage($pageId) as $s) {
        if (($s['type'] ?? '') === 'hero_media_cards') {
            $heroId = (int)$s['id'];
            $status = $s['status'] ?? 'active';
            $order  = (int)($s['sort_order'] ?? 0);
            break;
        }
    }
    if ($heroId === null) {
        $heroId = (int)Section::addSection($pageId, 'Hero — visuel et cartes', 'hero_media_cards', -1);
        Database::query("UPDATE sections SET status = 'active', sort_order = -1 WHERE id = :id", ['id' => $heroId]);
        echo "  Section hero créée (#$heroId), active, position -1.\n";
    } elseif ($status !== 'active' || $order !== -1) {
        Database::query("UPDATE sections SET status = 'active', sort_order = -1 WHERE id = :id", ['id' => $heroId]);
        echo "  Section hero #$heroId réalignée : '$status' -> 'active', $order -> -1.\n";
    } else {
        echo "  Section hero #$heroId déjà active en position -1.\n";
    }

    // ─── 5. Hero de page désactivé une seule fois ────────────────────────────
    if ($first) {
        Database::query("UPDATE pages SET hero_status = 0 WHERE id = :id", ['id' => $pageId]);
        echo "  pages.hero_status -> 0 (hero de page retiré).\n";
    }

    // ─── 6. Contenu du hero — semé uniquement si la section est vide ─────────
    $heroContent = Block::getStructuredContent($heroId);
    if (!empty($heroContent['single']) || !empty($heroContent['groups'])) {
        echo "  Contenu du hero déjà présent — non modifié.\n";
    } else {
        [$titleMain, $accent] = splitHeroTitle((string)($page['hero_title'] ?? ''));

        $badge = trim((string)($page['hero_badge'] ?? ''));
        if ($badge === '') { $badge = 'Nos services'; }

        $lead = trim(strip_tags((string)($page['hero_subtitle'] ?? '')));

        $cta1T = trim((string)($page['hero_cta1_text'] ?? ''));
        $cta1U = trim((string)($page['hero_cta1_url'] ?? ''));
        if ($cta1T === '') { $cta1T = 'Demander un devis gratuit'; $cta1U = '/contact'; }
        $cta2T = trim((string)($page['hero_cta2_text'] ?? ''));
        $cta2U = trim((string)($page['hero_cta2_url'] ?? ''));
        if ($cta2T === '') { $cta2T = 'Voir nos services'; $cta2U = '#services-grid'; }

        $singles = [
            ['badge',        'text',     $badge],
            ['title',        'textarea', $titleMain],
            ['title_accent', 'text',     $accent],
            ['text',         'textarea', $lead],
            ['cta1_text',    'text',     $cta1T],
            ['cta1_url',     'link',     $cta1U],
            ['cta1_icon',    'text',     'arrow-right'],
            ['cta2_text',    'text',     $cta2T],
            ['cta2_url',     'link',     $cta2U],
            ['cta2_icon',    'text',     'layout-grid'],
            ['image',        'image',    trim((string)($page['hero_image'] ?? ''))],
            ['image_alt',    'text',     trim($titleMain . ' ' . $accent)],
            ['decor',        'text',     '1'],
        ];
        $n = 0;
        foreach ($singles as [$k, $t, $v]) {
            if ($v === '') { continue; }
            Block::setVal($heroId, $k, $t, $v);
            $n++;
        }
        echo "  $n blocs 'single' posés — titre : \"$titleMain\" | accent : \"$accent\"\n";

        // Cartes flottantes : chaque valeur provient de contenus DÉJÀ publiés sur
        // cette page (grille de services, processus, bandeau CTA). Aucune
        // affirmation nouvelle n'est introduite. Toutes éditables en admin.
        $cards = [
            ['card_icon' => 'layout-grid', 'card_label' => 'Domaines couverts',
             'card_value' => '6', 'card_badge' => 'Sur mesure',
             'card_top' => '4', 'card_left' => '46'],
            ['card_icon' => 'route', 'card_label' => 'Notre processus',
             'card_value' => '4', 'card_unit' => 'étapes', 'card_progress' => '100',
             'card_top' => '32', 'card_left' => '30'],
            ['card_icon' => 'file-text', 'card_label' => 'Devis',
             'card_title' => 'Gratuit et sans engagement', 'card_meta' => 'Réponse sous 24 h ouvrées',
             'card_top' => '58', 'card_left' => '18'],
            ['card_icon' => 'headphones',
             'card_title' => 'Support continu', 'card_meta' => 'Déploiement, formation, suivi.',
             'card_top' => '82', 'card_left' => '6'],
        ];
        foreach ($cards as $g => $card) {
            foreach ($card as $k => $v) {
                Block::setVal($heroId, $k, 'text', $v, $g + 1, $g);
            }
        }
        echo "  " . count($cards) . " cartes flottantes créées.\n";
    }

    if ($first) {
        file_put_contents($lock, date('c') . " — page service v2 construite (hero #$heroId)\n");
        echo "  Verrou écrit : storage/service_v2.lock\n";
    }

    \App\Services\Cache::clear();
    echo "Page /service éditable depuis /admin/pages -> Services.\n";
    echo "=== TERMINÉ ===\n";
} catch (\Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}
