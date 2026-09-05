<?php
namespace App\Models;

use App\Services\Database;

/**
 * Menus de navigation.
 *
 * Un menu est identifié par son EMPLACEMENT, pas par son identifiant : le
 * frontend demande « le menu du pied de page », jamais « le menu numéro 3 ».
 * Supprimer puis recréer un menu ne casse donc rien, tant que l'emplacement est
 * le même.
 */
class Menu extends Model {
    protected static string $table = 'menus';

    /**
     * Emplacements réellement câblés dans le gabarit du site.
     *
     * Un menu peut porter un emplacement absent de cette liste : ce sera un
     * menu secondaire, administrable mais rendu nulle part tant qu'on ne
     * l'appelle pas. L'écran de liste le signale, pour éviter de chercher
     * pourquoi un menu « ne s'affiche pas ».
     */
    public const EMPLACEMENTS = [
        'primary'         => "Navigation de l'en-tête",
        'footer'          => 'Pied de page — colonne Navigation',
        'footer_services' => 'Pied de page — colonne Services',
    ];

    public static function findBySlug(string $slug): ?array {
        return Database::fetch("SELECT * FROM menus WHERE slug = :s LIMIT 1", ['s' => $slug]);
    }

    public static function findByLocation(string $location): ?array {
        if (trim($location) === '') { return null; }
        return Database::fetch(
            "SELECT * FROM menus WHERE location = :l ORDER BY id ASC LIMIT 1", ['l' => $location]
        );
    }

    /** Un menu est-il rendu quelque part sur le site ? */
    public static function estCable(string $location): bool {
        return isset(self::EMPLACEMENTS[$location]);
    }

    public static function libelleEmplacement(string $location): string {
        return self::EMPLACEMENTS[$location] ?? 'Menu secondaire';
    }

    public static function create(string $name, string $location = 'primary'): string {
        $slug = self::slugify($name);
        if (self::findBySlug($slug)) { $slug .= '-' . time(); }
        return Database::insert(
            "INSERT INTO menus (name, slug, location) VALUES (:name, :slug, :location)",
            ['name' => $name, 'slug' => $slug, 'location' => $location]
        );
    }

    /**
     * Renvoie le menu d'un emplacement, en le créant s'il n'existe pas.
     * Utilisé par les migrations : idempotent par construction.
     */
    public static function ensure(string $location, string $name): array {
        $menu = self::findByLocation($location);
        if ($menu) { return $menu; }
        self::create($name, $location);
        return self::findByLocation($location) ?? [];
    }

    public static function update(int $id, string $name, string $location): bool {
        Database::query(
            "UPDATE menus SET name = :name, location = :location WHERE id = :id",
            ['name' => $name, 'location' => $location, 'id' => $id]
        );
        return true;
    }

    public static function slugify(string $text): string {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $translit = @iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        if ($translit !== false) { $text = $translit; }
        $text = preg_replace('~[^-\w]+~', '', $text);
        return strtolower(trim($text, '-')) ?: 'menu';
    }
}
