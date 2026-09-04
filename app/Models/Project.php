<?php
namespace App\Models;

use App\Services\Database;

class Project extends Model {
    protected static string $table = 'projects';

    /**
     * Ensure the projects table exists in the database.
     */
    public static function checkTableExists(): void {
        try {
            Database::getConnection()->query("SELECT 1 FROM `projects` LIMIT 1");
        } catch (\Throwable $e) {
            $sql = "CREATE TABLE IF NOT EXISTS `projects` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `title` VARCHAR(255) NOT NULL,
              `category` VARCHAR(100) NOT NULL,
              `logo` VARCHAR(255) NULL,
              `main_image` VARCHAR(255) NOT NULL,
              `gallery` TEXT NULL,
              `context` TEXT NULL,
              `impact` TEXT NULL,
              `technologies` VARCHAR(255) NULL,
              `external_link` VARCHAR(255) NULL,
              `sort_order` INT DEFAULT 0,
              `is_featured` TINYINT DEFAULT 0,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            Database::getConnection()->exec($sql);
        }
    }

    /**
     * Get all records from the table, ensuring the table exists.
     */
    public static function all(string $orderBy = 'id ASC'): array {
        self::checkTableExists();
        return parent::all($orderBy);
    }

    /**
     * Find a record, ensuring the table exists.
     */
    public static function find(int $id): ?array {
        self::checkTableExists();
        return parent::find($id);
    }

    /**
     * Delete a record, ensuring the table exists.
     */
    public static function delete(int $id): bool {
        self::checkTableExists();
        return parent::delete($id);
    }

    /**
     * Champs éditables d'une réalisation, avec leur valeur par défaut.
     *
     * Une seule liste alimente l'insertion ET la mise à jour : ajouter une
     * colonne se fait ici, et nulle part ailleurs. `context` porte le problème
     * rencontré et `impact` les résultats obtenus — ces colonnes existaient
     * déjà, les dupliquer aurait été un doublon (Règle #1).
     */
    private const FIELDS = [
        'title'              => '',
        'slug'               => '',
        'status'             => 'draft',
        'category'           => '',
        'sector'             => null,
        'client'             => null,
        'year'               => null,
        'project_date'       => null,
        'description'        => null,
        'logo'               => null,
        'main_image'         => null,
        'gallery'            => null,
        'context'            => null,   // Le problème
        'objectives'         => null,
        'solution'           => null,
        'technologies'       => null,
        'features'           => null,
        'impact'             => null,   // Les résultats
        'testimonial_quote'  => null,
        'testimonial_author' => null,
        'testimonial_role'   => null,
        'external_link'      => null,
        'meta_title'         => null,
        'meta_description'   => null,
        'sort_order'         => 0,
        'is_featured'        => 0,
    ];

    /** Construit le jeu de paramètres à partir des champs déclarés. */
    private static function bind(array $data): array {
        $params = [];
        foreach (self::FIELDS as $field => $default) {
            $value = $data[$field] ?? $default;
            if (in_array($field, ['sort_order', 'is_featured'], true)) {
                $value = (int)$value;
            } elseif (in_array($field, ['project_date'], true)) {
                $value = $value ?: null;   // Une date vide doit rester NULL
            }
            $params[$field] = $value;
        }
        $params['slug']   = $params['slug'] !== '' ? $params['slug'] : self::slugify((string)$params['title']);
        $params['status'] = $params['status'] === 'published' ? 'published' : 'draft';
        return $params;
    }

    /**
     * Add a new project to the portfolio database.
     */
    public static function add(array $data): string {
        self::checkTableExists();
        $params  = self::bind($data);
        $columns = implode(', ', array_keys($params));
        $holders = ':' . implode(', :', array_keys($params));
        return Database::insert(
            "INSERT INTO " . static::$table . " ($columns) VALUES ($holders)",
            $params
        );
    }

    /**
     * Update an existing project in the database.
     */
    public static function updateProject(int $id, array $data): bool {
        self::checkTableExists();
        $params = self::bind($data);
        $set    = [];
        foreach (array_keys($params) as $field) {
            $set[] = "$field = :$field";
        }
        $params['id'] = $id;
        Database::query(
            "UPDATE " . static::$table . " SET " . implode(', ', $set) . " WHERE id = :id",
            $params
        );
        return true;
    }

    /**
     * Find project by slug.
     */
    public static function findBySlug(string $slug): ?array {
        self::checkTableExists();
        return Database::fetch("SELECT * FROM " . static::$table . " WHERE slug = :slug LIMIT 1", ['slug' => $slug]);
    }

    /**
     * Condition de publication.
     *
     * La colonne `status` a été ajoutée après coup : les lignes antérieures ont
     * NULL. Les traiter comme des brouillons ferait disparaître du contenu déjà
     * en ligne, donc NULL vaut « publié ».
     */
    private const PUBLISHED = "(status = 'published' OR status IS NULL OR status = '')";

    /**
     * Get published/featured projects for frontend.
     */
    public static function getFeatured(): array {
        self::checkTableExists();
        return Database::fetchAll(
            "SELECT * FROM " . static::$table . " WHERE is_featured = 1 AND " . self::PUBLISHED . "
             ORDER BY sort_order ASC, id DESC"
        );
    }

    /**
     * Get all published projects ordered for frontend display.
     */
    public static function getPublic(string $category = ''): array {
        self::checkTableExists();
        if ($category !== '') {
            return Database::fetchAll(
                "SELECT * FROM " . static::$table . " WHERE category = :cat AND " . self::PUBLISHED . "
                 ORDER BY sort_order ASC, id DESC",
                ['cat' => $category]
            );
        }
        return Database::fetchAll(
            "SELECT * FROM " . static::$table . " WHERE " . self::PUBLISHED . " ORDER BY sort_order ASC, id DESC"
        );
    }

    /**
     * Get distinct categories for filter UI — publiées uniquement, sinon un
     * filtre pourrait ne renvoyer aucun résultat.
     */
    public static function getCategories(): array {
        self::checkTableExists();
        $rows = Database::fetchAll(
            "SELECT DISTINCT category FROM " . static::$table . "
             WHERE category IS NOT NULL AND category != '' AND " . self::PUBLISHED . "
             ORDER BY category ASC"
        );
        return array_column($rows, 'category');
    }

    /** Retrouve une réalisation publiée par son slug. */
    public static function findPublishedBySlug(string $slug): ?array {
        self::checkTableExists();
        return Database::fetch(
            "SELECT * FROM " . static::$table . " WHERE slug = :slug AND " . self::PUBLISHED . " LIMIT 1",
            ['slug' => $slug]
        );
    }

    /**
     * Découpe une valeur saisie en liste : une entrée par ligne, ou séparée
     * par « | ». Sert aux technologies, objectifs, fonctionnalités, résultats.
     */
    public static function toList(?string $raw): array {
        if ($raw === null || trim($raw) === '') { return []; }
        $parts = preg_split('/\r\n|\r|\n|\|/', $raw);
        return array_values(array_filter(array_map('trim', $parts), fn($v) => $v !== ''));
    }

    /**
     * Slugify helper.
     */
    public static function slugify(string $text): string {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        return strtolower($text) ?: 'projet';
    }
}
