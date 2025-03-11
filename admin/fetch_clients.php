<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../includes/db_connect.php';  // Database connection

header('Content-Type: application/json'); // Ensure JSON output

// Debug: Check if database connection is successful
if ($conn->connect_error) {
    echo json_encode([
        "error" => true,
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit;
}

try {
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
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
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
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    $conn->close();
}
?>
