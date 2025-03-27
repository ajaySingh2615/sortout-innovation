<?php
require '../includes/db_connect.php';

// Set header to return JSON
header('Content-Type: application/json');

// Check if category_id is provided
if (!isset($_POST['category_id'])) {
    echo json_encode(['error' => 'Category ID is required']);
    exit();
}

$categoryId = (int)$_POST['category_id'];

try {
    // Fetch apps for the selected category
    $query = "SELECT a.* FROM artist_apps a 
              INNER JOIN artist_app_categories ac ON a.id = ac.app_id 
              WHERE ac.category_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();

    $apps = [];
    while ($app = $result->fetch_assoc()) {
        // Set default Instagram app image if no image URL provided
        $imageUrl = $app['image_url'] ?? "https://images.unsplash.com/photo-1611162616305-c69b3fa7fbe0?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NHx8aW5zdGFncmFtJTIwYXBwfGVufDB8fDB8fHww";
        
        $apps[] = [
            'id' => $app['id'],
            'name' => $app['name'],
            'image_url' => $imageUrl,
            'form_url' => $app['form_url'],
            'description' => $app['description']
        ];
    }

    echo json_encode($apps);

} catch (Exception $e) {
    error_log("Error in get_apps.php: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?> 