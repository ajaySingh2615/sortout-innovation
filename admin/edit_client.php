<?php
require_once '../includes/db_connect.php';
require_once '../includes/config.php'; // Include Cloudinary configuration
use Cloudinary\Api\Upload\UploadApi;

// Ensure headers are set before any output
header('Content-Type: application/json');

// Enable error reporting for debugging but don't display errors in production
error_reporting(E_ALL);
ini_set('display_errors', 0); // Changed to 0 to prevent PHP errors in JSON output

// Function to sanitize input
function sanitize($conn, $input) {
    return mysqli_real_escape_string($conn, trim($input));
}

// Wrap everything in a try-catch block
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        // First, get the current client data to check their professional type
        $checkQuery = "SELECT * FROM clients WHERE id = ?";
        $stmt = mysqli_prepare($conn, $checkQuery);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement");
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to execute query");
        }
        
        $result = mysqli_stmt_get_result($stmt);
        $clientData = mysqli_fetch_assoc($result);
        
        if (!$clientData) {
            throw new Exception("Client not found");
        }

        // Common fields for both Artist and Employee
        $name = sanitize($conn, $_POST['name']);
        $age = intval($_POST['age']);
        $gender = sanitize($conn, $_POST['gender']);
        $city = sanitize($conn, $_POST['city']);
        $language = sanitize($conn, $_POST['language']);
        $phone = isset($_POST['phone']) ? sanitize($conn, $_POST['phone']) : $clientData['phone'];

        // Handle image upload if a new image is provided
        $image_url = $clientData['image_url']; // Keep existing image URL by default
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                $uploadApi = new UploadApi();
                $result = $uploadApi->upload($_FILES['image']['tmp_name'], [
                    'folder' => 'client_images',
                    'resource_type' => 'image'
                ]);
                $image_url = $result['secure_url'];
            } catch (Exception $e) {
                // Log error but continue with existing image
                error_log("Image upload error: " . $e->getMessage());
            }
        }

        // Initialize update arrays
        $updateFields = [];
        $params = [];
        $types = "";

        // Add common fields for both types
        $updateFields = [
            "name = ?",
            "age = ?",
            "gender = ?",
            "city = ?",
            "language = ?",
            "phone = ?",
            "image_url = ?"
        ];
        
        $params = [$name, $age, $gender, $city, $language, $phone, $image_url];
        $types = "sisssss";

        if ($clientData['professional'] === 'Artist') {
            // Artist specific fields
            $category = sanitize($conn, $_POST['category']);
            $followers = sanitize($conn, $_POST['followers']);
            
            $updateFields[] = "category = ?";
            $updateFields[] = "followers = ?";
            $updateFields[] = "role = NULL";
            $updateFields[] = "experience = NULL";
            $updateFields[] = "resume_url = NULL";
            
            $params[] = $category;
            $params[] = $followers;
            $types .= "ss";
        } else {
            // Employee specific fields
            $role = sanitize($conn, $_POST['role']);
            $experience = sanitize($conn, $_POST['experience']);
            
            // Handle resume upload if provided
            $resume_url = $clientData['resume_url']; // Keep existing resume URL by default
            if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/resumes/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION);
                $new_filename = uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['resume']['tmp_name'], $upload_path)) {
                    $resume_url = 'uploads/resumes/' . $new_filename;
                }
            }
            
            $updateFields[] = "role = ?";
            $updateFields[] = "experience = ?";
            $updateFields[] = "resume_url = ?";
            $updateFields[] = "category = NULL";
            $updateFields[] = "followers = NULL";
            
            $params[] = $role;
            $params[] = $experience;
            $params[] = $resume_url;
            $types .= "sss";
        }

        // Add ID for WHERE clause
        $params[] = $id;
        $types .= "i";

        // Construct and execute the update query
        $updateQuery = "UPDATE clients SET " . implode(", ", $updateFields) . " WHERE id = ?";
        $stmt = mysqli_prepare($conn, $updateQuery);
        
        // Debug information
        error_log("Types string: " . $types);
        error_log("Number of params: " . count($params));
        error_log("Update query: " . $updateQuery);
        
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Client updated successfully'
            ]);
        } else {
            throw new Exception("Error updating client: " . mysqli_error($conn));
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Handle GET request - this is for fetching client data
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$id) {
            throw new Exception("Invalid client ID");
        }
        
        $query = "SELECT * FROM clients WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement");
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to execute query");
        }
        
        $result = mysqli_stmt_get_result($stmt);
        $client = mysqli_fetch_assoc($result);
        
        if (!$client) {
            throw new Exception("Client not found");
        }
        
        echo json_encode([
            'status' => 'success',
            'client' => $client
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request method'
        ]);
    }
} catch (Exception $e) {
    // Log the error to a file instead of displaying it
    error_log("Error in edit_client.php: " . $e->getMessage());
    
    // Return a clean JSON error response
    echo json_encode([
        'status' => 'error',
        'message' => 'Error loading client data. Please try again.'
    ]);
} finally {
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>
