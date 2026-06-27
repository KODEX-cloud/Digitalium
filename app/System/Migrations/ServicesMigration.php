<?php
namespace App\System\Migrations;

use App\Services\Database;
use App\System\DSMResult;

class ServicesMigration implements MigrationInterface {
    public static function getName(): string { return 'services'; }
    public static function getDescription(): string { return 'Assure les clés de configuration des services dans les settings'; }

    private static array $serviceDefaults = [
        'services_primary_list' => 'Web Design & Développement|Maintenance Informatique|Câblage Réseaux|Vidéo TikTok/YouTube|Contenu Digital|IA & Automatisation|Audit Numérique',
        'services_extra_list'   => 'Web Design|Maintenance TI|Câblage Réseau|Vidéo TikTok/YT|Contenu Digital|IA & Stratégie',
    ];

    public static function run(): array {
        $t       = DSMResult::timer();
        $created = 0;
        $errors  = [];

        foreach (self::$serviceDefaults as $key => $value) {
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

        $msg    = $created > 0 ? "Services: {$created} clé(s) créée(s)" : "Services: clés déjà présentes";
        $status = empty($errors) ? 'ok' : 'warning';
        return DSMResult::make($status, self::getName(), $msg, ['created' => $created], $errors, DSMResult::elapsed($t));
    }
}
