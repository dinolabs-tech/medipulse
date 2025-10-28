<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


?>

<div class="sidebar" data-background-color="dark">
  <div class="sidebar-logo">
    <!-- Logo Header -->
    <div class="logo-header" data-background-color="dark">
      <!-- <a href="index.html" class="logo">
        <img
          src="assets/img/kaiadmin/logo_light.svg"
          alt="navbar brand"
          class="navbar-brand"
          height="20" />
      </a> -->
      <div class="nav-toggle">
        <button class="btn btn-toggle toggle-sidebar">
          <i class="gg-menu-right"></i>
        </button>
        <button class="btn btn-toggle sidenav-toggler">
          <i class="gg-menu-left"></i>
        </button>
      </div>
      <button class="topbar-toggler more">
        <i class="gg-more-vertical-alt"></i>
      </button>
    </div>
    <!-- End Logo Header -->
  </div>
  <div class="sidebar-wrapper scrollbar scrollbar-inner">
    <div class="sidebar-content">
      <ul class="nav nav-secondary">
        <li class="nav-item active">
          <a href="index.php">
            <i class="fas fa-home"></i>
            <p>Home</p>
          </a>
        </li>

        <!-- Admin only -->
        <?php if ($_SESSION['role'] == 'Superuser' || $_SESSION['role'] == 'admin') { ?>
          <li class="nav-item">
            <a href="dashboard.php">
              <i class="fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
        <?php } ?>

        <!-- Medicines & Prescriptions (Superuser, Admin, Pharmacist) -->
        <?php if ($_SESSION['role'] == 'Superuser' || $_SESSION['role'] == 'admin' || $_SESSION['role'] == 'pharmacist') { ?>
          <li class="nav-item">
            <a href="medicines.php">
              <i class="fas fa-medkit"></i>
              <p>Medicines</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="prescriptions.php">
              <i class="fas fa-prescription"></i>
              <p>Prescriptions</p>
            </a>
          </li>
        <?php } ?>

        <!-- Suppliers & Purchase Orders (Superuser, Admin, Assistant) -->
        <?php if ($_SESSION['role'] == 'Superuser' || $_SESSION['role'] == 'admin' || $_SESSION['role'] == 'assistant') { ?>
          <li class="nav-item">
            <a href="suppliers.php">
              <i class="fas fa-truck"></i>
              <p>Suppliers</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="purchase_orders.php">
              <i class="fas fa-shopping-basket"></i>
              <p>Purchase Orders</p>
            </a>
          </li>
        <?php } ?>

        <!-- Patients (Superuser, Admin, Pharmacist, Assistant) -->
        <?php if ($_SESSION['role'] == 'Superuser' || $_SESSION['role'] == 'admin' || $_SESSION['role'] == 'pharmacist' || $_SESSION['role'] == 'assistant') { ?>
          <li class="nav-item">
            <a href="patients.php">
              <i class="fas fa-procedures"></i>
              <p>Patients</p>
            </a>
          </li>
        <?php } ?>

        <!-- Sales (Superuser, Admin, Cashier) -->
        <?php if ($_SESSION['role'] == 'Superuser' || $_SESSION['role'] == 'admin' || $_SESSION['role'] == 'cashier') { ?>
          <li class="nav-item">
            <a href="sales.php">
              <i class="fas fa-tags"></i>
              <p>Sales</p>
            </a>
          </li>
        <?php } ?>

        <!-- Sales History (Superuser, Admin, Assistant, Cashier) -->
        <?php if ($_SESSION['role'] == 'Superuser' || $_SESSION['role'] == 'admin' || $_SESSION['role'] == 'assistant' || $_SESSION['role'] == 'cashier') { ?>
          <li class="nav-item">
            <a href="sales_history.php">
              <i class="fas fa-history"></i>
              <p>Sales History</p>
            </a>
          </li>
        <?php } ?>

        <!-- Admin only -->
        <?php if ($_SESSION['role'] == 'Superuser' || $_SESSION['role'] == 'admin') { ?>
          <li class="nav-item">
            <a href="users.php">
              <i class="fas fa-users"></i>
              <p>Users</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="logs.php">
              <i class="fas fa-desktop"></i>
              <p>Logs</p>
            </a>
          </li>
        <?php } ?>

        <!-- Superuser only -->
        <?php if ($_SESSION['role'] == 'Superuser') { ?>
          <li class="nav-item">
            <a href="session_logs.php">
              <i class="fas fa-desktop"></i>
              <p>Session Logs</p>
            </a>
          </li>
        <?php } ?>

        <!-- Always visible -->
        <li class="nav-item">
          <a href="profile.php">
            <i class="fas fa-user"></i>
            <p>Profile</p>
          </a>
        </li>

        <!-- Superuser and Admin only -->
        <?php if ($_SESSION['role'] == 'Superuser' || $_SESSION['role'] == 'admin') { ?>
          <li class="nav-item">
            <a href="documentation/index.php">
              <i class="fas fa-book"></i>
              <p>Documentation</p>
            </a>
          </li>
        <?php } ?>
        
        <li class="nav-item">
          <a href="logout.php">
            <i class="fas fa-arrow-right"></i>
            <p>Logout</p>
          </a>
        </li>
      </ul>
    </div>
  </div>
</div>