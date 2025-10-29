<?php
require_once 'components/functions.php';
require_once 'database/db_connection.php';

$current_branch_id = $_SESSION['current_branch_id'] ?? null;

// Add Medicine
if (isset($_POST['add'])) {
  $name = $_POST['name'];
  $description = $_POST['description'];
  $quantity = $_POST['quantity'];
  $price = $_POST['price'];
  $cost_price = $_POST['cost_price'];
  $batch_number = $_POST['batch_number'];
  $expiry_date = $_POST['expiry_date'];
  $profit_per_unit = $price - $cost_price;

  $sql = "INSERT INTO medicines (name, description, quantity, price, cost_price, profit_per_unit, batch_number, expiry_date, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssidddssi", $name, $description, $quantity, $price, $cost_price, $profit_per_unit, $batch_number, $expiry_date, $current_branch_id);
  if ($stmt->execute()) {
    log_action($conn, $_SESSION['user_id'], "Added medicine: $name", $current_branch_id);
  } else {
    echo "<p style='color:red;'>Error adding medicine: " . $stmt->error . "</p>";
  }
  $stmt->close();
}

// Edit Medicine
if (isset($_POST['edit'])) {
  $id           = (int) $_POST['id'];
  $name         = trim($_POST['name']);
  $description  = trim($_POST['description']);
  $quantity     = (int) $_POST['quantity'];
  $price        = (float) $_POST['price'];
  $cost_price   = (float) $_POST['cost_price'];
  $batch_number = trim($_POST['batch_number']);
  $expiry_date  = $_POST['expiry_date']; // validate format if needed
  $profit_per_unit = $price - $cost_price;
  $branch_id = $_POST['branch_id'];

  $sql = "UPDATE medicines 
            SET name = ?, description = ?, quantity = ?, price = ?, cost_price = ?, profit_per_unit = ?, batch_number = ?, expiry_date = ?, branch_id = ?
            WHERE id = ? AND branch_id = ?";

  $stmt = $conn->prepare($sql);
  if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
  }

  $stmt->bind_param(
    "ssidddssiii",
    $name,
    $description,
    $quantity,
    $price,
    $cost_price,
    $profit_per_unit,
    $batch_number,
    $expiry_date,
    $branch_id,
    $id,
    $current_branch_id
  );

  if ($stmt->execute()) {
    log_action($conn, $_SESSION['user_id'], "Edited medicine: $name (ID: $id)", $current_branch_id);
  } else {
    echo "<p style='color:red;'>Error editing medicine: " . $stmt->error . "</p>";
  }

  $stmt->close();
}


// Delete Medicine
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $sql_select = "SELECT name FROM medicines WHERE id=? AND branch_id = ?";
  $stmt_select = $conn->prepare($sql_select);
  $stmt_select->bind_param("ii", $id, $current_branch_id);
  $stmt_select->execute();
  $result_select = $stmt_select->get_result();
  $medicine_name = $result_select->fetch_assoc()['name'];
  $stmt_select->close();

  $sql = "DELETE FROM medicines WHERE id=? AND branch_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $id, $current_branch_id);
  if ($stmt->execute()) {
    log_action($conn, $_SESSION['user_id'], "Deleted medicine: $medicine_name (ID: $id)", $current_branch_id);
  }
  $stmt->close();
}

// Fetch Medicines
$sql = "SELECT * FROM medicines";
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
                <a href="#">Medicines</a>
              </li>
            </ul>
          </div>

          <!-- add new Medicine -->
          <div class="card p-3">
            <div class="card-header">
              <h2>Add New Medicine</h2>
            </div>
            <div class="card-body">
              <form action="medicines.php" method="post">
                <div class="row">
                  <div class="col-md-3 mb-3">
                    <input type="text" class="form-control" name="name" placeholder="Medicine Name" required>
                  </div>
                  <div class="col-md-3 mb-3">
                    <textarea name="description" class="form-control" placeholder="Description"></textarea>
                  </div>
                  <div class="col-md-3 mb-3">
                    <input type="number" class="form-control" name="quantity" placeholder="Quantity" required>
                  </div>
                  <div class="col-md-3 mb-3">
                    <input type="number" class="form-control" name="cost_price" placeholder="Cost Price" required>
                    
                  </div>
                  <div class="col-md-3 mb-3">
                    <input type="number" class="form-control" name="price" placeholder="Selling Price" required>
                  </div>
                  <div class="col-md-3 mb-3">
                    <input type="text" class="form-control" name="batch_number" placeholder="Batch Number">
                  </div>
                  <div class="col-md-3 mb-3">
                    <input type="date" class="form-control" name="expiry_date" placeholder="Expiry Date">
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
                    <button type="submit" name="add" class="btn btn-primary btn-round btn-icon"><i class="fas fa-save"></i></button>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <!-- existing medicines -->
          <div class="card p-3">
            <div class="card-header">
              <h2>Existing Medicines</h2>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <!-- <table class="table table-bordered" id="basic-datatables"> -->
                  <table class="table table-bordered" id="multi-filter-select">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Description</th>
                      <th>Quantity</th>
                      <th>Cost Price</th>
                      <th>Selling Price</th>
                      <th>Profit Per Unit</th>
                      <th>Batch Number</th>
                      <th>Expiry Date</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                 
                    <tbody>
                       <?php while ($row = $result->fetch_assoc()): ?>
                      <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['description']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                         <td><?php echo $row['cost_price']; ?></td>
                        <td><?php echo $row['price']; ?></td>
                        <td><?php echo $row['profit_per_unit']; ?></td>
                        <td><?php echo $row['batch_number']; ?></td>
                        <td><?php echo $row['expiry_date']; ?></td>
                        <td class="d-flex">
                          <a href="medicines.php?edit_id=<?php echo $row['id']; ?>" class="btn btn-primary me-3 btn-icon btn-round mb-3"><i class="fas fa-edit"></i></a>
                          <a href="medicines.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-icon btn-round mb-3"><i class="fas fa-trash"></i></a>
                        </td>
                      </tr>
                      <?php endwhile; ?>
                    </tbody>
                  
                </table>
              </div>
            </div>
          </div>

          <?php
          // Edit medicines
          if (isset($_GET['edit_id'])) {
            $edit_id = $_GET['edit_id'];
            $sql = "SELECT * FROM medicines WHERE id=$edit_id";
            $edit_result = $conn->query($sql);
            $edit_medicine = $edit_result->fetch_assoc();
            if ($edit_medicine) {
          ?>
              <div class="card p-3">
                <div class="card-header">
                  <h2>Edit Medicine</h2>
                </div>
                <div class="card-body">
                  <form action="medicines.php" method="post">
                    <div class="row">
                      <input type="hidden" name="id" value="<?php echo $edit_medicine['id']; ?>">
                      <div class="col-md-3 mb-3">
                        <input type="text" name="name" class="form-control" placeholder="Medicine Name" value="<?php echo $edit_medicine['name']; ?>" required>
                      </div>
                      <div class="col-md-3 mb-3">
                        <textarea name="description" class="form-control" placeholder="Description"><?php echo $edit_medicine['description']; ?></textarea>
                      </div>
                      <div class="col-md-3 mb-3">
                        <input type="number" name="quantity" class="form-control" placeholder="Quantity" value="<?php echo $edit_medicine['quantity']; ?>" required>
                      </div>
                      
                      <div class="col-md-3 mb-3">
                        <input type="text" name="cost_price" class="form-control" placeholder="Cost Price" value="<?php echo $edit_medicine['cost_price']; ?>" required>
                      </div>
                      <div class="col-md-3 mb-3">
                        <input type="text" name="price" class="form-control" placeholder="Selling Price" value="<?php echo $edit_medicine['price']; ?>" required>
                      </div>
                      <div class="col-md-3 mb-3">
                        <input type="text" name="batch_number" class="form-control" placeholder="Batch Number" value="<?php echo $edit_medicine['batch_number']; ?>">
                      </div>
                      <div class="col-md-3 mb-3">
                        <input type="date" name="expiry_date" class="form-control" placeholder="Expiry Date" value="<?php echo $edit_medicine['expiry_date']; ?>">
                      </div>
                      <div class="col-md-3 mb-3">
                        <select name="branch_id" class="form-control form-select" required>
                          <option value="">Select Branch</option>
                          <?php
                          if ($branches_result->num_rows > 0) {
                            while ($branch = $branches_result->fetch_assoc()) {
                              $selected = ($edit_medicine['branch_id'] == $branch['id']) ? 'selected' : '';
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
