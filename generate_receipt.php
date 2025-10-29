<?php
require_once 'database/db_connection.php';
session_start();

$receiptData = null;
$patientName = "N/A";
$saleDate = date('Y-m-d H:i:s');
$branchName = "N/A";

$current_branch_id = $_SESSION['current_branch_id'] ?? null;

if (isset($_GET['invoice_number']) && !empty($_GET['invoice_number'])) {
    // Reprinting an existing receipt using invoice number
    $invoice_number = $_GET['invoice_number'];

    $sql = "SELECT s.id, s.invoice_number, s.patient_id, s.quantity_sold, s.total_price, s.sale_date, s.branch_id,
                   m.id as medicine_id, m.name as medicine_name, m.price as medicine_price
            FROM sales s
            JOIN medicines m ON s.medicine_id = m.id
            WHERE s.invoice_number = ?";
    if ($current_branch_id) {
        $sql .= " AND s.branch_id = ?";
    }
    $stmt = $conn->prepare($sql);
    if ($current_branch_id) {
        $stmt->bind_param("si", $invoice_number, $current_branch_id);
    } else {
        $stmt->bind_param("s", $invoice_number);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $receipt_items = [];
    $total_sale_price = 0;
    $patient_id = null;
    $fetched_invoice_number = null;
    $fetched_branch_id = null;

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $receipt_items[] = [
                'id' => $row['medicine_id'],
                'name' => $row['medicine_name'],
                'price' => $row['medicine_price'],
                'quantity' => $row['quantity_sold']
            ];
            $total_sale_price += $row['total_price'];
            $patient_id = $row['patient_id'];
            $fetched_invoice_number = $row['invoice_number'];
            $saleDate = $row['sale_date']; // Use actual sale date from DB
            $fetched_branch_id = $row['branch_id'];
        }
        $receiptData = ['invoice_number' => $fetched_invoice_number, 'items' => $receipt_items, 'total' => $total_sale_price, 'patient_id' => $patient_id, 'branch_id' => $fetched_branch_id];
    } else {
        echo "<p style='color:red;'>Error: Receipt not found for invoice number: " . htmlspecialchars($invoice_number) . "</p>";
        exit();
    }
    $stmt->close();
} elseif (isset($_SESSION['print_receipt']) && $_SESSION['print_receipt'] === true && isset($_SESSION['last_receipt'])) {
    // New sale, retrieve from session
    $receiptData = $_SESSION['last_receipt'];
    $receiptData['branch_id'] = $current_branch_id; // Add current branch ID to receipt data
    // Clear session variables immediately after fetching them for printing
    unset($_SESSION['last_receipt']);
    unset($_SESSION['print_receipt']);
} else {
    // Redirect if no receipt data is available
    header('Location: sales.php');
    exit();
}

if ($receiptData['patient_id']) {
    $sql = "SELECT first_name, last_name FROM patients WHERE id=? AND branch_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $receiptData['patient_id'], $receiptData['branch_id']);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($res) {
        $patientName = $res['first_name'] . ' ' . $res['last_name'];
    }
}

if ($receiptData['branch_id']) {
    $sql = "SELECT name FROM branches WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $receiptData['branch_id']);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($res) {
        $branchName = $res['name'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Consolas', monospace;
            font-size: 10pt;
            width: 80mm; /* Standard POS printer width */
        }
        .receipt-container {
            padding: 5mm;
        }
        h3 {
            text-align: center;
            margin-bottom: 5mm;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5mm;
            margin-bottom: 5mm;
        }
        th, td {
            padding: 1mm 2mm;
            text-align: left;
        }
        th {
            border-bottom: 1px dashed #000;
        }
        td {
            border-bottom: 1px dotted #ccc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 5mm 0;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <h3>PHARMACY RECEIPT</h3>
        <p><strong>Invoice No:</strong> <?php echo $receiptData['invoice_number']; ?></p>
        <p><strong>Date:</strong> <?php echo $saleDate; ?></p>
        <p><strong>Patient:</strong> <?php echo $patientName; ?></p>
        <hr>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($receiptData['items'] as $item): ?>
                    <tr>
                        <td><?php echo $item['name']; ?></td>
                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                        <td class="text-right">$<?php echo number_format($item['price'], 2); ?></td>
                        <td class="text-right">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <hr>
        <p class="text-right font-bold">Grand Total: $<?php echo number_format($receiptData['total'], 2); ?></p>
        <p class="text-center">Thank you for your purchase!</p>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
