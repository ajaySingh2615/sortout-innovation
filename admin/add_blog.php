<?php
require '../auth/auth.php';
require '../includes/db_connect.php';
require '../includes/config.php'; // Cloudinary config
require '../vendor/autoload.php';

use Cloudinary\Api\Upload\UploadApi;

$submissionSuccess = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (!isset($_SESSION['user_id'])) {
        die("❌ Session Error: User ID is missing.");
    }

    // ✅ Upload Image to Cloudinary
    $image_url = '';
    if (!empty($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
        try {
            $uploadApi = new UploadApi();
            $upload = $uploadApi->upload($_FILES['image']['tmp_name'], [
                'folder' => 'blog_images'
            ]);
            $image_url = $upload['secure_url'];
        } catch (Exception $e) {
            die("❌ Cloudinary Upload Error: " . $e->getMessage());
        }
    }

    // ✅ Insert Data into Database
    $stmt = $conn->prepare("INSERT INTO blogs (title, content, image_url, created_by) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $title, $content, $image_url, $_SESSION['user_id']);

    if ($stmt->execute()) {
        $submissionSuccess = true; // ✅ Set success flag
    } else {
        die("❌ Database Insert Error: " . $stmt->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Blog</title>

    <!-- ✅ Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
      rel="stylesheet"
    />

    <style>
        body {
    background: radial-gradient(circle, rgba(235, 235, 235, 1) 0%, rgba(220, 220, 220, 1) 100%);
    background-image: url("https://www.transparenttextures.com/patterns/cubes.png");
    background-repeat: repeat;
}

body::before {
    content: "";
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.05); /* Light shadow effect */
    z-index: -1;
}


        /* ✅ Success Modal Styling */
        .modal-content {
            border-radius: 15px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            text-align: center;
            padding: 20px;
        }

        .modal-header {
            border-bottom: none;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #28a745;
        }

        .modal-body p {
            font-size: 1.1rem;
            color: #555;
        }

        .modal-footer {
            border-top: none;
        }

        .modal-footer .btn {
            width: 48%;
            font-size: 1.1rem;
            padding: 10px;
            border-radius: 8px;
        }

        /* ✅ Submit Button Spinner */
        .spinner-border {
            display: none;
        }

        /* ✅ Image Preview */
        .image-preview {
            width: 100%;
            max-height: 250px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #ddd;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            display: none;
        }

        /* ✅ Cancel Button */
        .cancel-btn {
            display: none;
            margin-top: 10px;
            font-size: 1rem;
            padding: 8px 14px;
            border-radius: 8px;
            background: #dc3545;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
            border: none;
            width: 100%;
        }
        .cancel-btn:hover {
            background: #b02a37;
        }
    </style>
</head>
<body>

<!-- ✅ Modern Red & White Navbar -->
<nav class="navbar navbar-expand-lg fixed-top" style="background: linear-gradient(135deg, #fff, #fff); box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);">
    <div class="container">
        <!-- ✅ Logo -->
        <a class="navbar-brand text-white fw-bold d-flex align-items-center" href="#">
            <img src="../public/logo.png" alt="Logo" height="40" class="me-2"> 
        </a>

        <!-- ✅ Mobile Menu Button -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- ✅ Navbar Links -->
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link text-black fw-semibold px-3" href="#">Home</a></li>
                <li class="nav-item"><a class="nav-link text-black fw-semibold px-3" href="../admin/dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link text-black fw-semibold px-3" href="../blog/index.php">Blogs</a></li>
                <!-- <li class="nav-item">
                    <a class="btn btn-light text-danger fw-bold px-4 rounded-pill shadow-sm" href="logout.php">Logout</a>
                </li> -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-black fw-semibold px-3" href="#" role="button" data-bs-toggle="dropdown">
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



<!-- ✅ Spacing for Fixed Navbar -->
<div style="height: 80px;"></div>

<!-- ✅ Blog Form -->
<div class="container d-flex align-items-center justify-content-center min-vh-100">
        <div class="bg-white shadow-lg rounded-4 p-5 w-100" style="max-width: 700px;">
            <h2 class="text-center fw-bold mb-3">Add a New Blog</h2>
            <p class="text-center text-muted mb-4">Share your thoughts and insights by writing an engaging blog.</p>

            <form method="POST" enctype="multipart/form-data" class="row g-3" id="blogForm">
                <div class="col-12">
                    <label class="form-label fw-semibold">Title:</label>
                    <input type="text" name="title" class="form-control" placeholder="Enter blog title" required>
                </div>

                <div class="col-12">
    <label class="form-label fw-semibold">Content:</label>
    <textarea name="content" id="content" class="form-control" rows="5" placeholder="Write your blog content here..." required></textarea>

    <!-- ✅ Instructions for Users -->
    <p class="text-gray-500 text-sm mt-1">
        ℹ️ <strong>Tip:</strong> To add a hyperlink, select the word in the content box and click 
        <span class="text-blue-600 font-semibold">"Insert Link"</span>. Enter the URL, and your selected word will be clickable.
    </p>

    <!-- ✅ Insert Link Button -->
    <button type="button" class="btn btn-secondary mt-2" onclick="insertLink()">🔗 Insert Link</button>
</div>


                <div class="col-12">
                    <label class="form-label fw-semibold">Upload Image:</label>
                    <input type="file" name="image" id="imageUpload" class="form-control" accept="image/*" onchange="previewImage(event)">
                    <img id="imagePreview" class="img-fluid mt-3" style="display:none; border-radius: 10px; max-width: 100%;">
                    <button type="button" class="cancel-btn" id="cancelImage" style="display:none;" onclick="removeImage()">❌ Remove Image</button>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary w-100 py-3" id="submitBtn">
                        🚀 Publish Blog <span class="spinner-border spinner-border-sm ms-2"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ✅ Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">✅ Blog Published Successfully!</h5>
                </div>
                <div class="modal-body">
                    <p>Your blog has been successfully added.</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" id="submitAnother">➕ Submit Another Blog</button>
                    <a href="dashboard.php" class="btn btn-success">🏠 Go to Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Modern Black Footer -->
<footer class="text-white text-center py-5" style="background: #111; box-shadow: 0px -4px 10px rgba(0, 0, 0, 0.2);">
    <div class="container">
        <div class="row">
            <!-- About Section -->
            <div class="col-md-4">
                <h5 class="fw-bold text-uppercase">About Us</h5>
                <p class="small">We share the latest insights in technology, marketing, and innovation.</p>
            </div>

            <!-- Quick Links -->
            <div class="col-md-4">
                <h5 class="fw-bold text-uppercase">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-white text-decoration-none">🏠 Home</a></li>
                    <li><a href="blogs.php" class="text-white text-decoration-none">📝 Blogs</a></li>
                    <li><a href="contact.php" class="text-white text-decoration-none">📞 Contact</a></li>
                </ul>
            </div>

            <!-- Social Media -->
            <div class="col-md-4">
                <h5 class="fw-bold text-uppercase">Follow Us</h5>
                <div class="d-flex justify-content-center">
                    <a href="#" class="me-3 text-white fs-4"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="me-3 text-white fs-4"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="me-3 text-white fs-4"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="mt-4 small">
            © 2025 YourBrand. All Rights Reserved.
        </div>
    </div>
</footer>




    <!-- ✅ Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- ✅ JavaScript for Image Preview, Spinner & Modal -->
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

        document.addEventListener("DOMContentLoaded", function () {
            <?php if ($submissionSuccess): ?>
                new bootstrap.Modal(document.getElementById('successModal')).show();
            <?php endif; ?>
        });

        document.getElementById('submitAnother').addEventListener('click', function() {
            document.getElementById('blogForm').reset();
            removeImage();
            bootstrap.Modal.getInstance(document.getElementById('successModal')).hide();
        });

        function insertLink() {
        let textarea = document.getElementById("content");
        let selectionStart = textarea.selectionStart;
        let selectionEnd = textarea.selectionEnd;

        // ✅ Get the selected text
        let selectedText = textarea.value.substring(selectionStart, selectionEnd).trim();

        if (selectedText.length === 0) {
            alert("Please select a word or phrase to hyperlink.");
            return;
        }

        let url = prompt("Enter the URL (e.g., https://example.com):");
        if (!url) return; // If the user cancels, do nothing

        // ✅ Ensure URL has "https://" or "http://"
        if (!url.startsWith("http://") && !url.startsWith("https://")) {
            url = "https://" + url;
        }

        // ✅ Format the hyperlink correctly
        let hyperlink = `<a href="${url}" target="_blank">${selectedText}</a>`;

        // ✅ Insert the hyperlink into the text area
        let beforeText = textarea.value.substring(0, selectionStart);
        let afterText = textarea.value.substring(selectionEnd);
        textarea.value = beforeText + hyperlink + afterText;

        // ✅ Restore cursor position after inserting the link
        textarea.setSelectionRange(selectionStart, selectionStart + hyperlink.length);
        textarea.focus();
    }

    </script>

</body>
</html>
