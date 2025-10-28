<?php
require_once 'database/db_connection.php';
require_once 'database/database_schema.php';

foreach ($schema as $tableName => $columns) {
    $sql = "CREATE TABLE IF NOT EXISTS $tableName (";
    $columnDefinitions = [];
    $foreignKeys = [];

    foreach ($columns as $columnName => $columnType) {
        // Check if the column definition contains 'FOREIGN KEY'
        if (strpos(strtoupper($columnType), 'FOREIGN KEY') !== false) {
            $foreignKeys[] = $columnType; // Add the full foreign key definition
        } else {
            $columnDefinitions[] = "`$columnName` $columnType";
        }
    }

    $sql .= implode(', ', $columnDefinitions);
    if (!empty($foreignKeys)) {
        $sql .= ', ' . implode(', ', $foreignKeys);
    }
    $sql .= ") ENGINE=InnoDB;";

    if ($conn->query($sql) === TRUE) {
        echo "Table '$tableName' created successfully or already exists.<br>";
    } else {
        echo "Error creating table '$tableName': " . $conn->error . "<br>";
    }
}

// Insert a default branch if it doesn't exist
$defaultBranchName = "Main Branch";
$sql = "SELECT id FROM branches WHERE name = '$defaultBranchName'";
$result = $conn->query($sql);
$branchId = null;

if ($result->num_rows == 0) {
    $sql = "INSERT INTO branches (name, address, city, state, country) VALUES ('$defaultBranchName', '123 Main St', 'Anytown', 'Anystate', 'Anycountry')";
    if ($conn->query($sql) === TRUE) {
        $branchId = $conn->insert_id;
        echo "Default branch '$defaultBranchName' created successfully.<br>";
    } else {
        echo "Error creating default branch: " . $conn->error . "<br>";
    }
} else {
    $branchId = $result->fetch_assoc()['id'];
    echo "Default branch '$defaultBranchName' already exists.<br>";
}

// Create default user if not exists
$username = "dinolabs";
$password = password_hash("dinolabs", PASSWORD_DEFAULT);
$role = "Superuser";

$sql = "SELECT id FROM users WHERE username = '$username'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    $sql = "INSERT INTO users (username, password, role, branch_id) VALUES ('$username', '$password', '$role', " . ($branchId ? $branchId : 'NULL') . ")";
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
