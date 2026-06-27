<?php
namespace App\System\Migrations;

use App\Services\Database;
use App\System\DSMResult;

class HeaderMigration implements MigrationInterface {
    public static function getName(): string { return 'header'; }
    public static function getDescription(): string { return 'Assure la présence des settings header avec valeurs par défaut'; }

    private static array $headerDefaults = [
        'logo_desktop'       => '',
        'logo_mobile'        => '',
        'logo_height_desktop'=> '48',
        'logo_height_mobile' => '36',
        'header_cta_label'   => 'Nous contacter',
        'header_cta_url'     => '/contact',
        'header_transparent' => '1',
        'header_bg_color'    => 'rgba(255,255,255,0)',
        'header_shadow'      => '1',
    ];

    public static function run(): array {
        $t       = DSMResult::timer();
        $created = 0;
        $errors  = [];

        foreach (self::$headerDefaults as $key => $value) {
            try {
                $existing = Database::fetch("SELECT id FROM settings WHERE setting_key = :k", ['k' => $key]);
                if (!$existing) {
                    Database::query("INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v)", ['k' => $key, 'v' => $value]);
                    $created++;
                }
            } catch (\Exception $e) {
                $errors[] = "Erreur sur {$key}: " . $e->getMessage();
            }
        }

        $msg    = $created > 0 ? "Header: {$created} setting(s) créé(s)" : "Header: settings déjà présents";
        $status = empty($errors) ? 'ok' : 'warning';
        return DSMResult::make($status, self::getName(), $msg, ['created' => $created], $errors, DSMResult::elapsed($t));
    }
}
