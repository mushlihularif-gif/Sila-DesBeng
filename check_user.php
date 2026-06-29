<?php
$mysqli = new mysqli("127.0.0.1", "root", "", "sila_desbeng");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$result = $mysqli->query("SELECT * FROM users WHERE email = 'admin@isewa.com'");
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "User found!\n";
    echo "ID: " . $user['id'] . "\n";
    echo "Email: " . $user['email'] . "\n";
    echo "Role: " . $user['role'] . "\n"; // Assuming role column exists
    
    // Check role in model/table (spatie roles?)
    // Let's also check spatie roles if it uses that
    $id = $user['id'];
    $roleResult = $mysqli->query("SELECT roles.name FROM model_has_roles JOIN roles ON roles.id = model_has_roles.role_id WHERE model_has_roles.model_id = $id AND model_has_roles.model_type = 'App\\\\Models\\\\User'");
    if ($roleResult && $roleResult->num_rows > 0) {
        while($row = $roleResult->fetch_assoc()) {
            echo "Spatie Role: " . $row['name'] . "\n";
        }
    } else {
        echo "No Spatie roles found.\n";
    }
} else {
    echo "User admin@isewa.com not found in the database.\n";
}
$mysqli->close();
?>
