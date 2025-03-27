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
        // Add new app
        if ($_POST['action'] == 'add') {
            $name = $conn->real_escape_string($_POST['name']);
            $description = $conn->real_escape_string($_POST['description']);
            $image_url = "https://images.unsplash.com/photo-1611162616305-c69b3fa7fbe0?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NHx8aW5zdGFncmFtJTIwYXBwfGVufDB8fDB8fHww";
            $form_url = $conn->real_escape_string($_POST['form_url']);
            $categories = isset($_POST['categories']) ? $_POST['categories'] : [];

            $sql = "INSERT INTO artist_apps (name, description, form_url, image_url) VALUES ('$name', '$description', '$form_url', '$image_url')";
            if ($conn->query($sql) === TRUE) {
                $app_id = $conn->insert_id;
                
                // Add category associations
                foreach ($categories as $category_id) {
                    $sql = "INSERT INTO artist_app_categories (app_id, category_id) VALUES ($app_id, $category_id)";
                    $conn->query($sql);
                }
                
                $message = "App added successfully!";
                $messageType = "success";
            } else {
                $message = "Error: " . $conn->error;
                $messageType = "danger";
            }
        }

        // Edit app
        if ($_POST['action'] == 'edit' && isset($_POST['id'])) {
            $id = (int) $_POST['id'];
            $name = $conn->real_escape_string($_POST['name']);
            $description = $conn->real_escape_string($_POST['description']);
            $image_url = $conn->real_escape_string($_POST['image_url']);
            $form_url = $conn->real_escape_string($_POST['form_url']);

            $sql = "UPDATE artist_apps SET name='$name', description='$description', image_url='$image_url', form_url='$form_url' WHERE id=$id";
            if ($conn->query($sql) === TRUE) {
                // Delete existing category associations
                $sql = "DELETE FROM artist_app_categories WHERE app_id=$id";
                $conn->query($sql);
                
                // Add new category associations
                if (isset($_POST['categories']) && is_array($_POST['categories'])) {
                    foreach ($_POST['categories'] as $category_id) {
                        $sql = "INSERT INTO artist_app_categories (app_id, category_id) VALUES ($id, $category_id)";
                        $conn->query($sql);
                    }
                }
                
                $message = "App updated successfully!";
                $messageType = "success";
            } else {
                $message = "Error: " . $conn->error;
                $messageType = "danger";
            }
        }

        // Delete app
        if ($_POST['action'] == 'delete' && isset($_POST['id'])) {
            $id = (int) $_POST['id'];

            $sql = "DELETE FROM artist_apps WHERE id=$id";
            if ($conn->query($sql) === TRUE) {
                $message = "App deleted successfully!";
                $messageType = "success";
            } else {
                $message = "Error: " . $conn->error;
                $messageType = "danger";
            }
        }
    }
}

// Get all apps
$sql = "SELECT * FROM artist_apps ORDER BY name";
$result = $conn->query($sql);

// Get all categories
$categoriesSql = "SELECT * FROM artist_categories ORDER BY name";
$categoriesResult = $conn->query($categoriesSql);
$categories = [];
while ($row = $categoriesResult->fetch_assoc()) {
    $categories[$row['id']] = $row['name'];
}

$title = "Manage Apps";
include '../../includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Apps</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="artist_dashboard.php" class="btn btn-sm btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addAppModal">
                        <i class="fas fa-plus"></i> Add New App
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
                <div class="card-header bg-success text-white">
                    <h5><i class="fas fa-mobile-alt"></i> All Apps</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Image</th>
                                    <th>Form URL</th>
                                    <th>Categories</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        // Get categories for this app
                                        $appCategoriesSql = "SELECT c.id, c.name FROM artist_categories c 
                                                           JOIN artist_app_categories ac ON c.id = ac.category_id 
                                                           WHERE ac.app_id = {$row['id']}";
                                        $appCategoriesResult = $conn->query($appCategoriesSql);
                                        $appCategories = [];
                                        $appCategoryIds = [];
                                        while ($catRow = $appCategoriesResult->fetch_assoc()) {
                                            $appCategories[] = $catRow['name'];
                                            $appCategoryIds[] = $catRow['id'];
                                        }
                                        
                                        echo "<tr>";
                                        echo "<td>" . $row['id'] . "</td>";
                                        echo "<td>" . $row['name'] . "</td>";
                                        echo "<td>" . (strlen($row['description']) > 50 ? substr($row['description'], 0, 50) . "..." : $row['description']) . "</td>";
                                        echo "<td><img src='" . $row['image_url'] . "' alt='" . $row['name'] . "' style='width: 50px; height: 50px;' onerror=\"this.src='../../assets/images/default-app.png'\"></td>";
                                        echo "<td><a href='" . $row['form_url'] . "' target='_blank'>" . (strlen($row['form_url']) > 30 ? substr($row['form_url'], 0, 30) . "..." : $row['form_url']) . "</a></td>";
                                        echo "<td>" . implode(", ", $appCategories) . "</td>";
                                        echo "<td>
                                            <button class='btn btn-sm btn-primary edit-btn' 
                                                data-id='" . $row['id'] . "' 
                                                data-name='" . htmlspecialchars($row['name'], ENT_QUOTES) . "' 
                                                data-description='" . htmlspecialchars($row['description'], ENT_QUOTES) . "' 
                                                data-image-url='" . htmlspecialchars($row['image_url'], ENT_QUOTES) . "' 
                                                data-form-url='" . htmlspecialchars($row['form_url'], ENT_QUOTES) . "' 
                                                data-categories='" . htmlspecialchars(json_encode($appCategoryIds), ENT_QUOTES) . "' 
                                                data-bs-toggle='modal' data-bs-target='#editAppModal'>
                                                <i class='fas fa-edit'></i>
                                            </button>
                                            <button class='btn btn-sm btn-danger delete-btn' data-id='" . $row['id'] . "' data-name='" . htmlspecialchars($row['name'], ENT_QUOTES) . "' data-bs-toggle='modal' data-bs-target='#deleteAppModal'>
                                                <i class='fas fa-trash'></i>
                                            </button>
                                        </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='7' class='text-center'>No apps found</td></tr>";
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

<!-- Add App Modal -->
<div class="modal fade" id="addAppModal" tabindex="-1" aria-labelledby="addAppModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addAppModalLabel">Add New App</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="name" class="form-label">App Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image_url" class="form-label">Image URL</label>
                        <input type="url" class="form-control" id="image_url" name="image_url" placeholder="https://example.com/app-image.png">
                    </div>
                    <div class="mb-3">
                        <label for="form_url" class="form-label">Google Form URL</label>
                        <input type="url" class="form-control" id="form_url" name="form_url" placeholder="https://forms.google.com/...">
                    </div>
                    <div class="mb-3">
                        <label for="categories" class="form-label">Categories</label>
                        <select class="form-select" id="categories" name="categories[]" multiple size="10">
                            <?php
                            foreach ($categories as $id => $name) {
                                echo "<option value=\"$id\">$name</option>";
                            }
                            ?>
                        </select>
                        <small class="form-text text-muted">Hold Ctrl (or Cmd on Mac) to select multiple categories</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add App</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit App Modal -->
<div class="modal fade" id="editAppModal" tabindex="-1" aria-labelledby="editAppModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editAppModalLabel">Edit App</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-3">
                        <label for="edit-name" class="form-label">App Name</label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit-description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit-image_url" class="form-label">Image URL</label>
                        <input type="url" class="form-control" id="edit-image_url" name="image_url">
                    </div>
                    <div class="mb-3">
                        <label for="edit-form_url" class="form-label">Google Form URL</label>
                        <input type="url" class="form-control" id="edit-form_url" name="form_url">
                    </div>
                    <div class="mb-3">
                        <label for="edit-categories" class="form-label">Categories</label>
                        <select class="form-select" id="edit-categories" name="categories[]" multiple size="10">
                            <?php
                            foreach ($categories as $id => $name) {
                                echo "<option value=\"$id\">$name</option>";
                            }
                            ?>
                        </select>
                        <small class="form-text text-muted">Hold Ctrl (or Cmd on Mac) to select multiple categories</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update App</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete App Modal -->
<div class="modal fade" id="deleteAppModal" tabindex="-1" aria-labelledby="deleteAppModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteAppModalLabel">Delete App</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete-id">
                    <p>Are you sure you want to delete the app "<span id="delete-name"></span>"?</p>
                    <p class="text-danger">This action cannot be undone and will also delete all category associations.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete App</button>
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
        const imageUrl = this.getAttribute('data-image-url');
        const formUrl = this.getAttribute('data-form-url');
        const categories = JSON.parse(this.getAttribute('data-categories'));
        
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-description').value = description;
        document.getElementById('edit-image_url').value = imageUrl;
        document.getElementById('edit-form_url').value = formUrl;
        
        // Select categories
        const categorySelect = document.getElementById('edit-categories');
        for (let i = 0; i < categorySelect.options.length; i++) {
            categorySelect.options[i].selected = categories.includes(parseInt(categorySelect.options[i].value));
        }
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