<?php
namespace App\Services;

class Cache {
    private static ?string $cacheDir = null;

    /**
     * Retrieve the cache directory path, creating it if necessary.
     */
    private static function getCacheDir(): string {
        if (self::$cacheDir === null) {
            self::$cacheDir = ROOT_PATH . '/storage/cache';
        }

        if (!file_exists(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }

        return self::$cacheDir;
    }

    /**
     * Get the absolute path to a cache file by its key.
     */
    private static function getFilePath(string $key): string {
        // Safe filename using md5 of the key
        return self::getCacheDir() . '/' . md5($key) . '.cache';
    }

    /**
     * Retrieve value from cache if it exists and has not expired.
     */
    public static function get(string $key, int $ttl = 3600): mixed {
        // Bypass cache in development mode to see changes instantly
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            return null;
        }

        $filePath = self::getFilePath($key);

        if (!file_exists($filePath)) {
            return null;
        }

        // Check TTL expiration
        if ((time() - filemtime($filePath)) > $ttl) {
            self::delete($key);
            return null;
        }

        try {
            $content = file_get_contents($filePath);
            if ($content === false) {
                return null;
            }
            return unserialize($content);
        } catch (\Exception $e) {
            // Silently fail on corruption
            return null;
        }
    }

    /**
     * Write value to cache.
     */
    public static function set(string $key, mixed $value): bool {
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            return true; // Skip caching in development
        }

        $filePath = self::getFilePath($key);
        try {
            $serialized = serialize($value);
            return file_put_contents($filePath, $serialized) !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete an individual item from cache.
     */
    public static function delete(string $key): bool {
        $filePath = self::getFilePath($key);
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    /**
     * Clear all cached files.
     */
    public static function clear(): bool {
        $dir = self::getCacheDir();
        $files = glob($dir . '/*.cache');
        if ($files === false) {
            return true;
        }

        $success = true;
        foreach ($files as $file) {
            if (file_exists($file)) {
                if (!unlink($file)) {
                    $success = false;
                }
            }
        }

        // Log cache purge
        $logDir = ROOT_PATH . '/storage/logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/app.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] [CACHE-PURGED] Entire system cache has been invalidated.\n", FILE_APPEND);

        return $success;
    }
}
