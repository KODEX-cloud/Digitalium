<?php
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
