<?php
session_start();
require '../../includes/db_connect.php';

// Check if user is logged in and is an admin or super_admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'super_admin')) {
    header("Location: ../../auth/login.php");
    exit();
}

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        // Add new category
        if ($_POST['action'] == 'add') {
            $name = $conn->real_escape_string($_POST['name']);
            $description = $conn->real_escape_string($_POST['description']);
            $icon = $conn->real_escape_string($_POST['icon']);

            $sql = "INSERT INTO artist_categories (name, description, icon) VALUES ('$name', '$description', '$icon')";
            if ($conn->query($sql) === TRUE) {
                $message = "Category added successfully!";
                $messageType = "success";
            } else {
                $message = "Error: " . $conn->error;
                $messageType = "danger";
            }
        }

        // Edit category
        if ($_POST['action'] == 'edit' && isset($_POST['id'])) {
            $id = (int) $_POST['id'];
            $name = $conn->real_escape_string($_POST['name']);
            $description = $conn->real_escape_string($_POST['description']);
            $icon = $conn->real_escape_string($_POST['icon']);

            $sql = "UPDATE artist_categories SET name='$name', description='$description', icon='$icon' WHERE id=$id";
            if ($conn->query($sql) === TRUE) {
                $message = "Category updated successfully!";
                $messageType = "success";
            } else {
                $message = "Error: " . $conn->error;
                $messageType = "danger";
            }
        }

        // Delete category
        if ($_POST['action'] == 'delete' && isset($_POST['id'])) {
            $id = (int) $_POST['id'];

            $sql = "DELETE FROM artist_categories WHERE id=$id";
            if ($conn->query($sql) === TRUE) {
                $message = "Category deleted successfully!";
                $messageType = "success";
            } else {
                $message = "Error: " . $conn->error;
                $messageType = "danger";
            }
        }
    }
}

// Get all categories
$sql = "SELECT * FROM artist_categories ORDER BY name";
$result = $conn->query($sql);

$title = "Manage Categories";
include '../../includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Categories</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="artist_dashboard.php" class="btn btn-sm btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="fas fa-plus"></i> Add New Category
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fas fa-list"></i> All Categories</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Icon</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $row['id'] . "</td>";
                                        echo "<td>" . $row['name'] . "</td>";
                                        echo "<td>" . (strlen($row['description']) > 50 ? substr($row['description'], 0, 50) . "..." : $row['description']) . "</td>";
                                        echo "<td>" . $row['icon'] . "</td>";
                                        echo "<td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";
                                        echo "<td>
                                            <button class='btn btn-sm btn-primary edit-btn' data-id='" . $row['id'] . "' data-name='" . htmlspecialchars($row['name'], ENT_QUOTES) . "' data-description='" . htmlspecialchars($row['description'], ENT_QUOTES) . "' data-icon='" . htmlspecialchars($row['icon'], ENT_QUOTES) . "' data-bs-toggle='modal' data-bs-target='#editCategoryModal'>
                                                <i class='fas fa-edit'></i>
                                            </button>
                                            <button class='btn btn-sm btn-danger delete-btn' data-id='" . $row['id'] . "' data-name='" . htmlspecialchars($row['name'], ENT_QUOTES) . "' data-bs-toggle='modal' data-bs-target='#deleteCategoryModal'>
                                                <i class='fas fa-trash'></i>
                                            </button>
                                        </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center'>No categories found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="icon" class="form-label">Icon (Font Awesome class)</label>
                        <input type="text" class="form-control" id="icon" name="icon" placeholder="fas fa-paint-brush">
                        <small class="form-text text-muted">Enter a Font Awesome icon class, e.g., "fas fa-paint-brush"</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-3">
                        <label for="edit-name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit-description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit-icon" class="form-label">Icon (Font Awesome class)</label>
                        <input type="text" class="form-control" id="edit-icon" name="icon" placeholder="fas fa-paint-brush">
                        <small class="form-text text-muted">Enter a Font Awesome icon class, e.g., "fas fa-paint-brush"</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Category Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteCategoryModalLabel">Delete Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete-id">
                    <p>Are you sure you want to delete the category "<span id="delete-name"></span>"?</p>
                    <p class="text-danger">This action cannot be undone and will also delete all associations with apps.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle Edit Button Click
document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const name = this.getAttribute('data-name');
        const description = this.getAttribute('data-description');
        const icon = this.getAttribute('data-icon');
        
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-description').value = description;
        document.getElementById('edit-icon').value = icon;
    });
});

// Handle Delete Button Click
document.querySelectorAll('.delete-btn').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const name = this.getAttribute('data-name');
        
        document.getElementById('delete-id').value = id;
        document.getElementById('delete-name').textContent = name;
    });
});
</script>

<?php
$conn->close();
include '../../includes/admin_footer.php';
?> 