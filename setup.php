<?php
require_once 'database/db_connection.php';
require_once 'database/database_schema.php';

foreach ($schema as $tableName => $columns) {
    $sql = "CREATE TABLE IF NOT EXISTS $tableName (";
    $columnDefinitions = [];
    $foreignKeys = [];

    foreach ($columns as $columnName => $columnType) {
        if (strpos(strtoupper($columnName), 'FOREIGN KEY') !== false) {
            $foreignKeys[] = "$columnName $columnType";
        } else {
            $columnDefinitions[] = "$columnName $columnType";
        }
    }

    $sql .= implode(', ', $columnDefinitions);
    if (!empty($foreignKeys)) {
        $sql .= ', ' . implode(', ', $foreignKeys);
    }
    $sql .= ");";

    if ($conn->query($sql) === TRUE) {
        echo "Table '$tableName' created successfully or already exists.<br>";
    } else {
        echo "Error creating table '$tableName': " . $conn->error . "<br>";
    }
}

// Create default user if not exists
$username = "dinolabs";
$password = password_hash("dinolabs", PASSWORD_DEFAULT);
$role = "Superuser";

$sql = "SELECT id FROM users WHERE username = '$username'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
    if ($conn->query($sql) === TRUE) {
        echo "Default user 'dinolabs' created successfully.<br>";
    } else {
        echo "Error creating default user: " . $conn->error . "<br>";
    }
} else {
    echo "Default user 'dinolabs' already exists.<br>";
}

$conn->close();
?>
