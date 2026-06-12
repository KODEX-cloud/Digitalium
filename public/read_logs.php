<?php
/**
 * Diagnostic tool to read php_error.log on production
 */
define('SECURE_ACCESS', true);
require_once __DIR__ . '/../config/config.php';

header('Content-Type: text/plain; charset=utf-8');

$logFile = ROOT_PATH . '/storage/logs/php_error.log';

echo "=== DIAGNOSTIC LOGS DIGITALIUM GROUP ===\n\n";

if (!file_exists($logFile)) {
    echo "Fichier de log introuvable à l'emplacement : $logFile\n";
    echo "Vérifiez que APP_ENV est configuré sur 'production' pour générer ce fichier, ou que les droits d'écriture sont corrects sur le dossier storage/logs.\n";
    
    // Check directory permissions
    $logsDir = dirname($logFile);
    echo "\nPermissions du dossier storage/logs : " . (file_exists($logsDir) ? substr(sprintf('%o', fileperms($logsDir)), -4) : "Dossier introuvable") . "\n";
    exit;
}

$logs = file_get_contents($logFile);
if (empty(trim($logs))) {
    echo "Le fichier de log est vide.\n";
    exit;
}

// Print last 100 lines
$lines = explode("\n", $logs);
$lastLines = array_slice($lines, -100);

echo "Dernières erreurs PHP enregistrées (max 100 lignes) :\n";
echo str_repeat("-", 80) . "\n";
echo implode("\n", $lastLines);
echo "\n" . str_repeat("-", 80) . "\n";
