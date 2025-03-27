<?php
session_start();
require '../../includes/db_connect.php';

// Check if user is logged in and is an admin or super_admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'super_admin')) {
    header("Location: ../../auth/login.php");
    exit();
}

// Creating tables if they don't exist
$tables = [
    // Artist Categories table
    "CREATE TABLE IF NOT EXISTS `artist_categories` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `description` text,
        `icon` varchar(100) DEFAULT 'fas fa-palette',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // Artist Apps table
    "CREATE TABLE IF NOT EXISTS `artist_apps` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `description` text,
        `image_url` varchar(255) DEFAULT NULL,
        `form_url` varchar(255) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    
    // Artist App Categories (relationship table)
    "CREATE TABLE IF NOT EXISTS `artist_app_categories` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `app_id` int(11) NOT NULL,
        `category_id` int(11) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `app_id` (`app_id`),
        KEY `category_id` (`category_id`),
        CONSTRAINT `artist_app_categories_ibfk_1` FOREIGN KEY (`app_id`) REFERENCES `artist_apps` (`id`) ON DELETE CASCADE,
        CONSTRAINT `artist_app_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `artist_categories` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

$success = true;
$messages = [];

// Execute each table creation query
foreach ($tables as $sql) {
    if ($conn->query($sql) !== TRUE) {
        $success = false;
        $messages[] = "Error creating table: " . $conn->error;
    }
}

// Sample data - only insert if tables are empty
$categoriesCount = $conn->query("SELECT COUNT(*) as count FROM artist_categories")->fetch_assoc()['count'];
if ($categoriesCount == 0) {
    // Insert sample categories
    $categories = [
        ["name" => "Graphic Design", "description" => "Jobs related to graphic design, logos, and visual identity", "icon" => "fas fa-palette"],
        ["name" => "Web Design", "description" => "Jobs related to website design and UI/UX", "icon" => "fas fa-laptop-code"],
        ["name" => "Photography", "description" => "Photography and videography opportunities", "icon" => "fas fa-camera"],
        ["name" => "Music Production", "description" => "Music production, composition, and audio engineering", "icon" => "fas fa-music"],
        ["name" => "Animation", "description" => "2D and 3D animation projects", "icon" => "fas fa-film"]
    ];
    
    foreach ($categories as $category) {
        $name = $conn->real_escape_string($category['name']);
        $description = $conn->real_escape_string($category['description']);
        $icon = $conn->real_escape_string($category['icon']);
        
        $sql = "INSERT INTO artist_categories (name, description, icon) VALUES ('$name', '$description', '$icon')";
        if ($conn->query($sql) !== TRUE) {
            $messages[] = "Error inserting category: " . $conn->error;
        }
    }
    
    // Insert sample apps
    $apps = [
        ["name" => "Logo Design", "description" => "Create stunning logos for businesses", "form_url" => "https://forms.google.com/logodesign"],
        ["name" => "Website Design", "description" => "Design modern websites with great UX", "form_url" => "https://forms.google.com/webdesign"],
        ["name" => "Portrait Photography", "description" => "Professional portrait photography services", "form_url" => "https://forms.google.com/photography"],
        ["name" => "Music Composition", "description" => "Original music composition for various media", "form_url" => "https://forms.google.com/music"],
        ["name" => "Character Animation", "description" => "Animated character design and motion", "form_url" => "https://forms.google.com/animation"]
    ];
    
    foreach ($apps as $index => $app) {
        $name = $conn->real_escape_string($app['name']);
        $description = $conn->real_escape_string($app['description']);
        $form_url = $conn->real_escape_string($app['form_url']);
        
        $sql = "INSERT INTO artist_apps (name, description, form_url) VALUES ('$name', '$description', '$form_url')";
        if ($conn->query($sql) === TRUE) {
            $app_id = $conn->insert_id;
            
            // Associate with a category (simplified for this sample)
            $category_id = $index + 1; // Simple mapping to match indices
            $sql = "INSERT INTO artist_app_categories (app_id, category_id) VALUES ($app_id, $category_id)";
            if ($conn->query($sql) !== TRUE) {
                $messages[] = "Error creating association: " . $conn->error;
            }
        } else {
            $messages[] = "Error inserting app: " . $conn->error;
        }
    }
}

// Prepare the response
$title = "Database Setup";
include '../../includes/admin_header.php';
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                <h1 class="h2 fw-bold text-dark">
                    <i class="fas fa-database text-primary me-2"></i>Database Setup
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="artist_dashboard.php" class="btn btn-outline-primary me-2">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <?php if ($success && empty($messages)): ?>
                        <div class="alert alert-success">
                            <h4 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Setup Completed Successfully!</h4>
                            <p>All database tables have been created and sample data has been added (if tables were empty).</p>
                            <hr>
                            <p class="mb-0">You can now start using the Artist Job Management system.</p>
                        </div>
                    <?php else: ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <h4 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Tables Created Successfully</h4>
                                <p>All required database tables have been created.</p>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <h4 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Setup Encountered Issues</h4>
                                <p>There were some issues with the database setup:</p>
                                <ul>
                                    <?php foreach ($messages as $message): ?>
                                        <li><?php echo $message; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="mt-4">
                        <h5>Table Status:</h5>
                        <table class="table table-bordered">
                            <thead class="table-primary">
                                <tr>
                                    <th>Table Name</th>
                                    <th>Status</th>
                                    <th>Row Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $tableNames = ['artist_categories', 'artist_apps', 'artist_app_categories'];
                                foreach ($tableNames as $tableName) {
                                    $exists = $conn->query("SHOW TABLES LIKE '$tableName'")->num_rows > 0;
                                    $count = $exists ? $conn->query("SELECT COUNT(*) as count FROM $tableName")->fetch_assoc()['count'] : 0;
                                    
                                    echo "<tr>";
                                    echo "<td><code>$tableName</code></td>";
                                    echo "<td>" . ($exists ? "<span class='badge bg-success'>Created</span>" : "<span class='badge bg-danger'>Missing</span>") . "</td>";
                                    echo "<td>" . ($exists ? $count : "N/A") . "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="artist_dashboard.php" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                        </a>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-success">
                            <i class="fas fa-sync-alt me-2"></i>Run Setup Again
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