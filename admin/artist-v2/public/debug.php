<?php
header('Content-Type: text/html');
require_once '../../../includes/db_connect.php';
include_once '../includes/functions.php';

echo "<h1>API Debugging</h1>";

// Test database connection
echo "<h2>Database Connection</h2>";
if ($conn->ping()) {
    echo "<p style='color:green'>Database connection is working</p>";
} else {
    echo "<p style='color:red'>Database connection failed: " . $conn->error . "</p>";
}

// Test categories retrieval
echo "<h2>Categories</h2>";
$categories = getCategories();
echo "<p>Found " . count($categories) . " categories</p>";
if (count($categories) > 0) {
    echo "<ul>";
    foreach ($categories as $category) {
        echo "<li>ID: {$category['id']}, Name: {$category['name']}</li>";
    }
    echo "</ul>";
}

// Test apps retrieval
echo "<h2>Apps</h2>";
$apps = getApps();
echo "<p>Found " . count($apps) . " apps</p>";
if (count($apps) > 0) {
    echo "<ul>";
    foreach ($apps as $app) {
        echo "<li>ID: {$app['id']}, Name: {$app['name']}</li>";
    }
    echo "</ul>";
}

// Test app-category relationships
echo "<h2>App-Category Relationships</h2>";
$sql = "SELECT * FROM artist_v2_app_categories";
$result = $conn->query($sql);
$relationships = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $relationships[] = $row;
    }
}
echo "<p>Found " . count($relationships) . " app-category relationships</p>";
if (count($relationships) > 0) {
    echo "<ul>";
    foreach ($relationships as $rel) {
        echo "<li>App ID: {$rel['app_id']}, Category ID: {$rel['category_id']}</li>";
    }
    echo "</ul>";
}

// Test specific category's apps
if (isset($_GET['category_id']) && is_numeric($_GET['category_id'])) {
    $categoryId = (int)$_GET['category_id'];
    echo "<h2>Apps for Category ID: {$categoryId}</h2>";
    
    $category = getCategoryById($categoryId);
    if ($category) {
        echo "<p>Category: {$category['name']}</p>";
        
        $categoryApps = getAppsForCategory($categoryId);
        echo "<p>Found " . count($categoryApps) . " apps in this category</p>";
        
        if (count($categoryApps) > 0) {
            echo "<ul>";
            foreach ($categoryApps as $app) {
                echo "<li>ID: {$app['id']}, Name: {$app['name']}</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p style='color:red'>Category not found</p>";
    }
}

// Check image paths
echo "<h2>Image Paths</h2>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "</p>";
echo "<p>Current Script Directory: " . dirname(__FILE__) . "</p>";

// Check if default images exist
$defaultAppImage = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/default-app.jpg';
$defaultCategoryImage = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/default-category.jpg';

echo "<p>Default App Image: " . $defaultAppImage . " - " . (file_exists($defaultAppImage) ? "Exists" : "Missing") . "</p>";
echo "<p>Default Category Image: " . $defaultCategoryImage . " - " . (file_exists($defaultCategoryImage) ? "Exists" : "Missing") . "</p>";

// Check directories
$appsDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/apps';
$categoriesDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/categories';

echo "<p>Apps Directory: " . $appsDir . " - " . (file_exists($appsDir) ? "Exists" : "Missing") . "</p>";
echo "<p>Categories Directory: " . $categoriesDir . " - " . (file_exists($categoriesDir) ? "Exists" : "Missing") . "</p>";

echo "<p><a href='index.php'>Return to Frontend</a></p>";
?> 