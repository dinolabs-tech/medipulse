<?php
require_once 'components/functions.php';
require_once 'database/db_connection.php';

$current_branch_id = $_SESSION['current_branch_id'] ?? null;

// Add Prescription
if (isset($_POST['add'])) {
  $patient_id = $_POST['patient_id'];
  $medicine_id = $_POST['medicine_id'];
  $doctor_name = $_POST['doctor_name'];
  $dosage = $_POST['dosage'];
  $frequency = $_POST['frequency'];
  $duration = $_POST['duration'];
  $refills = $_POST['refills'];
  $prescription_date = $_POST['prescription_date'];

  $sql = "INSERT INTO prescriptions (patient_id, medicine_id, doctor_name, dosage, frequency, duration, refills, prescription_date, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("iissssisi", $patient_id, $medicine_id, $doctor_name, $dosage, $frequency, $duration, $refills, $prescription_date, $current_branch_id);
  if ($stmt->execute()) {
    log_action($conn, $_SESSION['user_id'], "Added prescription for patient ID: $patient_id, medicine ID: $medicine_id", $current_branch_id);
  } else {
    echo "<p style='color:red;'>Error adding prescription: " . $stmt->error . "</p>";
  }
  $stmt->close();
}

// Edit Prescription
if (isset($_POST['edit'])) {
  $id = $_POST['id'];
  $patient_id = $_POST['patient_id'];
  $medicine_id = $_POST['medicine_id'];
  $doctor_name = $_POST['doctor_name'];
  $dosage = $_POST['dosage'];
  $frequency = $_POST['frequency'];
  $duration = $_POST['duration'];
  $refills = $_POST['refills'];
  $prescription_date = $_POST['prescription_date'];

  $sql = "UPDATE prescriptions SET patient_id=?, medicine_id=?, doctor_name=?, dosage=?, frequency=?, duration=?, refills=?, prescription_date=? WHERE id=? AND branch_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("iisssisiii", $patient_id, $medicine_id, $doctor_name, $dosage, $frequency, $duration, $refills, $prescription_date, $id, $current_branch_id);
  if ($stmt->execute()) {
    log_action($conn, $_SESSION['user_id'], "Edited prescription ID: $id (Patient ID: $patient_id, Medicine ID: $medicine_id)", $current_branch_id);
  } else {
    echo "<p style='color:red;'>Error editing prescription: " . $stmt->error . "</p>";
  }
  $stmt->close();
}

// Delete Prescription
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $sql_select = "SELECT patient_id, medicine_id FROM prescriptions WHERE id=? AND branch_id = ?";
  $stmt_select = $conn->prepare($sql_select);
  $stmt_select->bind_param("ii", $id, $current_branch_id);
  $stmt_select->execute();
  $result_select = $stmt_select->get_result();
  $prescription_details = $result_select->fetch_assoc();
  $patient_id = $prescription_details['patient_id'];
  $medicine_id = $prescription_details['medicine_id'];
  $stmt_select->close();

  $sql = "DELETE FROM prescriptions WHERE id=? AND branch_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $id, $current_branch_id);
  if ($stmt->execute()) {
    log_action($conn, $_SESSION['user_id'], "Deleted prescription ID: $id (Patient ID: $patient_id, Medicine ID: $medicine_id)", $current_branch_id);
  }
  $stmt->close();
}

// Fetch Prescriptions
$sql = "SELECT pr.*, p.first_name, p.last_name, m.name as medicine_name FROM prescriptions pr JOIN patients p ON pr.patient_id = p.id JOIN medicines m ON pr.medicine_id = m.id";
if ($current_branch_id && $_SESSION['role'] != 'superuser' && $_SESSION['role'] != 'admin') {
  $sql .= " WHERE pr.branch_id = ?";
}
$sql .= " ORDER BY pr.prescription_date DESC";
$stmt = $conn->prepare($sql);
if ($current_branch_id && $_SESSION['role'] != 'superuser' && $_SESSION['role'] != 'admin') {
  $stmt->bind_param("i", $current_branch_id);
}
$stmt->execute();
$prescriptions_result = $stmt->get_result();
$stmt->close();

// Fetch Patients for dropdown
$sql = "SELECT id, first_name, last_name FROM patients";
if ($current_branch_id && $_SESSION['role'] != 'superuser' && $_SESSION['role'] != 'admin') {
  $sql .= " WHERE branch_id = ?";
}
$stmt = $conn->prepare($sql);
if ($current_branch_id && $_SESSION['role'] != 'superuser' && $_SESSION['role'] != 'admin') {
  $stmt->bind_param("i", $current_branch_id);
}
$stmt->execute();
$patients_result = $stmt->get_result();
$stmt->close();

// Fetch Medicines for dropdown
$sql = "SELECT id, name FROM medicines";
if ($current_branch_id && $_SESSION['role'] != 'superuser' && $_SESSION['role'] != 'admin') {
  $sql .= " WHERE branch_id = ?";
}
$stmt = $conn->prepare($sql);
if ($current_branch_id && $_SESSION['role'] != 'superuser' && $_SESSION['role'] != 'admin') {
  $stmt->bind_param("i", $current_branch_id);
}
$stmt->execute();
$medicines_result = $stmt->get_result();
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
          <!-- breadcrumbs -->
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
                <a href="#">Prescriptions</a>
              </li>
            </ul>
          </div>

          <!-- new prescription -->
          <div class="card p-3">
            <div class="card-header">
            <h2>Add New Prescription</h2>
            </div>
            <div class="card-body">
            <form action="prescriptions.php" method="post">
              <div class="row">
                <div class="col-md-4 mb-3">
                  <select name="patient_id" class="form-control" required>
                    <option value="" selected disabled>Select Patient</option>
                    <?php while ($row = $patients_result->fetch_assoc()): ?>
                      <option value="<?php echo $row['id']; ?>"><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>
                <div class="col-md-4 mb-3">
                  <select name="medicine_id" class="form-control" required>
                    <option value="" selected disabled>Select Medicine</option>
                    <?php while ($row = $medicines_result->fetch_assoc()): ?>
                      <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>
                <div class="col-md-4 mb-3">
                  <input type="text" class="form-control" name="doctor_name" placeholder="Doctor's Name">
                </div>
                <div class="col-md-4 mb-3">
                  <input type="text" class="form-control" name="dosage" placeholder="Dosage">
                </div>
                <div class="col-md-4 mb-3">
                  <input type="text" class="form-control" name="frequency" placeholder="Frequency">
                </div>
                <div class="col-md-4 mb-3">
                  <input type="text" class="form-control" name="duration" placeholder="Duration">
                </div>
                <div class="col-md-4 mb-3">
                  <input type="number" class="form-control" name="refills" placeholder="Refills">
                </div>
                <div class="col-md-4 mb-3">
                  <input type="date" class="form-control" name="prescription_date" placeholder="Prescription Date">
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

          <!-- existing prescription -->
          <div class="card p-3">
            <div class="card-header">
            <h2>Existing Prescriptions</h2>
            </div>
            <div class="card-body">
            <div class="table-responsive">
              <table id="basic-datatables" class="table table-bordered">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Patient</th>
                    <th>Medicine</th>
                    <th>Doctor</th>
                    <th>Dosage</th>
                    <th>Frequency</th>
                    <th>Duration</th>
                    <th>Refills</th>
                    <th>Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                
                  <tbody>
                    <?php while ($row = $prescriptions_result->fetch_assoc()): ?>
                    <tr>
                      <td><?php echo $row['id']; ?></td>
                      <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                      <td><?php echo $row['medicine_name']; ?></td>
                      <td><?php echo $row['doctor_name']; ?></td>
                      <td><?php echo $row['dosage']; ?></td>
                      <td><?php echo $row['frequency']; ?></td>
                      <td><?php echo $row['duration']; ?></td>
                      <td><?php echo $row['refills']; ?></td>
                      <td><?php echo $row['prescription_date']; ?></td>
                      <td>
                        <a href="prescriptions.php?edit_id=<?php echo $row['id']; ?>" class="btn btn-primary btn-icon btn-round mb-3 "><i class="fas fa-edit"></i></a>
                        <a href="prescriptions.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-icon btn-round mb-3"><i class="fas fa-trash"></i></a>
                      </td>
                    </tr>
                    <?php endwhile; ?>
                  </tbody>
                
              </table>
            </div>
            </div>
          </div>

          <!-- edit prescriptions -->
          <?php
          // Edit form
          if (isset($_GET['edit_id'])) {
            $edit_id = $_GET['edit_id'];
            $sql = "SELECT * FROM prescriptions WHERE id=$edit_id";
            $edit_result = $conn->query($sql);
            $edit_prescription = $edit_result->fetch_assoc();
            if ($edit_prescription) {
              // Re-fetch patients and medicines for the dropdowns in the edit form
              $patients_result->data_seek(0); // Reset pointer
              $medicines_result->data_seek(0); // Reset pointer
          ?>
              <div class="card p-3">
                <div class="card-header">
                <h2>Edit Prescription</h2>
                </div>
                <div class="card-body">
                <form action="prescriptions.php" method="post">
                  <div class="row">
                    <input type="hidden" name="id" value="<?php echo $edit_prescription['id']; ?>">
                    <div class="col-md-4 mb-3">
                      <select name="patient_id" class="form-control" required>
                        <option value="">Select Patient</option>
                        <?php while ($row = $patients_result->fetch_assoc()): ?>
                          <option value="<?php echo $row['id']; ?>" <?php echo ($edit_prescription['patient_id'] == $row['id']) ? 'selected' : ''; ?>><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></option>
                        <?php endwhile; ?>
                      </select>
                    </div>
                    <div class="col-md-4 mb-3">
                      <select name="medicine_id" class="form-control" required>
                        <option value="">Select Medicine</option>
                        <?php while ($row = $medicines_result->fetch_assoc()): ?>
                          <option value="<?php echo $row['id']; ?>" <?php echo ($edit_prescription['medicine_id'] == $row['id']) ? 'selected' : ''; ?>><?php echo $row['name']; ?></option>
                        <?php endwhile; ?>
                      </select>
                    </div>
                    <div class="col-md-4 mb-3">
                      <input type="text" class="form-control" name="doctor_name" placeholder="Doctor's Name" value="<?php echo $edit_prescription['doctor_name']; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                      <input type="text" class="form-control" name="dosage" placeholder="Dosage" value="<?php echo $edit_prescription['dosage']; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                      <input type="text" class="form-control" name="frequency" placeholder="Frequency" value="<?php echo $edit_prescription['frequency']; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                      <input type="text" class="form-control" name="duration" placeholder="Duration" value="<?php echo $edit_prescription['duration']; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                      <input type="number" class="form-control" name="refills" placeholder="Refills" value="<?php echo $edit_prescription['refills']; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                      <input type="date" class="form-control" name="prescription_date" placeholder="Prescription Date" value="<?php echo $edit_prescription['prescription_date']; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                      <select name="branch_id" class="form-control form-select" required>
                        <option value="">Select Branch</option>
                        <?php
                        if ($branches_result->num_rows > 0) {
                          while ($branch = $branches_result->fetch_assoc()) {
                            $selected = ($edit_prescription['branch_id'] == $branch['id']) ? 'selected' : '';
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
