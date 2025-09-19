<?php
require_once 'components/functions.php';
// Include the database connection file
include 'database/db_connection.php';
// Start the session to access session variables



// Check if the user is logged in, otherwise redirect to the login page
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Superuser') {
  header("location: login.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include('components/head.php'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">

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
                <a href="#">Session Logs</a>
              </li>

            </ul>
          </div>


          <div class="card p-3">
            <h2>🔐 Session Activity Logs</h2>

            <button id="toggleRefresh" class="btn btn-primary rounded mb-3" style="width: 250px;">⏸ Pause Auto-Refresh</button>

            <div class="filters">
              <div class="row">
                <div class="col-md-2">
                  <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon3">Event</span>
                    <select class="form-control form-select" id="eventFilter">
                      <option value="">All</option>
                      <option value="login">Login</option>
                      <option value="logout">Logout</option>
                      <option value="timeout">Timeout</option>
                      <option value="hijack">Hijack</option>
                    </select>
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon3">Date Range</span>
                    <input type="text" id="dateRange" class="form-control" placeholder="Select range">
                  </div>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <table id="logsTable" class="display table table-bordered">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Event Type</th>
                    <th>IP Address</th>
                    <th>User Agent</th>
                    <th>Date/Time</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <?php include('components/footer.php'); ?>
    </div>
  </div>
  <!--   Core JS Files   -->
  <?php include('components/script.php'); ?>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
  <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

  <script>
    $(document).ready(function() {
      var table = $('#logsTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
        order: [
          [5, 'desc']
        ],
        pageLength: 10,
        ajax: {
          url: 'fetch_logs.php',
          dataSrc: ''
        },
        columns: [{
            data: 'id'
          },
          {
            data: 'username'
          },
          {
            data: 'event_type'
          },
          {
            data: 'ip_address'
          },
          {
            data: 'user_agent'
          },
          {
            data: 'created_at'
          }
        ]
      });

      // Event type filter
      $('#eventFilter').on('change', function() {
        table.column(2).search(this.value).draw();
      });

      // Date range filter
      var startDate, endDate;
      $('#dateRange').daterangepicker({
        opens: 'left',
        autoUpdateInput: false
      }, function(start, end) {
        startDate = start;
        endDate = end;
        $('#dateRange').val(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
        table.draw();
      });

      $.fn.dataTable.ext.search.push(function(settings, data) {
        var min = startDate ? startDate.startOf('day') : null;
        var max = endDate ? endDate.endOf('day') : null;
        var date = moment(data[5]);

        if ((min === null && max === null) ||
          (min === null && date <= max) ||
          (min <= date && max === null) ||
          (min <= date && date <= max)) {
          return true;
        }
        return false;
      });

      // Auto-refresh every 5 seconds
      var refreshEnabled = true;
      var refreshInterval = setInterval(function() {
        if (refreshEnabled) {
          table.ajax.reload(null, false); // false = keep current page & pagination
        }
      }, 5000);

      // Pause/Resume button
      $('#toggleRefresh').on('click', function() {
        refreshEnabled = !refreshEnabled;
        $(this).text(refreshEnabled ? "⏸ Pause Auto-Refresh" : "▶ Resume Auto-Refresh");
      });
    });
  </script>
</body>

</html>