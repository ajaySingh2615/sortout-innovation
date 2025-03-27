<?php
// This is a simple file to check database connection and table existence
require '../../includes/db_connect.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Database connection successful<br>";

// Check if tables exist
$tables = ['artist_categories', 'artist_apps', 'artist_app_categories'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "Table $table exists<br>";
        
        // Count rows
        $count = $conn->query("SELECT COUNT(*) as count FROM $table")->fetch_assoc()['count'];
        echo "Table $table has $count rows<br>";
    } else {
        echo "Table $table does not exist<br>";
    }
}

// Get PHP and MySQL version info
echo "<br>PHP Version: " . phpversion() . "<br>";
echo "MySQL Version: " . $conn->server_info . "<br>";

// Close connection
$conn->close();
?> 