<?php
require_once 'components/functions.php';
require_once 'database/db_connection.php';

// Fetch total profit
$sql = "SELECT SUM(profit) as total_profit FROM sales";
$result = $conn->query($sql);
$total_profit = $result->fetch_assoc()['total_profit'];

// Fetch total sales count
$sql = "SELECT COUNT(id) as total_sales_count FROM sales";
$result = $conn->query($sql);
$total_sales_count = $result->fetch_assoc()['total_sales_count'];

// Fetch total amount sold (quantity of medicines)
$sql = "SELECT SUM(quantity_sold) as total_quantity_sold FROM sales";
$result = $conn->query($sql);
$total_quantity_sold = $result->fetch_assoc()['total_quantity_sold'];

// Fetch total Inventory amount (sum of all medicine quantities)
$sql = "SELECT SUM(cost_price) as total_inventory, SUM(profit_per_unit) as total_profit FROM medicines";
$result = $conn->query($sql);
$total_inventory = $result->fetch_assoc();


// Fetch total unique medicines
$sql = "SELECT COUNT(DISTINCT id) as total_unique_medicines FROM medicines";
$result = $conn->query($sql);
$total_unique_medicines = $result->fetch_assoc()['total_unique_medicines'];

// Fetch total patients
$sql = "SELECT COUNT(id) as total_patients FROM patients";
$result = $conn->query($sql);
$total_patients = $result->fetch_assoc()['total_patients'];

// Fetch recent sales with cashier name, sorted by date
$sql = "SELECT s.id, p.first_name, p.last_name, m.name as medicine_name, s.quantity_sold, s.total_price, s.profit, s.sale_date, u.username as cashier_name 
        FROM sales s 
        LEFT JOIN patients p ON s.patient_id = p.id 
        JOIN medicines m ON s.medicine_id = m.id 
        JOIN users u ON s.user_id = u.id
        ORDER BY s.sale_date DESC LIMIT 5";
$recent_sales = $conn->query($sql);

// Fetch Low Stock Products (e.g., quantity < 10)
$sql_low_stock = "SELECT id, name, quantity FROM medicines WHERE quantity > 0 AND quantity <= 10 ORDER BY quantity ASC";
$low_stock_products = $conn->query($sql_low_stock);

// Fetch Out of Stock Products (quantity = 0)
$sql_out_of_stock = "SELECT id, name FROM medicines WHERE quantity = 0 ORDER BY name ASC";
$out_of_stock_products = $conn->query($sql_out_of_stock);

// Fetch Expired Products
$current_date = date('Y-m-d');
$sql_expired = "SELECT id, name, expiry_date FROM medicines WHERE expiry_date < '$current_date' ORDER BY expiry_date ASC";
$expired_products = $conn->query($sql_expired);

// Fetch Products About to Expire (within 3 months)
$three_months_from_now = date('Y-m-d', strtotime('+3 months'));
$sql_about_to_expire = "SELECT id, name, expiry_date FROM medicines WHERE expiry_date >= '$current_date' AND expiry_date <= '$three_months_from_now' ORDER BY expiry_date ASC";
$about_to_expire_products = $conn->query($sql_about_to_expire);
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
                        <h4 class="card-title">$<?php echo number_format($total_inventory['total_inventory'], 2); ?></h4>
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
                        <h4 class="card-title">$<?php echo number_format($total_profit, 2); ?></h4>
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
                        <h4 class="card-title">$<?php echo number_format($total_inventory['total_profit'], 2); ?></h4>
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
                    <div class="card-tools">
                      <a
                        href="#"
                        class="btn btn-label-success btn-round btn-sm me-2">
                        <span class="btn-label">
                          <i class="fa fa-pencil"></i>
                        </span>
                        Export
                      </a>
                      <a href="#" class="btn btn-label-info btn-round btn-sm">
                        <span class="btn-label">
                          <i class="fa fa-print"></i>
                        </span>
                        Print
                      </a>
                    </div>
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
                        <?php while ($row = $about_to_expire_products->fetch_assoc()): ?>
                          <tbody>
                          <tr>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['expiry_date']; ?></td>
                          </tr>
                          </tbody>
                        <?php endwhile; ?>
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
                            <td>$<?php echo number_format($row['total_price'], 2); ?></td>
                            <td>$<?php echo number_format($row['profit'], 2); ?></td>
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
  
</body>

</html>