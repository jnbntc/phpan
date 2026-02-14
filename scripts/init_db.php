<?php
require_once __DIR__ . '/../src/Database.php';

$db = new Database();
$pdo = $db->getPDO();
$result = $pdo->query("PRAGMA database_list")->fetch(PDO::FETCH_ASSOC);
$dbPath = $result['file'] ?? 'data/recipes.sqlite';

echo "Base de datos lista en: $dbPath" . PHP_EOL;
