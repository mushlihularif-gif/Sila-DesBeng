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
    
    // Update children of 174 to have parent 2
    $stmt = $pdo->prepare("UPDATE regions SET parent_id = 2 WHERE parent_id = 174");
    $stmt->execute();
    
    // Delete 174
    $stmt2 = $pdo->prepare("DELETE FROM regions WHERE id = 174");
    $stmt2->execute();
    
    echo "Done resolving duplicates!";
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
