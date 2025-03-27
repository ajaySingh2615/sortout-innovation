<?php
// Simplified setup script without authentication
require_once '../../includes/db_connect.php';

echo "<h1>Artist Dashboard Database Setup</h1>";

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
    if ($conn->query($sql) === TRUE) {
        echo "Table created successfully<br>";
    } else {
        $success = false;
        $messages[] = "Error creating table: " . $conn->error;
        echo "Error creating table: " . $conn->error . "<br>";
    }
}

// Sample data - only insert if tables are empty
$categoriesCount = $conn->query("SELECT COUNT(*) as count FROM artist_categories")->fetch_assoc()['count'];
if ($categoriesCount == 0) {
    echo "<h2>Inserting sample data</h2>";
    
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
        if ($conn->query($sql) === TRUE) {
            echo "Category '$name' added successfully<br>";
        } else {
            echo "Error inserting category: " . $conn->error . "<br>";
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
            echo "App '$name' added successfully<br>";
            
            // Associate with a category (simplified for this sample)
            $category_id = $index + 1; // Simple mapping to match indices
            $sql = "INSERT INTO artist_app_categories (app_id, category_id) VALUES ($app_id, $category_id)";
            if ($conn->query($sql) === TRUE) {
                echo "App-Category association created successfully<br>";
            } else {
                echo "Error creating association: " . $conn->error . "<br>";
            }
        } else {
            echo "Error inserting app: " . $conn->error . "<br>";
        }
    }
}

// Check if tables exist and show row counts
echo "<h2>Table Status</h2>";
$tableNames = ['artist_categories', 'artist_apps', 'artist_app_categories'];
foreach ($tableNames as $tableName) {
    $exists = $conn->query("SHOW TABLES LIKE '$tableName'")->num_rows > 0;
    $count = $exists ? $conn->query("SELECT COUNT(*) as count FROM $tableName")->fetch_assoc()['count'] : 0;
    
    echo "Table: <strong>$tableName</strong> - ";
    echo $exists ? "Exists with $count rows<br>" : "Does not exist<br>";
}

echo "<h2>Setup Complete</h2>";
echo "<p>You can now <a href='artist_dashboard.php'>visit the dashboard</a>.</p>";

$conn->close();
?> 