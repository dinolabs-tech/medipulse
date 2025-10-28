<?php
session_start();
require_once 'components/functions.php';
require_once 'database/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$branch_id = $_SESSION['branch_id'] ?? null;

// Base WHERE clause for branch filtering
$branch_where_clause = ($branch_id !== null) ? " WHERE branch_id = ?" : "";
$branch_param_type = "i";

// Fetch total profit
$sql = "SELECT SUM(profit) as total_profit FROM sales" . $branch_where_clause;
$stmt = $conn->prepare($sql);
if ($branch_id !== null) {
    $stmt->bind_param($branch_param_type, $branch_id);
}
$stmt->execute();
$result = $stmt->get_result();
$total_profit = $result->fetch_assoc()['total_profit'] ?? 0;
$stmt->close();

// Fetch total sales count
$sql = "SELECT COUNT(id) as total_sales_count FROM sales" . $branch_where_clause;
$stmt = $conn->prepare($sql);
if ($branch_id !== null) {
    $stmt->bind_param($branch_param_type, $branch_id);
}
$stmt->execute();
$result = $stmt->get_result();
$total_sales_count = $result->fetch_assoc()['total_sales_count'] ?? 0;
$stmt->close();

// Fetch total amount sold (quantity of medicines)
$sql = "SELECT SUM(quantity_sold) as total_quantity_sold FROM sales" . $branch_where_clause;
$stmt = $conn->prepare($sql);
if ($branch_id !== null) {
    $stmt->bind_param($branch_param_type, $branch_id);
}
$stmt->execute();
$result = $stmt->get_result();
$total_quantity_sold = $result->fetch_assoc()['total_quantity_sold'] ?? 0;
$stmt->close();

// Fetch total Inventory amount (sum of all medicine quantities)
$sql = "SELECT SUM(cost_price) as total_inventory, SUM(profit_per_unit) as total_profit FROM medicines" . $branch_where_clause;
$stmt = $conn->prepare($sql);
if ($branch_id !== null) {
    $stmt->bind_param($branch_param_type, $branch_id);
}
$stmt->execute();
$result = $stmt->get_result();
$total_inventory = $result->fetch_assoc();
$stmt->close();

// Fetch total sales revenue
$sql = "SELECT SUM(total_price) as total_sales FROM sales" . $branch_where_clause;
$stmt = $conn->prepare($sql);
if ($branch_id !== null) {
    $stmt->bind_param($branch_param_type, $branch_id);
}
$stmt->execute();
$result = $stmt->get_result();
$total_sales = $result->fetch_assoc()['total_sales'] ?? 0;
$stmt->close();

// Fetch total unique medicines
$sql = "SELECT COUNT(DISTINCT id) as total_unique_medicines FROM medicines" . $branch_where_clause;
$stmt = $conn->prepare($sql);
if ($branch_id !== null) {
    $stmt->bind_param($branch_param_type, $branch_id);
}
$stmt->execute();
$result = $stmt->get_result();
$total_unique_medicines = $result->fetch_assoc()['total_unique_medicines'] ?? 0;
$stmt->close();

// Fetch total patients
$sql = "SELECT COUNT(id) as total_patients FROM patients" . $branch_where_clause;
$stmt = $conn->prepare($sql);
if ($branch_id !== null) {
    $stmt->bind_param($branch_param_type, $branch_id);
}
$stmt->execute();
$result = $stmt->get_result();
$total_patients = $result->fetch_assoc()['total_patients'] ?? 0;
$stmt->close();

// Fetch recent sales with cashier name, sorted by date
$sql = "SELECT s.id, p.first_name, p.last_name, m.name as medicine_name, s.quantity_sold, s.total_price, s.profit, s.sale_date, u.username as cashier_name 
        FROM sales s 
        LEFT JOIN patients p ON s.patient_id = p.id 
        JOIN medicines m ON s.medicine_id = m.id 
        JOIN users u ON s.user_id = u.id";
if ($branch_id !== null) {
    $sql .= " WHERE s.branch_id = ?";
}
$sql .= " ORDER BY s.sale_date DESC LIMIT 5";
$stmt = $conn->prepare($sql);
if ($branch_id !== null) {
    $stmt->bind_param($branch_param_type, $branch_id);
}
$stmt->execute();
$recent_sales = $stmt->get_result();
$stmt->close();

// Fetch Low Stock Products (e.g., quantity < 10)
$sql_low_stock = "SELECT id, name, quantity FROM medicines";
$low_stock_where_clause = " WHERE quantity > 0 AND quantity <= 10";
if ($branch_id !== null) {
    $low_stock_where_clause .= " AND branch_id = ?";
}
$sql_low_stock .= $low_stock_where_clause . " ORDER BY quantity ASC";
$stmt = $conn->prepare($sql_low_stock);
if ($branch_id !== null) {
    $stmt->bind_param($branch_param_type, $branch_id);
}
$stmt->execute();
$low_stock_products = $stmt->get_result();
$stmt->close();

// Fetch Out of Stock Products (quantity = 0)
$sql_out_of_stock = "SELECT id, name FROM medicines";
$out_of_stock_where_clause = " WHERE quantity = 0";
if ($branch_id !== null) {
    $out_of_stock_where_clause .= " AND branch_id = ?";
}
$sql_out_of_stock .= $out_of_stock_where_clause . " ORDER BY name ASC";
$stmt = $conn->prepare($sql_out_of_stock);
if ($branch_id !== null) {
    $stmt->bind_param($branch_param_type, $branch_id);
}
$stmt->execute();
$out_of_stock_products = $stmt->get_result();
$stmt->close();

// Fetch Expired Products
$current_date = date('Y-m-d');
$sql_expired = "SELECT id, name, expiry_date FROM medicines";
$expired_where_clause = " WHERE expiry_date < ?";
if ($branch_id !== null) {
    $expired_where_clause .= " AND branch_id = ?";
}
$sql_expired .= $expired_where_clause . " ORDER BY expiry_date ASC";
$stmt = $conn->prepare($sql_expired);
if ($branch_id !== null) {
    $stmt->bind_param("si", $current_date, $branch_id);
} else {
    $stmt->bind_param("s", $current_date);
}
$stmt->execute();
$expired_products = $stmt->get_result();
$stmt->close();

// Fetch Products About to Expire (within 3 months)
$three_months_from_now = date('Y-m-d', strtotime('+3 months'));
$sql_about_to_expire = "SELECT id, name, expiry_date FROM medicines";
$about_to_expire_where_clause = " WHERE expiry_date >= ? AND expiry_date <= ?";
if ($branch_id !== null) {
    $about_to_expire_where_clause .= " AND branch_id = ?";
}
$sql_about_to_expire .= $about_to_expire_where_clause . " ORDER BY expiry_date ASC";
$stmt = $conn->prepare($sql_about_to_expire);
if ($branch_id !== null) {
    $stmt->bind_param("ssi", $current_date, $three_months_from_now, $branch_id);
} else {
    $stmt->bind_param("ss", $current_date, $three_months_from_now);
}
$stmt->execute();
$about_to_expire_products = $stmt->get_result();
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
      <!-- Navbar Header -->
      <?php include('components/navbar.php'); ?>
      <!-- End Navbar -->

      <div class="container">
        <div class="page-inner">
          <div
            class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
              <h3 class="fw-bold mb-3">Dashboard</h3>
              <h6 class="op-7 mb-2">Key Metrics</h6>
            </div>
            <!-- <div class="ms-md-auto py-2 py-md-0">
              <a href="#" class="btn btn-label-info btn-round me-2">Manage</a>
              <a href="#" class="btn btn-primary btn-round">Add Customer</a>
            </div> -->
          </div>

          <div class="row">
            <div class="col-sm-6 col-md-4">
              <div class="card card-stats card-primary card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div
                        class="icon-big text-center">
                        <i class="fas fa-boxes"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Inventory Sum</p>
                        <h4 class="card-title">&#8358; <?php echo number_format($total_inventory['total_inventory'] ?? 0, 2); ?></h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-4">
              <div class="card card-stats card-success card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div
                        class="icon-big text-center">
                        <i class="fas fa-chart-line"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Total Profit</p>
                        <h4 class="card-title">&#8358; <?php echo number_format($total_profit, 2); ?></h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-4">
              <div class="card card-stats card-secondary card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div
                        class="icon-big text-center">
                        <i class="fas fa-chart-area"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Expected Profit</p>
                        <h4 class="card-title">&#8358; <?php echo number_format($total_inventory['total_profit'] ?? 0, 2); ?></h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-info card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div
                        class="icon-big text-center">
                        <i class="fas fa-shopping-cart"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Total Sales</p>
                        <h4 class="card-title"><?php echo $total_sales_count; ?></h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-warning card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div
                        class="icon-big text-center">
                        <i class="fas fa-dolly"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Total Quantity Sold</p>
                        <h4 class="card-title"><?php echo $total_quantity_sold; ?></h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-secondary card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div
                        class="icon-big text-center">
                        <i class="fas fa-capsules"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Total Unique Medicines</p>
                        <h4 class="card-title"><?php echo $total_unique_medicines; ?></h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-danger card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div
                        class="icon-big text-center">
                        <i class="fas fa-users"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Total Patients</p>
                        <h4 class="card-title"><?php echo $total_patients; ?></h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
           
            <div class="col-md-12">
              <div class="card card-round">
                <div class="card-header">
                  <div class="card-head-row">
                    <div class="card-title">User Statistics</div>
                  </div>
                </div>
                <div class="card-body">
                  <div class="chart-container" style="min-height: 375px">
                    <canvas id="statisticsChart"></canvas>
                  </div>
                  <div id="myChartLegend"></div>
                </div>
              </div>
            </div>
           
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="card card-secondary card-round p-3">
                <div class="card-title">
                  <h3>Low Stock Products (<= 10)</h3>
                </div>
                <?php if ($low_stock_products->num_rows > 0): ?>
                  <table>
                    <tr>
                      <th>Medicine</th>
                      <th>Quantity</th>
                    </tr>
                    <?php while ($row = $low_stock_products->fetch_assoc()): ?>
                      <tr>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                      </tr>
                    <?php endwhile; ?>
                  </table>
                <?php else: ?>
                  <p>No low stock products.</p>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card card-info card-round p-3">
                <div class="card-title">
                  <h3>Out of Stock Products</h3>
                </div>
                <?php if ($out_of_stock_products->num_rows > 0): ?>
                  <table>
                    <tr>
                      <th>Medicine</th>
                    </tr>
                    <?php while ($row = $out_of_stock_products->fetch_assoc()): ?>
                      <tr>
                        <td><?php echo $row['name']; ?></td>
                      </tr>
                    <?php endwhile; ?>
                  </table>
                <?php else: ?>
                  <p>No out of stock products.</p>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="card card-danger card-round p-3">
                <div class="card-title">
                  <h3>Expired Products</h3>
                </div>
                <?php if ($expired_products->num_rows > 0): ?>
                  <div class="table-responsive">
                      <table class="table table-bordered">
                        <tr>
                          <th>Medicine</th>
                          <th>Expiry Date</th>
                        </tr>
                        <?php while ($row = $expired_products->fetch_assoc()): ?>
                          <tr>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['expiry_date']; ?></td>
                          </tr>
                        <?php endwhile; ?>
                      </table>
                      </div>
                    <?php else: ?>
                      <p>No expired products.</p>
                    <?php endif; ?>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card card-warning card-round p-3">
                <div class="card-title">
                  <h3>Products about to Expire (3 months)</h3>
                </div>
                <?php if ($about_to_expire_products->num_rows > 0): ?>
                  <div class="table-responsive">
                      <table id="multi-filter-select" class="table table-bordered">
                        <thead>
                        <tr>
                          <th>Medicine</th>
                          <th>Expiry Date</th>
                        </tr>
                        </thead>
                        
                          <tbody>
                            <?php while ($row = $about_to_expire_products->fetch_assoc()): ?>
                          <tr>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['expiry_date']; ?></td>
                          </tr>
                          <?php endwhile; ?>
                          </tbody>
                        
                      </table>
                      </div>
                    <?php else: ?>
                      <p>No products about to expire.</p>
                    <?php endif; ?>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title">Recent Sales</h4>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table
                      id="basic-datatables"
                      class="display table table-striped table-hover">
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th>Patient</th>
                          <th>Medicine Name</th>
                          <th>Quantity Sold</th>
                          <th>Total Price</th>
                          <th>Profit</th>
                          <th>Sale Date</th>
                          <th>Cashier</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php while ($row = $recent_sales->fetch_assoc()): ?>
                          <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                            <td><?php echo $row['medicine_name']; ?></td>
                            <td><?php echo $row['quantity_sold']; ?></td>
                            <td>&#8358; <?php echo number_format($row['total_price'], 2); ?></td>
                            <td>&#8358; <?php echo number_format($row['profit'], 2); ?></td>
                            <td><?php echo $row['sale_date']; ?></td>
                            <td><?php echo $row['cashier_name']; ?></td>
                          </tr>
                        <?php endwhile; ?>
                      </tbody>
                    </table>
                  </div>
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
  <script>
    var ctx = document.getElementById('statisticsChart').getContext('2d');

    var statisticsChart = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: ["Total Profit", "Inventory Sum", "Total Sales"],
        datasets: [{
          label: "Amount",
          backgroundColor: [
            'rgba(255, 99, 132, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)'
          ],
          borderColor: [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)'
          ],
          data: [<?php echo $total_profit; ?>, <?php echo $total_inventory["total_inventory"] ?? 0; ?>, <?php echo $total_sales; ?>]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
      }
    });
  </script>
</body>

</html>
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
      <!-- Navbar Header -->
      <?php include('components/navbar.php'); ?>
      <!-- End Navbar -->

      <div class="container">
        <div class="page-inner">
          <div
            class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
              <h3 class="fw-bold mb-3">Dashboard</h3>
              <h6 class="op-7 mb-2">Key Metrics</h6>
            </div>
            <!-- <div class="ms-md-auto py-2 py-md-0">
              <a href="#" class="btn btn-label-info btn-round me-2">Manage</a>
              <a href="#" class="btn btn-primary btn-round">Add Customer</a>
            </div> -->
          </div>

          <div class="row">
            <div class="col-sm-6 col-md-4">
              <div class="card card-stats card-primary card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div
                        class="icon-big text-center">
                        <i class="fas fa-boxes"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Inventory Sum</p>
                        <h4 class="card-title">&#8358; <?php echo number_format($total_inventory['total_inventory'], 2); ?></h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-4">
              <div class="card card-stats card-success card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div
                        class="icon-big text-center">
                        <i class="fas fa-chart-line"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Total Profit</p>
                        <h4 class="card-title">&#8358; <?php echo number_format($total_profit, 2); ?></h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-4">
              <div class="card card-stats card-secondary card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div
                        class="icon-big text-center">
                        <i class="fas fa-chart-area"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Expected Profit</p>
                        <h4 class="card-title">&#8358; <?php echo number_format($total_inventory['total_profit'], 2); ?></h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-info card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div
                        class="icon-big text-center">
                        <i class="fas fa-shopping-cart"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Total Sales</p>
                        <h4 class="card-title"><?php echo $total_sales_count; ?></h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-warning card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div
                        class="icon-big text-center">
                        <i class="fas fa-dolly"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Total Quantity Sold</p>
                        <h4 class="card-title"><?php echo $total_quantity_sold; ?></h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-secondary card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div
                        class="icon-big text-center">
                        <i class="fas fa-capsules"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Total Unique Medicines</p>
                        <h4 class="card-title"><?php echo $total_unique_medicines; ?></h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-md-3">
              <div class="card card-stats card-danger card-round">
                <div class="card-body">
                  <div class="row align-items-center">
                    <div class="col-icon">
                      <div
                        class="icon-big text-center">
                        <i class="fas fa-users"></i>
                      </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                      <div class="numbers">
                        <p class="card-category">Total Patients</p>
                        <h4 class="card-title"><?php echo $total_patients; ?></h4>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
           
            <div class="col-md-12">
              <div class="card card-round">
                <div class="card-header">
                  <div class="card-head-row">
                    <div class="card-title">User Statistics</div>
                  </div>
                </div>
                <div class="card-body">
                  <div class="chart-container" style="min-height: 375px">
                    <canvas id="statisticsChart"></canvas>
                  </div>
                  <div id="myChartLegend"></div>
                </div>
              </div>
            </div>
           
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="card card-secondary card-round p-3">
                <div class="card-title">
                  <h3>Low Stock Products (<= 10)</h3>
                </div>
                <?php if ($low_stock_products->num_rows > 0): ?>
                  <table>
                    <tr>
                      <th>Medicine</th>
                      <th>Quantity</th>
                    </tr>
                    <?php while ($row = $low_stock_products->fetch_assoc()): ?>
                      <tr>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                      </tr>
                    <?php endwhile; ?>
                  </table>
                <?php else: ?>
                  <p>No low stock products.</p>
                <?php endif; ?>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card card-info card-round p-3">
                <div class="card-title">
                  <h3>Out of Stock Products</h3>
                </div>
                <?php if ($out_of_stock_products->num_rows > 0): ?>
                  <table>
                    <tr>
                      <th>Medicine</th>
                    </tr>
                    <?php while ($row = $out_of_stock_products->fetch_assoc()): ?>
                      <tr>
                        <td><?php echo $row['name']; ?></td>
                      </tr>
                    <?php endwhile; ?>
                  </table>
                <?php else: ?>
                  <p>No out of stock products.</p>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="card card-danger card-round p-3">
                <div class="card-title">
                  <h3>Expired Products</h3>
                </div>
                <?php if ($expired_products->num_rows > 0): ?>
                  <div class="table-responsive">
                      <table class="table table-bordered">
                        <tr>
                          <th>Medicine</th>
                          <th>Expiry Date</th>
                        </tr>
                        <?php while ($row = $expired_products->fetch_assoc()): ?>
                          <tr>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['expiry_date']; ?></td>
                          </tr>
                        <?php endwhile; ?>
                      </table>
                      </div>
                    <?php else: ?>
                      <p>No expired products.</p>
                    <?php endif; ?>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card card-warning card-round p-3">
                <div class="card-title">
                  <h3>Products about to Expire (3 months)</h3>
                </div>
                <?php if ($about_to_expire_products->num_rows > 0): ?>
                  <div class="table-responsive">
                      <table id="multi-filter-select" class="table table-bordered">
                        <thead>
                        <tr>
                          <th>Medicine</th>
                          <th>Expiry Date</th>
                        </tr>
                        </thead>
                        
                          <tbody>
                            <?php while ($row = $about_to_expire_products->fetch_assoc()): ?>
                          <tr>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['expiry_date']; ?></td>
                          </tr>
                          <?php endwhile; ?>
                          </tbody>
                        
                      </table>
                      </div>
                    <?php else: ?>
                      <p>No products about to expire.</p>
                    <?php endif; ?>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-header">
                  <h4 class="card-title">Recent Sales</h4>
                </div>
                <div class="card-body">
                  <div class="table-responsive">
                    <table
                      id="basic-datatables"
                      class="display table table-striped table-hover">
                      <thead>
                        <tr>
                          <th>ID</th>
                          <th>Patient</th>
                          <th>Medicine Name</th>
                          <th>Quantity Sold</th>
                          <th>Total Price</th>
                          <th>Profit</th>
                          <th>Sale Date</th>
                          <th>Cashier</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php while ($row = $recent_sales->fetch_assoc()): ?>
                          <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                            <td><?php echo $row['medicine_name']; ?></td>
                            <td><?php echo $row['quantity_sold']; ?></td>
                            <td>&#8358; <?php echo number_format($row['total_price'], 2); ?></td>
                            <td>&#8358; <?php echo number_format($row['profit'], 2); ?></td>
                            <td><?php echo $row['sale_date']; ?></td>
                            <td><?php echo $row['cashier_name']; ?></td>
                          </tr>
                        <?php endwhile; ?>
                      </tbody>
                    </table>
                  </div>
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
  <script>
    var ctx = document.getElementById('statisticsChart').getContext('2d');

    var statisticsChart = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: ["Total Profit", "Inventory Sum", "Total Sales"],
        datasets: [{
          label: "Amount",
          backgroundColor: [
            'rgba(255, 99, 132, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)'
          ],
          borderColor: [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)'
          ],
          data: [<?php echo $total_profit; ?>, <?php echo $total_inventory["total_inventory"]; ?>, <?php echo $total_sales; ?>]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
      }
    });
  </script>
</body>

</html>
