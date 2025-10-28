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
    'branches' => [
        'id' => 'INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY',
        'name' => 'VARCHAR(255) NOT NULL',
        'address' => 'TEXT',
        'city' => 'VARCHAR(100)',
        'state' => 'VARCHAR(100)',
        'country' => 'VARCHAR(100)',
        'phone' => 'VARCHAR(20)',
        'email' => 'VARCHAR(100)',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ],
    'users' => [
        'id' => 'INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY',
        'staffname' => 'VARCHAR(255) NOT NULL',
        'username' => 'VARCHAR(50) NOT NULL UNIQUE',
        'password' => 'VARCHAR(255) NOT NULL',
        'role' => 'VARCHAR(50) NOT NULL',
        'branch_id' => 'INT(6) UNSIGNED',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL'
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
        'city' => 'VARCHAR(100)',
        'state' => 'VARCHAR(100)',
        'country' => 'VARCHAR(100)',
        'branch_id' => 'INT(6) UNSIGNED',
        'insurance_details' => 'TEXT',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL'
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
        'branch_id' => 'INT(6) UNSIGNED',
        'added_date' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL'
    ],
    'suppliers' => [
        'id' => 'INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY',
        'name' => 'VARCHAR(255) NOT NULL',
        'contact_person' => 'VARCHAR(255)',
        'phone' => 'VARCHAR(20)',
        'email' => 'VARCHAR(100)',
        'address' => 'TEXT',
        'city' => 'VARCHAR(100)',
        'state' => 'VARCHAR(100)',
        'country' => 'VARCHAR(100)',
        'branch_id' => 'INT(6) UNSIGNED',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL'
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
        'branch_id' => 'INT(6) UNSIGNED',
        'sale_date' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`) ON DELETE SET NULL',
        'FOREIGN KEY (`medicine_id`) REFERENCES `medicines`(`id`) ON DELETE CASCADE',
        'FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE',
        'FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL'
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
        'branch_id' => 'INT(6) UNSIGNED',
        'prescription_date' => 'DATE',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`) ON DELETE CASCADE',
        'FOREIGN KEY (`medicine_id`) REFERENCES `medicines`(`id`) ON DELETE CASCADE',
        'FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL'
    ],
    'purchase_orders' => [
        'id' => 'INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY',
        'supplier_id' => 'INT(6) UNSIGNED NOT NULL',
        'order_date' => 'DATE',
        'expected_delivery_date' => 'DATE',
        'product_id' => 'INT(6) UNSIGNED',
        'status' => 'VARCHAR(50)',
        'total_amount' => 'DECIMAL(10, 2)',
        'branch_id' => 'INT(6) UNSIGNED',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE CASCADE',
        'FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL'
    ],
    'logs' => [
        'id' => 'INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY',
        'user_id' => 'INT(6) UNSIGNED NOT NULL',
        'branch_id' => 'INT(6) UNSIGNED',
        'action' => 'VARCHAR(255) NOT NULL',
        'action_date' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE',
        'FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL'
    ],
    'session_logs' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'user_id' => 'INT(6) UNSIGNED NULL',
        'branch_id' => 'INT(6) UNSIGNED',
        'event_type' => "VARCHAR(50) NOT NULL COMMENT 'login, logout, timeout, hijack'",
        'ip_address' => 'VARCHAR(45) NOT NULL',
        'user_agent' => 'TEXT NOT NULL',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL',
        'FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL'
    ]
];

// Execute table creation
foreach ($schema as $tableName => $columns) {
    createTable($conn, $tableName, $columns);
}

?>
