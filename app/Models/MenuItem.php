<?php
namespace App\Models;

use App\Services\Database;

/**
 * Liens d'un menu de navigation.
 *
 * ── Deux niveaux, pas trois ─────────────────────────────────────────────────
 * L'en-tête sait afficher une racine et un niveau d'enfants. Un lien placé au
 * troisième niveau serait enregistré mais INVISIBLE. Le modèle le ramène donc à
 * la racine : mieux vaut un lien mal placé et visible qu'un lien disparu. Cette
 * règle élimine du même coup les cycles (A parent de B, B parent de A), qui
 * feraient boucler le rendu.
 */
class MenuItem extends Model {
    protected static string $table = 'menu_items';

    /** Profondeur maximale réellement rendue par le frontend. */
    public const PROFONDEUR_MAX = 2;

    /**
     * Tous les liens d'un menu, à plat, dans l'ordre d'affichage.
     */
    public static function getByMenu(int $menuId): array {
        return Database::fetchAll(
            "SELECT mi.*, p.slug as page_slug
             FROM menu_items mi
             LEFT JOIN pages p ON p.id = mi.page_id
             WHERE mi.menu_id = :menu_id
             ORDER BY mi.parent_id ASC, mi.sort_order ASC, mi.id ASC",
            ['menu_id' => $menuId]
        );
    }

    /**
     * Arbre des liens d'un menu (rendu frontend).
     */
    public static function getTree(int $menuId): array {
        $flat = self::getByMenu($menuId);
        $tree = [];
        $indexed = [];

        foreach ($flat as $item) {
            $item['children'] = [];
            $indexed[$item['id']] = $item;
        }

        foreach ($indexed as $id => $item) {
            if ($item['parent_id'] && isset($indexed[$item['parent_id']])) {
                $indexed[$item['parent_id']]['children'][] = &$indexed[$id];
            } else {
                $tree[] = &$indexed[$id];
            }
        }

        return $tree;
    }

    /**
     * Liens actifs d'un menu.
     */
    public static function getActiveByMenu(int $menuId): array {
        return Database::fetchAll(
            "SELECT mi.*, p.slug as page_slug
             FROM menu_items mi
             LEFT JOIN pages p ON p.id = mi.page_id
             WHERE mi.menu_id = :menu_id AND mi.is_active = 1
             ORDER BY mi.parent_id ASC, mi.sort_order ASC, mi.id ASC",
            ['menu_id' => $menuId]
        );
    }

    /**
     * Liens actifs d'un EMPLACEMENT — le frontend demande un emplacement,
     * jamais un identifiant de menu.
     *
     * Renvoie un tableau vide si aucun menu n'occupe cet emplacement : c'est au
     * gabarit de décider de son repli.
     */
    public static function getActiveByLocation(string $location): array {
        $menu = Menu::findByLocation($location);
        return $menu ? self::getActiveByMenu((int)$menu['id']) : [];
    }

    /** Nombre de liens d'un menu, pour l'écran de liste. */
    public static function compter(int $menuId): int {
        $row = Database::fetch(
            "SELECT COUNT(*) AS n FROM menu_items WHERE menu_id = :m", ['m' => $menuId]
        );
        return (int)($row['n'] ?? 0);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  ENREGISTREMENT — RÉCONCILIATION
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Réaligne les liens d'un menu sur ce que le formulaire a envoyé.
     *
     * ── Pourquoi ce n'est plus « effacer puis réécrire » ────────────────────
     * L'ancienne version supprimait toutes les lignes puis les réinsérait, en
     * réutilisant les `parent_id` de l'ancien cycle. Or `parent_id` porte une
     * clé étrangère vers `menu_items(id)` : l'INSERT violait la contrainte, la
     * transaction était annulée, et TOUT enregistrement d'un menu comportant un
     * sous-lien échouait. Un parent tout juste ajouté arrivait sous la forme
     * « new_1001 », que (int) réduisait à 0 — même violation.
     *
     * On réaligne donc, en trois temps dans une seule transaction :
     *   1. écriture à plat, tous les parents à NULL, en notant la
     *      correspondance « référence du formulaire → identifiant réel » ;
     *   2. suppression des lignes absentes de l'envoi — aucun parent n'étant
     *      posé à cet instant, aucune contrainte ne peut bloquer ;
     *   3. repose des parents via la correspondance.
     *
     * Un lien conserve ainsi son identifiant d'un enregistrement à l'autre.
     *
     * @param array $items chaque entrée : id (identifiant existant ou référence
     *                     « new_… »), label, url, page_id, target, icon,
     *                     parent_id (référence), sort_order, is_active
     */
    public static function saveForMenu(int $menuId, array $items): void {
        $existants = array_column(
            Database::fetchAll("SELECT id FROM menu_items WHERE menu_id = :m", ['m' => $menuId]),
            'id'
        );
        $existants = array_map('intval', $existants);

        Database::beginTransaction();
        try {
            $correspondance = [];   // référence du formulaire => identifiant réel
            $conserves      = [];   // identifiants réels vus dans l'envoi
            $parents        = [];   // identifiant réel => référence du parent

            // ── 1. Écriture à plat ──────────────────────────────────────────
            foreach (array_values($items) as $rang => $item) {
                $champs = self::normaliser($item, $menuId, $rang);
                $ref    = trim((string)($item['id'] ?? ''));

                // Un identifiant n'est réutilisé que s'il appartient BIEN à ce
                // menu : sans cette vérification, un formulaire forgé pourrait
                // réaffecter le lien d'un autre menu.
                $idExistant = (ctype_digit($ref) && in_array((int)$ref, $existants, true))
                    ? (int)$ref : 0;

                if ($idExistant > 0) {
                    Database::query(
                        "UPDATE menu_items
                         SET parent_id = NULL, page_id = :page_id, label = :label, url = :url,
                             target = :target, icon = :icon, sort_order = :sort_order,
                             is_active = :is_active
                         WHERE id = :id AND menu_id = :menu_id",
                        $champs + ['id' => $idExistant, 'menu_id' => $menuId]
                    );
                    $id = $idExistant;
                } else {
                    $id = (int)Database::insert(
                        "INSERT INTO menu_items
                            (menu_id, parent_id, page_id, label, url, target, icon, sort_order, is_active)
                         VALUES (:menu_id, NULL, :page_id, :label, :url, :target, :icon, :sort_order, :is_active)",
                        $champs + ['menu_id' => $menuId]
                    );
                }

                $conserves[] = $id;
                if ($ref !== '') { $correspondance[$ref] = $id; }
                $correspondance[(string)$id] = $id;

                $refParent = trim((string)($item['parent_id'] ?? ''));
                if ($refParent !== '') { $parents[$id] = $refParent; }
            }

            // ── 2. Suppression des lignes retirées ──────────────────────────
            foreach (array_diff($existants, $conserves) as $aSupprimer) {
                Database::query(
                    "DELETE FROM menu_items WHERE id = :id AND menu_id = :m",
                    ['id' => $aSupprimer, 'm' => $menuId]
                );
            }

            // ── 3. Repose des parents ───────────────────────────────────────
            // Un parent introuvable, égal à soi-même, ou lui-même enfant, laisse
            // le lien à la racine : on préfère un lien visible et mal placé à un
            // enregistrement qui échoue en bloc.
            $racines = [];
            foreach ($conserves as $id) {
                $racines[$id] = !isset($parents[$id]);
            }

            foreach ($parents as $id => $refParent) {
                $idParent = $correspondance[$refParent] ?? 0;
                if ($idParent <= 0 || $idParent === $id) { continue; }
                if (empty($racines[$idParent])) { continue; }   // profondeur > 2 refusée

                Database::query(
                    "UPDATE menu_items SET parent_id = :p WHERE id = :id AND menu_id = :m",
                    ['p' => $idParent, 'id' => $id, 'm' => $menuId]
                );
            }

            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    /** Champs d'un lien, nettoyés. */
    private static function normaliser(array $item, int $menuId, int $rang): array {
        $cible = $item['target'] ?? '_self';
        return [
            'page_id'    => !empty($item['page_id']) ? (int)$item['page_id'] : null,
            'label'      => mb_substr(trim((string)($item['label'] ?? '')), 0, 150),
            'url'        => mb_substr(trim((string)($item['url'] ?? '')), 0, 500),
            'target'     => in_array($cible, ['_self', '_blank'], true) ? $cible : '_self',
            'icon'       => mb_substr(trim((string)($item['icon'] ?? '')), 0, 50),
            'sort_order' => (int)($item['sort_order'] ?? $rang),
            'is_active'  => (int)($item['is_active'] ?? 1) === 1 ? 1 : 0,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  AJOUT AUTOMATIQUE D'UNE PAGE
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Ajoute une page en fin de menu principal — UNE SEULE FOIS.
     *
     * Le drapeau `pages.nav_seeded` est ce qui rend l'opération unique : une
     * fois la page proposée, le menu fait autorité. Si l'administration retire
     * ou renomme le lien, rien ne le remet au prochain enregistrement de la
     * page. Sans ce drapeau, un simple « vérifier qu'aucun lien ne pointe déjà
     * vers cette page » ressusciterait un lien volontairement supprimé.
     *
     * @return bool true si un lien a été ajouté
     */
    public static function semerPage(array $page, string $location = 'primary'): bool {
        if (($page['status'] ?? '') !== 'published')      { return false; }
        if ((int)($page['in_navigation'] ?? 0) !== 1)     { return false; }
        if ((int)($page['nav_seeded'] ?? 0) === 1)        { return false; }

        $pageId = (int)($page['id'] ?? 0);
        if ($pageId <= 0) { return false; }

        $menu = Menu::findByLocation($location);
        if (!$menu) { return false; }
        $menuId = (int)$menu['id'];

        // Déjà présent : on pose seulement le drapeau, sans doubler le lien.
        $deja = Database::fetch(
            "SELECT id FROM menu_items WHERE menu_id = :m AND page_id = :p LIMIT 1",
            ['m' => $menuId, 'p' => $pageId]
        );

        if (!$deja) {
            $ordre = (int)(Database::fetch(
                "SELECT COALESCE(MAX(sort_order), 0) AS o FROM menu_items
                 WHERE menu_id = :m AND parent_id IS NULL", ['m' => $menuId]
            )['o'] ?? 0) + 1;

            Database::query(
                "INSERT INTO menu_items (menu_id, parent_id, page_id, label, url, target, icon, sort_order, is_active)
                 VALUES (:m, NULL, :p, :label, '', '_self', '', :o, 1)",
                [
                    'm'     => $menuId,
                    'p'     => $pageId,
                    'label' => mb_substr(trim((string)($page['title'] ?? 'Page')), 0, 150),
                    'o'     => $ordre,
                ]
            );
        }

        Database::query("UPDATE pages SET nav_seeded = 1 WHERE id = :id", ['id' => $pageId]);
        return $deja === null;
    }

    /**
     * Adresse finale d'un lien.
     */
    public static function resolveUrl(array $item): string {
        if (!empty($item['url'])) {
            return $item['url'];
        }
        if (!empty($item['page_slug'])) {
            return $item['page_slug'] === 'home' ? '/' : '/' . $item['page_slug'];
        }
        return '#';
    }
}
