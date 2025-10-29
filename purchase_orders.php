<?php
require_once 'components/functions.php';
require_once 'database/db_connection.php';

$current_branch_id = $_SESSION['current_branch_id'] ?? null;

// Prepare WHERE clause for branch filtering
$branch_filter = $current_branch_id ? " WHERE branch_id = ?" : "";
$branch_filter_join = $current_branch_id ? " AND t.branch_id = ?" : ""; // For joined tables

// Fetch products for dropdown (expired, low stock, out of stock)
$current_date = date('Y-m-d');
$low_stock_threshold = 10; // Define your low stock threshold

$expired_products_sql = "SELECT id, name, expiry_date FROM medicines WHERE expiry_date < ?";
if ($current_branch_id) {
  $expired_products_sql .= " AND branch_id = ?";
}
$expired_products_sql .= " ORDER BY name ASC";
$stmt_expired = $conn->prepare($expired_products_sql);
if ($current_branch_id) {
  $stmt_expired->bind_param("si", $current_date, $current_branch_id);
} else {
  $stmt_expired->bind_param("s", $current_date);
}
$stmt_expired->execute();
$expired_products_result = $stmt_expired->get_result();
$stmt_expired->close();

$out_of_stock_products_sql = "SELECT id, name, quantity FROM medicines WHERE quantity = 0";
if ($current_branch_id) {
  $out_of_stock_products_sql .= " AND branch_id = ?";
}
$out_of_stock_products_sql .= " ORDER BY name ASC";
$stmt_out_of_stock = $conn->prepare($out_of_stock_products_sql);
if ($current_branch_id) {
  $stmt_out_of_stock->bind_param("i", $current_branch_id);
}
$stmt_out_of_stock->execute();
$out_of_stock_products_result = $stmt_out_of_stock->get_result();
$stmt_out_of_stock->close();

$low_stock_products_sql = "SELECT id, name, quantity FROM medicines WHERE quantity > 0 AND quantity <= ?";
if ($current_branch_id) {
  $low_stock_products_sql .= " AND branch_id = ?";
}
$low_stock_products_sql .= " ORDER BY name ASC";
$stmt_low_stock = $conn->prepare($low_stock_products_sql);
if ($current_branch_id) {
  $stmt_low_stock->bind_param("ii", $low_stock_threshold, $current_branch_id);
} else {
  $stmt_low_stock->bind_param("i", $low_stock_threshold);
}
$stmt_low_stock->execute();
$low_stock_products_result = $stmt_low_stock->get_result();
$stmt_low_stock->close();

// Add Purchase Order
if (isset($_POST['add'])) {
  $supplier_id = $_POST['supplier_id'];
  $order_date = $_POST['order_date'];
  $expected_delivery_date = $_POST['expected_delivery_date'];
  $product_id = isset($_POST['product_id']) && $_POST['product_id'] !== '' ? $_POST['product_id'] : null;
  $status = $_POST['status'];
  $total_amount = $_POST['total_amount'];

  if ($product_id) {
    $sql = "INSERT INTO purchase_orders (supplier_id, order_date, expected_delivery_date, product_id, status, total_amount, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssdsi", $supplier_id, $order_date, $expected_delivery_date, $product_id, $status, $total_amount, $current_branch_id);
  } else {
    $sql = "INSERT INTO purchase_orders (supplier_id, order_date, expected_delivery_date, status, total_amount, branch_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssdi", $supplier_id, $order_date, $expected_delivery_date, $status, $total_amount, $current_branch_id);
  }

  if ($stmt->execute()) {
    log_action($conn, $_SESSION['user_id'], "Added purchase order for supplier ID: $supplier_id, total: $total_amount", $current_branch_id);
  } else {
    echo "<p style='color:red;'>Error adding purchase order: " . $stmt->error . "</p>";
  }
  $stmt->close();
}

// Edit Purchase Order
if (isset($_POST['edit'])) {
  $id = $_POST['id'];
  $supplier_id = $_POST['supplier_id'];
  $order_date = $_POST['order_date'];
  $expected_delivery_date = $_POST['expected_delivery_date'];
  $product_id = isset($_POST['product_id']) && $_POST['product_id'] !== '' ? $_POST['product_id'] : null;
  $status = $_POST['status'];
  $total_amount = $_POST['total_amount'];

  if ($product_id) {
    $sql = "UPDATE purchase_orders SET supplier_id=?, order_date=?, expected_delivery_date=?, product_id=?, status=?, total_amount=? WHERE id=? AND branch_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssii", $supplier_id, $order_date, $expected_delivery_date, $product_id, $status, $total_amount, $id, $current_branch_id);
  } else {
    $sql = "UPDATE purchase_orders SET supplier_id=?, order_date=?, expected_delivery_date=?, product_id=?, status=?, total_amount=? WHERE id=? AND branch_id = ?";
    $stmt = $conn->prepare($sql);
    $null_product_id = NULL; // Explicitly set to NULL for binding
    $stmt->bind_param("issssiii", $supplier_id, $order_date, $expected_delivery_date, $null_product_id, $status, $total_amount, $id, $current_branch_id);
  }

  if ($stmt->execute()) {
    log_action($conn, $_SESSION['user_id'], "Edited purchase order ID: $id (Supplier ID: $supplier_id, Total: $total_amount)", $current_branch_id);
  } else {
    echo "<p style='color:red;'>Error editing purchase order: " . $stmt->error . "</p>";
  }
  $stmt->close();
}

// Delete Purchase Order
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $sql_select = "SELECT supplier_id, total_amount FROM purchase_orders WHERE id=? AND branch_id = ?";
  $stmt_select = $conn->prepare($sql_select);
  $stmt_select->bind_param("ii", $id, $current_branch_id);
  $stmt_select->execute();
  $result_select = $stmt_select->get_result();
  $po_details = $result_select->fetch_assoc();
  $supplier_id = $po_details['supplier_id'];
  $total_amount = $po_details['total_amount'];
  $stmt_select->close();

  $sql = "DELETE FROM purchase_orders WHERE id=? AND branch_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $id, $current_branch_id);
  if ($stmt->execute()) {
    log_action($conn, $_SESSION['user_id'], "Deleted purchase order ID: $id (Supplier ID: $supplier_id, Total: $total_amount)", $current_branch_id);
  }
  $stmt->close();
}

// Fetch Purchase Orders
$sql = "SELECT po.*, s.name as supplier_name, m.name as product_name FROM purchase_orders po JOIN suppliers s ON po.supplier_id = s.id LEFT JOIN medicines m ON po.product_id = m.id";
if ($current_branch_id) {
  $sql .= " WHERE po.branch_id = ?";
}
$sql .= " ORDER BY po.order_date DESC";
$stmt = $conn->prepare($sql);
if ($current_branch_id) {
  $stmt->bind_param("i", $current_branch_id);
}
$stmt->execute();
$po_result = $stmt->get_result();
$stmt->close();

// Fetch Suppliers for dropdown
$sql = "SELECT id, name FROM suppliers";
if ($current_branch_id) {
  $sql .= " WHERE branch_id = ?";
}
$stmt = $conn->prepare($sql);
if ($current_branch_id) {
  $stmt->bind_param("i", $current_branch_id);
}
$stmt->execute();
$suppliers_result = $stmt->get_result();
$stmt->close();
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
                <a href="#">Purchase Orders</a>
              </li>

            </ul>
          </div>

          <!-- new purchase order  -->
          <div class="card p-3">
            <div class="card-header">
              <h2>Add New Purchase Order</h2>
            </div>
            <div class="card-body">
              <form action="purchase_orders.php" method="post">
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <select name="supplier_id" class="form-control" required>
                      <option value="" selected disabled>Select Supplier</option>
                      <?php while ($row = $suppliers_result->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                      <?php endwhile; ?>
                    </select>
                  </div>
                  <div class="col-md-4 mb-3">
                    <input type="date" class="form-control" name="order_date" placeholder="Order Date" onfocus="(this.type='date')" onblur="if(!this.value)this.type='text'" required>
                  </div>
                  <div class="col-md-4 mb-3">
                    <input type="date" class="form-control" name="expected_delivery_date" onfocus="(this.type='date')" onblur="if(!this.value)this.type='text'" placeholder="Expected Delivery Date">
                  </div>
                  <div class="col-md-4 mb-3">
                    <select name="product_id" class="form-control" id="product_id">
                      <option value="" selected disabled>Select Product (Optional)</option>
                      <?php if ($expired_products_result->num_rows > 0): ?>
                        <optgroup label="Expired Products">
                          <?php while ($row = $expired_products_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?> (Expires: <?php echo $row['expiry_date']; ?>)</option>
                          <?php endwhile; ?>
                        </optgroup>
                      <?php endif; ?>
                      <?php if ($out_of_stock_products_result->num_rows > 0): ?>
                        <optgroup label="Out of Stock Products">
                          <?php while ($row = $out_of_stock_products_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                          <?php endwhile; ?>
                        </optgroup>
                      <?php endif; ?>
                      <?php if ($low_stock_products_result->num_rows > 0): ?>
                        <optgroup label="Low Stock Products">
                          <?php while ($row = $low_stock_products_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?> (Qty: <?php echo $row['quantity']; ?>)</option>
                          <?php endwhile; ?>
                        </optgroup>
                      <?php endif; ?>
                    </select>
                  </div>
                  <div class="col-md-4 mb-3">
                    <select name="status" class="form-control" id="status" required>
                      <option value="Pending" selected>Pending</option>
                      <option value="Ordered">Ordered</option>
                      <option value="Received">Received</option>
                      <option value="Cancelled">Cancelled</option>
                    </select>
                  </div>
                  <div class="col-md-3 mb-3">
                    <input type="text" class="form-control" name="total_amount" placeholder="Total Amount">
                  </div>
                  <div class="col-md-1">
                    <button type="submit" name="add" class="btn btn-primary btn-icon btn-round"><i class="fas fa-save"></i></button>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <!-- existing purchase orders  -->
          <div class="card p-3">
            <div class="card-header">
              <h2>Existing Purchase Orders</h2>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table id="basic-datatables" class="table table-bordered">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Supplier</th>
                      <th>Order Date</th>
                      <th>Expected Delivery</th>
                      <th>Product</th>
                      <th>Status</th>
                      <th>Total Amount</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  
                    <tbody>
                      <?php while ($row = $po_result->fetch_assoc()): ?>
                      <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['supplier_name']; ?></td>
                        <td><?php echo $row['order_date']; ?></td>
                        <td><?php echo $row['expected_delivery_date']; ?></td>
                        <td><?php echo $row['product_name'] ? $row['product_name'] : 'N/A'; ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <td><?php echo $row['total_amount']; ?></td>
                        <td>
                          <a href="purchase_orders.php?edit_id=<?php echo $row['id']; ?>" class="btn btn-primary btn-icon btn-round mb-3"><i class="fas fa-edit"></i></a>
                          <a href="purchase_orders.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-icon btn-round mb-3"><i class="fas fa-trash"></i></a>
                        </td>
                      </tr>
                       <?php endwhile; ?>
                      </thead>
                   
                </table>
              </div>
            </div>
          </div>

          <!-- edit purchase order  -->
          <?php
          // Edit form
          if (isset($_GET['edit_id'])) {
            $edit_id = $_GET['edit_id'];
            $sql = "SELECT * FROM purchase_orders WHERE id=$edit_id";
            $edit_result = $conn->query($sql);
            $edit_po = $edit_result->fetch_assoc();
            if ($edit_po) {
              // Re-fetch suppliers for the dropdown in the edit form
              $suppliers_result->data_seek(0); // Reset pointer
          ?>
              <div class="card p-3">
                <div class="card-header">
                  <h2>Edit Purchase Order</h2>
                </div>
                <div class="card-body">
                  <form action="purchase_orders.php" method="post">
                    <div class="row">
                      <input type="hidden" name="id" value="<?php echo $edit_po['id']; ?>">
                      <div class="col-md-4 mb-3">
                        <select name="supplier_id" class="form-control" required>
                          <option value="">Select Supplier</option>
                          <?php while ($row = $suppliers_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>" <?php echo ($edit_po['supplier_id'] == $row['id']) ? 'selected' : ''; ?>><?php echo $row['name']; ?></option>
                          <?php endwhile; ?>
                        </select>
                      </div>
                      <div class="col-md-4 mb-3">
                        <input class="form-control" type="date" name="order_date" placeholder="Order Date" value="<?php echo $edit_po['order_date']; ?>" required>
                      </div>
                      <div class="col-md-4 mb-3">
                        <input class="form-control" type="date" name="expected_delivery_date" placeholder="Expected Delivery Date" value="<?php echo $edit_po['expected_delivery_date']; ?>">
                      </div>
                      <div class="col-md-4 mb-3">
                        <select class="form-control" name="product_id" id="edit_product_id">
                          <option value="">Select Product (Optional)</option>
                          <?php
                          // Re-fetch product results for edit form
                          $expired_products_result->data_seek(0);
                          $out_of_stock_products_result->data_seek(0);
                          $low_stock_products_result->data_seek(0);
                          ?>
                          <?php if ($expired_products_result->num_rows > 0): ?>
                            <optgroup label="Expired Products">
                              <?php while ($row = $expired_products_result->fetch_assoc()): ?>
                                <option value="<?php echo $row['id']; ?>" <?php echo ($edit_po['product_id'] == $row['id']) ? 'selected' : ''; ?>><?php echo $row['name']; ?> (Expires: <?php echo $row['expiry_date']; ?>)</option>
                              <?php endwhile; ?>
                            </optgroup>
                          <?php endif; ?>
                          <?php if ($out_of_stock_products_result->num_rows > 0): ?>
                            <optgroup label="Out of Stock Products">
                              <?php while ($row = $out_of_stock_products_result->fetch_assoc()): ?>
                                <option value="<?php echo $row['id']; ?>" <?php echo ($edit_po['product_id'] == $row['id']) ? 'selected' : ''; ?>><?php echo $row['name']; ?></option>
                              <?php endwhile; ?>
                            </optgroup>
                          <?php endif; ?>
                          <?php if ($low_stock_products_result->num_rows > 0): ?>
                            <optgroup label="Low Stock Products">
                              <?php while ($row = $low_stock_products_result->fetch_assoc()): ?>
                                <option value="<?php echo $row['id']; ?>" <?php echo ($edit_po['product_id'] == $row['id']) ? 'selected' : ''; ?>><?php echo $row['name']; ?> (Qty: <?php echo $row['quantity']; ?>)</option>
                              <?php endwhile; ?>
                            </optgroup>
                          <?php endif; ?>
                        </select>
                      </div>
                      <div class="col-md-4 mb-3">
                        <select class="form-control" name="status" id="edit_status" required>
                          <option value="Pending" <?= ($edit_po['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                          <option value="Ordered" <?= ($edit_po['status'] == 'Ordered') ? 'selected' : ''; ?>>Ordered</option>
                          <option value="Received" <?= ($edit_po['status'] == 'Received') ? 'selected' : ''; ?>>Received</option>
                          <option value="Cancelled" <?= ($edit_po['status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                      </div>
                      <div class="col-md-3 mb-3">
                        <input class="form-control" type="text" name="total_amount" placeholder="Total Amount" value="<?php echo $edit_po['total_amount']; ?>">
                      </div>
                      <div class="col-md-1">
                        <button type="submit" name="edit" class="btn btn-primary btn-icon btn-round"><i class="fas fa-save"></i></button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
          <?php
            }
          }
          ?>

        </div>
      </div>

      <?php include('components/footer.php'); ?>
    </div>
  </div>
  <!--   Core JS Files   -->
  <?php include('components/script.php'); ?>
</body>

</html>
