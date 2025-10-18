<?php
require_once 'components/functions.php';
require_once 'database/db_connection.php';
require_once 'database/database_schema.php';
include_once 'secure_session.php'; // Include the secure session

// Check for superuser role and create if not exists
$check_superuser_sql = "SELECT * FROM users WHERE role = 'superuser'";
$superuser_result = $conn->query($check_superuser_sql);

if ($superuser_result->num_rows == 0) {
    // Superuser does not exist, create it
    $username = 'dinolabs';
    $password = password_hash('dinolabs', PASSWORD_DEFAULT); // Hash the password
    $staffname = 'Dinolabs';
    $role = 'Superuser';

    $insert_superuser_sql = "INSERT INTO users (username, password, staffname, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_superuser_sql);
    $stmt->bind_param("ssss", $username, $password, $staffname, $role);

    if ($stmt->execute()) {
        // Superuser created successfully
    } else {
        // Error creating superuser
        error_log("Error creating superuser: " . $stmt->error);
    }
    $stmt->close();
}

if (isset($_POST['login'])) {
  $username = $_POST['username'];
  $password = $_POST['password'];

  $sql = "SELECT * FROM users WHERE username='$username'";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['role'] = $user['role'];
      $_SESSION['staffname'] = $user['staffname'];

      // Regenerate session ID for security
      // Log login
      $log_event = secure_session_start($conn);
      $log_event('login');

      header("Location: index.php");
    } else {
      echo "Invalid password.";
    }
  } else {
    echo "No user found.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include('components/head.php'); ?>

<body>
  <div class="container">
  <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">
        <div class="card w-100">
          <div class="card-header">
            <h3>Loginaa</h3>
          </div>
          <div class="card-body">
          <form action="login.php" method="post">
            <input type="text" class="form-control mb-3" name="username" placeholder="Username" required>
            <input type="password" class="form-control mb-3" name="password" placeholder="Password" required>
            <button type="submit" class="btn btn-primary rounded" name="login">Login</button>
          </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  </section>
  </div>
</body>

</html>
