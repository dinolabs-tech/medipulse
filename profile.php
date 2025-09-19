<?php
require_once 'components/functions.php';
require_once 'database/db_connection.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$sql = "SELECT id, username, role FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
  echo "<p style='color:red;'>User not found.</p>";
  exit();
}

// Handle profile update
if (isset($_POST['update_profile'])) {
  $new_username = $_POST['username'];
  $new_password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];

  if ($new_username !== $user['username']) {
    // Check if new username already exists
    $sql_check_username = "SELECT id FROM users WHERE username = ? AND id != ?";
    $stmt_check = $conn->prepare($sql_check_username);
    $stmt_check->bind_param("si", $new_username, $user_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
      echo "<p style='color:red;'>Error: Username already taken.</p>";
      $stmt_check->close();
    } else {
      $stmt_check->close();
      $update_fields = "username = ?";
      $bind_types = "si";
      $bind_params = [$new_username, $user_id];

      if (!empty($new_password)) {
        if ($new_password === $confirm_password) {
          $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
          $update_fields .= ", password = ?";
          $bind_types .= "s";
          $bind_params[] = $hashed_password;
        } else {
          echo "<p style='color:red;'>Error: Passwords do not match.</p>";
        }
      }

      $sql_update = "UPDATE users SET $update_fields WHERE id = ?";
      $stmt_update = $conn->prepare($sql_update);
      $stmt_update->bind_param($bind_types, ...$bind_params);
      if ($stmt_update->execute()) {
        log_action($conn, $_SESSION['user_id'], "Updated own profile (ID: $user_id)");
        echo "<p style='color:green;'>Profile updated successfully.</p>";
        // Refresh user data
        $sql = "SELECT id, username, role FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
      } else {
        echo "<p style='color:red;'>Error updating profile: " . $stmt_update->error . "</p>";
      }
      $stmt_update->close();
    }
  } else { // Username is the same, only check password
    if (!empty($new_password)) {
      if ($new_password === $confirm_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql_update = "UPDATE users SET password = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $hashed_password, $user_id);
        if ($stmt_update->execute()) {
          log_action($conn, $_SESSION['user_id'], "Updated own password (ID: $user_id)");
          echo "<p style='color:green;'>Password updated successfully.</p>";
        } else {
          echo "<p style='color:red;'>Error updating password: " . $stmt_update->error . "</p>";
        }
        $stmt_update->close();
      } else {
        echo "<p style='color:red;'>Error: Passwords do not match.</p>";
      }
    } else {
      echo "<p style='color:blue;'>No changes submitted.</p>";
    }
  }
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
                <a href="#">Profile</a>
              </li>

            </ul>
          </div>
          <div class="card p-3">
            <div class="card-header">
              <h2>User Profile</h2>
            </div>
            <div class="card-body">
              <form action="profile.php" method="post">
                <div class="row">
                  <div class="col-md-6">
                    <div class="input-group mb-3">
                      <span class="input-group-text" id="basic-addon3">Username</span>
                      <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="input-group mb-3">
                      <span class="input-group-text" id="basic-addon3">Role</span>
                      <input type="text" id="role" class="form-control" name="role" value="<?php echo htmlspecialchars($user['role']); ?>" disabled>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="input-group mb-3">
                      <span class="input-group-text" id="basic-addon3">Password (leave blank to keep current password)</span>
                      <input type="password" class="form-control" id="password" name="password">
                    </div>
                  </div>

                  <div class="col-md-5">
                    <div class="input-group mb-3">
                      <span class="input-group-text" id="basic-addon3">Confirm New Password</span>
                      <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                  </div>

                  <div class="col-md-1">
                    <button type="submit" name="update_profile" class="btn btn-primary btn-icon btn-round"><i class="fas fa-save"></i></button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <?php include('components/footer.php'); ?>
    </div>
  </div>
</body>
<?php include('components/script.php'); ?>
</html>