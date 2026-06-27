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
    
    // Update users
    $stmtUsers = $pdo->prepare("UPDATE users SET region_id = 12 WHERE region_id = 175");
    $stmtUsers->execute();
    
    // Update bumdes_members
    $stmtBumdes = $pdo->prepare("UPDATE bumdes_members SET region_id = 12 WHERE region_id = 175");
    $stmtBumdes->execute();
    
    // Update region_services
    $stmtServices = $pdo->prepare("UPDATE region_services SET region_id = 12 WHERE region_id = 175");
    $stmtServices->execute();
    
    // Delete duplicate region 175
    $stmtDelete = $pdo->prepare("DELETE FROM regions WHERE id = 175");
    $stmtDelete->execute();
    
    echo "Done merging 175 into 12";
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
