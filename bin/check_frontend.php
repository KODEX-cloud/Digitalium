<?php

// ─── Sécurité : CLI uniquement ────────────────────────────────────────────────
// Le dossier bin/ était servi par Apache : /bin/read_logs.php et /bin/deploy.php
// répondaient en HTTP. Le .htaccess les bloque désormais, mais un .htaccess perdu
// ou ignoré ne doit pas suffire à rendre ces scripts exécutables depuis le Web.
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Access denied — CLI only');
}
define('SECURE_ACCESS', true);

require_once __DIR__.'/../config/config.php';
require_once __DIR__.'/../app/Services/Database.php';

try {
    $conn = App\Services\Database::getConnection();

    $stmt = $conn->query("SELECT * FROM pages LIMIT 1");

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<pre>";
    print_r($row);
    echo "</pre>";

} catch (Throwable $e) {
    echo "<h1>ERROR</h1>";
    echo $e->getMessage();
    echo "<br>";
    echo $e->getFile();
    echo "<br>";
    echo $e->getLine();
}
