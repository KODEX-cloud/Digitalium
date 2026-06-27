<?php
namespace App\System\Migrations;

use App\Services\Database;
use App\System\DSMResult;

class ContactMigration implements MigrationInterface {
    public static function getName(): string { return 'contact'; }
    public static function getDescription(): string { return 'Assure la présence des settings de contact et coordonnées'; }

    private static array $contactDefaults = [
        'site_phone'         => '',
        'site_email'         => 'contact@digitaliumgroup.com',
        'site_address'       => '',
        'site_whatsapp'      => '',
        'contact_hours'      => 'Lun – Ven : 8h – 18h',
        'contact_map_embed'  => '',
    ];

    public static function run(): array {
        $t       = DSMResult::timer();
        $created = 0;
        $errors  = [];

        foreach (self::$contactDefaults as $key => $value) {
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

        $msg    = $created > 0 ? "Contact: {$created} setting(s) créé(s)" : "Contact: settings déjà présents";
        $status = empty($errors) ? 'ok' : 'warning';
        return DSMResult::make($status, self::getName(), $msg, ['created' => $created], $errors, DSMResult::elapsed($t));
    }
}
