<?php
require '../includes/db_connect.php';

// Set pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6; // Posts per page
$offset = ($page - 1) * $limit;

// Category filter
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Define category colors for styling
$category_colors = [
    'Digital Marketing' => [
        'bg' => 'bg-primary bg-opacity-10',
        'text' => 'text-primary',
        'border' => 'border-primary'
    ],
    'Live Streaming Service' => [
        'bg' => 'bg-danger bg-opacity-10',
        'text' => 'text-danger',
        'border' => 'border-danger'
    ],
    'Find Talent' => [
        'bg' => 'bg-info bg-opacity-10',
        'text' => 'text-info',
        'border' => 'border-info'
    ],
    'Information Technology' => [
        'bg' => 'bg-success bg-opacity-10',
        'text' => 'text-success',
        'border' => 'border-success'
    ],
    'Chartered Accountant' => [
        'bg' => 'bg-warning bg-opacity-10',
        'text' => 'text-warning',
        'border' => 'border-warning'
    ],
    'Human Resources' => [
        'bg' => 'bg-secondary bg-opacity-10',
        'text' => 'text-secondary',
        'border' => 'border-secondary'
    ],
    'Courier' => [
        'bg' => 'bg-dark bg-opacity-10',
        'text' => 'text-dark',
        'border' => 'border-dark'
    ],
    'Shipping and Fulfillment' => [
        'bg' => 'bg-primary bg-opacity-10',
        'text' => 'text-primary',
        'border' => 'border-primary'
    ],
    'Stationery' => [
        'bg' => 'bg-danger bg-opacity-10',
        'text' => 'text-danger',
        'border' => 'border-danger'
    ],
    'Real Estate and Property' => [
        'bg' => 'bg-info bg-opacity-10',
        'text' => 'text-info',
        'border' => 'border-info'
    ],
    'Event Management' => [
        'bg' => 'bg-success bg-opacity-10',
        'text' => 'text-success',
        'border' => 'border-success'
    ],
    'Design and Creative' => [
        'bg' => 'bg-warning bg-opacity-10',
        'text' => 'text-warning',
        'border' => 'border-warning'
    ],
    'Corporate Insurance' => [
        'bg' => 'bg-secondary bg-opacity-10',
        'text' => 'text-secondary',
        'border' => 'border-secondary'
    ],
    'Business Strategy' => [
        'bg' => 'bg-dark bg-opacity-10',
        'text' => 'text-dark',
        'border' => 'border-dark'
    ],
    'Innovation' => [
        'bg' => 'bg-primary bg-opacity-10',
        'text' => 'text-primary',
        'border' => 'border-primary'
    ],
    'Industry News' => [
        'bg' => 'bg-danger bg-opacity-10',
        'text' => 'text-danger',
        'border' => 'border-danger'
    ],
    'Marketing and Sales' => [
        'bg' => 'bg-info bg-opacity-10',
        'text' => 'text-info',
        'border' => 'border-info'
    ],
    'Finance and Investment' => [
        'bg' => 'bg-success bg-opacity-10',
        'text' => 'text-success',
        'border' => 'border-success'
    ],
    'Legal Services' => [
        'bg' => 'bg-warning bg-opacity-10',
        'text' => 'text-warning',
        'border' => 'border-warning'
    ],
    'Healthcare Services' => [
        'bg' => 'bg-secondary bg-opacity-10',
        'text' => 'text-secondary',
        'border' => 'border-secondary'
    ],
];

// Base query for blogs
$baseQuery = "FROM blogs WHERE 1=1";

// Add category filter if specified
if ($category_filter) {
    $baseQuery .= " AND JSON_CONTAINS(categories, ?)";
}

// Count total blogs for pagination
$countStmt = $conn->prepare("SELECT COUNT(*) as total " . $baseQuery);
if ($category_filter) {
    $category_json = json_encode($category_filter);
    $countStmt->bind_param("s", $category_json);
}
$countStmt->execute();
$totalBlogs = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalBlogs / $limit);

// Fetch blogs with pagination
$query = "SELECT id, title, seo_title, slug, content, meta_description, categories, image_url, image_alt, created_at " . 
         $baseQuery . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);

if ($category_filter) {
    $category_json = json_encode($category_filter);
    $stmt->bind_param("sii", $category_json, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
$blogs = [];

while ($row = $result->fetch_assoc()) {
    // Decode categories from JSON
    $row['categories_array'] = json_decode($row['categories'] ?? '[]', true);
    
    // Create excerpt
    $row['excerpt'] = substr(strip_tags($row['content']), 0, 160) . '...';
    
    // Format date
    $row['formatted_date'] = date('M d, Y', strtotime($row['created_at']));
    
    $blogs[] = $row;
}

// Fetch categories for filter dropdown
$allCategories = [
    'Digital Marketing',
    'Live Streaming Service',
    'Find Talent',
    'Information Technology',
    'Chartered Accountant',
    'Human Resources',
    'Courier',
    'Shipping and Fulfillment',
    'Stationery',
    'Real Estate and Property',
    'Event Management',
    'Design and Creative',
    'Corporate Insurance',
    'Business Strategy',
    'Innovation',
    'Industry News',
    'Marketing and Sales',
    'Finance and Investment',
    'Legal Services',
    'Healthcare Services'
];
sort($allCategories);

// Meta information for SEO
$meta_title = "Blog | Latest Insights and News";
$meta_description = "Explore our latest articles on business, technology, and industry insights. Stay informed with expert opinions and trends.";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title><?= $meta_title ?></title>
    <meta name="description" content="<?= $meta_description ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>">
    <meta property="og:title" content="<?= $meta_title ?>">
    <meta property="og:description" content="<?= $meta_description ?>">
    <meta property="og:image" content="<?= "https://" . $_SERVER['HTTP_HOST'] ?>/public/blog-default-image.jpg">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>">
    <meta property="twitter:title" content="<?= $meta_title ?>">
    <meta property="twitter:description" content="<?= $meta_description ?>">
    <meta property="twitter:image" content="<?= "https://" . $_SERVER['HTTP_HOST'] ?>/public/blog-default-image.jpg">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .blog-card {
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
            border: none;
        }
        
        .blog-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .blog-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        
        .blog-title {
            font-weight: 700;
            margin-top: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            height: 50px;
        }
        
        .blog-excerpt {
            color: #6c757d;
            margin-top: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            height: 80px;
        }
        
        .blog-meta {
            margin-top: 15px;
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        .category-badge {
            display: inline-block;
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 30px;
            margin-right: 5px;
            margin-bottom: 5px;
            border-width: 1px;
            border-style: solid;
        }
        
        .pagination-link {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: 600;
            margin: 0 5px;
        }
        
        .pagination-link.active {
            background-color: #0d6efd;
            color: white;
        }
        
        .pagination-link:hover:not(.active) {
            background-color: #e9ecef;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            padding: 100px 0;
            color: white;
            margin-bottom: 50px;
        }
        
        .filter-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <!-- Header/Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <img src="../public/logo.png" alt="Logo" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/about-us.html">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/contact-us.html">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active fw-bold" href="index.php">Blog</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Spacing for fixed navbar -->
    <div style="height: 70px;"></div>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-3">Our Blog</h1>
            <p class="lead mb-4">Stay up to date with the latest insights, trends, and expert opinions</p>
        </div>
    </section>

    <!-- Blog Filters -->
    <div class="container">
        <div class="filter-box">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-3 mb-md-0">Filter by Category:</h5>
                </div>
                <div class="col-md-6">
                    <select class="form-select" id="categoryFilter">
                        <option value="">All Categories</option>
                        <?php foreach ($allCategories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= $category_filter === $cat ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Blog Listing -->
    <div class="container">
        <div class="row">
            <?php if (count($blogs) > 0): ?>
                <?php foreach ($blogs as $blog): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="blog-card card shadow-sm h-100">
                            <?php if ($blog['image_url']): ?>
                                <img src="<?= htmlspecialchars($blog['image_url']) ?>" 
                                     alt="<?= htmlspecialchars($blog['image_alt'] ?: $blog['title']) ?>" 
                                     class="blog-image">
                            <?php else: ?>
                                <div class="blog-image bg-light d-flex align-items-center justify-content-center">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body d-flex flex-column">
                                <!-- Categories -->
                                <div class="mb-2">
                                    <?php if (count($blog['categories_array']) > 0): ?>
                                        <?php foreach ($blog['categories_array'] as $category): ?>
                                            <?php 
                                                $colors = $category_colors[$category] ?? [
                                                    'bg' => 'bg-secondary bg-opacity-10',
                                                    'text' => 'text-secondary',
                                                    'border' => 'border-secondary'
                                                ];
                                            ?>
                                            <span class="category-badge <?= $colors['bg'] ?> <?= $colors['text'] ?> <?= $colors['border'] ?>">
                                                <?= htmlspecialchars($category) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="category-badge bg-secondary bg-opacity-10 text-secondary border-secondary">
                                            Uncategorized
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Title -->
                                <h5 class="blog-title">
                                    <a href="post.php?slug=<?= urlencode($blog['slug']) ?>" class="text-decoration-none text-dark">
                                        <?= htmlspecialchars($blog['title']) ?>
                                    </a>
                                </h5>
                                
                                <!-- Excerpt -->
                                <p class="blog-excerpt"><?= $blog['excerpt'] ?></p>
                                
                                <!-- Meta -->
                                <div class="blog-meta mt-auto">
                                    <span><i class="far fa-calendar-alt me-1"></i> <?= $blog['formatted_date'] ?></span>
                                </div>
                                
                                <!-- Read More -->
                                <a href="post.php?slug=<?= urlencode($blog['slug']) ?>" class="btn btn-outline-primary mt-3">
                                    Read More <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        No blog posts found. Please check back later!
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="d-flex justify-content-center my-5">
                <nav aria-label="Blog pagination">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?><?= $category_filter ? '&category=' . urlencode($category_filter) : '' ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= $category_filter ? '&category=' . urlencode($category_filter) : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?><?= $category_filter ? '&category=' . urlencode($category_filter) : '' ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="mb-3">About Us</h5>
                    <p class="text-muted">We provide valuable insights and information through our blog to help you make informed decisions.</p>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="../index.php" class="text-decoration-none text-muted">Home</a></li>
                        <li><a href="../pages/about-us.html" class="text-decoration-none text-muted">About</a></li>
                        <li><a href="../pages/contact-us.html" class="text-decoration-none text-muted">Contact</a></li>
                        <li><a href="index.php" class="text-decoration-none text-muted">Blog</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="mb-3">Connect with Us</h5>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-muted fs-5"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-muted fs-5"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-muted fs-5"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-muted fs-5"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center text-muted">
                <p class="mb-0">&copy; 2024 Blog. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Category filter handling
        document.getElementById('categoryFilter').addEventListener('change', function() {
            const category = this.value;
            if (category) {
                window.location.href = 'index.php?category=' + encodeURIComponent(category);
            } else {
                window.location.href = 'index.php';
            }
        });
    </script>
</body>
</html>
