<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'components/functions.php';
require_once 'database/db_connection.php';
require_once 'database/database_schema.php';

// Check for superuser role and create if not exists
$check_superuser_sql = "SELECT * FROM users WHERE role = 'superuser'";
$superuser_result = $conn->query($check_superuser_sql);

if ($superuser_result->num_rows == 0) {
  // Superuser does not exist, create it
  $username = 'dinolabs';
  $password = password_hash('dinolabs', PASSWORD_DEFAULT); // Hash the password
  $staffname = 'Dinolabs';
  $role = 'Superuser';

  // Create default country if not exists
  $check_country_sql = "SELECT id FROM countries WHERE name = 'Default Country'";
  $country_result = $conn->query($check_country_sql);
  if ($country_result->num_rows == 0) {
    $insert_country_sql = "INSERT INTO countries (name) VALUES ('Default Country')";
    $conn->query($insert_country_sql);
    $country_id = $conn->insert_id;
  } else {
    $country_id = $country_result->fetch_assoc()['id'];
  }

  // Create default state if not exists
  $check_state_sql = "SELECT id FROM states WHERE name = 'Default State' AND country_id = $country_id";
  $state_result = $conn->query($check_state_sql);
  if ($state_result->num_rows == 0) {
    $insert_state_sql = "INSERT INTO states (name, country_id) VALUES ('Default State', $country_id)";
    $conn->query($insert_state_sql);
    $state_id = $conn->insert_id;
  } else {
    $state_id = $state_result->fetch_assoc()['id'];
  }

  // Create default branch if not exists
  $check_branch_sql = "SELECT id FROM branches WHERE name = 'Main Branch' AND state_id = $state_id";
  $branch_result = $conn->query($check_branch_sql);
  if ($branch_result->num_rows == 0) {
    $insert_branch_sql = "INSERT INTO branches (name, address, phone, email, state_id) VALUES ('Main Branch', '123 Main St', '555-1234', 'main@example.com', $state_id)";
    $conn->query($insert_branch_sql);
    $branch_id = $conn->insert_id;
  } else {
    $branch_id = $branch_result->fetch_assoc()['id'];
  }

  $insert_superuser_sql = "INSERT INTO users (username, password, staffname, role, branch_id) VALUES (?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($insert_superuser_sql);
  $stmt->bind_param("ssssi", $username, $password, $staffname, $role, $branch_id);

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
  $branch_id = isset($_POST['branch_id']) ? $_POST['branch_id'] : null;

  $sql = "SELECT * FROM users WHERE username='$username'";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['role'] = $user['role'];
      $_SESSION['staffname'] = $user['staffname'];
      $_SESSION['branch_id'] = $user['branch_id']; // Store user's assigned branch

      // If a branch was selected in the form, and the user is a superuser, allow them to switch
      if ($user['role'] == 'superuser' && $branch_id) {
        $_SESSION['current_branch_id'] = $branch_id;
      } else {
        $_SESSION['current_branch_id'] = $user['branch_id']; // Default to user's assigned branch
      }


      // Regenerate session ID for security
      // Log login
      log_action($conn, $_SESSION['user_id'], 'login', $_SESSION['current_branch_id']);

      header("Location: index.php");
      exit();
    } else {
      $error_message = "Invalid password.";
    }
  } else {
    $error_message = "No user found.";
  }
}

// Fetch branches for the login form
$branches_sql = "SELECT id, name FROM branches";
$branches_result = $conn->query($branches_sql);
$branches = [];
if ($branches_result->num_rows > 0) {
  while ($row = $branches_result->fetch_assoc()) {
    $branches[] = $row;
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

                  <?php if (!empty($branches)) : ?>
                    <div class="form-group mb-3">
                      <label for="branch_id">Select Branch:</label>
                      <select class="form-control" id="branch_id" name="branch_id">
                        <?php foreach ($branches as $branch) : ?>
                          <option value="<?php echo $branch['id']; ?>"><?php echo $branch['name']; ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  <?php endif; ?>

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
</body>

</html>
