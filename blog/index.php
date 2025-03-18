<?php
require_once '../includes/db_connect.php';

// Pagination setup
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 6; // Number of blogs per page
$offset = ($page - 1) * $limit;

// Get category filter
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Get search query
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Base query
$query = "SELECT * FROM blogs WHERE 1=1";
$countQuery = "SELECT COUNT(*) as total FROM blogs WHERE 1=1";

// Add search condition
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= " AND (title LIKE '%$search%' OR content LIKE '%$search%' OR excerpt LIKE '%$search%')";
    $countQuery .= " AND (title LIKE '%$search%' OR content LIKE '%$search%' OR excerpt LIKE '%$search%')";
}

// Add category filter
if (!empty($category_filter)) {
    $category_filter = $conn->real_escape_string($category_filter);
    $query .= " AND categories LIKE '%$category_filter%'";
    $countQuery .= " AND categories LIKE '%$category_filter%'";
}

// Order by creation date (newest first)
$query .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";

// Execute queries
$result = $conn->query($query);
$countResult = $conn->query($countQuery);
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Get all unique categories for filter
$categoriesQuery = "SELECT DISTINCT categories FROM blogs";
$categoriesResult = $conn->query($categoriesQuery);
$allCategories = [];

if ($categoriesResult) {
    while ($row = $categoriesResult->fetch_assoc()) {
        if (!empty($row['categories'])) {
            $cats = json_decode($row['categories'], true);
            if (is_array($cats)) {
                foreach ($cats as $cat) {
                    if (!in_array($cat, $allCategories)) {
                        $allCategories[] = $cat;
                    }
                }
            }
        }
    }
}
sort($allCategories);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sortout Innovation - Blog</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" />
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* Reset and basic styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        a{
            text-decoration: none;
        }
        
        :root {
            --primary-color: #d90429;
            --secondary-color: #ef233c;
            --dark-color: #2b2d42;
            --light-color: #f8f9fa;
            --text-color: #333333;
            --gray-color: #6c757d;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fff;
            color: var(--text-color);
            line-height: 1.6;
        }
        
        /* Navbar Styles */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: white;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            padding: 10px 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-logo {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .navbar-logo img {
            height: 40px;
        }

        .navbar-links ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .navbar-links li {
            margin-left: 25px;
        }

        .navbar-links .nav-link {
            color: var(--dark-color);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: color 0.3s ease;
        }

        .navbar-links .nav-link:hover,
        .navbar-links .nav-link.active {
            color: var(--primary-color);
        }

        .navbar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--dark-color);
            cursor: pointer;
        }

        /* Mobile Menu */
        @media (max-width: 991px) {
            .navbar-links {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                background-color: white;
                flex-direction: column;
                transition: all 0.3s ease;
                box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
                overflow-y: auto;
                z-index: 999;
            }

            .navbar-links.active {
                left: 0;
            }

            .navbar-links ul {
                flex-direction: column;
                width: 100%;
                padding: 20px;
            }

            .navbar-links li {
                margin: 0;
                margin-bottom: 15px;
                width: 100%;
            }

            .navbar-links .nav-link {
                display: block;
                padding: 10px 0;
                font-size: 16px;
            }

            .navbar-toggle {
                display: block;
            }
        }
        
        /* Header/Hero Section */
        .blog-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 100px 0 80px;
            position: relative;
            margin-bottom: 0;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            margin-top: 70px; /* Add margin to account for fixed navbar */
        }
        
        .blog-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(30%, -30%);
            z-index: 1;
        }
        
        .blog-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(-30%, 30%);
            z-index: 1;
        }
        
        .blog-header .container {
            position: relative;
            z-index: 2;
        }
        
        .blog-header h1 {
            color: white;
            font-weight: 800;
            font-size: 3.2rem;
            margin-bottom: 1.3rem;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .blog-header p {
            color: rgba(255, 255, 255, 0.95);
            font-size: 1.25rem;
            max-width: 650px;
            margin-bottom: 2rem;
            line-height: 1.7;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .header-cta {
            display: inline-flex;
            align-items: center;
            background-color: white;
            color: var(--primary-color);
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .header-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            color: var(--secondary-color);
        }
        
        .header-cta i {
            margin-left: 8px;
            transition: transform 0.3s ease;
        }
        
        .header-cta:hover i {
            transform: translateX(3px);
        }
        
        /* Responsive Hero Section */
        @media (max-width: 992px) {
            .blog-header {
                padding: 80px 0 60px;
            }
            
            .blog-header h1 {
                font-size: 2.8rem;
            }
            
            .blog-header p {
                font-size: 1.15rem;
            }
        }
        
        @media (max-width: 768px) {
            .blog-header {
                padding: 70px 0 50px;
            }
            
            .blog-header h1 {
                font-size: 2.5rem;
                margin-bottom: 1rem;
            }
            
            .blog-header p {
                font-size: 1.1rem;
                margin-bottom: 1.5rem;
            }
            
            .header-cta {
                padding: 10px 20px;
            }
        }
        
        @media (max-width: 576px) {
            .blog-header {
                padding: 60px 0 40px;
            }
            
            .blog-header h1 {
                font-size: 2.2rem;
            }
            
            .blog-header p {
                font-size: 1rem;
            }
        }
        
        /* Search and Filter Section */
        .search-filter-section {
            background-color: #fff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 30px 0;
            margin-bottom: 50px;
        }
        
        .search-box {
            position: relative;
            margin-bottom: 0;
        }
        
        .search-input {
            border: none;
            background-color: #f5f5f5;
            padding: 15px 20px;
            border-radius: 5px;
            width: 100%;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            background-color: #fff;
            box-shadow: 0 0 0 2px var(--primary-color);
            outline: none;
        }
        
        .search-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray-color);
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .search-btn:hover {
            color: var(--primary-color);
        }
        
        .filter-dropdown {
            border: none;
            background-color: #f5f5f5;
            padding: 15px 20px;
            border-radius: 5px;
            width: 100%;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        
        .filter-dropdown:focus {
            background-color: #fff;
            box-shadow: 0 0 0 2px var(--primary-color);
            outline: none;
        }
        
        /* Blog Posts Section */
        .blog-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .blog-list {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 30px;
            margin-bottom: 60px;
        }
        
        @media (min-width: 576px) {
            .blog-list {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
        }
        
        @media (min-width: 768px) {
            .blog-list {
                grid-template-columns: repeat(2, 1fr);
                gap: 25px;
            }
        }
        
        @media (min-width: 992px) {
            .blog-list {
                grid-template-columns: repeat(3, 1fr);
                gap: 30px;
            }
        }
        
        /* Blog Card */
        .blog-card {
            display: flex;
            flex-direction: column;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        
        .blog-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        
        .blog-img-container {
            width: 100%;
            position: relative;
            overflow: hidden;
            aspect-ratio: 1/1;
            background-color: #f5f5f5;
        }
        
        .blog-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        /* Add responsive adjustments for different screen sizes */
        @media (max-width: 767px) {
            .blog-img-container {
                aspect-ratio: 1/1; /* Keep square on mobile */
            }
        }

        @media (min-width: 768px) and (max-width: 1023px) {
            .blog-img-container {
                aspect-ratio: 1/1; /* Keep square on tablets */
            }
        }

        @media (min-width: 1024px) {
            .blog-img-container {
                aspect-ratio: 1/1; /* Keep square on desktop */
            }
        }
        
        .blog-card:hover .blog-img {
            transform: scale(1.05);
        }
        
        .blog-category {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 6px 12px;
            border-radius: 20px;
            z-index: 2;
            backdrop-filter: blur(4px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .blog-content {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            background-color: #fff;
        }
        
        .blog-date {
            font-size: 0.85rem;
            color: var(--gray-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .blog-date i {
            margin-right: 5px;
        }
        
        .blog-title {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 12px;
            line-height: 1.4;
            color: var(--dark-color);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .blog-excerpt {
            font-size: 0.9rem;
            color: var(--gray-color);
            margin-bottom: 15px;
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex-grow: 1;
        }
        
        .read-more {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            margin-top: auto;
            transition: color 0.3s;
        }
        
        .read-more:hover {
            color: var(--secondary-color);
        }
        
        .read-more i {
            margin-left: 5px;
            transition: transform 0.3s;
        }
        
        .read-more:hover i {
            transform: translateX(3px);
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-bottom: 50px;
        }
        
        .pagination .page-item .page-link {
            color: var(--text-color);
            border: none;
            margin: 0 5px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            color: white;
        }
        
        .pagination .page-item .page-link:hover:not(.active) {
            background-color: #f5f5f5;
        }
        
        .pagination .page-item.disabled .page-link {
            color: #dee2e6;
        }
        
        /* No results */
        .no-results {
            text-align: center;
            padding: 80px 0;
        }
        
        .no-results i {
            font-size: 3rem;
            color: #e9ecef;
            margin-bottom: 20px;
        }
        
        .no-results h3 {
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark-color);
        }
        
        .no-results p {
            color: var(--gray-color);
            max-width: 500px;
            margin: 0 auto 20px;
        }
        
        .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
            padding: 8px 20px;
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        /* Footer Section */
        .footer-section {
            background-color: #000;
            padding: 60px 0 20px;
            color: #f8f9fa;
        }
        
        .footer-section h4 {
            color: #fff;
            font-weight: 600;
            margin-bottom: 25px;
            font-size: 18px;
        }
        
        .footer-logo {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .footer-logo img {
            height: 60px;
            margin-bottom: 15px;
            background-color: #fff;
            padding: 5px;
            border-radius: 5px;
        }
        
        .footer-logo p {
            color: #d1d1d1;
        }
        
        /* Footer Links */
        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-links li {
            margin-bottom: 12px;
        }
        
        .footer-links li a {
            color: #d1d1d1;
            text-decoration: none;
            transition: color 0.3s;
            font-size: 14px;
        }
        
        .footer-links li a:hover {
            color: #fff;
        }
        
        .footer-links li i {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        /* Social Icons */
        .social-icons {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .social-icons a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            border-radius: 50%;
            transition: all 0.3s;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        }
        
        .social-icons a:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-3px);
        }
        
        /* Footer Bottom */
        .footer-bottom {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            padding-top: 30px;
            margin-top: 40px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .footer-bottom p {
            margin: 0;
            font-size: 14px;
            color: #a0a0a0;
        }
        
        .footer-bottom ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .footer-bottom ul li {
            margin-left: 20px;
        }
        
        .footer-bottom ul li a {
            color: #a0a0a0;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }
        
        .footer-bottom ul li a:hover {
            color: #fff;
        }
        
        /* Responsive Footer */
        @media (max-width: 767px) {
            .footer-section .col-md-6 {
                margin-bottom: 40px;
            }
            
            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }
            
            .footer-bottom ul {
                margin-top: 15px;
                justify-content: center;
            }
            
            .footer-bottom ul li {
                margin: 0 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <header class="navbar">
        <div class="container">
            <!-- Logo -->
            <a href="/" class="navbar-logo">
                <img src="/images/sortoutInnovation-icon/sortout-innovation-only-s.gif" alt="SortOut Innovation" />
            </a>

            <!-- Navigation Links -->
            <nav class="navbar-links">
                <ul>
                    <li><a href="/" class="nav-link">Home</a></li>
                    <li><a href="/pages/about-page/about.html" class="nav-link">About</a></li>
                    <li><a href="/pages/career.html" class="nav-link">Jobs</a></li>
                    <li><a href="/modal_agency.php" class="nav-link">Find Talent</a></li>
                    <li><a href="/pages/our-services-page/service.html" class="nav-link">Service</a></li>
                    <li><a href="/pages/contact-page/contact-page.html" class="nav-link">Contact</a></li>
                    <li><a href="/blog/index.php" class="nav-link active">Blog</a></li>
                </ul>
            </nav>

            <!-- Mobile Menu Button -->
            <button class="navbar-toggle" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <!-- Header -->
    <header class="blog-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h1>Insights & Perspectives</h1>
                    <p>Explore our collection of expert analysis, industry trends, and actionable insights designed to help you make informed decisions and stay ahead in today's rapidly evolving landscape.</p>
                    <a href="#search-section" class="header-cta">
                        Explore Articles <i class="fas fa-chevron-down"></i>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Search and Filter -->
    <div id="search-section" class="search-filter-section">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <form action="index.php" method="GET" class="search-box">
                        <input type="text" name="search" class="search-input" placeholder="Search articles..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
    </div>
                <div class="col-md-4">
                    <form action="index.php" method="GET" id="categoryForm">
                        <select name="category" class="filter-dropdown" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php foreach($allCategories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($category_filter == $cat) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
    </div>
    </div>
        </div>
    </div>

    <!-- Blog Posts -->
    <div class="blog-container">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php 
                // Reset data pointer to beginning
                $result->data_seek(0);
                
                // Count total blogs
                $total_blogs = $result->num_rows;
                $blog_count = 0;
                
                // Array to store blogs
                $blogs = [];
                while($blog = $result->fetch_assoc()) {
                    $blogs[] = $blog;
                }
            ?>
            
            <div class="blog-list">
                <?php foreach($blogs as $blog): ?>
                    <?php 
                        // Parse categories
                        $categories = [];
                        if (!empty($blog['categories'])) {
                            if (strpos($blog['categories'], '[') === 0) {
                                // JSON format
                                $categories = json_decode($blog['categories'], true) ?: [];
                            } else {
                                // Comma-separated format
                                $categories = explode(',', $blog['categories']);
                            }
                        }
                        
                        // Format date
                        $date = new DateTime($blog['created_at']);
                        $formatted_date = $date->format('M d, Y');
                        
                        // Generate excerpt if not present
                        $excerpt = isset($blog['excerpt']) && !empty($blog['excerpt']) 
                            ? $blog['excerpt'] 
                            : substr(strip_tags($blog['content']), 0, 150) . '...';
                    ?>
                    
                    <div class="blog-card">
                        <div class="blog-img-container">
                            <?php if(!empty($categories)): ?>
                                <div class="blog-category"><?php echo htmlspecialchars($categories[0]); ?></div>
                            <?php endif; ?>
                            
                            <?php if(!empty($blog['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($blog['image_url']); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>" class="blog-img">
                            <?php else: ?>
                                <img src="../public/images/blog-placeholder.jpg" alt="<?php echo htmlspecialchars($blog['title']); ?>" class="blog-img">
                            <?php endif; ?>
                    </div>
                        
                        <div class="blog-content">
                            <div class="blog-date">
                                <i class="far fa-calendar-alt me-2"></i> <?php echo $formatted_date; ?>
                </div>

                            <h3 class="blog-title"><?php echo htmlspecialchars($blog['title']); ?></h3>
                            
                            <p class="blog-excerpt"><?php echo htmlspecialchars($excerpt); ?></p>
                            
                            <a href="post.php?slug=<?php echo urlencode($blog['slug']); ?>" class="read-more">
                                Read More <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if($totalPages > 1): ?>
                <nav aria-label="Blog pagination">
                    <ul class="pagination">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($category_filter) ? '&category='.urlencode($category_filter) : ''; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php for($i = max(1, $page-2); $i <= min($page+2, $totalPages); $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($category_filter) ? '&category='.urlencode($category_filter) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo !empty($category_filter) ? '&category='.urlencode($category_filter) : ''; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>No Blog Posts Found</h3>
                <p>We couldn't find any blog posts matching your criteria. Try adjusting your search or browse all articles.</p>
                <a href="index.php" class="btn btn-outline-primary">View All Articles</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="footer-section">
        <div class="container">
            <div class="row">
                <!-- Column 1: Company Info -->
                <div class="col-lg-3 col-md-6">
                    <div class="footer-logo">
                        <img src="/images/sortoutInnovation-icon/Sortout innovation.jpg" alt="SortOut Innovation" />
                        <p class="text-center">
                            Empowering businesses with top-notch solutions in digital, IT, and business services.
                        </p>
                    </div>
                </div>

                <!-- Column 2: Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="/">Home</a></li>
                        <li><a href="/pages/about-page/about.html">About Us</a></li>
                        <li><a href="/pages/contact-page/contact-page.html">Contact</a></li>
                        <li><a href="/pages/career.html">Careers</a></li>
                        <li><a href="/pages/our-services-page/service.html">Services</a></li>
                        <li><a href="/blog/index.php">Blogs</a></li>
                        <li><a href="/auth/register.php">Register</a></li>
                        <li><a href="/modal_agency.php">talent</a></li>
                    </ul>
                </div>

                <!-- Column 3: Our Services -->
                <div class="col-lg-2 col-md-6">
                    <h4>Our Services</h4>
                    <ul class="footer-links">
                        <li><a href="/pages/services/socialMediaInfluencers.html">Digital Marketing</a></li>
                        <li><a href="/pages/services/itServices.html">IT Support</a></li>
                        <li><a href="/pages/services/caServices.html">CA Services</a></li>
                        <li><a href="/pages/services/hrServices.html">HR Services</a></li>
                        <li><a href="/pages/services/courierServices.html">Courier Services</a></li>
                        <li><a href="/pages/services/shipping.html">Shipping & Fulfillment</a></li>
                        <li><a href="/pages/services/stationeryServices.html">Stationery Services</a></li>
                        <li><a href="/pages/services/propertyServices.html">Real Estate & Property</a></li>
                        <li><a href="/pages/services/event-managementServices.html">Event Management</a></li>
                        <li><a href="/pages/services/designAndCreative.html">Design & Creative</a></li>
                        <li><a href="/pages/services/designAndCreative.html">Web & App Development</a></li>
                        <li><a href="/pages/talent.page/talent.html">Find Talent</a></li>
                    </ul>
</div>

                <!-- Column 4: Contact Info -->
                <div class="col-lg-3 col-md-6">
                    <h4>Contact Us</h4>
                    <ul class="footer-links">
                        <li>
                            <i class="fas fa-phone"></i>
                            <a href="tel:+919818559036">+91 9818559036</a>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:info@sortoutinnovation.com">info@sortoutinnovation.com</a>
                        </li>
                        <li>
                            <i class="fas fa-map-marker-alt"></i> Spaze i-Tech Park, Gurugram, India
                        </li>
                    </ul>
                </div>

                <!-- Column 5: Social Media -->
                <div class="col-lg-2 col-md-6">
                    <h4>Follow Us</h4>
                    <div class="social-icons">
                        <a href="https://www.facebook.com/profile.php?id=61556452066209"><i class="fab fa-facebook"></i></a>
                        <a href="https://youtu.be/tw-xk-Pb-zA?si=QMTwuvhEuTegpqDr"><i class="fab fa-youtube"></i></a>
                        <a href="https://www.linkedin.com/company/sortout-innovation/"><i class="fab fa-linkedin"></i></a>
                        <a href="https://www.instagram.com/sortoutinnovation"><i class="fab fa-instagram"></i></a>
                    </div>
    </div>
</div>

            <!-- Copyright & Legal Links -->
            <div class="footer-bottom">
                <p>&copy; 2025 SortOut Innovation. All Rights Reserved.</p>
                <ul>
                    <li><a href="/privacy-policy">Privacy Policy</a></li>
                    <li><a href="/terms">Terms & Conditions</a></li>
        </ul>
            </div>
    </div>
</footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Navbar Script -->
    <script>
        // Mobile Menu Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const navbarToggle = document.querySelector(".navbar-toggle");
            const navbarLinks = document.querySelector(".navbar-links");

            navbarToggle.addEventListener("click", () => {
                navbarLinks.classList.toggle("active");
            });

            // Sticky Navbar on Scroll
            window.addEventListener("scroll", () => {
                const navbar = document.querySelector(".navbar");
                if (window.scrollY > 50) {
                    navbar.classList.add("scrolled");
                } else {
                    navbar.classList.remove("scrolled");
                }
            });
        });
    </script>
</body>
</html>
