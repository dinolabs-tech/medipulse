<?php
require_once 'components/functions.php';
require_once 'database/db_connection.php';

$current_branch_id = $_SESSION['current_branch_id'] ?? null;

// Add User
if (isset($_POST['add'])) {
  $username = $_POST['username'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $role = $_POST['role'];
  $branch_id = $_POST['branch_id'];

  $sql = "INSERT INTO users (username, password, role, branch_id) VALUES (?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssi", $username, $password, $role, $branch_id);
  if ($stmt->execute()) {
    log_action($conn, $_SESSION['user_id'], "Added user: $username (Role: $role, Branch ID: $branch_id)", $current_branch_id);
  } else {
    echo "<p style='color:red;'>Error adding user: " . $stmt->error . "</p>";
  }
  $stmt->close();
}

// Edit User
if (isset($_POST['edit'])) {
  $id = $_POST['id'];
  $username = $_POST['username'];
  $role = $_POST['role'];
  $branch_id = $_POST['branch_id'];
  $password_update = "";
  if (!empty($_POST['password'])) {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $password_update = ", password=?";
  }

  $sql = "UPDATE users SET username=?, role=?, branch_id=? $password_update WHERE id=?";
  $stmt = $conn->prepare($sql);
  if (!empty($_POST['password'])) {
    $stmt->bind_param("ssisi", $username, $role, $branch_id, $password, $id);
  } else {
    $stmt->bind_param("ssii", $username, $role, $branch_id, $id);
  }

  if ($stmt->execute()) {
    log_action($conn, $_SESSION['user_id'], "Edited user: $username (ID: $id, Branch ID: $branch_id)", $current_branch_id);
  } else {
    echo "<p style='color:red;'>Error editing user: " . $stmt->error . "</p>";
  }
  $stmt->close();
}

// Delete User
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $sql_select = "SELECT username FROM users WHERE id=? AND branch_id = ?";
  $stmt_select = $conn->prepare($sql_select);
  $stmt_select->bind_param("ii", $id, $current_branch_id);
  $stmt_select->execute();
  $result_select = $stmt_select->get_result();
  $user_data = $result_select->fetch_assoc();
  $username = $user_data ? $user_data['username'] : 'Unknown User';
  $stmt_select->close();

  $sql = "DELETE FROM users WHERE id=? AND branch_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $id, $current_branch_id);
  if ($stmt->execute()) {
    log_action($conn, $_SESSION['user_id'], "Deleted user: $username (ID: $id)", $current_branch_id);
  }
  $stmt->close();
}

// Fetch Users
$sql = "SELECT u.*, b.name as branch_name FROM users u LEFT JOIN branches b ON u.branch_id = b.id WHERE u.role != 'superuser'";
if ($current_branch_id && $_SESSION['role'] != 'superuser' && $_SESSION['role'] != 'admin') {
  $sql .= " AND u.branch_id = ?";
}
$sql .= " ORDER BY u.username ASC";
$stmt = $conn->prepare($sql);
if ($current_branch_id && $_SESSION['role'] != 'superuser' && $_SESSION['role'] != 'admin') {
  $stmt->bind_param("i", $current_branch_id);
}
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// Fetch Branches for dropdown
$branches_sql = "SELECT id, name FROM branches ORDER BY name ASC";
$branches_result = $conn->query($branches_sql);
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
                <a href="#">Users</a>
              </li>

            </ul>
          </div>

          <!-- new user  -->
          <div class="card p-3">
            <div class="card-header">
              <h2>Add New User</h2>
            </div>
            <div class="card-body">
              <form action="users.php" method="post">
                <div class="row">
                  <div class="col-md-3 mb-3">
                    <input type="text" class="form-control" name="username" placeholder="Username" required>
                  </div>
                  <div class="col-md-3 mb-3">
                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                  </div>
                  <div class="col-md-3 mb-3">
                    <select name="role" class="form-control form-select" required>
                      <option selected disabled value="">Select Role</option>
                      <option value="admin">Admin</option>
                      <option value="pharmacist">Pharmacist</option>
                      <option value="assistant">Assistant</option>
                      <option value="cashier">Cashier</option>
                    </select>
                  </div>
                  <div class="col-md-3 mb-3">
                    <select name="branch_id" class="form-control form-select" required>
                      <option selected disabled value="">Select Branch</option>
                      <?php
                      if ($branches_result->num_rows > 0) {
                        while ($branch = $branches_result->fetch_assoc()) {
                          echo '<option value="' . $branch['id'] . '">' . $branch['name'] . '</option>';
                        }
                        // Reset pointer for later use if needed
                        $branches_result->data_seek(0);
                      }
                      ?>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <button type="submit" name="add" class="btn btn-primary btn-icon btn-round"><i class="fas fa-save"></i></button>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <!-- existing users  -->
          <div class="card p-3">
            <div class="card-header">
              <h2>Existing Users</h2>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table id="basic-datatables" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Username</th>
                      <th>Role</th>
                      <th>Action</th>
                    </tr>
                  </thead>

                  <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                      <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['role']; ?></td>
                        <td class="d-flex">
                          <a href="users.php?edit_id=<?php echo $row['id']; ?>" class="btn btn-primary btn-icon btn-round mx-2"><i class="fas fa-edit"></i></a>
                          <a href="users.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-icon btn-round mb-3"><i class="fas fa-trash"></i></a>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>

                </table>
              </div>
            </div>
          </div>

          <!-- edit users  -->
          <?php
          // Edit form
          if (isset($_GET['edit_id'])) {
            $edit_id = $_GET['edit_id'];
            $sql = "SELECT * FROM users WHERE id=$edit_id";
            $edit_result = $conn->query($sql);
            $edit_user = $edit_result->fetch_assoc();
            if ($edit_user) {
          ?>
              <div class="card p-3">
                <div class="card-header">
                  <h2>Edit User</h2>
                </div>
                <div class="card-body">
                  <form action="users.php" method="post">
                    <div class="row">
                      <input type="hidden" name="id" value="<?php echo $edit_user['id']; ?>">
                      <div class="col-md-3 mb-3">
                        <input type="text" class="form-control" name="username" placeholder="Username" value="<?php echo $edit_user['username']; ?>" required>
                      </div>
                      <div class="col-md-3 mb-3">
                        <input type="password" class="form-control" name="password" placeholder="New Password (leave blank to keep current)">
                      </div>
                      <div class="col-md-3 mb-3">
                        <select name="role" class="form-control" required>
                          <option value="">Select Role</option>
                          <option value="admin" <?php echo ($edit_user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                          <option value="pharmacist" <?php echo ($edit_user['role'] == 'pharmacist') ? 'selected' : ''; ?>>Pharmacist</option>
                          <option value="assistant" <?php echo ($edit_user['role'] == 'assistant') ? 'selected' : ''; ?>>Assistant</option>
                          <option value="cashier" <?php echo ($edit_user['role'] == 'cashier') ? 'selected' : ''; ?>>Cashier</option>
                        </select>
                      </div>
                      <div class="col-md-3 mb-3">
                        <select name="branch_id" class="form-control form-select" required>
                          <option value="">Select Branch</option>
                          <?php
                          if ($branches_result->num_rows > 0) {
                            while ($branch = $branches_result->fetch_assoc()) {
                              $selected = ($edit_user['branch_id'] == $branch['id']) ? 'selected' : '';
                              echo '<option value="' . $branch['id'] . '" ' . $selected . '>' . $branch['name'] . '</option>';
                            }
                            // Reset pointer for later use if needed
                            $branches_result->data_seek(0);
                          }
                          ?>
                        </select>
                      </div>
                      <div class="col-md-3">
                        <button type="submit" name="edit" class="btn btn-primary btn-icon btn-round"><i class="fas fa-save"></i></button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
          <?php
            }
          }
          ?>
        </div>
      </div>

      <?php include('components/footer.php'); ?>
    </div>
  </div>
  <!--   Core JS Files   -->
  <?php include('components/script.php'); ?>
</body>

</html>
