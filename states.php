<?php
require_once 'components/functions.php';
require_once 'database/db_connection.php';

// Add State
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $country_id = $_POST['country_id'];

    $sql = "INSERT INTO states (name, country_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $name, $country_id);
    if ($stmt->execute()) {
        log_action($conn, $_SESSION['user_id'], "Added state: $name (Country ID: $country_id)");
    } else {
        echo "<p style='color:red;'>Error adding state: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Edit State
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $country_id = $_POST['country_id'];

    $sql = "UPDATE states SET name=?, country_id=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $name, $country_id, $id);
    if ($stmt->execute()) {
        log_action($conn, $_SESSION['user_id'], "Edited state: $name (ID: $id, Country ID: $country_id)");
    } else {
        echo "<p style='color:red;'>Error editing state: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Delete State
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql_select = "SELECT name FROM states WHERE id=?";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param("i", $id);
    $stmt_select->execute();
    $result_select = $stmt_select->get_result();
    $state_name = $result_select->fetch_assoc()['name'];
    $stmt_select->close();

    $sql = "DELETE FROM states WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        log_action($conn, $_SESSION['user_id'], "Deleted state: $state_name (ID: $id)");
    }
    $stmt->close();
}

// Fetch States
$sql = "SELECT s.*, c.name as country_name FROM states s JOIN countries c ON s.country_id = c.id ORDER BY s.name ASC";
$states_result = $conn->query($sql);

// Fetch Countries for dropdown
$countries_sql = "SELECT id, name FROM countries ORDER BY name ASC";
$countries_result = $conn->query($countries_sql);
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
                                <a href="#">States</a>
                            </li>
                        </ul>
                    </div>

                    <!-- Add New State -->
                    <div class="card p-3">
                        <div class="card-header">
                            <h2>Add New State</h2>
                        </div>
                        <div class="card-body">
                            <form action="states.php" method="post">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <input type="text" class="form-control" name="name" placeholder="State Name" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <select name="country_id" class="form-control" required>
                                            <option value="" selected disabled>Select Country</option>
                                            <?php $countries_result->data_seek(0); // Reset pointer ?>
                                            <?php while ($row = $countries_result->fetch_assoc()): ?>
                                                <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" name="add" class="btn btn-primary btn-icon btn-round"><i class="fas fa-save"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Existing States -->
                    <div class="card p-3">
                        <div class="card-header">
                            <h2>Existing States</h2>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="basic-datatables" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Country</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $states_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $row['id']; ?></td>
                                                <td><?php echo $row['name']; ?></td>
                                                <td><?php echo $row['country_name']; ?></td>
                                                <td>
                                                    <a href="states.php?edit_id=<?php echo $row['id']; ?>" class="btn btn-primary btn-icon btn-round mb-3"><i class="fas fa-edit"></i></a>
                                                    <a href="states.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-icon btn-round mb-3"><i class="fas fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <?php
                    // Edit State Form
                    if (isset($_GET['edit_id'])) {
                        $edit_id = $_GET['edit_id'];
                        $sql = "SELECT * FROM states WHERE id=?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $edit_id);
                        $stmt->execute();
                        $edit_result = $stmt->get_result();
                        $edit_state = $edit_result->fetch_assoc();
                        $stmt->close();

                        if ($edit_state) {
                            $countries_result->data_seek(0); // Reset pointer for dropdown
                    ?>
                            <div class="card p-3">
                                <div class="card-header">
                                    <h2>Edit State</h2>
                                </div>
                                <div class="card-body">
                                    <form action="states.php" method="post">
                                        <div class="row">
                                            <input type="hidden" name="id" value="<?php echo $edit_state['id']; ?>">
                                            <div class="col-md-4 mb-3">
                                                <input type="text" class="form-control" name="name" placeholder="State Name" value="<?php echo $edit_state['name']; ?>" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <select name="country_id" class="form-control" required>
                                                    <option value="">Select Country</option>
                                                    <?php while ($row = $countries_result->fetch_assoc()): ?>
                                                        <option value="<?php echo $row['id']; ?>" <?php echo ($edit_state['country_id'] == $row['id']) ? 'selected' : ''; ?>><?php echo $row['name']; ?></option>
                                                    <?php endwhile; ?>
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
