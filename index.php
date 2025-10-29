<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}

require_once 'database/db_connection.php';

// Fetch current branch name
$current_branch_name = "N/A";
if (isset($_SESSION['current_branch_id'])) {
  $branch_id = $_SESSION['current_branch_id'];
  $stmt = $conn->prepare("SELECT name FROM branches WHERE id = ?");
  $stmt->bind_param("i", $branch_id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
    $current_branch_name = $result->fetch_assoc()['name'];
  }
  $stmt->close();
}
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
        <div class="px-3 mt-3">
          <div class="card p-3">
            <div class="card-header">
              <p>Your role: <?= $_SESSION['role']; ?></p>
              <p>Current Branch: <?= $current_branch_name; ?></p>

              <?php if ($_SESSION['role'] == 'Superuser') { ?>
                <div class="ms-md-auto py-2 py-md-0">
                  <a href="developer.php" class="btn btn-danger btn-round me-2"><i class="fas fa-code"></i> &nbsp; Developer Tools</a>
                  <!-- <a href="#" class="btn btn-primary btn-round">Add Customer</a> -->
                </div>
              <?php } ?>
            </div>
            <div class="card-body">
              <h2>Welcome to the Pharmacy Management System</h2>
              <p>Select a section from the navigation to get started.</p>
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