<?php
// Comment out or remove display_errors for production
error_reporting(E_ALL);
ini_set('display_errors', 0); // Change to 0 to prevent displaying errors in output
require '../includes/db_connect.php';  // Database connection

// Set headers before any output
header('Content-Type: application/json'); // Ensure JSON output

// Wrap everything in a try-catch to handle any errors
try {
    // Debug: Check if database connection is successful
    if ($conn->connect_error) {
        echo json_encode([
            "status" => "error",
            "message" => "Database connection failed"
        ]);
        exit;
    }

    // Get parameters
    $status = isset($_GET['status']) ? $_GET['status'] : 'approved';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // Build query
    $query = "SELECT SQL_CALC_FOUND_ROWS * FROM clients WHERE approval_status = ?";
    $params = [$status];
    $types = "s";

    // Add search condition if provided
    if (!empty($search)) {
        $query .= " AND (name LIKE ? OR category LIKE ? OR role LIKE ? OR language LIKE ? OR city LIKE ?)";
        $searchParam = "%{$search}%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
        $types .= "sssss";
    }

    // Add filter condition if provided
    if (!empty($filter)) {
        $query .= " AND professional = ?";
        $params[] = $filter;
        $types .= "s";
    }

    // Add pagination
    $query .= " ORDER BY id DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    // Prepare and execute query
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed");
    }

    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed");
    }

    $result = $stmt->get_result();
    $clients = [];

    while ($row = $result->fetch_assoc()) {
        // Clean up the data
        $row['name'] = htmlspecialchars($row['name']);
        $row['category'] = htmlspecialchars($row['category']);
        $row['role'] = htmlspecialchars($row['role']);
        $row['language'] = htmlspecialchars($row['language']);
        $row['image_url'] = htmlspecialchars($row['image_url']);
        
        $clients[] = $row;
    }

    // Get total count
    $totalResult = $conn->query("SELECT FOUND_ROWS() as total");
    if (!$totalResult) {
        throw new Exception("Count query failed");
    }
    $totalRow = $totalResult->fetch_assoc();
    $total = $totalRow['total'];

    echo json_encode([
        'status' => 'success',
        'clients' => $clients,
        'total' => $total,
        'page' => $page,
        'totalPages' => ceil($total / $limit)
    ]);

} catch (Exception $e) {
    // Log the error to a file instead of displaying it
    error_log("Error in fetch_clients.php: " . $e->getMessage());
    
    // Return a clean JSON error response
    echo json_encode([
        'status' => 'error',
        'message' => 'Error loading clients. Please try again.'
    ]);
} finally {
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>
