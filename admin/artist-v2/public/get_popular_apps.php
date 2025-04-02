<?php
header('Content-Type: application/json');
require_once '../../../includes/db_connect.php';
include_once '../includes/functions.php';

$response = [
    'success' => false,
    'message' => '',
    'apps' => []
];

// Get popular apps (for simplicity, we're getting the most recent 8 active apps)
// In a real application, you might use metrics like download count, ratings, etc.
$sql = "SELECT * FROM artist_v2_apps WHERE status = 1 ORDER BY created_at DESC LIMIT 8";
$result = $conn->query($sql);

if ($result) {
    $apps = [];
    
    while ($row = $result->fetch_assoc()) {
        // Make sure image URLs are absolute
        $row['image_url'] = ensureAbsoluteUrl($row['image_url']);
        $apps[] = $row;
    }
    
    $response['success'] = true;
    $response['apps'] = $apps;
} else {
    $response['message'] = 'Failed to fetch apps: ' . $conn->error;
}

// Return JSON response
echo json_encode($response);
?> 