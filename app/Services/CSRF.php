<?php
namespace App\Services;

class CSRF {
    /**
     * Retrieve the current CSRF token from the session, initializing it if absent.
     */
    public static function getToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Verify the supplied token against the session token in a timing-attack-safe manner.
     */
    public static function validate(?string $token): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $sessionToken = $_SESSION['csrf_token'] ?? '';
        if (empty($sessionToken) || empty($token)) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Generate a hidden HTML input field containing the current CSRF token.
     */
    public static function field(): string {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::getToken()) . '">';
    }
}
