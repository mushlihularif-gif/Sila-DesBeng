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
    
    // Check duplicate Bengkalis:
    $stmt = $pdo->query("SELECT id, name FROM regions WHERE type = 'kecamatan' AND name LIKE '%Bengkalis%'");
    while ($row = $stmt->fetch()) {
        echo "Kecamatan: " . $row['id'] . " - " . $row['name'] . "\n";
        $stmtChild = $pdo->prepare("SELECT count(*) as cnt FROM regions WHERE parent_id = ?");
        $stmtChild->execute([$row['id']]);
        $childCount = $stmtChild->fetchColumn();
        echo "  Children: $childCount\n";
    }
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
