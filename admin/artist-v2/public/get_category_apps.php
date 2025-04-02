<?php
header('Content-Type: application/json');
require_once '../../../includes/db_connect.php';
include_once '../includes/functions.php';

$response = [
    'success' => false,
    'message' => '',
    'category' => null,
    'apps' => []
];

// Check if category ID is provided
if (isset($_GET['category_id']) && is_numeric($_GET['category_id'])) {
    $categoryId = (int)$_GET['category_id'];
    
    // Get category details
    $category = getCategoryById($categoryId);
    
    if ($category) {
        // Category exists, get associated apps
        $apps = getAppsForCategory($categoryId, true); // Only active apps
        
        $response['success'] = true;
        $response['category'] = $category;
        $response['apps'] = $apps;
    } else {
        // Category does not exist
        $response['message'] = 'Category not found';
    }
} else {
    // Invalid or missing category ID
    $response['message'] = 'Invalid category ID';
}

// Return JSON response
echo json_encode($response);
?>