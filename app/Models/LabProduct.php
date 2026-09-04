<?php
namespace App\Models;

use App\Services\Database;

/**
 * Produits Digitalium Labs — logiciels, SaaS et prototypes propriétaires.
 *
 * ── Pourquoi une table dédiée et non `projects` ─────────────────────────────
 * `projects` est le catalogue des RÉALISATIONS, publié sur /realisations : du
 * travail livré pour un client. Un produit Labs appartient à Digitalium, suit
 * un cycle de vie (idée → disponible) et n'a pas de client. Les mélanger ferait
 * apparaître les produits comme des réalisations clientes sur une page que le
 * cahier des charges demande de ne pas modifier, et obligerait `projects` à
 * porter un cycle de vie qui n'a aucun sens pour une mission terminée.
 *
 * ── Deux « statuts » à ne pas confondre ─────────────────────────────────────
 * Le cahier des charges emploie le mot « statut » pour le cycle de vie. Or
 * `status` désigne partout ailleurs dans ce projet la PUBLICATION
 * (draft/published). Ici :
 *   `stage`  = où en est le produit  (idee, prototype, developpement, beta, disponible)
 *   `status` = est-il visible en ligne (draft, published)
 * Un produit peut donc être « disponible » et pourtant hors ligne, ou « idée »
 * et déjà annoncé — ce sont deux décisions distinctes.
 *
 * Aucun produit n'est semé par le code : la grille reste vide tant que
 * l'administration n'en a pas saisi, plutôt que d'afficher un produit inventé.
 */
class LabProduct extends Model {
    protected static string $table = 'lab_products';

    /** Cycle de vie, dans l'ordre de maturité croissante. */
    public const STAGES = [
        'idee'          => 'Idée',
        'prototype'     => 'Prototype',
        'developpement' => 'En développement',
        'beta'          => 'Bêta',
        'disponible'    => 'Disponible',
    ];

    /** Champs acceptés en écriture. */
    private const CHAMPS = [
        'name', 'slug', 'tagline', 'description', 'sector', 'stage',
        'logo', 'main_image', 'technologies', 'external_link', 'availability',
        'sort_order', 'is_featured', 'status', 'meta_title', 'meta_description',
    ];

    private static ?array $colonnes = null;

    /**
     * Colonnes réellement présentes. Comme pour Post, l'écriture s'y adapte :
     * tant que la migration n'a pas tourné, l'enregistrement reste possible et
     * ignore les champs absents au lieu d'échouer sur un SQL invalide.
     */
    private static function colonnesExistantes(): array {
        if (self::$colonnes === null) {
            $rows = Database::fetchAll("SHOW COLUMNS FROM " . static::$table);
            self::$colonnes = $rows ? array_column($rows, 'Field') : [];
        }
        return self::$colonnes;
    }

    public static function libelleStage(?string $stage): string {
        return self::STAGES[(string)$stage] ?? '';
    }

    public static function findBySlug(string $slug): ?array {
        if (trim($slug) === '') { return null; }
        return Database::fetch(
            "SELECT * FROM " . static::$table . " WHERE slug = :s LIMIT 1", ['s' => $slug]
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  LECTURE PUBLIQUE
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Produits visibles en ligne.
     *
     * L'ordre manuel prime, puis les produits mis en avant, puis les plus
     * récents : l'administration garde la main sans avoir à tout renuméroter.
     *
     * @param int  $limit        0 = tous
     * @param bool $featuredOnly n'afficher que les produits mis en avant
     * @param string $stage      ne garder qu'une étape du cycle de vie
     */
    public static function getPublic(int $limit = 0, bool $featuredOnly = false, string $stage = ''): array {
        $where  = ["status = 'published'"];
        $params = [];

        if ($featuredOnly) { $where[] = 'is_featured = 1'; }
        if (isset(self::STAGES[$stage])) {
            $where[] = 'stage = :st';
            $params['st'] = $stage;
        }

        // Borne injectée en clair après plafonnement : PDO tourne sans émulation
        // des requêtes préparées, où MySQL refuse un paramètre lié dans LIMIT.
        $limite = ($limit > 0) ? ' LIMIT ' . max(1, min(100, $limit)) : '';

        return Database::fetchAll(
            "SELECT * FROM " . static::$table . " WHERE " . implode(' AND ', $where)
            . " ORDER BY sort_order ASC, is_featured DESC, id DESC" . $limite,
            $params
        );
    }

    /** Étapes réellement utilisées par des produits en ligne, dans l'ordre du cycle. */
    public static function stagesUtilises(): array {
        $rows = Database::fetchAll(
            "SELECT DISTINCT stage FROM " . static::$table . " WHERE status = 'published'"
        );
        $presents = array_filter(array_column($rows, 'stage'));
        $ordonnes = [];
        foreach (self::STAGES as $cle => $libelle) {
            if (in_array($cle, $presents, true)) { $ordonnes[$cle] = $libelle; }
        }
        return $ordonnes;
    }

    /** Découpe une liste saisie avec des virgules ou des barres verticales. */
    public static function toList(?string $brut): array {
        $brut = trim((string)$brut);
        if ($brut === '') { return []; }
        $parts = preg_split('/\s*[|,]\s*/', $brut) ?: [];
        return array_values(array_filter(array_map('trim', $parts), static fn($v) => $v !== ''));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  ÉCRITURE
    // ─────────────────────────────────────────────────────────────────────────

    private static function normaliser(array $d): array {
        $d['name']  = trim((string)($d['name'] ?? ''));
        $d['slug']  = self::slugUnique(
            self::slugify(trim((string)($d['slug'] ?? '')) ?: $d['name']),
            (int)($d['id'] ?? 0)
        );
        $d['stage']       = isset(self::STAGES[$d['stage'] ?? '']) ? $d['stage'] : 'idee';
        $d['status']      = ($d['status'] ?? '') === 'published' ? 'published' : 'draft';
        $d['is_featured'] = (int)($d['is_featured'] ?? 0) === 1 ? 1 : 0;
        $d['sort_order']  = max(0, min(9999, (int)($d['sort_order'] ?? 0)));
        $d['meta_title']  = trim((string)($d['meta_title'] ?? '')) ?: $d['name'];
        unset($d['id']);
        return $d;
    }

    private static function retenir(array $d): array {
        $colonnes = self::colonnesExistantes();
        $out = [];
        foreach (self::CHAMPS as $champ) {
            if (!in_array($champ, $colonnes, true) || !array_key_exists($champ, $d)) { continue; }
            $out[$champ] = $d[$champ];
        }
        return $out;
    }

    public static function add(array $data): string {
        $valeurs = self::retenir(self::normaliser($data));
        if (!$valeurs) { return '0'; }
        $cols = array_keys($valeurs);
        return Database::insert(
            "INSERT INTO " . static::$table . " (`" . implode('`, `', $cols) . "`)"
            . " VALUES (:" . implode(', :', $cols) . ")",
            $valeurs
        );
    }

    public static function updateProduct(int $id, array $data): bool {
        if (!self::find($id)) { return false; }
        $data['id'] = $id;
        $valeurs = self::retenir(self::normaliser($data));
        if (!$valeurs) { return false; }

        $sets = [];
        foreach (array_keys($valeurs) as $col) { $sets[] = "`$col` = :$col"; }
        $valeurs['id'] = $id;

        Database::query(
            "UPDATE " . static::$table . " SET " . implode(', ', $sets) . " WHERE id = :id",
            $valeurs
        );
        return true;
    }

    /**
     * Slug libre. Deux produits ne peuvent pas partager la même ancre :
     * le lien profond « #produit-x » désignerait alors deux cartes.
     */
    private static function slugUnique(string $slug, int $exclureId = 0): string {
        $base = $slug;
        $n = 2;
        while (true) {
            $existant = Database::fetch(
                "SELECT id FROM " . static::$table . " WHERE slug = :s LIMIT 1", ['s' => $slug]
            );
            if (!$existant || (int)$existant['id'] === $exclureId) { return $slug; }
            $slug = $base . '-' . $n++;
            if ($n > 50) { return $base . '-' . time(); }
        }
    }

    public static function slugify(string $text): string {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $translit = @iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        if ($translit !== false) { $text = $translit; }
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        return strtolower($text) ?: 'produit';
    }
}
