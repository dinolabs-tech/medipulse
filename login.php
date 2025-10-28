<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'components/functions.php';
require_once 'database/db_connection.php';
require_once 'database/database_schema.php';
session_start(); // Start a regular session

// Dummy log_event function since secure_session is removed
$log_event = function($type) use ($conn) {
    // Log to a file or simply do nothing if session_logs table is removed
    error_log("Action logged: " . $type . " by user ID: " . ($_SESSION['user_id'] ?? 'N/A'));
};

// Check for superuser role and create if not exists
$check_superuser_sql = "SELECT * FROM users WHERE role = 'Superuser'";
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

// Fetch branches for the dropdown
$branches_sql = "SELECT id, name FROM branches";
$branches_result = $conn->query($branches_sql);
$branches = [];
if ($branches_result->num_rows > 0) {
    while ($row = $branches_result->fetch_assoc()) {
        $branches[] = $row;
    }
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $branch_id = $_POST['branch_id'];

    // First, try to find the user by username only to check their role
    $sql_check_role = "SELECT id, username, password, role, staffname, branch_id FROM users WHERE username=?";
    $stmt_check_role = $conn->prepare($sql_check_role);
    $stmt_check_role->bind_param("s", $username);
    $stmt_check_role->execute();
    $result_check_role = $stmt_check_role->get_result();
    $user_data = $result_check_role->fetch_assoc();
    $stmt_check_role->close();

    if ($user_data) {
        if (password_verify($password, $user_data['password'])) {
            // If Superuser, bypass branch_id check
            if ($user_data['role'] === 'Superuser') {
                $_SESSION['user_id'] = $user_data['id'];
                $_SESSION['username'] = $user_data['username'];
                $_SESSION['role'] = $user_data['role'];
                $_SESSION['staffname'] = $user_data['staffname'];
                $_SESSION['branch_id'] = null; // Superuser is not tied to a specific branch

                $log_event('login');

                header("Location: index.php");
                exit();
            } else {
                // For other roles, proceed with branch_id verification
                $sql = "SELECT id, username, password, role, staffname, branch_id FROM users WHERE username=? AND branch_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $username, $branch_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    // Password already verified above, but re-verify for good measure if needed
                    if (password_verify($password, $user['password'])) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['staffname'] = $user['staffname'];
                        $_SESSION['branch_id'] = $user['branch_id'];

                        $log_event('login');

                        header("Location: index.php");
                        exit();
                    } else {
                        $error_message = "Invalid password.";
                    }
                } else {
                    $error_message = "No user found with that username and branch.";
                }
                $stmt->close();
            }
        } else {
            $error_message = "Invalid password.";
        }
    } else {
        $error_message = "No user found with that username.";
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
                <h3 class="text-center">Login</h3>
              </div>
              <div class="card-body">
                <?php if (isset($error_message)) : ?>
                  <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                  </div>
                <?php endif; ?>
                <form action="login.php" method="post">
                  <input type="text" class="form-control mb-3" name="username" placeholder="Username" required>
                  <input type="password" class="form-control mb-3" name="password" placeholder="Password" required>
                  <select class="form-control mb-3" name="branch_id" id="branchSelect">
                    <option value="">Select Branch (Optional for Superuser)</option>
                    <?php foreach ($branches as $branch) : ?>
                      <option value="<?php echo $branch['id']; ?>"><?php echo $branch['name']; ?></option>
                    <?php endforeach; ?>
                  </select>
                  <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-primary rounded text-center" name="login">Login</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
  <script>
    document.getElementById('branchSelect').addEventListener('change', function() {
        // Only make required if a value is selected and it's not the "Select Branch" option
        if (this.value !== '') {
            this.setAttribute('required', 'required');
        } else {
            this.removeAttribute('required');
        }
    });
  </script>
</body>

</html>
