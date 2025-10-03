<?php
require_once 'components/functions.php';
require_once 'database/db_connection.php';

// Fetch Logs
$sql = "SELECT l.*, u.username FROM logs l JOIN users u ON l.user_id = u.id ORDER BY l.action_date DESC";
$logs_result = $conn->query($sql);
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
                <a href="#">Logs</a>
              </li>

            </ul>
          </div>

          <div class="card p-3">
            <div class="card-header">
              <h5>System Logs (Audit Trail)</h5>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered" id="basic-datatables">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>User</th>
                      <th>Action</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                 
                    <tbody>
                       <?php while ($row = $logs_result->fetch_assoc()): ?>
                      <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['action']; ?></td>
                        <td><?php echo $row['action_date']; ?></td>
                      </tr>
                      <?php endwhile; ?>
                    </tbody>
                  
                </table>
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