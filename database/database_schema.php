<?php
// Include DB connection
require_once 'database/db_connection.php';

// Function to create a table
function createTable($conn, $tableName, $columns)
{
    // Build columns into SQL
    $columns_sql = [];
    foreach ($columns as $name => $definition) {
        $columns_sql[] = "`$name` $definition";
    }
    $sql = "CREATE TABLE IF NOT EXISTS `$tableName` (" . implode(", ", $columns_sql) . ") ENGINE=InnoDB";

    if ($conn->query($sql) === TRUE) {
        // echo "✅ Table '$tableName' created successfully.<br>";
        return true;
    } else {
        echo "❌ Error creating table '$tableName': " . $conn->error . "<br>";
        error_log("Error creating table '$tableName': " . $conn->error);
        return false;
    }
}

// Database schema
$schema = [
    'users' => [
        'id' => 'INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY',
        'username' => 'VARCHAR(50) NOT NULL UNIQUE',
        'password' => 'VARCHAR(255) NOT NULL',
        'role' => 'VARCHAR(50) NOT NULL',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ],
    'patients' => [
        'id' => 'INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY',
        'first_name' => 'VARCHAR(100) NOT NULL',
        'last_name' => 'VARCHAR(100) NOT NULL',
        'date_of_birth' => 'DATE',
        'gender' => 'VARCHAR(10)',
        'phone' => 'VARCHAR(20)',
        'email' => 'VARCHAR(100)',
        'address' => 'TEXT',
        'insurance_details' => 'TEXT',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ],
    'medicines' => [
        'id' => 'INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY',
        'name' => 'VARCHAR(255) NOT NULL',
        'description' => 'TEXT',
        'quantity' => 'INT(10) NOT NULL',
        'price' => 'DECIMAL(10, 2) NOT NULL',
        'cost_price' => 'DECIMAL(10, 2) NOT NULL',
        'profit_per_unit' => 'DECIMAL(10, 2) NOT NULL',
        'batch_number' => 'VARCHAR(100)',
        'expiry_date' => 'DATE',
        'added_date' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ],
    'suppliers' => [
        'id' => 'INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY',
        'name' => 'VARCHAR(255) NOT NULL',
        'contact_person' => 'VARCHAR(255)',
        'phone' => 'VARCHAR(20)',
        'email' => 'VARCHAR(100)',
        'address' => 'TEXT',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ],
    'sales' => [
        'id' => 'INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY',
        'invoice_number' => 'VARCHAR(20) NOT NULL',
        'patient_id' => 'INT(6) UNSIGNED',
        'medicine_id' => 'INT(6) UNSIGNED NOT NULL',
        'quantity_sold' => 'INT(10) NOT NULL',
        'total_price' => 'DECIMAL(10, 2) NOT NULL',
        'profit' => 'DECIMAL(10, 2) NOT NULL',
        'user_id' => 'INT(6) UNSIGNED NOT NULL',
        'sale_date' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ],
    'prescriptions' => [
        'id' => 'INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY',
        'patient_id' => 'INT(6) UNSIGNED NOT NULL',
        'medicine_id' => 'INT(6) UNSIGNED NOT NULL',
        'doctor_name' => 'VARCHAR(255)',
        'dosage' => 'VARCHAR(100)',
        'frequency' => 'VARCHAR(100)',
        'duration' => 'VARCHAR(100)',
        'refills' => 'INT(3)',
        'prescription_date' => 'DATE',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ],
    'purchase_orders' => [
        'id' => 'INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY',
        'supplier_id' => 'INT(6) UNSIGNED NOT NULL',
        'order_date' => 'DATE',
        'expected_delivery_date' => 'DATE',
        'product_id' => 'INT(6) UNSIGNED',
        'status' => 'VARCHAR(50)',
        'total_amount' => 'DECIMAL(10, 2)',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ],
    'logs' => [
        'id' => 'INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY',
        'user_id' => 'INT(6) UNSIGNED NOT NULL',
        'action' => 'VARCHAR(255) NOT NULL',
        'action_date' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ],
    'session_logs' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'user_id' => 'INT(6) UNSIGNED NULL',
        'event_type' => "VARCHAR(50) NOT NULL COMMENT 'login, logout, timeout, hijack'",
        'ip_address' => 'VARCHAR(45) NOT NULL',
        'user_agent' => 'TEXT NOT NULL',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ]
];

// Execute table creation
foreach ($schema as $tableName => $columns) {
    createTable($conn, $tableName, $columns);
}

?>
