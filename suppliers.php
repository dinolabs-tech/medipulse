<?php
require_once 'components/functions.php';
require_once 'database/db_connection.php';

$current_branch_id = $_SESSION['current_branch_id'] ?? null;

// Add Supplier
if (isset($_POST['add'])) {
  $name = $_POST['name'];
  $contact_person = $_POST['contact_person'];
  $phone = $_POST['phone'];
  $email = $_POST['email'];
  $address = $_POST['address'];

  $sql = "INSERT INTO suppliers (name, contact_person, phone, email, address, branch_id) VALUES (?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssssi", $name, $contact_person, $phone, $email, $address, $current_branch_id);
  if ($stmt->execute()) {
    log_action($conn, $_SESSION['user_id'], "Added supplier: $name", $current_branch_id);
  } else {
    echo "<p style='color:red;'>Error adding supplier: " . $stmt->error . "</p>";
  }
  $stmt->close();
}

// Edit Supplier
if (isset($_POST['edit'])) {
  $id = $_POST['id'];
  $name = $_POST['name'];
  $contact_person = $_POST['contact_person'];
  $phone = $_POST['phone'];
  $email = $_POST['email'];
  $address = $_POST['address'];
  $branch_id = $_POST['branch_id'];

  $sql = "UPDATE suppliers SET name=?, contact_person=?, phone=?, email=?, address=?, branch_id=? WHERE id=? AND branch_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssssiii", $name, $contact_person, $phone, $email, $address, $branch_id, $id, $current_branch_id);
  if ($stmt->execute()) {
    log_action($conn, $_SESSION['user_id'], "Edited supplier: $name (ID: $id)", $current_branch_id);
  } else {
    echo "<p style='color:red;'>Error editing supplier: " . $stmt->error . "</p>";
  }
  $stmt->close();
}

// Delete Supplier
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $sql_select = "SELECT name FROM suppliers WHERE id=? AND branch_id = ?";
  $stmt_select = $conn->prepare($sql_select);
  $stmt_select->bind_param("ii", $id, $current_branch_id);
  $stmt_select->execute();
  $result_select = $stmt_select->get_result();
  $supplier_name = $result_select->fetch_assoc()['name'];
  $stmt_select->close();

  $sql = "DELETE FROM suppliers WHERE id=? AND branch_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $id, $current_branch_id);
  if ($stmt->execute()) {
    log_action($conn, $_SESSION['user_id'], "Deleted supplier: $supplier_name (ID: $id)", $current_branch_id);
  }
  $stmt->close();
}

// Fetch Suppliers
$sql = "SELECT * FROM suppliers";
if ($current_branch_id && $_SESSION['role'] != 'superuser' && $_SESSION['role'] != 'admin') {
  $sql .= " WHERE branch_id = ?";
}
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
                <a href="#">Suppliers</a>
              </li>

            </ul>
          </div>

          <!-- new Supplier -->
          <div class="card p-3">
            <div class="card-header">
            <h2>Add New Supplier</h2>
            </div>
            <div class="card-body">
            <form action="suppliers.php" method="post">
              <div class="row">
                <div class="col-md-4 mb-3">
                  <input type="text" class="form-control" name="name" placeholder="Supplier Name" required>
                </div>
                <div class="col-md-4 mb-3">
                  <input type="text" class="form-control" name="contact_person" placeholder="Contact Person">
                </div>
                <div class="col-md-4 mb-3">
                  <input type="text" class="form-control" name="phone" placeholder="Phone">
                </div>
                <div class="col-md-4 mb-3">
                  <input type="email" class="form-control" name="email" placeholder="Email">
                </div>
                <div class="col-md-4 mb-3">
                  <textarea name="address" class="form-control" placeholder="Address"></textarea>
                </div>
                <div class="col-md-4 mb-3">
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
                <div class="col-md-4">
                  <button type="submit" name="add" class="btn btn-primary btn-icon btn-round"><i class="fas fa-save"></i></button>
                </div>
              </div>
            </form>
            </div>
          </div>

          <!-- exisiting suppliers -->
          <div class="card p-3">
            <div class="card-header">
            <h2>Existing Suppliers</h2>
            </div>
            <div class="card-body">
            <div class="table-responsive">
              <table id="basic-datatables" class="table table-bordered">
                <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Contact Person</th>
                  <th>Phone</th>
                  <th>Email</th>
                  <th>Address</th>
                  <th>Action</th>
                </tr>
                </thead>
             
                  <tbody>
                       <?php while ($row = $result->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['contact_person']; ?></td>
                    <td><?php echo $row['phone']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['address']; ?></td>
                    <td>
                      <a href="suppliers.php?edit_id=<?php echo $row['id']; ?>" class="btn btn-primary btn-icon btn-round mb-3"><i class="fas fa-edit"></i></a> 
                      <a href="suppliers.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-icon btn-round mb-3"><i class="fas fa-trash"></i></a>
                    </td>
                  </tr>
                    <?php endwhile; ?>
                  </tbody>
              
              </table>
            </div>
            </div>
          </div>

          <!-- edit suppliers -->
          <?php
          // Edit form
          if (isset($_GET['edit_id'])) {
            $edit_id = $_GET['edit_id'];
            $sql = "SELECT * FROM suppliers WHERE id=$edit_id";
            $edit_result = $conn->query($sql);
            $edit_supplier = $edit_result->fetch_assoc();
            if ($edit_supplier) {
          ?>
          <div class="card p-3">
            <div class="card-header">
              <h2>Edit Supplier</h2>
            </div>
            <div class="card-body">
              <form action="suppliers.php" method="post">
                <div class="row">
                <input type="hidden" name="id" value="<?php echo $edit_supplier['id']; ?>">
                <div class="col-md-4 mb-3">
                <input type="text" class="form-control" name="name" placeholder="Supplier Name" value="<?php echo $edit_supplier['name']; ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                <input type="text" class="form-control"  name="contact_person" placeholder="Contact Person" value="<?php echo $edit_supplier['contact_person']; ?>">
                </div>
                <div class="col-md-4 mb-3">
                <input type="text" class="form-control"  name="phone" placeholder="Phone" value="<?php echo $edit_supplier['phone']; ?>">
                </div>
                <div class="col-md-4 mb-3">
                <input type="email" class="form-control"  name="email" placeholder="Email" value="<?php echo $edit_supplier['email']; ?>">
                </div>
                <div class="col-md-4 mb-3">
                <textarea name="address" class="form-control"  placeholder="Address"><?php echo $edit_supplier['address']; ?></textarea>
                </div>
                <div class="col-md-4 mb-3">
                  <select name="branch_id" class="form-control form-select" required>
                    <option value="">Select Branch</option>
                    <?php
                    if ($branches_result->num_rows > 0) {
                      while ($branch = $branches_result->fetch_assoc()) {
                        $selected = ($edit_supplier['branch_id'] == $branch['id']) ? 'selected' : '';
                        echo '<option value="' . $branch['id'] . '" ' . $selected . '>' . $branch['name'] . '</option>';
                      }
                      // Reset pointer for later use if needed
                      $branches_result->data_seek(0);
                    }
                    ?>
                  </select>
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
