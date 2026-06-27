<?php
namespace App\System;

class ConfigManager {
    private static array $requiredKeys = ['APP_ENV', 'DB_HOST', 'DB_NAME', 'DB_USER', 'APP_SECRET'];

    public static function check(): array {
        $t       = DSMResult::timer();
        $envFile = ROOT_PATH . '/.env';
        $errors  = [];

        if (!file_exists($envFile)) {
            return DSMResult::warning('Config', '.env absent — valeurs par défaut en place',
                ['env_file' => false, 'using_defaults' => true], [], DSMResult::elapsed($t));
        }

        $lines   = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $defined = [];

        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) continue;
            if (str_contains($line, '=')) {
                [$key] = explode('=', $line, 2);
                $defined[] = trim($key);
            }
        }

        foreach (self::$requiredKeys as $key) {
            if (!in_array($key, $defined)) {
                $errors[] = "Clé manquante dans .env: {$key}";
            }
        }

        $env = defined('ENVIRONMENT') ? ENVIRONMENT : 'unknown';

        if (!empty($errors)) {
            return DSMResult::warning('Config', "Certaines clés .env absentes", ['env' => $env, 'defined' => $defined], $errors, DSMResult::elapsed($t));
        }

        // Warn if APP_SECRET is the default value
        if (defined('APP_SECRET') && APP_SECRET === '4f923b784a92c0199e8293e9da2884a0d927d6d5ef18c7c90b0be83984d76fe2') {
            $errors[] = 'APP_SECRET utilise la valeur par défaut — générer une clé unique en production';
            return DSMResult::warning('Config', "APP_SECRET par défaut détecté — À changer en production",
                ['env' => $env], $errors, DSMResult::elapsed($t));
        }

        return DSMResult::ok('Config', ".env valide — ENV: {$env}",
            ['env' => $env, 'defined_keys' => count($defined)],
            DSMResult::elapsed($t)
        );
    }

    public static function get(string $key, mixed $default = null): mixed {
        if (defined($key)) return constant($key);

        $envFile = ROOT_PATH . '/.env';
        if (!file_exists($envFile)) return $default;

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) continue;
            if (str_contains($line, '=')) {
                [$k, $v] = explode('=', $line, 2);
                if (trim($k) === $key) return trim($v);
            }
        }
        return $default;
    }
}
