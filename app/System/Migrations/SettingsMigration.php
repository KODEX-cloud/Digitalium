<?php
namespace App\System\Migrations;

use App\Services\Database;
use App\System\DSMResult;

class SettingsMigration implements MigrationInterface {
    public static function getName(): string { return 'settings'; }
    public static function getDescription(): string { return 'Assure la présence des settings clés avec valeurs par défaut'; }

    private static array $defaults = [
        'site_name'          => 'Digitalium Group',
        'site_email'         => 'contact@digitaliumgroup.com',
        'site_phone'         => '',
        'site_address'       => '',
        'site_whatsapp'      => '',
        'logo_desktop'       => '',
        'logo_mobile'        => '',
        'favicon'            => '',
        'footer_copyright'   => '© ' . null . ' Digitalium Group. Tous droits réservés.',
        'footer_slogan'      => '',
        'footer_cta_label'   => 'Démarrer un projet',
        'footer_cta_url'     => '/contact',
        'header_cta_label'   => 'Contact',
        'header_cta_url'     => '/contact',
        'meta_title_default' => 'Digitalium Group',
        'meta_description_default' => 'CMS Enterprise Digitalium Group',
        'color_primary'      => '#2563eb',
        'color_accent'       => '#f59e0b',
        'color_text_main'    => '#0f172a',
        'color_text_muted'   => '#64748b',
        'color_bg_base'      => '#ffffff',
    ];

    public static function run(): array {
        $t       = DSMResult::timer();
        $created = 0;
        $skipped = 0;
        $errors  = [];

        foreach (self::$defaults as $key => $value) {
            // Replace null placeholder for copyright year
            if ($key === 'footer_copyright') {
                $value = '© ' . date('Y') . ' Digitalium Group. Tous droits réservés.';
            }

            try {
                $existing = Database::fetch("SELECT id FROM settings WHERE setting_key = :k", ['k' => $key]);
                if (!$existing) {
                    Database::query(
                        "INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v)",
                        ['k' => $key, 'v' => $value]
                    );
                    $created++;
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $errors[] = "Erreur sur {$key}: " . $e->getMessage();
            }
        }

        $msg    = "Settings: {$created} créé(s), {$skipped} déjà présent(s)";
        $status = empty($errors) ? 'ok' : 'warning';

        return DSMResult::make($status, self::getName(), $msg,
            ['created' => $created, 'skipped' => $skipped],
            $errors,
            DSMResult::elapsed($t)
        );
    }
}
