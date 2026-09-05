<?php
/**
 * Retrait des pages en doublon — /service et /blog
 *
 * Le site portait deux couples d'adresses pour un même contenu :
 *   /service  ← remplacée par /solutions  (qui porte les cinq pages filles)
 *   /blog     ← remplacée par /insights   (redirection déjà en place)
 *
 * Ce script retire les PAGES CMS résiduelles et leurs entrées de menu. Les
 * redirections 301 vivent dans `routes/web.php` et ne sont PAS touchées ici :
 * ce sont elles qui préservent l'indexation et les liens déjà partagés.
 *
 * ── CE QUI N'EST PAS SUPPRIMÉ, ET POURQUOI ──────────────────────────────────
 * `BlogController`, `blog_posts`, `blog_categories` et `/admin/blog` RESTENT.
 * /insights/{slug} est servi par `BlogController@frontendPost` et les articles
 * vivent dans `blog_posts` : le « blog » n'est pas un module concurrent
 * d'Insights, c'est son MOTEUR. Le supprimer détruirait la vitrine éditoriale
 * que l'on demande justement de conserver. Ce qui disparaît est l'adresse
 * publique et la page CMS résiduelle, pas le moteur.
 *
 * ── OPÉRATION UNIQUE ────────────────────────────────────────────────────────
 * Chaque page est retirée UNE SEULE FOIS, sous drapeau `settings`. Sans cela,
 * une page volontairement recréée sous le même slug serait supprimée au
 * déploiement suivant — le script deviendrait une régression permanente.
 *
 * ── ISOLATION ───────────────────────────────────────────────────────────────
 * Chaque étape a son try/catch. Au premier déploiement d'Insights, une erreur
 * sur `settings` avait remonté jusqu'au try extérieur et tué le script entier.
 *
 * ── RÉCUPÉRATION ────────────────────────────────────────────────────────────
 * Le pipeline crée une sauvegarde SQL (RollbackManager) AVANT chaque
 * déploiement : une suppression reste récupérable.
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

use App\Services\Database;

echo "=== RETRAIT DES PAGES EN DOUBLON ===\n";

/**
 * Pages à retirer.
 *   slug      : la page CMS à supprimer
 *   urls      : les formes d'URL susceptibles d'être dans un menu
 *   drapeau   : garantit que l'opération n'a lieu qu'une fois
 *   remplacee : uniquement pour le message affiché
 */
$aRetirer = [
    [
        'slug'      => 'service',
        'urls'      => ['/service', 'service', '/public/service'],
        'drapeau'   => 'service_page_retired_v1',
        'remplacee' => '/solutions',
    ],
    [
        'slug'      => 'blog',
        'urls'      => ['/blog', 'blog', '/public/blog'],
        'drapeau'   => 'blog_page_retired_v1',
        'remplacee' => '/insights',
    ],
];

foreach ($aRetirer as $cible) {
    $slug = $cible['slug'];
    echo "\n--- /$slug  →  {$cible['remplacee']} ---\n";

    // ── Le drapeau, lu dans son propre try : sa lecture ne doit pas pouvoir
    //    interrompre le traitement de la page suivante.
    $dejaFait = false;
    try {
        $dejaFait = (bool)Database::fetch(
            "SELECT id FROM settings WHERE setting_key = :k LIMIT 1",
            ['k' => $cible['drapeau']]
        );
    } catch (\Throwable $e) {
        echo "  ATTENTION drapeau illisible : " . $e->getMessage() . "\n";
        echo "  -> retrait ignoré par prudence (mieux vaut une page en trop qu'une page perdue).\n";
        continue;
    }

    if ($dejaFait) {
        echo "  Déjà retirée lors d'un déploiement précédent — rien à faire.\n";
        echo "  (Une page recréée volontairement sous ce slug est donc conservée.)\n";
        continue;
    }

    // ── Entrées de menu ─────────────────────────────────────────────────────
    // À faire AVANT la suppression de la page : `menu_items.page_id` est en
    // ON DELETE SET NULL, pas en CASCADE. Une fois la page partie, l'entrée
    // survivrait avec son `url` intacte et son lien resterait au pied de page.
    $pageId = null;
    try {
        $ligne = Database::fetch("SELECT id FROM pages WHERE slug = :s LIMIT 1", ['s' => $slug]);
        $pageId = $ligne ? (int)$ligne['id'] : null;
    } catch (\Throwable $e) {
        echo "  ATTENTION page illisible : " . $e->getMessage() . "\n";
        continue;
    }

    try {
        $marqueurs = [];
        $params    = [];
        foreach ($cible['urls'] as $i => $u) {
            $marqueurs[] = ':u' . $i;
            $params['u' . $i] = $u;
        }
        $ou = 'url IN (' . implode(', ', $marqueurs) . ')';
        if ($pageId !== null) {
            $ou .= ' OR page_id = :p';
            $params['p'] = $pageId;
        }

        $items = Database::fetchAll("SELECT id, menu_id, label FROM menu_items WHERE $ou", $params);
        if (!$items) {
            echo "  Aucune entrée de menu à retirer.\n";
        } else {
            foreach ($items as $it) {
                // Un enfant éventuel remonte à la racine plutôt que de partir
                // avec son parent : perdre un lien silencieusement serait pire.
                Database::query(
                    "UPDATE menu_items SET parent_id = NULL WHERE parent_id = :id",
                    ['id' => (int)$it['id']]
                );
                Database::query("DELETE FROM menu_items WHERE id = :id", ['id' => (int)$it['id']]);
                echo "  Entrée de menu retirée : « {$it['label']} » (menu #{$it['menu_id']}).\n";
            }
        }
    } catch (\Throwable $e) {
        echo "  ATTENTION entrées de menu non retirées : " . $e->getMessage() . "\n";
        echo "  -> à vérifier dans /admin/menus.\n";
    }

    // ── La page, ses sections, ses blocs ────────────────────────────────────
    if ($pageId === null) {
        echo "  Aucune page CMS '$slug' — rien à supprimer.\n";
    } else {
        try {
            // Les clés étrangères sont en CASCADE (database.sql:105 et :119),
            // mais on supprime explicitement : le script reste correct même sur
            // une base montée sans ces contraintes.
            $sections = Database::fetchAll(
                "SELECT id FROM sections WHERE page_id = :p", ['p' => $pageId]
            );
            foreach ($sections as $s) {
                Database::query("DELETE FROM blocks WHERE section_id = :s", ['s' => (int)$s['id']]);
            }
            Database::query("DELETE FROM sections WHERE page_id = :p", ['p' => $pageId]);
            Database::query("DELETE FROM pages WHERE id = :p", ['p' => $pageId]);
            echo "  Page #$pageId supprimée (" . count($sections) . " section(s) et leurs blocs).\n";
        } catch (\Throwable $e) {
            echo "  ATTENTION page non supprimée : " . $e->getMessage() . "\n";
            echo "  -> la redirection 301 reste active, la page est donc déjà invisible.\n";
            continue;   // pas de drapeau : on retentera au prochain déploiement
        }
    }

    // ── Drapeau ─────────────────────────────────────────────────────────────
    try {
        Database::query(
            "INSERT INTO settings (setting_key, setting_value) VALUES (:k, '1')",
            ['k' => $cible['drapeau']]
        );
        echo "  Drapeau {$cible['drapeau']} posé — opération non rejouable.\n";
    } catch (\Throwable $e) {
        echo "  ATTENTION drapeau non posé : " . $e->getMessage() . "\n";
    }
}

try {
    \App\Services\Cache::clear();
    echo "\nCache vidé.\n";
} catch (\Throwable $e) {
    echo "\nATTENTION cache non vidé : " . $e->getMessage() . "\n";
}

echo "=== TERMINÉ ===\n";
echo "Rappel : les routes /blog et /service restent déclarées — ce sont elles\n";
echo "qui portent les redirections 301. Les retirer donnerait des 404.\n";
