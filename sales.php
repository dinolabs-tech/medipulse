<?php
require_once 'components/functions.php';
require_once 'database/db_connection.php';

// Handle AJAX request to clear print session variables
if (isset($_POST['clear_print_session']) && $_POST['clear_print_session'] === 'true') {
    unset($_SESSION['last_receipt']);
    unset($_SESSION['print_receipt']);
    echo json_encode(['status' => 'success']);
    exit();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to Cart
if (isset($_POST['add_to_cart'])) {
    $medicine_id = $_POST['medicine_id'];
    $quantity_to_add = $_POST['quantity_to_add'];

    $sql = "SELECT id, name, price, quantity, expiry_date, profit_per_unit FROM medicines WHERE id=$medicine_id";
    $result = $conn->query($sql);
    $medicine = $result->fetch_assoc();

    if ($medicine) {
        $current_date = date('Y-m-d');
        if ($medicine['expiry_date'] < $current_date) {
            echo "<p style='color:red;'>Error: " . $medicine['name'] . " has expired and cannot be added to cart.</p>";
        } else {
            $available_stock = $medicine['quantity'];
            $current_cart_quantity = isset($_SESSION['cart'][$medicine_id]) ? $_SESSION['cart'][$medicine_id]['quantity'] : 0;

            if (($current_cart_quantity + $quantity_to_add) > $available_stock) {
                echo "<p style='color:red;'>Error: Not enough stock available for " . $medicine['name'] . ". Available: " . $available_stock . "</p>";
            } else {
                if (isset($_SESSION['cart'][$medicine_id])) {
                    $_SESSION['cart'][$medicine_id]['quantity'] += $quantity_to_add;
                } else {
                    $_SESSION['cart'][$medicine_id] = [
                        'id' => $medicine['id'],
                        'name' => $medicine['name'],
                        'price' => $medicine['price'],
                        'quantity' => $quantity_to_add,
                        'profit_per_unit' => $medicine['profit_per_unit']
                    ];
                }
                log_action($conn, $_SESSION['user_id'], "Added " . $quantity_to_add . " of " . $medicine['name'] . " to cart.");
            }
        }
    }
}

// Update Cart Item Quantity
if (isset($_POST['update_cart_item'])) {
    $medicine_id = $_POST['medicine_id'];
    $new_quantity = $_POST['new_quantity'];

    if ($new_quantity <= 0) {
        unset($_SESSION['cart'][$medicine_id]);
        log_action($conn, $_SESSION['user_id'], "Removed item from cart (ID: $medicine_id).");
    } else {
        $sql = "SELECT quantity FROM medicines WHERE id=$medicine_id";
        $result = $conn->query($sql);
        $medicine_stock = $result->fetch_assoc()['quantity'];

        if ($new_quantity > $medicine_stock) {
            echo "<p style='color:red;'>Error: Not enough stock available. Available: " . $medicine_stock . "</p>";
        } else {
            $_SESSION['cart'][$medicine_id]['quantity'] = $new_quantity;
            log_action($conn, $_SESSION['user_id'], "Updated quantity of item (ID: $medicine_id) in cart to $new_quantity.");
        }
    }
}

// Remove from Cart
if (isset($_GET['remove_from_cart'])) {
    $medicine_id = $_GET['remove_from_cart'];
    if (isset($_SESSION['cart'][$medicine_id])) {
        $medicine_name = $_SESSION['cart'][$medicine_id]['name'];
        unset($_SESSION['cart'][$medicine_id]);
        log_action($conn, $_SESSION['user_id'], "Removed " . $medicine_name . " from cart.");
    }
}

// Function to generate a unique invoice number
function generateUniqueInvoiceNumber($conn) {
    $invoice_number = '';
    $is_unique = false;
    while (!$is_unique) {
        $invoice_number = 'INV-' . date('Ymd') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $sql = "SELECT id FROM sales WHERE invoice_number = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $invoice_number);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows == 0) {
            $is_unique = true;
        }
        $stmt->close();
    }
    return $invoice_number;
}

// Checkout
if (isset($_POST['checkout'])) {
    $patient_id = NULL;
    if (isset($_POST['patient_id']) && $_POST['patient_id'] !== '') {
        $patient_id = $_POST['patient_id'];
    }
    $total_sale_price = 0;
    $sale_successful = true;
    $receipt_items = [];

    foreach ($_SESSION['cart'] as $item_id => $item) {
        $sql = "SELECT quantity, expiry_date FROM medicines WHERE id=" . $item['id'];
        $result = $conn->query($sql);
        $medicine_data = $result->fetch_assoc();
        $medicine_stock = $medicine_data['quantity'];
        $medicine_expiry_date = $medicine_data['expiry_date'];

        $current_date = date('Y-m-d');
        if ($medicine_expiry_date < $current_date) {
            echo "<p style='color:red;'>Error: " . $item['name'] . " has expired and cannot be sold.</p>";
            $sale_successful = false;
            break;
        }

        if ($item['quantity'] > $medicine_stock) {
            echo "<p style='color:red;'>Error: Not enough stock for " . $item['name'] . ". Available: " . $medicine_stock . "</p>";
            $sale_successful = false;
            break;
        }
    }

    if ($sale_successful) {
        $invoice_number = generateUniqueInvoiceNumber($conn);
        $user_id = $_SESSION['user_id'];

        foreach ($_SESSION['cart'] as $item_id => $item) {
            $total_item_price = $item['price'] * $item['quantity'];
            $profit_per_item = $item['profit_per_unit'] * $item['quantity'];
            $total_sale_price += $total_item_price;

            // Insert into sales
            $sql = "INSERT INTO sales (invoice_number, patient_id, medicine_id, quantity_sold, total_price, profit, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siiiddd", $invoice_number, $patient_id, $item['id'], $item['quantity'], $total_item_price, $profit_per_item, $user_id);
            $stmt->execute();

            // Update medicine quantity
            $new_quantity = $medicine_stock - $item['quantity'];
            $sql = "UPDATE medicines SET quantity=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $new_quantity, $item['id']);
            $stmt->execute();

            $receipt_items[] = $item;
        }
        log_action($conn, $_SESSION['user_id'], "Completed sale with Invoice No: " . $invoice_number . " for patient ID: " . ($patient_id ?? 'N/A') . ", total: $total_sale_price");
        $_SESSION['last_receipt'] = ['invoice_number' => $invoice_number, 'items' => $receipt_items, 'total' => $total_sale_price, 'patient_id' => $patient_id];
        $_SESSION['cart'] = []; // Clear cart after successful checkout
        $_SESSION['print_receipt'] = true; // Set flag to trigger print
    }
}

// Fetch Medicines for dropdown
$current_date = date('Y-m-d');
$sql = "SELECT id, name, price, quantity, expiry_date FROM medicines WHERE quantity > 0 AND expiry_date >= '$current_date' ORDER BY name ASC";
$medicines_result = $conn->query($sql);

// Fetch Patients for dropdown
$sql = "SELECT id, first_name, last_name FROM patients ORDER BY first_name ASC";
$patients_result = $conn->query($sql);

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
                <a href="#">Sales</a>
              </li>
            </ul>
          </div>

          <h2>Point of Sale</h2>
          <div class="pos-container">
            <div class="row">
              <div class="col-md-6">
                <div class="card p-3">
                  <div class="pos-section">
                    <div class="card-header">
                      <h3>Add Item to Cart</h3>
                    </div>
                    <div class="card-body">
                      <form action="sales.php" method="post">
                        <div class="row">
                          <div class="col-md-12 mb-3">
                            <input type="text" id="medicineSearch" class="form-control" onkeyup="filterOptions('medicineSearch', 'medicineSelect')" placeholder="Search for medicine...">
                          </div>
                          <div class="col-md-12 mb-3">
                            <select name="medicine_id" class="form-control" id="medicineSelect" required>
                              <option value="" selected disabled>Select Medicine</option>
                              <?php while ($row = $medicines_result->fetch_assoc()): ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo $row['name'] . " (Stock: " . $row['quantity'] . ", &#8358;" . $row['price'] . ")"; ?></option>
                              <?php endwhile; ?>
                            </select>
                          </div>
                          <div class="col-md-10 mb-3">
                            <input type="number" class="form-control" name="quantity_to_add" placeholder="Quantity" value="1" min="1" required>
                          </div>
                          <div class="col-md-2">
                            <button type="submit" name="add_to_cart" class="btn btn-primary btn-icon btn-round"><i class="fas fa-shopping-cart"></i></button>
                          </div>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="card p-3">
                  <div class="pos-section cart-section">
                    <div class="card-header">
                      <h3>Current Cart</h3>
                    </div>
                    <div class="card-body">
                      <?php if (empty($_SESSION['cart'])): ?>
                        <p>Your cart is empty.</p>
                      <?php else: ?>
                        <div class="table-responsive">
                          <table class="table table-bordered">
                            <tr>
                              <th>Medicine</th>
                              <th>Price</th>
                              <th>Quantity</th>
                              <th>Subtotal</th>
                              <th>Action</th>
                            </tr>
                            <?php $cart_total = 0; ?>
                            <?php foreach ($_SESSION['cart'] as $item): ?>
                              <?php
                              $subtotal = $item['price'] * $item['quantity'];
                              $cart_total += $subtotal;
                              ?>
                              <tr>
                                <td><?php echo $item['name']; ?></td>
                                <td>&#8358;<?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                  <form action="sales.php" method="post" style="display:inline;">
                                    <div class="row">
                                      <input type="hidden" name="medicine_id" value="<?php echo $item['id']; ?>">
                                      <input type="number" class="form-control" name="new_quantity" value="<?php echo $item['quantity']; ?>" min="0" style="width: 60px;">
                                      <button type="submit" name="update_cart_item" class="btn btn-primary btn-icon btn-round ms-3"><i class="fas fa-plus"></i></button>
                                    </div>
                                  </form>
                                <td>&#8358;<?php echo number_format($subtotal, 2); ?></td>
                                <td><a href="sales.php?remove_from_cart=<?php echo $item['id']; ?>" class="btn btn-danger btn-icon btn-round"><i class="fas fa-trash"></i></a></td>
                              </tr>
                            <?php endforeach; ?>
                            <tr>
                              <td colspan="3" style="text-align:right;"><strong>Total:</strong></td>
                              <td><strong>&#8358;<?php echo number_format($cart_total, 2); ?></strong></td>
                              <td></td>
                            </tr>
                          </table>
                        </div>
                    </div>

                    <div class="card-header">
                      <h3>Checkout</h3>
                    </div>
                    <div class="card-body">
                      <form action="sales.php" method="post">
                        <div class="row">
                          <div class="col-md-12 mb-3">
                            <input type="text" id="patientSearch" class="form-control" onkeyup="filterOptions('patientSearch', 'patientSelect')" placeholder="Search for patient...">
                          </div>
                          <div class="col-md-10 mb-3">
                            <select name="patient_id" class="form-control" id="patientSelect">
                              <option value="" selected disabled>Select Patient (Optional)</option>
                              <?php $patients_result->data_seek(0); ?>
                              <?php while ($row = $patients_result->fetch_assoc()): ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></option>
                              <?php endwhile; ?>
                            </select>
                          </div>
                          <div class="col-md-2">
                            <button type="submit" name="checkout" class="btn btn-primary btn-icon btn-round"><i class="fas fa-check-double"></i></button>
                          </div>
                        </div>
                      </form>
                    <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <?php include('components/footer.php'); ?>
    </div>
  </div>
  <!--   Core JS Files   -->
  <?php include('components/script.php'); ?>
  <script>
    function filterOptions(searchInputId, selectId) {
      const input = document.getElementById(searchInputId);
      const filter = input.value.toLowerCase();
      const select = document.getElementById(selectId);
      const options = select.getElementsByTagName('option');

      for (let i = 0; i < options.length; i++) {
        const option = options[i];
        const textValue = option.textContent || option.innerText;
        option.style.display = textValue.toLowerCase().indexOf(filter) > -1 ? "" : "none";
      }
    }
  </script>

  <?php if (isset($_SESSION['print_receipt']) && $_SESSION['print_receipt'] === true): ?>
    <script>
      document.addEventListener("DOMContentLoaded", function() {
        window.open('generate_receipt.php', '_blank', 'height=600,width=400');
        // The session variables are now cleared in generate_receipt.php
        // No need to clear them via AJAX here anymore.
      });
    </script>
  <?php endif; ?>
</body>

</html>