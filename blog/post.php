<?php
require '../includes/db_connect.php';

// Check if slug parameter exists
if (!isset($_GET['slug'])) {
    header("Location: index.php");
    exit();
}

$slug = $_GET['slug'];

// Fetch blog post by slug
$stmt = $conn->prepare("SELECT id, title, seo_title, content, meta_description, focus_keyword, slug, categories, image_url, image_alt, created_at, created_by FROM blogs WHERE slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

// If blog post not found, redirect to blog index
if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$blog = $result->fetch_assoc();

// Decode categories
$blog['categories_array'] = json_decode($blog['categories'] ?? '[]', true);

// Format date
$blog['formatted_date'] = date('F d, Y', strtotime($blog['created_at']));

// Calculate reading time (average reading speed: 200 words per minute)
$word_count = str_word_count(strip_tags($blog['content']));
$reading_time = ceil($word_count / 200);

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

// Fetch related blog posts (same category, excluding current post)
$related_posts = [];
if (!empty($blog['categories_array'])) {
    $category = $blog['categories_array'][0]; // Use first category
    $related_query = "SELECT id, title, slug, image_url, image_alt, created_at FROM blogs 
                      WHERE JSON_CONTAINS(categories, ?) AND id != ? 
                      ORDER BY created_at DESC LIMIT 3";
    $related_stmt = $conn->prepare($related_query);
    $category_json = json_encode($category);
    $related_stmt->bind_param("si", $category_json, $blog['id']);
    $related_stmt->execute();
    $related_result = $related_stmt->get_result();
    
    while ($related = $related_result->fetch_assoc()) {
        $related['formatted_date'] = date('M d, Y', strtotime($related['created_at']));
        $related_posts[] = $related;
    }
}

// Truncate content for meta description if blog meta description is empty
if (empty($blog['meta_description'])) {
    $blog['meta_description'] = substr(strip_tags($blog['content']), 0, 160) . '...';
}

// Use SEO title or default to regular title
$page_title = !empty($blog['seo_title']) ? $blog['seo_title'] : $blog['title'];

// Get current URL for canonical and Open Graph tags
$current_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($blog['meta_description']) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($blog['focus_keyword']) ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= $current_url ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?= $current_url ?>">
    <meta property="og:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($blog['meta_description']) ?>">
    <?php if ($blog['image_url']): ?>
        <meta property="og:image" content="<?= htmlspecialchars($blog['image_url']) ?>">
    <?php endif; ?>
    <meta property="article:published_time" content="<?= date('c', strtotime($blog['created_at'])) ?>">
    <?php foreach ($blog['categories_array'] as $category): ?>
        <meta property="article:tag" content="<?= htmlspecialchars($category) ?>">
    <?php endforeach; ?>
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= $current_url ?>">
    <meta property="twitter:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta property="twitter:description" content="<?= htmlspecialchars($blog['meta_description']) ?>">
    <?php if ($blog['image_url']): ?>
        <meta property="twitter:image" content="<?= htmlspecialchars($blog['image_url']) ?>">
    <?php endif; ?>
    
    <!-- Schema.org / JSON-LD -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BlogPosting",
        "headline": "<?= htmlspecialchars($page_title) ?>",
        "description": "<?= htmlspecialchars($blog['meta_description']) ?>",
        "image": "<?= htmlspecialchars($blog['image_url'] ?? '') ?>",
        "datePublished": "<?= date('c', strtotime($blog['created_at'])) ?>",
        "url": "<?= $current_url ?>",
        "author": {
            "@type": "Person",
            "name": "Admin"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Your Organization",
            "logo": {
                "@type": "ImageObject",
                "url": "<?= "https://" . $_SERVER['HTTP_HOST'] ?>/public/logo.png"
            }
        },
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "<?= $current_url ?>"
        },
        "keywords": "<?= htmlspecialchars($blog['focus_keyword']) ?>"
    }
    </script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .blog-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            padding: 100px 0 50px;
            color: white;
            margin-bottom: 50px;
            position: relative;
        }
        
        .blog-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 70px;
            background: linear-gradient(to bottom right, transparent 49%, #f8f9fa 50%);
        }
        
        .blog-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .blog-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #343a40;
        }
        
        .blog-content p {
            margin-bottom: 1.5rem;
        }
        
        .blog-content h2 {
            font-weight: 700;
            margin-top: 2.5rem;
            margin-bottom: 1.5rem;
        }
        
        .blog-content h3 {
            font-weight: 600;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        
        .blog-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 1.5rem 0;
        }
        
        .featured-image {
            border-radius: 8px;
            width: 100%;
            height: auto;
            max-height: 500px;
            object-fit: cover;
            margin-bottom: 2rem;
        }
        
        .blog-meta {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .category-badge {
            display: inline-block;
            padding: 0.35em 0.65em;
            font-size: 0.85em;
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
        
        .related-card {
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
            border: none;
        }
        
        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .related-image {
            height: 180px;
            object-fit: cover;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        
        .related-title {
            font-weight: 600;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            height: 50px;
        }
        
        /* Table of Contents */
        .toc {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .toc-title {
            font-weight: 700;
            margin-bottom: 1rem;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 0.5rem;
        }
        
        .toc ul {
            padding-left: 1.5rem;
        }
        
        .toc li {
            margin-bottom: 0.5rem;
        }
        
        /* Share buttons */
        .share-container {
            margin: 3rem 0;
            text-align: center;
        }
        
        .share-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            color: white;
            margin: 0 5px;
            transition: transform 0.3s;
        }
        
        .share-button:hover {
            transform: translateY(-3px);
        }
        
        .share-facebook {
            background-color: #3b5998;
        }
        
        .share-twitter {
            background-color: #1da1f2;
        }
        
        .share-linkedin {
            background-color: #0077b5;
        }
        
        .share-pinterest {
            background-color: #bd081c;
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

    <!-- Blog Header -->
    <header class="blog-header">
        <div class="container text-center">
            <div class="blog-container">
                <h1 class="display-4 fw-bold mb-4"><?= htmlspecialchars($blog['title']) ?></h1>
                
                <!-- Categories -->
                <div class="mb-4">
                    <?php if (count($blog['categories_array']) > 0): ?>
                        <?php foreach ($blog['categories_array'] as $category): ?>
                            <?php 
                                $colors = $category_colors[$category] ?? [
                                    'bg' => 'bg-secondary bg-opacity-10',
                                    'text' => 'text-white',
                                    'border' => 'border-white'
                                ];
                                // Force white text for header categories
                                $colors['text'] = 'text-white';
                                $colors['bg'] = 'bg-white bg-opacity-10';
                                $colors['border'] = 'border-white';
                            ?>
                            <span class="category-badge <?= $colors['bg'] ?> <?= $colors['text'] ?> <?= $colors['border'] ?>">
                                <?= htmlspecialchars($category) ?>
                            </span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="category-badge bg-white bg-opacity-10 text-white border-white">
                            Uncategorized
                        </span>
                    <?php endif; ?>
                </div>
                
                <!-- Meta info -->
                <div class="d-flex justify-content-center text-white-50">
                    <span class="me-3"><i class="far fa-calendar-alt me-1"></i> <?= $blog['formatted_date'] ?></span>
                    <span><i class="far fa-clock me-1"></i> <?= $reading_time ?> min read</span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Featured Image -->
                <?php if ($blog['image_url']): ?>
                    <img src="<?= htmlspecialchars($blog['image_url']) ?>" 
                         alt="<?= htmlspecialchars($blog['image_alt'] ?: $blog['title']) ?>" 
                         class="featured-image shadow">
                <?php endif; ?>
                
                <!-- Blog Meta -->
                <div class="blog-meta">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <span><i class="far fa-calendar-alt me-1"></i> <?= $blog['formatted_date'] ?></span>
                            <span class="ms-3"><i class="far fa-clock me-1"></i> <?= $reading_time ?> min read</span>
                        </div>
                        <div class="mt-2 mt-md-0">
                            <a href="index.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Blog
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Blog Content -->
                <div class="blog-content">
                    <?= $blog['content'] ?>
                </div>
                
                <!-- Share Buttons -->
                <div class="share-container">
                    <h5 class="mb-3">Share this article</h5>
                    <div>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($current_url) ?>" 
                           target="_blank" class="share-button share-facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode($current_url) ?>&text=<?= urlencode($blog['title']) ?>" 
                           target="_blank" class="share-button share-twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode($current_url) ?>&title=<?= urlencode($blog['title']) ?>" 
                           target="_blank" class="share-button share-linkedin">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="https://pinterest.com/pin/create/button/?url=<?= urlencode($current_url) ?>&media=<?= urlencode($blog['image_url']) ?>&description=<?= urlencode($blog['title']) ?>" 
                           target="_blank" class="share-button share-pinterest">
                            <i class="fab fa-pinterest-p"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Tags -->
                <?php if (count($blog['categories_array']) > 0): ?>
                    <div class="mt-5">
                        <h5 class="mb-3">Tags</h5>
                        <?php foreach ($blog['categories_array'] as $category): ?>
                            <?php 
                                $colors = $category_colors[$category] ?? [
                                    'bg' => 'bg-secondary bg-opacity-10',
                                    'text' => 'text-secondary',
                                    'border' => 'border-secondary'
                                ];
                            ?>
                            <a href="index.php?category=<?= urlencode($category) ?>" class="text-decoration-none">
                                <span class="category-badge <?= $colors['bg'] ?> <?= $colors['text'] ?> <?= $colors['border'] ?>">
                                    <?= htmlspecialchars($category) ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Related Posts -->
        <?php if (count($related_posts) > 0): ?>
            <div class="row mt-5">
                <div class="col-lg-8 mx-auto">
                    <h3 class="mb-4">Related Articles</h3>
                </div>
            </div>
            <div class="row">
                <?php foreach ($related_posts as $related): ?>
                    <div class="col-md-4 mb-4">
                        <div class="related-card card shadow-sm h-100">
                            <?php if ($related['image_url']): ?>
                                <img src="<?= htmlspecialchars($related['image_url']) ?>" 
                                     alt="<?= htmlspecialchars($related['image_alt'] ?: $related['title']) ?>" 
                                     class="related-image">
                            <?php else: ?>
                                <div class="related-image bg-light d-flex align-items-center justify-content-center">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h5 class="related-title">
                                    <a href="post.php?slug=<?= urlencode($related['slug']) ?>" class="text-decoration-none text-dark">
                                        <?= htmlspecialchars($related['title']) ?>
                                    </a>
                                </h5>
                                
                                <div class="mt-2 text-muted">
                                    <small><i class="far fa-calendar-alt me-1"></i> <?= $related['formatted_date'] ?></small>
                                </div>
                                
                                <a href="post.php?slug=<?= urlencode($related['slug']) ?>" class="btn btn-outline-primary btn-sm mt-3">
                                    Read Article
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
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
</body>
</html>
