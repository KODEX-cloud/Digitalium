<?php
namespace App\System;

/**
 * Standardized result format for all DSM operations.
 */
class DSMResult {
    public static function ok(string $label, string $message, array $data = [], float $durationMs = 0): array {
        return self::make('ok', $label, $message, $data, [], $durationMs);
    }

    public static function warning(string $label, string $message, array $data = [], array $errors = [], float $durationMs = 0): array {
        return self::make('warning', $label, $message, $data, $errors, $durationMs);
    }

    public static function error(string $label, string $message, array $errors = [], array $data = [], float $durationMs = 0): array {
        return self::make('error', $label, $message, $data, $errors, $durationMs);
    }

    public static function make(string $status, string $label, string $message, array $data = [], array $errors = [], float $durationMs = 0): array {
        return [
            'status'      => $status,
            'label'       => $label,
            'message'     => $message,
            'data'        => $data,
            'errors'      => $errors,
            'duration_ms' => round($durationMs, 2),
            'timestamp'   => date('Y-m-d H:i:s'),
        ];
    }

    public static function timer(): float {
        return microtime(true) * 1000;
    }

    public static function elapsed(float $start): float {
        return microtime(true) * 1000 - $start;
    }

    /**
     * Aggregate multiple results into a single summary.
     */
    public static function aggregate(string $label, array $results): array {
        $hasError   = false;
        $hasWarning = false;
        $errors     = [];
        $totalMs    = 0;

        foreach ($results as $r) {
            if ($r['status'] === 'error')   $hasError   = true;
            if ($r['status'] === 'warning') $hasWarning = true;
            $errors  = array_merge($errors, $r['errors'] ?? []);
            $totalMs += $r['duration_ms'] ?? 0;
        }

        $status  = $hasError ? 'error' : ($hasWarning ? 'warning' : 'ok');
        $count   = count($results);
        $okCount = count(array_filter($results, fn($r) => $r['status'] === 'ok'));

        return self::make($status, $label, "{$okCount}/{$count} opérations réussies", ['results' => $results], $errors, $totalMs);
    }
}
