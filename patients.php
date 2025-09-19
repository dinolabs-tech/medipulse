<?php
require_once 'components/functions.php';
require_once 'database/db_connection.php';

// Add Patient
if (isset($_POST['add'])) {
  $first_name = $_POST['first_name'];
  $last_name = $_POST['last_name'];
  $date_of_birth = $_POST['date_of_birth'];
  $gender = $_POST['gender'];
  $phone = $_POST['phone'];
  $email = $_POST['email'];
  $address = $_POST['address'];
  $insurance_details = $_POST['insurance_details'];

  $sql = "INSERT INTO patients (first_name, last_name, date_of_birth, gender, phone, email, address, insurance_details) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssssssss", $first_name, $last_name, $date_of_birth, $gender, $phone, $email, $address, $insurance_details);
  if ($stmt->execute()) {
    log_action($conn, $_SESSION['user_id'], "Added patient: $first_name $last_name");
  } else {
    echo "<p style='color:red;'>Error adding patient: " . $stmt->error . "</p>";
  }
  $stmt->close();
}

// Edit Patient
if (isset($_POST['edit'])) {
  $id = $_POST['id'];
  $first_name = $_POST['first_name'];
  $last_name = $_POST['last_name'];
  $date_of_birth = $_POST['date_of_birth'];
  $gender = $_POST['gender'];
  $phone = $_POST['phone'];
  $email = $_POST['email'];
  $address = $_POST['address'];
  $insurance_details = $_POST['insurance_details'];

  $sql = "UPDATE patients SET first_name=?, last_name=?, date_of_birth=?, gender=?, phone=?, email=?, address=?, insurance_details=? WHERE id=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssssssssi", $first_name, $last_name, $date_of_birth, $gender, $phone, $email, $address, $insurance_details, $id);
  if ($stmt->execute()) {
    log_action($conn, $_SESSION['user_id'], "Edited patient: $first_name $last_name (ID: $id)");
  } else {
    echo "<p style='color:red;'>Error editing patient: " . $stmt->error . "</p>";
  }
  $stmt->close();
}

// Delete Patient
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $sql_select = "SELECT first_name, last_name FROM patients WHERE id=$id";
  $result_select = $conn->query($sql_select);
  $patient_data = $result_select->fetch_assoc();

  $patient_name = '';
  if ($patient_data) {
    $patient_name = $patient_data['first_name'] . ' ' . $patient_data['last_name'];
  }

  $sql = "DELETE FROM patients WHERE id=$id";
  if ($conn->query($sql) === TRUE) {
    log_action($conn, $_SESSION['user_id'], "Deleted patient: $patient_name (ID: $id)");
  }
}

// Fetch Patients
$sql = "SELECT * FROM patients";
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
                <a href="#">Patients</a>
              </li>

            </ul>
          </div>

          <!-- new patient  -->
          <div class="card p-3">
            <div class="card-header">
              <h2>Add New Patient</h2>
            </div>
            <div class="card-body">
              <form action="patients.php" method="post">
                <div class="row">
                  <div class="col-md-3 mb-3">
                    <input type="text" class="form-control" name="first_name" placeholder="First Name" required>
                  </div>
                  <div class="col-md-3 mb-3">
                    <input type="text" class="form-control" name="last_name" placeholder="Last Name" required>
                  </div>
                  <div class="col-md-3 mb-3">
                    <input type="date" class="form-control" name="date_of_birth" placeholder="Date of Birth" onfocus="(this.type='date')" onblur="if(!this.value)this.type='text'">
                  </div>
                  <div class="col-md-3 mb-3">
                    <!-- <input type="text" class="form-control" name="gender" placeholder="Gender"> -->
                    <select name="gender" class="form-control" id="">
                      <option value="" selected disabled>Select Gender</option>
                      <option value="male">Male</option>
                      <option value="female">Female</option>
                    </select>
                  </div>
                  <div class="col-md-3 mb-3">
                    <input type="text" class="form-control" name="phone" placeholder="Phone">
                  </div>
                  <div class="col-md-3 mb-3">
                    <input type="email" class="form-control" name="email" placeholder="Email">
                  </div>
                  <div class="col-md-3 mb-3">
                    <textarea name="address" class="form-control" placeholder="Address"></textarea>
                  </div>
                  <div class="col-md-2 mb-3">
                    <textarea name="insurance_details" class="form-control" placeholder="Insurance Details"></textarea>
                  </div>
                  <div class="col-md-1">
                    <button type="submit" name="add" class="btn btn-primary btn-icon btn-round"><i class="fas fa-save"></i></button>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <!-- existing patients  -->
          <div class="card p-3">
            <div class="card-header">
              <h2>Existing Patients</h2>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered" id="basic-datatables">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Date of Birth</th>
                      <th>Gender</th>
                      <th>Phone</th>
                      <th>Email</th>
                      <th>Address</th>
                      <th>Insurance</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <?php while ($row = $result->fetch_assoc()): ?>
                    <tbody>
                      <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                        <td><?php echo $row['date_of_birth']; ?></td>
                        <td><?php echo $row['gender']; ?></td>
                        <td><?php echo $row['phone']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['address']; ?></td>
                        <td><?php echo $row['insurance_details']; ?></td>
                        <td class="d-flex">
                          <a href="patients.php?edit_id=<?php echo $row['id']; ?>" class="btn btn-primary me-3 btn-icon btn-round mb-3"><i class="fas fa-edit"></i></a>
                          <a href="patients.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-icon btn-round mb-3"><i class="fas fa-trash"></i></a>
                        </td>
                      </tr>
                    </tbody>
                  <?php endwhile; ?>
                </table>
              </div>
            </div>
          </div>

          <!-- edit patient  -->
          <?php
          // Edit form
          if (isset($_GET['edit_id'])) {
            $edit_id = $_GET['edit_id'];
            $sql = "SELECT * FROM patients WHERE id=$edit_id";
            $edit_result = $conn->query($sql);
            $edit_patient = $edit_result->fetch_assoc();
            if ($edit_patient) {
          ?>
              <div class="card p-3">
                <div class="card-header">
                  <h2>Edit Patient</h2>
                </div>
                <div class="card-body">
                  <form action="patients.php" method="post">
                    <div class="row">
                      <input type="hidden" name="id" value="<?php echo $edit_patient['id']; ?>">
                      <div class="col-md-3 mb-3">
                        <input type="text" name="first_name" class="form-control" placeholder="First Name" value="<?php echo $edit_patient['first_name']; ?>" required>
                      </div>
                      <div class="col-md-3 mb-3">
                        <input type="text" name="last_name" class="form-control" placeholder="Last Name" value="<?php echo $edit_patient['last_name']; ?>" required>
                      </div>
                      <div class="col-md-3 mb-3">
                        <input type="date" name="date_of_birth" class="form-control" placeholder="Date of Birth" value="<?php echo $edit_patient['date_of_birth']; ?>">
                      </div>
                      <div class="col-md-3 mb-3">
                        <input type="text" name="gender" class="form-control" placeholder="Gender" value="<?php echo $edit_patient['gender']; ?>">
                      </div>
                      <div class="col-md-3 mb-3">
                        <input type="text" name="phone" class="form-control" placeholder="Phone" value="<?php echo $edit_patient['phone']; ?>">
                      </div>
                      <div class="col-md-3 mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Email" value="<?php echo $edit_patient['email']; ?>">
                      </div>
                      <div class="col-md-3 mb-3">
                        <textarea name="address" class="form-control" placeholder="Address"><?php echo $edit_patient['address']; ?></textarea>
                      </div>
                      <div class="col-md-2 mb-3">
                        <textarea name="insurance_details" class="form-control" placeholder="Insurance Details"><?php echo $edit_patient['insurance_details']; ?></textarea>
                      </div>
                      <div class="col-md-1">
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
