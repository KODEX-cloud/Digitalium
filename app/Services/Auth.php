<?php
namespace App\Services;

use App\Models\User;
use Exception;

class Auth {
    private static string $userSessionKey = 'admin_user_id';
    private static string $loginAttemptsKey = 'login_attempts';
    private static string $lockoutTimeKey = 'lockout_time';
    private static int $maxAttempts = 5;
    private static int $lockoutDuration = 900; // 15 minutes

    /**
     * Attempt login with rate-limiting.
     */
    public static function attempt(string $username, string $password): bool {
        self::checkLockout();

        $user = User::findByUsername($username);

        if (!$user) {
            self::registerFailedAttempt();
            return false;
        }

        if (password_verify($password, $user['password'])) {
            if (password_needs_rehash($user['password'], PASSWORD_ARGON2ID)) {
                $newHash = password_hash($password, PASSWORD_ARGON2ID);
                User::updatePassword($user['id'], $newHash);
            }

            self::login($user);
            return true;
        }

        self::registerFailedAttempt();
        return false;
    }

    /**
     * Standard login procedures.
     */
    public static function login(array $user): void {
        Session::regenerate();
        Session::set(self::$userSessionKey, $user['id']);
        Session::remove(self::$loginAttemptsKey);
        Session::remove(self::$lockoutTimeKey);
    }

    /**
     * Log user out.
     */
    public static function logout(): void {
        Session::remove(self::$userSessionKey);
        Session::destroy();
    }

    /**
     * Check if a valid admin is logged in.
     */
    public static function check(): bool {
        return Session::has(self::$userSessionKey);
    }

    /**
     * Get the logged-in user profile.
     */
    public static function user(): ?array {
        if (!self::check()) {
            return null;
        }
        $userId = Session::get(self::$userSessionKey);
        return User::find($userId);
    }

    /**
     * Register a failed login attempt.
     */
    private static function registerFailedAttempt(): void {
        $attempts = Session::get(self::$loginAttemptsKey, 0) + 1;
        Session::set(self::$loginAttemptsKey, $attempts);

        if ($attempts >= self::$maxAttempts) {
            Session::set(self::$lockoutTimeKey, time());
            throw new Exception("Trop de tentatives infructueuses. Votre compte est bloqué pour 15 minutes.", 429);
        }
    }

    /**
     * Check if lockout is active.
     */
    private static function checkLockout(): void {
        if (Session::has(self::$lockoutTimeKey)) {
            $lockoutTime = Session::get(self::$lockoutTimeKey);
            $timePassed = time() - $lockoutTime;

            if ($timePassed < self::$lockoutDuration) {
                $remaining = self::$lockoutDuration - $timePassed;
                $minutes = ceil($remaining / 60);
                throw new Exception("Sécurité : Veuillez patienter encore {$minutes} minutes avant de réessayer.", 429);
            } else {
                Session::remove(self::$loginAttemptsKey);
                Session::remove(self::$lockoutTimeKey);
            }
        }
    }
}
