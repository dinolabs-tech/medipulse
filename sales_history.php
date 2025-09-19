<?php
require_once 'components/functions.php';
require_once 'database/db_connection.php';

// Handle AJAX request to clear print session variables
if (isset($_POST['clear_print_session']) && $_POST['clear_print_session'] === 'true') {
  unset($_SESSION['last_receipt']);
  unset($_SESSION['print_receipt']);
  echo json_encode(['status' => 'success']);
  exit();
}

// Handle reprint receipt request
if (isset($_GET['reprint_invoice']) && !empty($_GET['reprint_invoice'])) {
  $invoice_number = $_GET['reprint_invoice'];

  // Fetch sale details for the given invoice_number
  $sql = "SELECT s.id, s.invoice_number, s.patient_id, s.quantity_sold, s.total_price, s.sale_date,
                   m.id as medicine_id, m.name as medicine_name, m.price as medicine_price
            FROM sales s
            JOIN medicines m ON s.medicine_id = m.id
            WHERE s.invoice_number = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $invoice_number);
  $stmt->execute();
  $result = $stmt->get_result();

  $receipt_items = [];
  $total_sale_price = 0;
  $patient_id = null;
  $fetched_invoice_number = null;

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
    }
    $_SESSION['last_receipt'] = ['invoice_number' => $fetched_invoice_number, 'items' => $receipt_items, 'total' => $total_sale_price, 'patient_id' => $patient_id];
    $_SESSION['print_receipt'] = true; // Set flag to trigger print
  } else {
    echo "<p style='color:red;'>Error: Sale not found for reprinting with invoice number: " . htmlspecialchars($invoice_number) . "</p>";
  }
}


// Fetch Sales History
$sql = "SELECT s.id, s.invoice_number, p.first_name, p.last_name, m.name as medicine_name, s.quantity_sold, s.total_price, s.sale_date
        FROM sales s
        LEFT JOIN patients p ON s.patient_id = p.id
        JOIN medicines m ON s.medicine_id = m.id
        ORDER BY s.sale_date DESC";
$sales_history_result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<?php include('components/head.php'); ?>

<body>
  <div class="wrapper">
    <!-- Sidebar -->
    <?php include('components/sidebar.php'); ?>
    <!-- End Sidebar -->

    <div class="main-panel">
      <?php include('components/navbar.php'); ?>

      <div class="container">
        <div class="px-3 mt-3">
          <div class="page-header">
            <!-- breadcrumbs -->
            <ul class="breadcrumbs mb-3">
              <li class="nav-home">
                <a href="index.php">
                  <i class="icon-home"></i>
                </a>
              </li>
              <li class="separator">
                <i class="icon-arrow-right"></i>
              </li>
              <li class="nav-item">
                <a href="#">Sales History</a>
              </li>

            </ul>
          </div>

          <div class="card p-3">
            <div class="card-header">
              <h2>Sales History</h2>
            </div>

            <div class="card-body">
              <div class="sales-history-container">
                <div class="table-responsive">
                  <table class="table table-bordered" id="basic-datatables">
                    <thead>
                      <tr>
                        <th>Invoice No.</th>
                        <th>Patient</th>
                        <th>Medicine Name</th>
                        <th>Quantity Sold</th>
                        <th>Total Price</th>
                        <th>Sale Date</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <?php while ($row = $sales_history_result->fetch_assoc()): ?>
                      <tbody>
                        <tr>
                          <td><?php echo $row['invoice_number']; ?></td>
                          <td><?php echo $row['first_name'] ? $row['first_name'] . ' ' . $row['last_name'] : 'N/A'; ?></td>
                          <td><?php echo $row['medicine_name']; ?></td>
                          <td><?php echo $row['quantity_sold']; ?></td>
                          <td>$<?php echo number_format($row['total_price'], 2); ?></td>
                          <td><?php echo $row['sale_date']; ?></td>
                          <td><a href="sales_history.php?reprint_invoice=<?php echo $row['invoice_number']; ?>" class="btn btn-primary btn-icon btn-round" target="_blank"><i class="fas fa-file-alt"></i></a></td>
                        </tr>
                      </tbody>
                    <?php endwhile; ?>
                  </table>
                </div>
              </div>
            </div>
            </div>
          </div>
        </div>

        <?php include('components/footer.php'); ?>
      </div>
    </div>
    <!--   Core JS Files   -->
    <?php include('components/script.php'); ?>
    <?php if (isset($_SESSION['print_receipt']) && $_SESSION['print_receipt'] === true): ?>
      <script>
        document.addEventListener("DOMContentLoaded", function() {
          window.open('generate_receipt.php', '_blank', 'height=600,width=400');
          // The session variables are now cleared in generate_receipt.php
          // No need to clear them via AJAX here anymore.
        });
      </script>
    <?php endif; ?>
</body>

</html>