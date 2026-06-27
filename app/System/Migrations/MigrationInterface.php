<?php
namespace App\System\Migrations;

interface MigrationInterface {
    /**
     * Execute this migration.
     * Return a DSMResult-compatible array.
     */
    public static function run(): array;

    /** Human-readable migration name. */
    public static function getName(): string;

    /** One-line description of what this migration does. */
    public static function getDescription(): string;
}
