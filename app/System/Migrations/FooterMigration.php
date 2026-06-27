<?php
namespace App\System\Migrations;

use App\Services\Database;
use App\System\DSMResult;

class FooterMigration implements MigrationInterface {
    public static function getName(): string { return 'footer'; }
    public static function getDescription(): string { return 'Assure la présence des settings footer avec valeurs par défaut'; }

    private static array $footerDefaults = [
        'footer_slogan'      => 'Votre partenaire digital de confiance',
        'footer_cta_label'   => 'Démarrer un projet',
        'footer_cta_url'     => '/contact',
        'footer_copyright'   => null, // dynamique
        'footer_legal_label' => 'Mentions légales',
        'footer_legal_url'   => '/mentions-legales',
        'site_facebook'      => '',
        'site_instagram'     => '',
        'site_linkedin'      => '',
        'site_twitter'       => '',
        'site_youtube'       => '',
        'site_tiktok'        => '',
    ];

    public static function run(): array {
        $t       = DSMResult::timer();
        $created = 0;
        $errors  = [];

        foreach (self::$footerDefaults as $key => $value) {
            if ($key === 'footer_copyright') {
                $value = '© ' . date('Y') . ' Digitalium Group. Tous droits réservés.';
            }

            try {
                $existing = Database::fetch("SELECT id FROM settings WHERE setting_key = :k", ['k' => $key]);
                if (!$existing) {
                    Database::query(
                        "INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v)",
                        ['k' => $key, 'v' => $value ?? '']
                    );
                    $created++;
                }
            } catch (\Exception $e) {
                $errors[] = "Erreur sur {$key}: " . $e->getMessage();
            }
        }

        $msg    = $created > 0 ? "Footer: {$created} setting(s) créé(s)" : "Footer: settings déjà présents";
        $status = empty($errors) ? 'ok' : 'warning';
        return DSMResult::make($status, self::getName(), $msg, ['created' => $created], $errors, DSMResult::elapsed($t));
    }
}
