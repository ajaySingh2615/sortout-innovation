<?php
require '../auth/auth.php'; // Ensure only admin access
require '../includes/db_connect.php';
require '../includes/config.php'; // Cloudinary config
require '../vendor/autoload.php';

use Cloudinary\Api\Upload\UploadApi;

if (!isset($_GET['id'])) {
    die("Invalid blog ID.");
}

$blog_id = $_GET['id'];

// Fetch existing blog details
$stmt = $conn->prepare("SELECT title, content, image_url FROM blogs WHERE id = ?");
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$result = $stmt->get_result();
$blog = $result->fetch_assoc();
$stmt->close();

if (!$blog) {
    die("Blog not found.");
}

// ✅ Handle Blog Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $image_url = $blog['image_url']; // Keep existing image if not updated

    // ✅ Handle Image Upload
    if (!empty($_FILES['image']['tmp_name'])) {
        try {
            $uploadApi = new UploadApi();
            $upload = $uploadApi->upload($_FILES['image']['tmp_name'], [
                'folder' => 'blog_images'
            ]);
            $image_url = $upload['secure_url'];
        } catch (Exception $e) {
            die("Cloudinary Upload Error: " . $e->getMessage());
        }
    }

    $stmt = $conn->prepare("UPDATE blogs SET title = ?, content = ?, image_url = ? WHERE id = ?");
    $stmt->bind_param("sssi", $title, $content, $image_url, $blog_id);

    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error updating blog: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog</title>

    <!-- ✅ Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* ✅ Background Pattern */
        body {
            background: radial-gradient(circle, rgba(255,255,255,1) 0%, rgba(240,240,240,1) 100%);
            background-image: url("https://www.transparenttextures.com/patterns/cubes.png");
            background-repeat: repeat;
        }

        /* ✅ Image Preview */
        .image-preview {
            width: 100%;
            max-height: 250px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #ddd;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* ✅ Button Styling */
        .btn-danger, .btn-primary {
            font-size: 1.1rem;
            padding: 10px;
            border-radius: 8px;
        }

        /* ✅ Navbar */
        .navbar {
            background: linear-gradient(135deg, #d90429, #ef233c);
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        }

        /* ✅ Footer */
        footer {
            background: black;
            color: white;
            padding: 30px 0;
            text-align: center;
        }
    </style>
</head>
<body>

<!-- ✅ Navbar -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand text-white fw-bold d-flex align-items-center" href="#">
            <img src="logo.png" alt="Logo" height="40" class="me-2"> 
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link text-white fw-semibold px-3" href="#">Home</a></li>
                <li class="nav-item"><a class="nav-link text-white fw-semibold px-3" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link text-white fw-semibold px-3" href="add_blog.php">Add Blog</a></li>
                <!-- ✅ Display Logged-in User Info -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white fw-semibold px-3" href="#" role="button" data-bs-toggle="dropdown">
                        👤 <?= $_SESSION['username']; ?> (<?= ucfirst($_SESSION['role']); ?>)
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="../auth/logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- ✅ Spacing for Navbar -->
<div style="height: 80px;"></div>

<!-- ✅ Edit Blog Form -->
<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="bg-white shadow-lg rounded-4 p-5 w-100" style="max-width: 700px;">
        <h2 class="text-center fw-bold mb-3">Edit Blog</h2>
        <p class="text-center text-muted mb-4">Update the blog details below.</p>

        <form method="POST" enctype="multipart/form-data" class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Title:</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($blog['title']) ?>" required>
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Content:</label>
                <textarea name="content" class="form-control" rows="5" required><?= htmlspecialchars($blog['content']) ?></textarea>
            </div>

            <!-- ✅ Current Image Preview -->
            <div class="col-12">
                <label class="form-label fw-semibold">Current Image:</label>
                <img src="<?= $blog['image_url'] ?>" class="image-preview img-fluid">
            </div>

            <!-- ✅ Upload New Image -->
            <div class="col-12">
                <label class="form-label fw-semibold">Upload New Image (optional):</label>
                <input type="file" name="image" id="imageUpload" class="form-control" accept="image/*" onchange="previewImage(event)">
                <img id="imagePreview" class="img-fluid mt-3" style="display:none;">
                <button type="button" class="btn btn-danger mt-2" id="cancelImage" style="display:none;" onclick="removeImage()">❌ Remove Image</button>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary w-100 py-3">🚀 Update Blog</button>
            </div>
        </form>
    </div>
</div>

<!-- ✅ Footer -->
<footer>© 2025 Admin Panel | All Rights Reserved.</footer>

<!-- ✅ Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- ✅ JavaScript for Image Preview & Remove -->
<script>
    function previewImage(event) {
        const imagePreview = document.getElementById('imagePreview');
        const cancelBtn = document.getElementById('cancelImage');
        const file = event.target.files[0];

        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
                cancelBtn.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    }

    function removeImage() {
        document.getElementById('imageUpload').value = "";
        document.getElementById('imagePreview').style.display = 'none';
        document.getElementById('cancelImage').style.display = 'none';
    }
</script>

</body>
</html>
