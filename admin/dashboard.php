<?php
require '../auth/auth.php';
require '../includes/db_connect.php';

// ✅ Ensure Only Admins & Super Admins Can Access
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'super_admin') {
    header("Location: ../index.php");
    exit();
}

// ✅ Fetch Pending Admins (Only for Super Admin)
$pendingAdmins = [];
if ($_SESSION['role'] === 'super_admin') {
    $adminQuery = "SELECT id, username, email FROM users WHERE role = 'admin' AND status = 'pending'";
    $pendingAdmins = $conn->query($adminQuery);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <!-- ✅ Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* ✅ Dashboard Styling */
        body {
            background: radial-gradient(circle, rgba(255,255,255,1) 0%, rgba(230,230,230,1) 100%);
            background-image: url("https://www.transparenttextures.com/patterns/cubes.png");
            background-repeat: repeat;
        }

        /* ✅ Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #d90429;
            color: white;
        }
        tr:hover {
            background: #f8d7da;
        }

         /* ✅ Buttons */
         .btn-approve {
            background: #28a745;
            color: white;
        }
        .btn-reject {
            background: #dc3545;
            color: white;
        }

        /* ✅ Filters */
        .filter-box {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .filter-box input, .filter-box select {
            max-width: 250px;
            flex-grow: 1;
        }

        /* ✅ Buttons */
        .btn-edit {
            background: #ffc107;
            color: black;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        .btn-edit:hover, .btn-delete:hover {
            opacity: 0.85;
        }

        /* ✅ Pagination */
        .pagination-container {
            background: #222;
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin-top: 20px;
        }
        .pagination-btn {
            background: #555;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
        }
        .pagination-btn:hover {
            background: #777;
        }

        /* ✅ Footer */
        footer {
            background: black;
            color: white;
            padding: 30px 0;
            text-align: center;
        }
    </style>
</head>
<body>

<!-- ✅ Navbar -->
<nav class="navbar navbar-expand-lg fixed-top bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center text-danger" href="#">
            <!-- <img src="../public/logo.png" alt="Logo" height="40" class="me-2"> -->
            Admin Panel
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link fw-semibold px-3" href="add_blog.php">Add Blog</a></li>
                <li class="nav-item"><a class="nav-link fw-semibold px-3" href="../blog/index.php">Blogs</a></li>
                <li class="nav-item"><a class="nav-link fw-semibold px-3" href="../pages/our-services-page/service.html">Services</a></li>
                <li class="nav-item"><a class="nav-link fw-semibold px-3" href="device_dashboard.php">Manage Devices</a></li>
                <li class="nav-item"><a class="nav-link fw-semibold px-3" href="model_agency_dashboard.php">Manage Talents</a></li>
                <!-- ✅ Display Logged-in User Info -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle fw-semibold px-3" href="#" role="button" data-bs-toggle="dropdown">
                        👤 <?= $_SESSION['username']; ?> (<?= ucfirst($_SESSION['role']); ?>)
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="../auth/logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- ✅ Spacing for Navbar -->
<div style="height: 80px;"></div>

<!-- ✅ Super Admin Panel (Approve Admins) -->
<?php if ($_SESSION['role'] === 'super_admin' && $pendingAdmins->num_rows > 0): ?>
    <div class="container my-4">
        <h3 class="text-danger fw-bold">Pending Admin Approvals</h3>
        <table class="table table-bordered">
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
            <?php while ($admin = $pendingAdmins->fetch_assoc()): ?>
                <tr id="admin-<?= $admin['id']; ?>">
                    <td><?= htmlspecialchars($admin['username']); ?></td>
                    <td><?= htmlspecialchars($admin['email']); ?></td>
                    <td>
                        <button class="btn btn-approve approve-btn" data-id="<?= $admin['id']; ?>">Approve</button>
                        <button class="btn btn-reject reject-btn" data-id="<?= $admin['id']; ?>">Reject</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
<?php endif; ?>


<!-- ✅ Dashboard Content -->
<div class="container my-5">
    <!-- <h2 class="text-center fw-bold text-danger mb-4">Admin Dashboard</h2> -->

    <!-- ✅ Filter Section -->
    <div class="filter-box mb-3">
        <input type="text" id="search" class="form-control" placeholder="🔎 Search by Title...">
        <select id="month" class="form-control">
            <option value="">📅 Filter by Month</option>
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m; ?>"><?= date("F", mktime(0, 0, 0, $m, 1)); ?></option>
            <?php endfor; ?>
        </select>
        <button id="reset" class="btn btn-secondary">Reset</button>
    </div>

      <!-- ✅ New Button for Device Dashboard -->
      <div class="text-center mb-4">
        <a href="device_dashboard.php" class="btn btn-custom btn-manage-devices">📌 Manage Devices</a>
    </div>

    <!-- ✅ Blog Table -->
    <div id="table-data">
        <!-- Data will be loaded dynamically here -->
    </div>
    
</div>

<!-- ✅ Footer -->
<footer>© 2025 Admin Panel | All Rights Reserved.</footer>

<!-- ✅ Bootstrap & jQuery -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function () {
        function loadData(page = 1, search = '', month = '') {
            $.ajax({
                url: "fetch_blogs.php",
                type: "GET",
                data: { page: page, search: search, month: month },
                success: function (response) {
                    $("#table-data").html(response);
                }
            });
        }

        // ✅ Load Initial Data
        loadData();

        // ✅ Search Event
        $("#search").on("keyup", function () {
            loadData(1, $(this).val(), $("#month").val());
        });

        // ✅ Month Filter
        $("#month").on("change", function () {
            loadData(1, $("#search").val(), $(this).val());
        });

        // ✅ Reset Filters
        $("#reset").on("click", function () {
            $("#search").val('');
            $("#month").val('');
            loadData(1, '', '');
        });

        // ✅ Pagination Click Event
        $(document).on("click", ".pagination-link", function (e) {
            e.preventDefault();
            let page = $(this).data("page");
            loadData(page, $("#search").val(), $("#month").val());
        });

        // ✅ Handle Delete Confirmation Modal (Attach Dynamically)
        let deleteId = null;

        $(document).on("click", ".delete-btn", function () {
            deleteId = $(this).data("id"); // Store the ID
            $("#deleteModal").modal("show"); // Show the modal
        });

        // ✅ Confirm Delete Button Click (Fix: Event Delegation)
        $(document).on("click", "#confirmDelete", function () {
            if (deleteId) {
                $.ajax({
                    url: "delete_blog.php",
                    type: "POST",
                    data: { id: deleteId },
                    success: function (response) {
                        $("#deleteModal").modal("hide"); // Hide modal after delete
                        loadData(); // Refresh Table
                    }
                });
            }
        });

        // ✅ Super Admin: Approve Admin
        $(document).on("click", ".approve-btn", function () {
            let adminId = $(this).data("id");
            $.ajax({
                url: "approve_admin.php",
                type: "POST",
                data: { id: adminId },
                success: function () {
                    loadData(); // Refresh table
                    alert("✅ Admin approved successfully.");
                }
            });
        });

        // ✅ Super Admin: Reject Admin
        $(document).on("click", ".reject-btn", function () {
            let adminId = $(this).data("id");
            $.ajax({
                url: "reject_admin.php",
                type: "POST",
                data: { id: adminId },
                success: function () {
                    loadData(); // Refresh table
                    alert("❌ Admin request rejected.");
                }
            });
        });

    });
</script>


</body>
</html>
