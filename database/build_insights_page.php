<?php
/**
 * Build Insights Page — centre de ressources (/insights)
 *
 * Monte la page, sa navigation, ses sections, les catégories du filtre et le
 * schéma dont le module a besoin. Aucun contenu n'est écrit dans les gabarits :
 * tout passe par les blocs CMS et reste modifiable depuis /admin (Règle #2).
 *
 * ── Script RÉCONCILIATEUR ───────────────────────────────────────────────────
 * Existence et position de chaque section sont réalignées à CHAQUE déploiement.
 * Le CONTENU n'est semé que si la section est vide, et le STATUT n'est posé
 * qu'à la création : rien de ce qui est décidé en admin ne peut être écrasé.
 *
 * ── Le blog devient Insights ────────────────────────────────────────────────
 * On ne crée pas un second système : /blog et /blog/{slug} redirigent en 301
 * vers /insights, la table `blog_posts` et l'administration existante sont
 * conservées. L'entrée de menu « Blog » est repointée sur la nouvelle page
 * plutôt que doublée — sinon la navigation afficherait deux fois le même
 * contenu. Ce repointage est une opération à faire UNE FOIS, gardée par un
 * drapeau en base : si l'administration renomme ensuite l'entrée, le
 * déploiement suivant ne défait pas son choix.
 *
 * ── Ordre des ALTER ─────────────────────────────────────────────────────────
 * Chaque ALTER est isolé dans son propre try/catch. Un ALTER verrouille la
 * table le temps qu'il dure ; `blog_posts` est lue par /insights, contrôlée par
 * les smoke tests du déploiement. Isoler évite qu'un échec entraîne les autres.
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

echo "=== BUILD INSIGHTS PAGE (/insights) ===\n";

try {

    // ══════════════════════════════════════════════════════════════════════
    //  1. SCHÉMA
    // ══════════════════════════════════════════════════════════════════════

    $colonnes = array_column(Database::fetchAll("SHOW COLUMNS FROM `blog_posts`"), 'Field');

    $ajouts = [
        'reading_time'  => "INT NULL",
        'sort_order'    => "INT NOT NULL DEFAULT 0",
        'og_image'      => "VARCHAR(255) NULL",
        'resource_type' => "VARCHAR(50) NULL",
        'resource_file' => "VARCHAR(255) NULL",
        'resource_cta'  => "VARCHAR(120) NULL",
    ];
    foreach ($ajouts as $col => $definition) {
        if (in_array($col, $colonnes, true)) {
            echo "  blog_posts.$col déjà présente.\n";
            continue;
        }
        try {
            Database::query("ALTER TABLE `blog_posts` ADD COLUMN `$col` $definition");
            echo "  blog_posts.$col ajoutée.\n";
        } catch (\Throwable $e) {
            echo "  ATTENTION blog_posts.$col : " . $e->getMessage() . "\n";
        }
    }

    /**
     * Abonnés à la newsletter.
     *
     * L'email est UNIQUE : deux inscriptions de la même adresse ne créent pas
     * deux lignes, et le modèle peut réactiver un désabonné sans doublon.
     */
    try {
        Database::query("CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
            `id`         INT AUTO_INCREMENT PRIMARY KEY,
            `email`      VARCHAR(190) NOT NULL UNIQUE,
            `source`     VARCHAR(100) NULL,
            `ip_address` VARCHAR(45) NULL,
            `status`     VARCHAR(20) NOT NULL DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "  Table newsletter_subscribers : OK.\n";
    } catch (\Throwable $e) {
        echo "  ATTENTION newsletter_subscribers : " . $e->getMessage() . "\n";
    }

    /** Pose un paramètre UNIQUEMENT s'il n'existe pas encore. */
    $reglageDefaut = function (string $cle, string $valeur): void {
        $existe = Database::fetch("SELECT id FROM settings WHERE `key` = :k LIMIT 1", ['k' => $cle]);
        if ($existe) { return; }
        Database::query(
            "INSERT INTO settings (`key`, `value`) VALUES (:k, :v)",
            ['k' => $cle, 'v' => $valeur]
        );
        echo "  Réglage $cle initialisé.\n";
    };

    $reglageDefaut('newsletter_rate_limit', '5');
    $reglageDefaut('insights_cta_title',  'Vous avez un projet lié à ce sujet ?');
    $reglageDefaut('insights_cta_text',   "Décrivez-nous votre contexte : nous vous dirons franchement ce qui est "
                                        . "faisable, dans quel ordre, et ce que cela suppose de votre côté.");
    $reglageDefaut('insights_cta_button', 'Parler à Digitalium');
    $reglageDefaut('insights_cta_url',    '/contact');

    // ══════════════════════════════════════════════════════════════════════
    //  2. CATÉGORIES DU FILTRE
    // ══════════════════════════════════════════════════════════════════════
    //
    // Les catégories sont des lignes de `blog_categories`, gérées dans
    // /admin/blog/categories : le filtre du frontend les lit directement.
    // On n'ajoute que celles qui manquent — une catégorie renommée en admin
    // n'est pas recréée sous son ancien nom.

    $categories = [
        ['Intelligence Artificielle',   "Modèles, agents, cas d'usage et limites de l'IA appliquée à l'entreprise."],
        ['Automatisation',              "Workflows, intégrations et automatisation des processus métiers."],
        ['Software & Applications',     "Conception, développement et maintenance d'applications métiers."],
        ['Data & Business Intelligence',"Collecte, qualité, modélisation et restitution de la donnée."],
        ['Cybersécurité',               "Protection des systèmes, des accès et des données."],
        ['Infrastructure & Cloud',      "Hébergement, réseaux, supervision et exploitation."],
        ['Transformation Digitale',     "Conduite du changement et modernisation des organisations."],
        ['Business & Innovation',       "Stratégie, modèles économiques et innovation technologique."],
    ];

    $slugifier = static function (string $texte): string {
        $texte = preg_replace('~[^\pL\d]+~u', '-', $texte);
        $translit = @iconv('utf-8', 'us-ascii//TRANSLIT', $texte);
        if ($translit !== false) { $texte = $translit; }
        $texte = preg_replace('~[^-\w]+~', '', $texte);
        return strtolower(trim($texte, '-')) ?: 'categorie';
    };

    $creees = 0;
    foreach ($categories as [$nom, $desc]) {
        $slug = $slugifier($nom);
        $existe = Database::fetch(
            "SELECT id FROM blog_categories WHERE slug = :s OR name = :n LIMIT 1",
            ['s' => $slug, 'n' => $nom]
        );
        if ($existe) { continue; }
        Database::query(
            "INSERT INTO blog_categories (name, slug, description) VALUES (:n, :s, :d)",
            ['n' => $nom, 's' => $slug, 'd' => $desc]
        );
        $creees++;
    }
    echo "  Catégories : $creees créée(s), " . (count($categories) - $creees) . " déjà présente(s).\n";

    // ══════════════════════════════════════════════════════════════════════
    //  3. PAGE
    // ══════════════════════════════════════════════════════════════════════

    $metaDesc = "Analyses, guides et retours d'expérience de Digitalium Group sur l'intelligence "
              . "artificielle, le logiciel, la donnée, la cybersécurité et la transformation "
              . "digitale des organisations en Afrique.";

    $page = Page::findBySlug('insights');
    if (!$page) {
        $pageId = (int)Page::createPage(
            'Insights',
            'insights',
            'Insights & Ressources — Digitalium Group',
            $metaDesc,
            'published'
        );
        echo "\nPage 'insights' créée (#$pageId).\n";
    } else {
        $pageId = (int)$page['id'];
        echo "\nPage 'insights' déjà présente (#$pageId).\n";
    }

    // La page ancienne 'blog' garde sa place : elle sert encore de repli au
    // contrôleur. Elle prend simplement la position de navigation qu'elle avait.
    $pageBlog = Page::findBySlug('blog');
    $navOrder = $pageBlog ? (int)($pageBlog['sort_order'] ?? 6) : 6;

    $aParentSlug = (bool)Database::fetch("SHOW COLUMNS FROM `pages` LIKE 'parent_slug'");
    Database::query(
        "UPDATE pages SET status = 'published', in_navigation = 1, sort_order = :o,
                          hero_status = 0" . ($aParentSlug ? ", parent_slug = NULL" : "") . "
         WHERE id = :id",
        ['o' => $navOrder, 'id' => $pageId]
    );
    echo "Navigation : publiée, in_navigation = 1, position $navOrder.\n";

    // Couleur d'accent — posée UNE SEULE FOIS, comme sur les autres pages.
    $aAccent = (bool)Database::fetch("SHOW COLUMNS FROM `pages` LIKE 'accent_color'");
    if ($aAccent) {
        $actuel = Database::fetch("SELECT accent_color FROM pages WHERE id = :id", ['id' => $pageId]);
        if (trim((string)($actuel['accent_color'] ?? '')) === '') {
            Database::query("UPDATE pages SET accent_color = '#0868B0' WHERE id = :id", ['id' => $pageId]);
            echo "Accent : #0868B0 (bleu clair du logo).\n";
        } else {
            echo "Accent : {$actuel['accent_color']} — choix conservé.\n";
        }
    }

    // ── Navigation : reprendre l'entrée « Blog » plutôt que la doubler ──────
    $drapeau = 'insights_nav_migrated_v1';
    $dejaFait = Database::fetch("SELECT id FROM settings WHERE `key` = :k LIMIT 1", ['k' => $drapeau]);

    $menu = Database::fetch("SELECT id FROM menus WHERE location = 'primary' LIMIT 1");
    if (!$menu) {
        echo "Aucun menu 'primary' — la page apparaît via in_navigation.\n";
    } elseif ($dejaFait) {
        echo "Navigation : migration déjà effectuée — entrée laissée telle quelle.\n";
    } else {
        $menuId = (int)$menu['id'];
        $nbItems = (int)(Database::fetch(
            "SELECT COUNT(*) AS n FROM menu_items WHERE menu_id = :m", ['m' => $menuId]
        )['n'] ?? 0);

        if ($nbItems === 0) {
            echo "Menu 'primary' vide — la page apparaît via in_navigation.\n";
        } else {
            $entree = Database::fetch(
                "SELECT id FROM menu_items
                 WHERE menu_id = :m AND (url IN ('/blog', 'blog') " . ($pageBlog ? "OR page_id = :pb" : "") . ")
                 LIMIT 1",
                $pageBlog ? ['m' => $menuId, 'pb' => (int)$pageBlog['id']] : ['m' => $menuId]
            );

            if ($entree) {
                Database::query(
                    "UPDATE menu_items SET page_id = :p, label = 'Insights', url = '/insights', is_active = 1
                     WHERE id = :id",
                    ['p' => $pageId, 'id' => (int)$entree['id']]
                );
                echo "Menu 'primary' : entrée « Blog » repointée sur Insights (#{$entree['id']}).\n";
            } else {
                $ordre = (int)(Database::fetch(
                    "SELECT COALESCE(MAX(sort_order), 0) AS o FROM menu_items
                     WHERE menu_id = :m AND parent_id IS NULL", ['m' => $menuId]
                )['o'] ?? 0) + 1;
                Database::query(
                    "INSERT INTO menu_items (menu_id, parent_id, page_id, label, url, target, icon, sort_order, is_active)
                     VALUES (:m, NULL, :p, 'Insights', '/insights', '_self', '', :o, 1)",
                    ['m' => $menuId, 'p' => $pageId, 'o' => $ordre]
                );
                echo "Menu 'primary' : entrée Insights ajoutée (position $ordre).\n";
            }
        }
    }

    // L'ancienne page 'blog' sort de la navigation automatique : son adresse
    // redirige désormais, l'y laisser afficherait deux fois la même rubrique.
    if ($pageBlog && !$dejaFait) {
        Database::query("UPDATE pages SET in_navigation = 0 WHERE id = :id", ['id' => (int)$pageBlog['id']]);
        echo "Ancienne page 'blog' : retirée de la navigation (son adresse redirige vers /insights).\n";
    }

    if (!$dejaFait) {
        Database::query("INSERT INTO settings (`key`, `value`) VALUES (:k, '1')", ['k' => $drapeau]);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  4. OUTILS DE RÉCONCILIATION
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Réaligne une section sur le couple (type, nom).
     * Position réalignée à chaque passage ; statut posé à la création seulement,
     * pour qu'une section éteinte en admin ne se rallume pas au déploiement.
     */
    $reconcile = function (string $type, string $nom, int $ordre) use ($pageId): int {
        foreach (Section::getByPage($pageId) as $s) {
            if (($s['type'] ?? '') === $type && ($s['name'] ?? '') === $nom) {
                $id = (int)$s['id'];
                Database::query("UPDATE sections SET sort_order = :o WHERE id = :id",
                    ['o' => $ordre, 'id' => $id]);
                return $id;
            }
        }
        $id = (int)Section::addSection($pageId, $nom, $type, $ordre);
        Database::query("UPDATE sections SET status = 'active', sort_order = :o WHERE id = :id",
            ['o' => $ordre, 'id' => $id]);
        echo "  section créée : #$id [$type] $nom (position $ordre)\n";
        return $id;
    };

    /** Sème les blocs UNIQUEMENT si la section est encore vide. */
    $seed = function (int $secId, array $singles, array $groups = []): bool {
        $contenu = Block::getStructuredContent($secId);
        if (!empty($contenu['single']) || !empty($contenu['groups'])) {
            echo "    contenu déjà présent — non modifié.\n";
            return false;
        }
        foreach ($singles as $cle => $valeur) {
            if ($valeur === '') { continue; }
            $type = \App\Helpers\BlockFieldHelper::type($cle);
            // BlockFieldHelper décrit comment ÉDITER le champ ; le stockage ne
            // connaît que text/textarea/image/link. « select » se range en text.
            if ($type === 'select') { $type = 'text'; }
            Block::setVal($secId, $cle, $type, $valeur);
        }
        foreach ($groups as $g => $champs) {
            foreach ($champs as $cle => $valeur) {
                if ($valeur === '') { continue; }
                Block::setVal($secId, $cle, 'text', $valeur, $g + 1, $g);
            }
        }
        echo "    " . count($singles) . " blocs + " . count($groups) . " groupes semés.\n";
        return true;
    };

    /**
     * Pose les blocs MANQUANTS d'une section déjà remplie.
     * `$seed` ne touche pas à une section non vide : sans ce complément, un
     * réglage ajouté après coup ne serait jamais appliqué en production.
     */
    $ensure = function (int $secId, array $defauts): void {
        $contenu = Block::getStructuredContent($secId);
        $poses = 0;
        foreach ($defauts as $cle => $valeur) {
            if (array_key_exists($cle, $contenu['single'] ?? [])) { continue; }
            $type = \App\Helpers\BlockFieldHelper::type($cle);
            if ($type === 'select') { $type = 'text'; }
            Block::setVal($secId, $cle, $type, $valeur);
            $poses++;
        }
        if ($poses > 0) { echo "    $poses bloc(s) manquant(s) posé(s).\n"; }
    };

    // ══════════════════════════════════════════════════════════════════════
    //  5. SECTIONS
    // ══════════════════════════════════════════════════════════════════════

    echo "\n[1/5] Hero\n";
    $id = $reconcile('hero_media_cards', 'Hero — Insights', -1);
    $seed($id, [
        'badge'              => 'Insights & Ressources',
        'title'              => "Comprendre aujourd'hui",
        'title_accent'       => "les technologies qui transforment demain.",
        'text'               => "Analyses, conseils, tendances et retours d'expérience autour de l'IA, du "
                              . "logiciel, de la donnée, de la cybersécurité et de la transformation "
                              . "digitale en Afrique.",
        'cta1_text'          => 'Explorer les ressources',
        'cta1_url'           => '#articles',
        'cta1_icon'          => 'arrow-down',
        'decor'              => '1',
        'layout'             => 'overlay',
        'image_max_width'    => '1250',
        'image_ratio'        => '1250 / 500',
        'image_ratio_mobile' => '16 / 9',
        'overlay_opacity'    => '64',
        'overlay_min_height' => '500',
        'image_radius'       => '0',
    ]);

    echo "[2/5] Article à la une\n";
    $id = $reconcile('insights_featured', 'Article à la une', 1);
    $seed($id, [
        'badge_label'     => 'À la une',
        'cta_text'        => "Lire l'analyse",
        'read_suffix'     => 'min de lecture',
        'fallback_latest' => '1',
    ]);
    // Réglages ajoutés après la première mise en ligne.
    $ensure($id, ['fallback_latest' => '1', 'read_suffix' => 'min de lecture']);

    echo "[3/5] Derniers articles\n";
    $id = $reconcile('insights_grid', 'Derniers articles', 2);
    $seed($id, [
        'tag'               => 'Publications',
        'title'             => 'Derniers articles',
        'subtitle'          => "Ce que nos équipes observent, testent et retiennent sur le terrain.",
        'filter_all'        => 'Tous',
        'show_filters'      => '1',
        'show_search'       => '1',
        'search_label'      => 'Rechercher une ressource',
        'search_placeholder'=> 'Un sujet, une technologie, un secteur…',
        'search_button'     => 'Rechercher',
        'per_page'          => '9',
        'read_label'        => 'Lire',
        'read_suffix'       => 'min',
        'count_label'       => '{n} résultat(s)',
        'reset_text'        => 'Tout afficher',
        'empty_text'        => "Aucune ressource ne correspond à cette recherche.",
    ]);

    echo "[4/5] Contenus stratégiques\n";
    $id = $reconcile('insights_resources', 'Contenus stratégiques', 3);
    $seed($id, [
        'tag'           => 'Ressources',
        'title'         => 'Contenus stratégiques',
        'subtitle'      => "Des formats plus longs, pensés pour être utilisés : cadrer un projet, "
                         . "arbitrer une décision, vérifier un existant.",
        'read_text'     => 'Consulter',
        'download_text' => 'Télécharger',
        'empty_text'    => "Nos premiers guides et études sont en cours de rédaction. "
                         . "Abonnez-vous pour être prévenu de leur publication.",
    ], [
        ['type_value' => 'guide',       'type_label' => 'Guide pratique',   'type_icon' => 'compass'],
        ['type_value' => 'rapport',     'type_label' => 'Étude / rapport',  'type_icon' => 'bar-chart-3'],
        ['type_value' => 'checklist',   'type_label' => 'Checklist',        'type_icon' => 'list-checks'],
        ['type_value' => 'livre-blanc', 'type_label' => 'Livre blanc',      'type_icon' => 'book-open'],
        ['type_value' => 'cas-usage',   'type_label' => "Cas d'usage",      'type_icon' => 'lightbulb'],
        ['type_value' => 'comparatif',  'type_label' => 'Comparatif',       'type_icon' => 'scale'],
    ]);

    echo "[5/5] Newsletter\n";
    $id = $reconcile('newsletter', 'Newsletter — Insights', 4);
    $seed($id, [
        'tag'          => 'Newsletter',
        'title'        => 'Recevez nos analyses et tendances digitales.',
        'subtitle'     => "Les publications de Digitalium Group, dans votre boîte mail, sans bruit inutile.",
        'placeholder'  => 'Votre adresse email',
        'button_text'  => "S'abonner",
        'note'         => "Vous pouvez vous désabonner à tout moment. Votre adresse ne sert qu'à cet envoi.",
        'success_text' => 'Merci — votre inscription est enregistrée.',
    ]);
    $ensure($id, [
        'placeholder'  => 'Votre adresse email',
        'button_text'  => "S'abonner",
        'success_text' => 'Merci — votre inscription est enregistrée.',
    ]);

    // ══════════════════════════════════════════════════════════════════════
    //  6. CACHE
    // ══════════════════════════════════════════════════════════════════════
    \App\Services\Cache::clear();
    echo "\nCache vidé.\n";
    echo "=== TERMINÉ ===\n";

} catch (\Throwable $e) {
    echo "ÉCHEC : " . $e->getMessage() . "\n";
    echo "  " . $e->getFile() . ':' . $e->getLine() . "\n";
    exit(1);
}
