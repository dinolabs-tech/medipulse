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