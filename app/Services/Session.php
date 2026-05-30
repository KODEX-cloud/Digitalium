<?php
namespace App\Services;

class Session {
    /**
     * Start the secure session.
     */
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
            
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path' => '/',
                'domain' => '',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);

            session_start();
        }

        self::validateSession();
    }

    /**
     * Check if session metadata matches signature.
     */
    private static function validateSession(): void {
        if (!isset($_SESSION['initiated'])) {
            self::regenerate();
            $_SESSION['initiated'] = true;
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
        } else {
            if ($_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '') ||
                $_SESSION['ip_address'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
                self::destroy();
                self::start();
            }
        }
    }

    /**
     * Regenerate session ID safely.
     */
    public static function regenerate(): void {
        session_regenerate_id(true);
    }

    /**
     * Set a session variable.
     */
    public static function set(string $key, mixed $value): void {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session variable.
     */
    public static function get(string $key, mixed $default = null): mixed {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session variable exists.
     */
    public static function has(string $key): bool {
        return isset($_SESSION[$key]);
    }

    /**
     * Delete a session variable.
     */
    public static function remove(string $key): void {
        unset($_SESSION[$key]);
    }

    /**
     * Destroy session completely.
     */
    public static function destroy(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Set a flash message for the next request.
     */
    public static function setFlash(string $type, string $message): void {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Get flash messages and delete them.
     */
    public static function getFlash(string $type): ?string {
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        return null;
    }

    /**
     * Check if flash messages exist.
     */
    public static function hasFlash(): bool {
        return !empty($_SESSION['flash']);
    }

    /**
     * Get all flash messages and clear them.
     */
    public static function getAllFlash(): array {
        $flashes = $_SESSION['flash'] ?? [];
        $_SESSION['flash'] = [];
        return $flashes;
    }
}
