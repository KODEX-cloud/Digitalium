<?php
namespace App\System;

use App\System\Migrations\SettingsMigration;
use App\System\Migrations\HeaderMigration;
use App\System\Migrations\FooterMigration;
use App\System\Migrations\MenuMigration;
use App\System\Migrations\HeroMigration;
use App\System\Migrations\BlogMigration;
use App\System\Migrations\ServicesMigration;
use App\System\Migrations\ProjectsMigration;
use App\System\Migrations\SeoMigration;
use App\System\Migrations\ContactMigration;

class BusinessMigrationManager {
    /**
     * Registry of all available business migrations.
     * Key = migration name, Value = class FQCN.
     */
    private static array $registry = [
        'settings' => SettingsMigration::class,
        'header'   => HeaderMigration::class,
        'footer'   => FooterMigration::class,
        'menu'     => MenuMigration::class,
        'hero'     => HeroMigration::class,
        'blog'     => BlogMigration::class,
        'services' => ServicesMigration::class,
        'projects' => ProjectsMigration::class,
        'seo'      => SeoMigration::class,
        'contact'  => ContactMigration::class,
    ];

    /**
     * Run all migrations or a specific one.
     */
    public static function run(?string $name = null): array {
        $t       = DSMResult::timer();
        $results = [];

        $toRun = $name ? [$name => self::$registry[$name] ?? null] : self::$registry;

        foreach ($toRun as $key => $class) {
            if ($class === null) {
                $results[] = DSMResult::error($key, "Migration '{$key}' introuvable dans le registre");
                continue;
            }

            try {
                $results[] = $class::run();
            } catch (\Exception $e) {
                $results[] = DSMResult::error($key, "Exception: " . $e->getMessage());
            }
        }

        return DSMResult::aggregate('Business Migrations', $results);
    }

    /**
     * Return the registry for display.
     */
    public static function list(): array {
        $list = [];
        foreach (self::$registry as $name => $class) {
            $list[] = [
                'name'        => $name,
                'class'       => $class,
                'description' => method_exists($class, 'getDescription') ? $class::getDescription() : '',
            ];
        }
        return $list;
    }

    /**
     * Register a new migration at runtime (extensible).
     */
    public static function register(string $name, string $class): void {
        self::$registry[$name] = $class;
    }
}
