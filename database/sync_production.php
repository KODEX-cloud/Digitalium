<?php
/**
 * SYNC PRODUCTION — Digitalium Group CMS
 *
 * Script CLI wrapper pour SyncProductionManager.
 *
 * Usage : php database/sync_production.php
 *
 * INCIDENT ROOT CAUSE (2026-07-04) :
 *   SQL : SELECT * FROM menus WHERE location = :l LIMIT 1
 *   Colonne manquante : menus.location (VARCHAR 50 DEFAULT 'primary')
 *   Fichier : app/Models/Menu.php:14 — Menu::findByLocation()
 *   Layout : app/Views/frontend/layout.php:313
 *   Cause  : CREATE TABLE IF NOT EXISTS dans master_migration.php —
 *            si menus existait déjà sans location, la création était skipée.
 *   Fix    : ALTER TABLE menus ADD COLUMN location — idempotent.
 */

define('SECURE_ACCESS', true);

$root = dirname(__DIR__);
if (!defined('ROOT_PATH')) define('ROOT_PATH', $root);

require $root . '/config/config.php';
require $root . '/app/Services/Database.php';
require $root . '/app/System/DSMResult.php';
require $root . '/app/System/SyncProductionManager.php';

use App\System\SyncProductionManager;

$result  = SyncProductionManager::run();
$data    = $result['data'] ?? [];
$results = $data['results'] ?? [];

$isCli = PHP_SAPI === 'cli';

if ($isCli) {
    echo "\n=== SYNC PRODUCTION — Digitalium CMS ===\n";
    echo "Timestamp : " . ($result['timestamp'] ?? date('Y-m-d H:i:s')) . "\n";
    echo "Statut    : " . strtoupper($result['status'] ?? '?') . "\n";
    echo "Message   : " . ($result['message'] ?? '') . "\n";
    echo "Durée     : " . ($result['duration_ms'] ?? 0) . "ms\n\n";

    foreach ($results as $r) {
        $icon = $r['status'] === 'ok' ? '✓' : ($r['status'] === 'error' ? '✗' : '·');
        echo "  {$icon} [{$r['status']}] {$r['action']}";
        if (!empty($r['detail'])) echo " — {$r['detail']}";
        echo "\n";
    }

    echo "\n--- Incident Root Cause ---\n";
    $inc = $data['incident'] ?? [];
    foreach ($inc as $k => $v) {
        echo "  {$k}: {$v}\n";
    }
    echo "\nDone.\n\n";
} else {
    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
