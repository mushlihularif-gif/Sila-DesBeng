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
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Assign super_admin and admin to region_id = 1
    $stmt = $pdo->prepare("UPDATE users SET region_id = 1 WHERE role IN ('super_admin', 'admin')");
    $stmt->execute();
    
    echo "Fixed admin region_id";
} catch (\PDOException $e) {
    echo $e->getMessage();
}
