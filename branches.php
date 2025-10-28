<?php
require_once 'components/functions.php';
require_once 'database/db_connection.php';

// Add Branch
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $country = $_POST['country'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    $sql = "INSERT INTO branches (name, address, city, state, country, phone, email) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $name, $address, $city, $state, $country, $phone, $email);
    if ($stmt->execute()) {
        log_action($conn, $_SESSION['user_id'], "Added branch: $name");
    } else {
        echo "<p style='color:red;'>Error adding branch: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Edit Branch
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $country = $_POST['country'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    $sql = "UPDATE branches SET name=?, address=?, city=?, state=?, country=?, phone=?, email=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $name, $address, $city, $state, $country, $phone, $email, $id);
    if ($stmt->execute()) {
        log_action($conn, $_SESSION['user_id'], "Edited branch: $name (ID: $id)");
    } else {
        echo "<p style='color:red;'>Error editing branch: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Delete Branch
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql_select = "SELECT name FROM branches WHERE id=$id";
    $result_select = $conn->query($sql_select);
    $branch_name = $result_select->fetch_assoc()['name'];

    $sql = "DELETE FROM branches WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        log_action($conn, $_SESSION['user_id'], "Deleted branch: $branch_name (ID: $id)");
    }
}

// Fetch Branches
$sql = "SELECT * FROM branches ORDER BY name ASC";
$result = $conn->query($sql);
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
                <a href="#">Branches</a>
              </li>
            </ul>
          </div>

          <!-- Add New Branch -->
          <div class="card p-3">
            <div class="card-header">
              <h2>Add New Branch</h2>
            </div>
            <div class="card-body">
              <form action="branches.php" method="post">
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <input type="text" class="form-control" name="name" placeholder="Branch Name" required>
                  </div>
                  <div class="col-md-4 mb-3">
                    <input type="text" class="form-control" name="address" placeholder="Address">
                  </div>
                  <div class="col-md-4 mb-3">
                    <input type="text" class="form-control" name="city" placeholder="City">
                  </div>
                  <div class="col-md-4 mb-3">
                    <input type="text" class="form-control" name="state" placeholder="State">
                  </div>
                  <div class="col-md-4 mb-3">
                    <input type="text" class="form-control" name="country" placeholder="Country">
                  </div>
                  <div class="col-md-4 mb-3">
                    <input type="text" class="form-control" name="phone" placeholder="Phone">
                  </div>
                  <div class="col-md-4 mb-3">
                    <input type="email" class="form-control" name="email" placeholder="Email">
                  </div>
                  <div class="col-md-4">
                    <button type="submit" name="add" class="btn btn-primary btn-icon btn-round"><i class="fas fa-save"></i></button>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <!-- Existing Branches -->
          <div class="card p-3">
            <div class="card-header">
              <h2>Existing Branches</h2>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table id="basic-datatables" class="table table-bordered">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Address</th>
                      <th>City</th>
                      <th>State</th>
                      <th>Country</th>
                      <th>Phone</th>
                      <th>Email</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                      <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['address']; ?></td>
                        <td><?php echo $row['city']; ?></td>
                        <td><?php echo $row['state']; ?></td>
                        <td><?php echo $row['country']; ?></td>
                        <td><?php echo $row['phone']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td>
                          <a href="branches.php?edit_id=<?php echo $row['id']; ?>" class="btn btn-primary btn-icon btn-round mb-3"><i class="fas fa-edit"></i></a>
                          <a href="branches.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-icon btn-round mb-3"><i class="fas fa-trash"></i></a>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Edit Branch -->
          <?php
          if (isset($_GET['edit_id'])) {
            $edit_id = $_GET['edit_id'];
            $sql = "SELECT * FROM branches WHERE id=$edit_id";
            $edit_result = $conn->query($sql);
            $edit_branch = $edit_result->fetch_assoc();
            if ($edit_branch) {
          ?>
              <div class="card p-3">
                <div class="card-header">
                  <h2>Edit Branch</h2>
                </div>
                <div class="card-body">
                  <form action="branches.php" method="post">
                    <div class="row">
                      <input type="hidden" name="id" value="<?php echo $edit_branch['id']; ?>">
                      <div class="col-md-4 mb-3">
                        <input type="text" class="form-control" name="name" placeholder="Branch Name" value="<?php echo $edit_branch['name']; ?>" required>
                      </div>
                      <div class="col-md-4 mb-3">
                        <input type="text" class="form-control" name="address" placeholder="Address" value="<?php echo $edit_branch['address']; ?>">
                      </div>
                      <div class="col-md-4 mb-3">
                        <input type="text" class="form-control" name="city" placeholder="City" value="<?php echo $edit_branch['city']; ?>">
                      </div>
                      <div class="col-md-4 mb-3">
                        <input type="text" class="form-control" name="state" placeholder="State" value="<?php echo $edit_branch['state']; ?>">
                      </div>
                      <div class="col-md-4 mb-3">
                        <input type="text" class="form-control" name="country" placeholder="Country" value="<?php echo $edit_branch['country']; ?>">
                      </div>
                      <div class="col-md-4 mb-3">
                        <input type="text" class="form-control" name="phone" placeholder="Phone" value="<?php echo $edit_branch['phone']; ?>">
                      </div>
                      <div class="col-md-4 mb-3">
                        <input type="email" class="form-control" name="email" placeholder="Email" value="<?php echo $edit_branch['email']; ?>">
                      </div>
                      <div class="col-md-4">
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
