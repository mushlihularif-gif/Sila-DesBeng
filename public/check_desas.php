<?php
$host = '127.0.0.1';
$db   = 'sila_desbeng';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $stmt = $pdo->query("SELECT id, name, type, parent_id FROM regions WHERE type = 'desa'");
    $desas = $stmt->fetchAll();
    echo "Total Desas: " . count($desas) . "\n";
    foreach ($desas as $desa) {
        echo $desa['name'] . " (Parent ID: " . $desa['parent_id'] . ")\n";
    }
} catch (\PDOException $e) {
    echo $e->getMessage();
}
