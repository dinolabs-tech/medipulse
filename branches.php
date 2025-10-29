<?php
require_once 'components/functions.php';
require_once 'database/db_connection.php';

// Add Branch
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $state_id = $_POST['state_id'];

    $sql = "INSERT INTO branches (name, address, phone, email, state_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $name, $address, $phone, $email, $state_id);
    if ($stmt->execute()) {
        log_action($conn, $_SESSION['user_id'], "Added branch: $name (State ID: $state_id)");
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
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $state_id = $_POST['state_id'];

    $sql = "UPDATE branches SET name=?, address=?, phone=?, email=?, state_id=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssii", $name, $address, $phone, $email, $state_id, $id);
    if ($stmt->execute()) {
        log_action($conn, $_SESSION['user_id'], "Edited branch: $name (ID: $id, State ID: $state_id)");
    } else {
        echo "<p style='color:red;'>Error editing branch: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Delete Branch
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql_select = "SELECT name FROM branches WHERE id=?";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param("i", $id);
    $stmt_select->execute();
    $result_select = $stmt_select->get_result();
    $branch_name = $result_select->fetch_assoc()['name'];
    $stmt_select->close();

    $sql = "DELETE FROM branches WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        log_action($conn, $_SESSION['user_id'], "Deleted branch: $branch_name (ID: $id)");
    }
    $stmt->close();
}

// Fetch Branches
$sql = "SELECT b.*, s.name as state_name, c.name as country_name FROM branches b JOIN states s ON b.state_id = s.id JOIN countries c ON s.country_id = c.id ORDER BY b.name ASC";
$branches_result = $conn->query($sql);

// Fetch States for dropdown
$states_sql = "SELECT s.id, s.name as state_name, c.name as country_name FROM states s JOIN countries c ON s.country_id = c.id ORDER BY s.name ASC";
$states_result = $conn->query($states_sql);
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
                                    <div class="col-md-3 mb-3">
                                        <input type="text" class="form-control" name="name" placeholder="Branch Name" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <textarea name="address" class="form-control" placeholder="Address"></textarea>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <input type="text" class="form-control" name="phone" placeholder="Phone">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <input type="email" class="form-control" name="email" placeholder="Email">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <select name="state_id" class="form-control" required>
                                            <option value="" selected disabled>Select State</option>
                                            <?php $states_result->data_seek(0); // Reset pointer ?>
                                            <?php while ($row = $states_result->fetch_assoc()): ?>
                                                <option value="<?php echo $row['id']; ?>"><?php echo $row['state_name'] . " (" . $row['country_name'] . ")"; ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
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
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>State</th>
                                            <th>Country</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $branches_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $row['id']; ?></td>
                                                <td><?php echo $row['name']; ?></td>
                                                <td><?php echo $row['address']; ?></td>
                                                <td><?php echo $row['phone']; ?></td>
                                                <td><?php echo $row['email']; ?></td>
                                                <td><?php echo $row['state_name']; ?></td>
                                                <td><?php echo $row['country_name']; ?></td>
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

                    <?php
                    // Edit Branch Form
                    if (isset($_GET['edit_id'])) {
                        $edit_id = $_GET['edit_id'];
                        $sql = "SELECT * FROM branches WHERE id=?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $edit_id);
                        $stmt->execute();
                        $edit_result = $stmt->get_result();
                        $edit_branch = $edit_result->fetch_assoc();
                        $stmt->close();

                        if ($edit_branch) {
                            $states_result->data_seek(0); // Reset pointer for dropdown
                    ?>
                            <div class="card p-3">
                                <div class="card-header">
                                    <h2>Edit Branch</h2>
                                </div>
                                <div class="card-body">
                                    <form action="branches.php" method="post">
                                        <div class="row">
                                            <input type="hidden" name="id" value="<?php echo $edit_branch['id']; ?>">
                                            <div class="col-md-3 mb-3">
                                                <input type="text" class="form-control" name="name" placeholder="Branch Name" value="<?php echo $edit_branch['name']; ?>" required>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <textarea name="address" class="form-control" placeholder="Address"><?php echo $edit_branch['address']; ?></textarea>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <input type="text" class="form-control" name="phone" placeholder="Phone" value="<?php echo $edit_branch['phone']; ?>">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <input type="email" class="form-control" name="email" placeholder="Email" value="<?php echo $edit_branch['email']; ?>">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <select name="state_id" class="form-control" required>
                                                    <option value="">Select State</option>
                                                    <?php while ($row = $states_result->fetch_assoc()): ?>
                                                        <option value="<?php echo $row['id']; ?>" <?php echo ($edit_branch['state_id'] == $row['id']) ? 'selected' : ''; ?>><?php echo $row['state_name'] . " (" . $row['country_name'] . ")"; ?></option>
                                                    <?php endwhile; ?>
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
