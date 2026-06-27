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
    $stmt = $pdo->query("SELECT * FROM regions WHERE type = 'kecamatan'");
    print_r($stmt->fetchAll());
} catch (\PDOException $e) {
    echo $e->getMessage();
}
