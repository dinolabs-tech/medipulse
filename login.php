<?php
require_once 'components/functions.php';
require_once 'database/db_connection.php';
require_once 'database/database_schema.php';
include_once 'secure_session.php'; // Include the secure session

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

    $sql = "SELECT * FROM users WHERE username=? AND branch_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $username, $branch_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['staffname'] = $user['staffname'];
            $_SESSION['branch_id'] = $user['branch_id'];

            // Log login
            $log_event = secure_session_start($conn);
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
                  <select class="form-control mb-3" name="branch_id" required>
                    <option value="">Select Branch</option>
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
</body>

</html>
