<?php
session_start();
require '../../includes/db_connect.php';

// Check if user is logged in and is an admin or super_admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'super_admin')) {
    header("Location: ../../auth/login.php");
    exit();
}

// Get statistics
$categoryCount = $conn->query("SELECT COUNT(*) as count FROM artist_categories")->fetch_assoc()['count'];
$appCount = $conn->query("SELECT COUNT(*) as count FROM artist_apps")->fetch_assoc()['count'];
$relationCount = $conn->query("SELECT COUNT(*) as count FROM artist_app_categories")->fetch_assoc()['count'];

$title = "Artist Job Dashboard";
include '../../includes/admin_header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                <h1 class="h2 fw-bold text-dark">
                    <i class="fas fa-palette text-danger me-2"></i>Artist Job Dashboard
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="../main_dashboard.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left"></i> Back to Main Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card h-100 bg-primary bg-gradient text-white">
                <div class="card-body position-relative p-4">
                    <div class="position-absolute top-0 end-0 p-3 opacity-25">
                        <i class="fas fa-list fa-3x"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-3"><i class="fas fa-list me-2"></i> Categories</h5>
                    <p class="card-text display-4 fw-bold mb-4"><?php echo $categoryCount; ?></p>
                    <a href="manage_categories.php" class="btn btn-light fw-semibold text-primary">
                        <i class="fas fa-cog me-1"></i> Manage Categories
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 bg-success bg-gradient text-white">
                <div class="card-body position-relative p-4">
                    <div class="position-absolute top-0 end-0 p-3 opacity-25">
                        <i class="fas fa-mobile-alt fa-3x"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-3"><i class="fas fa-mobile-alt me-2"></i> Apps</h5>
                    <p class="card-text display-4 fw-bold mb-4"><?php echo $appCount; ?></p>
                    <a href="manage_apps.php" class="btn btn-light fw-semibold text-success">
                        <i class="fas fa-cog me-1"></i> Manage Apps
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 bg-info bg-gradient text-white">
                <div class="card-body position-relative p-4">
                    <div class="position-absolute top-0 end-0 p-3 opacity-25">
                        <i class="fas fa-link fa-3x"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-3"><i class="fas fa-link me-2"></i> Connections</h5>
                    <p class="card-text display-4 fw-bold mb-4"><?php echo $relationCount; ?></p>
                    <a href="manage_apps.php" class="btn btn-light fw-semibold text-info">
                        <i class="fas fa-cog me-1"></i> Manage Connections
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark bg-gradient text-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-bolt me-2"></i> Quick Actions</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="manage_categories.php?action=add" class="btn btn-primary btn-lg d-flex align-items-center justify-content-center gap-2 mb-3 w-100 py-3">
                                <i class="fas fa-plus"></i> <span class="fw-semibold">Add New Category</span>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="manage_apps.php?action=add" class="btn btn-success btn-lg d-flex align-items-center justify-content-center gap-2 mb-3 w-100 py-3">
                                <i class="fas fa-plus"></i> <span class="fw-semibold">Add New App</span>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="../../Artist-job/index.php" target="_blank" class="btn btn-info btn-lg d-flex align-items-center justify-content-center gap-2 mb-3 w-100 py-3 text-white">
                                <i class="fas fa-eye"></i> <span class="fw-semibold">View Frontend</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Data -->
    <div class="row mb-4">
        <!-- Recent Categories -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary bg-gradient text-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-list me-2"></i> Recent Categories</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3">ID</th>
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = $conn->query("SELECT id, name, created_at FROM artist_categories ORDER BY created_at DESC LIMIT 5");
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td class='px-4 py-3'>" . $row['id'] . "</td>";
                                        echo "<td class='px-4 py-3 fw-semibold'>" . $row['name'] . "</td>";
                                        echo "<td class='px-4 py-3'>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3' class='text-center py-4'>No categories found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 pt-2 border-top">
                        <a href="manage_categories.php" class="btn btn-primary">
                            <i class="fas fa-list me-1"></i> View All Categories
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Apps -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success bg-gradient text-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-mobile-alt me-2"></i> Recent Apps</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3">ID</th>
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = $conn->query("SELECT id, name, created_at FROM artist_apps ORDER BY created_at DESC LIMIT 5");
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td class='px-4 py-3'>" . $row['id'] . "</td>";
                                        echo "<td class='px-4 py-3 fw-semibold'>" . $row['name'] . "</td>";
                                        echo "<td class='px-4 py-3'>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3' class='text-center py-4'>No apps found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 pt-2 border-top">
                        <a href="manage_apps.php" class="btn btn-success">
                            <i class="fas fa-mobile-alt me-1"></i> View All Apps
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
include '../../includes/admin_footer.php';
?> 