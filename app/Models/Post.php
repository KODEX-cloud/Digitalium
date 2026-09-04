<?php
namespace App\Models;

use App\Services\Database;

/**
 * Articles du centre de ressources (/insights).
 *
 * Une seule table, `blog_posts`, sert à la fois les analyses et les contenus
 * stratégiques (guides, rapports, checklists…) : ces derniers se distinguent
 * par `resource_type`. Créer une seconde table aurait dédoublé l'éditeur, la
 * médiathèque, les catégories et le SEO pour un contenu de même nature.
 *
 * ── Colonnes ajoutées par build_insights_page.php ───────────────────────────
 *   reading_time   durée de lecture en minutes (calculée si laissée vide)
 *   sort_order     ordre d'affichage manuel, avant la date
 *   og_image       visuel de partage, distinct de l'image principale
 *   resource_type  guide / rapport / checklist / livre-blanc / cas-usage / comparatif
 *   resource_file  fichier téléchargeable choisi dans la médiathèque
 *   resource_cta   libellé du bouton de téléchargement
 *
 * L'écriture se construit à partir des colonnes RÉELLEMENT présentes : tant que
 * la migration n'a pas tourné, l'enregistrement d'un article reste possible et
 * ignore simplement les champs neufs, au lieu d'échouer sur un SQL invalide.
 */
class Post extends Model {
    protected static string $table = 'blog_posts';

    /** Champs acceptés en écriture, dans l'ordre historique puis les ajouts. */
    private const CHAMPS = [
        'title', 'slug', 'excerpt', 'content', 'featured_image', 'category',
        'author', 'status', 'is_featured', 'meta_title', 'meta_description', 'tags',
        'reading_time', 'sort_order', 'og_image',
        'resource_type', 'resource_file', 'resource_cta',
    ];

    /** Colonnes réellement présentes en base — interrogé une fois par requête HTTP. */
    private static ?array $colonnes = null;

    private static function colonnesExistantes(): array {
        if (self::$colonnes === null) {
            $rows = Database::fetchAll("SHOW COLUMNS FROM blog_posts");
            self::$colonnes = array_column($rows, 'Field');
        }
        return self::$colonnes;
    }

    public static function findBySlug(string $slug): ?array {
        return Database::fetch("SELECT * FROM blog_posts WHERE slug = :s LIMIT 1", ['s' => $slug]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  LECTURE PUBLIQUE
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Fragment ORDER BY commun : l'ordre manuel prime, la date départage.
     * `sort_order` n'existe pas tant que la migration n'a pas tourné.
     */
    private static function ordre(string $prefixe = ''): string {
        $p = $prefixe;
        return in_array('sort_order', self::colonnesExistantes(), true)
            ? "{$p}sort_order DESC, {$p}published_at DESC, {$p}id DESC"
            : "{$p}published_at DESC, {$p}id DESC";
    }

    /** Condition excluant les contenus stratégiques du flux d'articles. */
    private static function sansRessource(string $prefixe = ''): string {
        return in_array('resource_type', self::colonnesExistantes(), true)
            ? " AND ({$prefixe}resource_type IS NULL OR {$prefixe}resource_type = '')"
            : '';
    }

    public static function getPublished(int $limit = 10, int $offset = 0): array {
        $limit  = max(1, min(100, $limit));
        $offset = max(0, $offset);
        return Database::fetchAll(
            "SELECT * FROM blog_posts WHERE status = 'published'" . self::sansRessource()
            . " ORDER BY " . self::ordre() . " LIMIT $limit OFFSET $offset"
        );
    }

    public static function countPublished(): int {
        $row = Database::fetch(
            "SELECT COUNT(*) as total FROM blog_posts WHERE status = 'published'" . self::sansRessource()
        );
        return (int)($row['total'] ?? 0);
    }

    /**
     * Recherche filtrée du centre de ressources.
     *
     * Les bornes LIMIT/OFFSET sont bridées puis injectées en clair : PDO tourne
     * avec ATTR_EMULATE_PREPARES à false, où MySQL refuse un paramètre lié dans
     * une clause LIMIT.
     *
     * @param array $f  category (nom exact), q (texte libre), tag (slug)
     * @return array{0: array, 1: int}  les lignes de la page demandée, et le total
     */
    public static function rechercher(array $f, int $limit = 9, int $offset = 0): array {
        $limit  = max(1, min(100, $limit));
        $offset = max(0, $offset);

        $where  = ["p.status = 'published'"];
        $params = [];
        $join   = '';

        if (in_array('resource_type', self::colonnesExistantes(), true)) {
            $where[] = "(p.resource_type IS NULL OR p.resource_type = '')";
        }

        $cat = trim((string)($f['category'] ?? ''));
        if ($cat !== '') {
            $where[] = 'p.category = :cat';
            $params['cat'] = $cat;
        }

        $q = trim((string)($f['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(p.title LIKE :q OR p.excerpt LIKE :q OR p.category LIKE :q OR p.tags LIKE :q)';
            $params['q'] = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';
        }

        // Article déjà montré en tête de page : on ne le répète pas dans la
        // grille, et le total tient compte de son retrait pour que la
        // pagination reste juste.
        $exclure = array_filter(array_map('intval', (array)($f['exclude'] ?? [])));
        if ($exclure) {
            $where[] = 'p.id NOT IN (' . implode(',', $exclure) . ')';
        }

        $tag = trim((string)($f['tag'] ?? ''));
        if ($tag !== '') {
            $join = ' INNER JOIN blog_post_tags pt ON pt.post_id = p.id'
                  . ' INNER JOIN blog_tags t ON t.id = pt.tag_id';
            $where[] = 't.slug = :tag';
            $params['tag'] = $tag;
        }

        $sqlWhere = ' WHERE ' . implode(' AND ', $where);

        $total = (int)(Database::fetch(
            "SELECT COUNT(DISTINCT p.id) AS n FROM blog_posts p{$join}{$sqlWhere}", $params
        )['n'] ?? 0);

        $rows = Database::fetchAll(
            "SELECT DISTINCT p.* FROM blog_posts p{$join}{$sqlWhere}"
            . " ORDER BY " . self::ordre('p.') . " LIMIT $limit OFFSET $offset",
            $params
        );

        return [$rows, $total];
    }

    public static function getFeatured(int $limit = 3): array {
        $limit = max(1, min(20, $limit));
        return Database::fetchAll(
            "SELECT * FROM blog_posts WHERE status = 'published' AND is_featured = 1"
            . self::sansRessource() . " ORDER BY " . self::ordre() . " LIMIT $limit"
        );
    }

    public static function getByCategory(string $category, int $limit = 10): array {
        $limit = max(1, min(100, $limit));
        return Database::fetchAll(
            "SELECT * FROM blog_posts WHERE status = 'published' AND category = :cat
             ORDER BY " . self::ordre() . " LIMIT $limit",
            ['cat' => $category]
        );
    }

    /**
     * Tout ce qui est publié, articles ET contenus stratégiques confondus.
     * Sert au sitemap : les deux ont une page à /insights/{slug}, les deux
     * doivent être déclarés à l'indexation.
     */
    public static function toutPublie(int $limit = 500): array {
        $limit = max(1, min(2000, $limit));
        return Database::fetchAll(
            "SELECT id, slug, published_at, updated_at, created_at FROM blog_posts
             WHERE status = 'published' ORDER BY " . self::ordre() . " LIMIT $limit"
        );
    }

    /** Contenus stratégiques : tout article portant un `resource_type`. */
    public static function getResources(int $limit = 12): array {
        if (!in_array('resource_type', self::colonnesExistantes(), true)) { return []; }
        $limit = max(1, min(50, $limit));
        return Database::fetchAll(
            "SELECT * FROM blog_posts
             WHERE status = 'published' AND resource_type IS NOT NULL AND resource_type <> ''
             ORDER BY " . self::ordre() . " LIMIT $limit"
        );
    }

    /** Articles proches : même catégorie d'abord, complétés par les plus récents. */
    public static function similaires(int $id, string $category, int $limit = 3): array {
        $limit = max(1, min(12, $limit));

        $rows = [];
        if (trim($category) !== '') {
            $rows = Database::fetchAll(
                "SELECT * FROM blog_posts
                 WHERE status = 'published' AND category = :cat AND id <> :id" . self::sansRessource()
                . " ORDER BY " . self::ordre() . " LIMIT $limit",
                ['cat' => $category, 'id' => $id]
            );
        }
        if (count($rows) >= $limit) { return $rows; }

        $exclus = array_map(static fn($r) => (int)$r['id'], $rows);
        $exclus[] = $id;
        $manque = $limit - count($rows);
        $reste = Database::fetchAll(
            "SELECT * FROM blog_posts
             WHERE status = 'published' AND id NOT IN (" . implode(',', $exclus) . ")"
            . self::sansRessource() . " ORDER BY " . self::ordre() . " LIMIT $manque"
        );
        return array_merge($rows, $reste);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  DURÉE DE LECTURE
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Durée de lecture affichée : la valeur saisie en admin si elle existe,
     * sinon une estimation à 200 mots/minute. Rien n'est inventé — c'est un
     * comptage sur le texte réel de l'article, que la rédaction peut corriger.
     */
    public static function dureeLecture(array $post): int {
        $saisie = (int)($post['reading_time'] ?? 0);
        if ($saisie > 0) { return $saisie; }

        $texte = strip_tags((string)($post['content'] ?? ''));
        $texte = html_entity_decode($texte, ENT_QUOTES, 'UTF-8');
        $mots  = (int)preg_match_all('/[\p{L}\p{N}]+/u', $texte);
        if ($mots < 1) { return 0; }
        return max(1, (int)ceil($mots / 200));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  ÉCRITURE
    // ─────────────────────────────────────────────────────────────────────────

    /** Ne garde que les champs connus ET réellement présents en base. */
    private static function retenir(array $data): array {
        $colonnes = self::colonnesExistantes();
        $out = [];
        foreach (self::CHAMPS as $champ) {
            if (!in_array($champ, $colonnes, true) || !array_key_exists($champ, $data)) { continue; }
            $out[$champ] = $data[$champ];
        }
        return $out;
    }

    public static function create(array $data): string {
        $data = self::normaliser($data);

        $valeurs = self::retenir($data);
        if (!$valeurs) { return '0'; }

        // La date de publication est décidée ici : une date saisie en admin fait
        // foi, sinon l'instant de la mise en ligne. Un brouillon n'en a pas.
        $publie = self::datePublication($data);
        if ($publie !== null) {
            $valeurs['published_at'] = $publie;
        }

        $cols = array_keys($valeurs);
        $sql  = "INSERT INTO blog_posts (`" . implode('`, `', $cols) . "`)"
              . " VALUES (:" . implode(', :', $cols) . ")";
        return Database::insert($sql, $valeurs);
    }

    public static function update(int $id, array $data): bool {
        $existant = self::find($id);
        if (!$existant) { return false; }

        $data    = self::normaliser($data);
        $valeurs = self::retenir($data);

        // La date n'est écrasée que si l'admin en a fourni une, ou si l'article
        // passe publié sans en avoir jamais eu. Republier ne rajeunit pas un
        // article déjà daté.
        $saisie = self::datePublication($data, true);
        if ($saisie !== null) {
            $valeurs['published_at'] = $saisie;
        } elseif ($data['status'] === 'published' && empty($existant['published_at'])) {
            $valeurs['published_at'] = date('Y-m-d H:i:s');
        }

        if (!$valeurs) { return false; }

        $sets = [];
        foreach (array_keys($valeurs) as $col) { $sets[] = "`$col` = :$col"; }
        $valeurs['id'] = $id;

        Database::query("UPDATE blog_posts SET " . implode(', ', $sets) . " WHERE id = :id", $valeurs);
        return true;
    }

    /** Valeurs par défaut et bornes communes à la création et à la mise à jour. */
    private static function normaliser(array $data): array {
        $data['title']       = trim((string)($data['title'] ?? ''));
        $data['slug']        = self::slugify(trim((string)($data['slug'] ?? '')) ?: $data['title']);
        $data['author']      = trim((string)($data['author'] ?? '')) ?: 'Équipe Digitalium';
        $data['status']      = ($data['status'] ?? '') === 'published' ? 'published' : 'draft';
        $data['is_featured'] = (int)($data['is_featured'] ?? 0) === 1 ? 1 : 0;
        $data['meta_title']  = trim((string)($data['meta_title'] ?? '')) ?: $data['title'];

        if (array_key_exists('reading_time', $data)) {
            $data['reading_time'] = max(0, min(999, (int)$data['reading_time']));
        }
        if (array_key_exists('sort_order', $data)) {
            $data['sort_order'] = max(0, min(9999, (int)$data['sort_order']));
        }
        return $data;
    }

    /**
     * Normalise la date de publication saisie en admin (champ datetime-local).
     * Retourne null si rien n'a été saisi ; en création, un article publié sans
     * date reçoit l'instant courant.
     */
    private static function datePublication(array $data, bool $miseAJour = false): ?string {
        $brut = trim((string)($data['published_at'] ?? ''));
        if ($brut !== '') {
            $ts = strtotime(str_replace('T', ' ', $brut));
            if ($ts !== false) { return date('Y-m-d H:i:s', $ts); }
        }
        if (!$miseAJour && ($data['status'] ?? '') === 'published') {
            return date('Y-m-d H:i:s');
        }
        return null;
    }

    public static function slugify(string $text): string {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $translit = @iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        if ($translit !== false) { $text = $translit; }
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        return strtolower($text) ?: 'post';
    }
}
