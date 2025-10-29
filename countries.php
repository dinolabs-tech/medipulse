<?php
require_once 'components/functions.php';
require_once 'database/db_connection.php';

// Add Country
if (isset($_POST['add'])) {
    $name = $_POST['name'];

    $sql = "INSERT INTO countries (name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $name);
    if ($stmt->execute()) {
        log_action($conn, $_SESSION['user_id'], "Added country: $name");
    } else {
        echo "<p style='color:red;'>Error adding country: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Edit Country
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];

    $sql = "UPDATE countries SET name=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $name, $id);
    if ($stmt->execute()) {
        log_action($conn, $_SESSION['user_id'], "Edited country: $name (ID: $id)");
    } else {
        echo "<p style='color:red;'>Error editing country: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Delete Country
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql_select = "SELECT name FROM countries WHERE id=?";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param("i", $id);
    $stmt_select->execute();
    $result_select = $stmt_select->get_result();
    $country_name = $result_select->fetch_assoc()['name'];
    $stmt_select->close();

    $sql = "DELETE FROM countries WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        log_action($conn, $_SESSION['user_id'], "Deleted country: $country_name (ID: $id)");
    }
    $stmt->close();
}

// Fetch Countries
$sql = "SELECT * FROM countries ORDER BY name ASC";
$countries_result = $conn->query($sql);
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
                                <a href="#">Countries</a>
                            </li>
                        </ul>
                    </div>

                    <!-- Add New Country -->
                    <div class="card p-3">
                        <div class="card-header">
                            <h2>Add New Country</h2>
                        </div>
                        <div class="card-body">
                            <form action="countries.php" method="post">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <input type="text" class="form-control" name="name" placeholder="Country Name" required>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" name="add" class="btn btn-primary btn-icon btn-round"><i class="fas fa-save"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Existing Countries -->
                    <div class="card p-3">
                        <div class="card-header">
                            <h2>Existing Countries</h2>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="basic-datatables" class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $countries_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $row['id']; ?></td>
                                                <td><?php echo $row['name']; ?></td>
                                                <td>
                                                    <a href="countries.php?edit_id=<?php echo $row['id']; ?>" class="btn btn-primary btn-icon btn-round mb-3"><i class="fas fa-edit"></i></a>
                                                    <a href="countries.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-icon btn-round mb-3"><i class="fas fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <?php
                    // Edit Country Form
                    if (isset($_GET['edit_id'])) {
                        $edit_id = $_GET['edit_id'];
                        $sql = "SELECT * FROM countries WHERE id=?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $edit_id);
                        $stmt->execute();
                        $edit_result = $stmt->get_result();
                        $edit_country = $edit_result->fetch_assoc();
                        $stmt->close();

                        if ($edit_country) {
                    ?>
                            <div class="card p-3">
                                <div class="card-header">
                                    <h2>Edit Country</h2>
                                </div>
                                <div class="card-body">
                                    <form action="countries.php" method="post">
                                        <div class="row">
                                            <input type="hidden" name="id" value="<?php echo $edit_country['id']; ?>">
                                            <div class="col-md-8 mb-3">
                                                <input type="text" class="form-control" name="name" placeholder="Country Name" value="<?php echo $edit_country['name']; ?>" required>
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
