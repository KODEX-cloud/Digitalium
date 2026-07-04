<?php
namespace App\Services;

use PDO;
use PDOException;
use Exception;

class Database {
    private static ?PDO $instance = null;

    /**
     * Get the PDO database connection singleton.
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                    DB_HOST,
                    DB_PORT,
                    DB_NAME,
                    DB_CHARSET
                );

                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
                ];

                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                if (ENVIRONMENT === 'development') {
                    throw new Exception("Database Connection Error: " . $e->getMessage(), (int)$e->getCode());
                } else {
                    throw new Exception("A database error occurred.", 500);
                }
            }
        }

        return self::$instance;
    }

    /**
     * Helper to run an SQL query with parameters.
     */
    public static function query(string $sql, array $params = []): \PDOStatement {
        try {
            $stmt = self::getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            if (ENVIRONMENT === 'development') {
                $logPath = ROOT_PATH . '/storage/logs/app.log';
                $timestamp = date('Y-m-d H:i:s');
                error_log("[{$timestamp}] [DB QUERY ERROR] " . $e->getMessage() . " | SQL: {$sql}\n", 3, $logPath);
            }
            throw new Exception("Query error: " . $e->getMessage() . " in SQL: " . $sql);
        }
    }

    /**
     * Fetch a single row.
     * Throws in development, logs + returns null in production.
     */
    public static function fetch(string $sql, array $params = []): ?array {
        try {
            $stmt   = self::query($sql, $params);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\Throwable $e) {
            self::handleReadError($e, $sql);
            return null;
        }
    }

    /**
     * Fetch all matching rows.
     * Throws in development, logs + returns [] in production.
     */
    public static function fetchAll(string $sql, array $params = []): array {
        try {
            return self::query($sql, $params)->fetchAll();
        } catch (\Throwable $e) {
            self::handleReadError($e, $sql);
            return [];
        }
    }

    /**
     * Insert a record and return last inserted ID.
     */
    public static function insert(string $sql, array $params = []): string {
        self::query($sql, $params);
        return self::getConnection()->lastInsertId();
    }

    /**
     * Handle a read-query error.
     * In development: rethrows. In production: logs silently and returns safe default.
     */
    private static function handleReadError(\Throwable $e, string $sql): void {
        $logPath  = ROOT_PATH . '/storage/logs/errors.log';
        $timestamp = date('Y-m-d H:i:s');
        $entry    = "{$timestamp} [DB READ ERROR] " . $e->getMessage() . " | SQL: {$sql}\n";
        @file_put_contents($logPath, $entry, FILE_APPEND | LOCK_EX);

        if (ENVIRONMENT === 'development') {
            throw $e; // rethrow in development for debugging
        }
        // Production: swallow, return safe default (null/[]) from the caller
    }

    /**
     * Start transaction
     */
    public static function beginTransaction(): bool {
        return self::getConnection()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public static function commit(): bool {
        return self::getConnection()->commit();
    }

    /**
     * Rollback transaction
     */
    public static function rollBack(): bool {
        return self::getConnection()->rollBack();
    }
}
