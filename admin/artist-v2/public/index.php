<?php
require_once '../../../includes/db_connect.php';
include_once '../includes/functions.php';

// Get all active categories
$categories = getCategories(true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>App Directory</title>
    <meta property="og:url" content="https://example.com/artist-v2/">
    <meta property="og:image" content="https://example.com/artist-v2/images/og-image.jpg">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
            --border-radius: 1rem;
        }

        body {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #444;
            line-height: 1.6;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 5px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Typography */
        .display-3, .display-4, .display-5 {
            font-weight: 700;
        }
        
        .text-gradient {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        /* Section styles */
        section {
            position: relative;
            overflow: hidden;
        }
        
        .section-title {
            font-weight: 700;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-radius: 3px;
        }
        
        /* Card styles */
        .card {
            border: none;
            transition: var(--transition);
            box-shadow: var(--box-shadow);
            border-radius: var(--border-radius);
        }
        
        .card-title {
            font-weight: 600;
        }
        
        .category-img, .app-img {
            transition: transform 0.5s ease;
        }
        
        .category-card:hover .category-img, 
        .app-card:hover .app-img {
            transform: scale(1.05);
        }
        
        /* Button styles */
        .btn {
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: var(--transition);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
        }
        
        .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        /* Badge styles */
        .badge {
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        
        /* Back to top button */
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 99;
            display: none;
            width: 45px;
            height: 45px;
            line-height: 45px;
            text-align: center;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }
        
        .back-to-top:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        /* Media queries */
        @media (max-width: 991px) {
            .display-3 {
                font-size: 2.5rem;
            }
            .display-4 {
                font-size: 2rem;
            }
            .display-5 {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 767px) {
            .py-5 {
                padding-top: 3rem !important;
                padding-bottom: 3rem !important;
            }
        }
    </style>
</head>
<body>
    <!-- Header/Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-mobile-alt me-2"></i> App Directory
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#categories">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#popular-apps">Popular Apps</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Modern Illustration Design -->
    <section class="hero-section py-5 position-relative overflow-hidden bg-gradient" style="background: linear-gradient(135deg, #ff3e3e 0%, #c50000 100%);">
        <!-- Floating Elements -->
        <div class="position-absolute top-0 start-0 d-none d-lg-block" style="transform: translate(-30%, -30%);">
            <div class="bg-white opacity-10 rounded-circle" style="width: 400px; height: 400px;"></div>
        </div>
        <div class="position-absolute bottom-0 end-0 d-none d-lg-block">
            <div class="bg-white opacity-10 rounded-circle" style="width: 300px; height: 300px;"></div>
        </div>
        <div class="position-absolute top-50 start-50 d-none d-xl-block" style="transform: translateX(-80%);">
            <div class="bg-white opacity-5 rounded-circle" style="width: 200px; height: 200px;"></div>
        </div>
        
        <div class="container py-4 py-md-5 position-relative hero-content">
            <div class="row align-items-center">
                <!-- Left Column: Text Content -->
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <span class="badge bg-white text-danger fw-bold px-3 py-2 rounded-pill mb-3 animate__animated animate__fadeInDown">App Directory</span>
                    <h1 class="hero-title fw-bold mb-3 animate__animated animate__fadeInUp">Discover <span class="text-highlight">Amazing</span> Apps</h1>
                    <div class="content-divider my-4 animate__animated animate__fadeInUp animate__delay-1s"></div>
                    <p class="hero-subtitle fw-light mb-4 animate__animated animate__fadeInUp animate__delay-1s">Explore our curated collection of the best apps across various categories, all in one place.</p>
                    <div class="d-flex flex-wrap gap-3 mt-4 animate__animated animate__fadeInUp animate__delay-2s">
                        <a href="#categories" class="btn btn-light btn-lg fw-bold px-4 py-2 shadow-sm rounded-pill">
                            <i class="fas fa-search me-2"></i> Explore Apps
                        </a>
                        <a href="#popular-apps" class="btn btn-danger btn-lg px-4 py-2 rounded-pill">
                            <i class="fas fa-fire me-2"></i> Popular Apps
                        </a>
                    </div>
                </div>

                <!-- Right Column: Illustration -->
                <div class="col-lg-6 position-relative">
                    <div class="position-relative">
                        <!-- Main Illustration with floating app icons -->
                        <div class="bg-white rounded-4 shadow-lg p-3 animate__animated animate__zoomIn">
                            <!-- 
                            RECOMMENDED IMAGE:
                            1. Use an image showing devices with apps or app interfaces
                            2. Recommended dimensions: 1200x800px (16:9 ratio)
                            3. Placed at: ../../assets/img/apps/hero-image.jpg
                            4. If no custom image, the default will show
                            -->
                            <img src="../../assets/img/apps/hero-image.webp" 
                                 alt="App Directory" 
                                 class="img-fluid rounded-4" 
                                 onerror="this.src='../../assets/img/default-app.webp';">
                            <div class="image-overlay"></div>
                        </div>
                        
                        <!-- Floating App Icons -->
                        <div class="position-absolute top-0 start-0 translate-middle animate__animated animate__fadeInTopLeft animate__delay-1s d-none d-md-block">
                            <div class="bg-danger text-white rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                                <i class="fas fa-camera fs-3"></i>
                            </div>
                        </div>
                        <div class="position-absolute top-100 start-50 translate-middle animate__animated animate__fadeInBottomRight animate__delay-2s d-none d-md-block">
                            <div class="bg-white text-danger rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="fas fa-music fs-3"></i>
                            </div>
                        </div>
                        <div class="position-absolute top-50 start-100 translate-middle animate__animated animate__fadeInRight animate__delay-1s d-none d-md-block">
                            <div class="bg-danger text-white rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="fas fa-gamepad fs-3"></i>
                            </div>
                        </div>
                        <div class="position-absolute top-0 start-100 translate-middle animate__animated animate__fadeInTopRight animate__delay-3s d-none d-md-block">
                            <div class="bg-white text-danger rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-shopping-cart fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Custom CSS for Hero Section -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
    
        .hero-section {
            font-family: 'Poppins', sans-serif;
            color: #fff;
        }
        
        .hero-content {
            position: relative;
            z-index: 5;
        }
        
        .hero-title {
            font-size: 3.5rem;
            letter-spacing: -0.5px;
            line-height: 1.2;
            color: #fff;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.4);
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            line-height: 1.6;
            color: #fff;
            opacity: 0.95;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }
        
        .text-highlight {
            color: #ffe0e0;
            position: relative;
            z-index: 1;
            font-weight: 700;
        }
        
        .content-divider {
            width: 80px;
            height: 4px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 4px;
        }
        
        .bg-gradient {
            position: relative;
        }
        
        .bg-gradient:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.4));
            z-index: 1;
        }
        
        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 0, 0, 0.03) 0%, rgba(255, 0, 0, 0.08) 100%);
            border-radius: 0.75rem;
            pointer-events: none;
        }
        
        .btn-danger {
            background-color: #ff2020;
            border-color: #ff2020;
        }
        
        .btn-danger:hover {
            background-color: #e60000;
            border-color: #e60000;
        }
        
        /* Responsive adjustments for hero section */
        @media (max-width: 991px) {
            .hero-title {
                font-size: 2.75rem;
            }
        }
        
        @media (max-width: 767px) {
            .hero-section .hero-title {
                font-size: 2.25rem;
            }
            .hero-section .hero-subtitle {
                font-size: 1.1rem;
            }
            .hero-section .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            .hero-section .container {
                padding-top: 2rem !important;
                padding-bottom: 2rem !important;
            }
        }
    </style>

    <!-- Link Animate.css for animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    
    <!-- Categories Section -->
    <section id="categories" class="py-5 mt-4 categories-section">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge bg-danger text-white fw-bold px-3 py-2 rounded-pill mb-3">Explore</span>
                <h2 class="display-5 fw-bold mb-3">App <span class="text-danger">Categories</span></h2>
                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <p class="lead text-muted">Find the perfect app for your needs by exploring our carefully curated categories</p>
                    </div>
                </div>
                <div class="custom-divider mx-auto mt-3"></div>
            </div>
            
            <div class="row g-4">
                <?php foreach ($categories as $category): ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card category-card border-0 rounded-4 shadow-sm h-100 animate__animated animate__fadeIn" 
                         data-category-id="<?php echo $category['id']; ?>" 
                         onclick="openCategoryModal(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name'], ENT_QUOTES); ?>')">
                        <div class="position-relative overflow-hidden rounded-top-4">
                            <div class="category-image-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                                <i class="fas fa-search-plus text-white fs-1 opacity-0 category-icon"></i>
                            </div>
                            <div class="category-ribbon">
                                <span>Category</span>
                            </div>
                            <img src="<?php echo $category['image_url']; ?>" 
                                 class="card-img-top category-img" 
                                 alt="<?php echo $category['name']; ?>" 
                                 onerror="this.src='../../assets/img/default-category.jpg';">
                        </div>
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold mb-2"><?php echo $category['name']; ?></h5>
                            <p class="card-text text-muted mb-3"><?php echo substr($category['description'], 0, 80); ?><?php echo (strlen($category['description']) > 80) ? '...' : ''; ?></p>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-light text-danger rounded-pill px-3 py-2">
                                    <i class="fas fa-mobile-alt me-1"></i> Explore Apps
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (count($categories) === 0): ?>
                <div class="col-12">
                    <div class="alert alert-info rounded-4 shadow-sm">
                        <i class="fas fa-info-circle me-2"></i> No categories available at the moment. Please check back later.
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Selected Category Apps -->
    <section id="category-apps" class="py-5 category-apps-section d-none">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                <div>
                    <span class="badge bg-danger text-white fw-bold px-3 py-2 rounded-pill mb-2">Category</span>
                    <h2 class="display-5 fw-bold" id="category-title">Category Apps</h2>
                </div>
                <button class="btn btn-outline-danger rounded-pill px-4 py-2 mt-3 mt-md-0" onclick="showAllCategories()">
                    <i class="fas fa-arrow-left me-2"></i> Back to Categories
                </button>
            </div>
            <div class="custom-divider mb-4"></div>
            <p id="category-description" class="lead mb-4"></p>
            <div class="row g-4" id="apps-container">
                <!-- Apps will be loaded here dynamically -->
            </div>
        </div>
    </section>

    <!-- Popular Apps Section -->
    <section id="popular-apps" class="py-5 popular-apps-section">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge bg-danger text-white fw-bold px-3 py-2 rounded-pill mb-3">Trending</span>
                <h2 class="display-5 fw-bold mb-3">Popular <span class="text-danger">Apps</span></h2>
                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <p class="lead text-muted">Discover the most popular apps loved by our users</p>
                    </div>
                </div>
                <div class="custom-divider mx-auto mt-3"></div>
            </div>
            
            <div class="row g-4" id="popular-apps-container">
                <!-- Popular apps will be loaded here -->
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-danger" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5 about-section">
        <div class="container py-4">
            <div class="text-center mb-5">
                <span class="badge bg-danger text-white fw-bold px-3 py-2 rounded-pill mb-3">About Us</span>
                <h2 class="display-5 fw-bold mb-3">About Our <span class="text-danger">App Directory</span></h2>
                <div class="custom-divider mx-auto mt-3"></div>
            </div>
            
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="about-content pe-lg-4">
                        <p class="lead mb-4">Our App Directory provides a curated collection of the best apps across various categories. Whether you're looking for productivity tools, entertainment, social media, or educational apps, we've got you covered.</p>
                        <p class="mb-4">We carefully review each app to ensure it meets our quality standards before adding it to our directory.</p>
                        
                        <div class="d-flex align-items-center mb-4">
                            <div class="about-icon-wrapper me-3">
                                <i class="fas fa-check text-danger"></i>
                            </div>
                            <p class="mb-0"><strong>Quality Assured:</strong> Every app is thoroughly tested</p>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <div class="about-icon-wrapper me-3">
                                <i class="fas fa-shield-alt text-danger"></i>
                            </div>
                            <p class="mb-0"><strong>Safety First:</strong> We prioritize user privacy and security</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card border-0 rounded-4 shadow feature-card">
                        <div class="card-body p-4 p-xl-5">
                            <h4 class="fw-bold mb-4 text-center">Why Use Our <span class="text-danger">Directory</span>?</h4>
                            
                            <div class="feature-item d-flex align-items-start mb-4">
                                <div class="feature-icon bg-danger text-white rounded-circle p-3 me-3 flex-shrink-0">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 fw-bold">Curated Selection</h5>
                                    <p class="text-muted mb-0">We hand-pick only the highest quality apps</p>
                                </div>
                            </div>
                            
                            <div class="feature-item d-flex align-items-start mb-4">
                                <div class="feature-icon bg-danger text-white rounded-circle p-3 me-3 flex-shrink-0">
                                    <i class="fas fa-list"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 fw-bold">Easy Browsing</h5>
                                    <p class="text-muted mb-0">Organized by categories for effortless discovery</p>
                                </div>
                            </div>
                            
                            <div class="feature-item d-flex align-items-start mb-4">
                                <div class="feature-icon bg-danger text-white rounded-circle p-3 me-3 flex-shrink-0">
                                    <i class="fas fa-link"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 fw-bold">Direct Links</h5>
                                    <p class="text-muted mb-0">Quick access to app download forms</p>
                                </div>
                            </div>
                            
                            <div class="feature-item d-flex align-items-start">
                                <div class="feature-icon bg-danger text-white rounded-circle p-3 me-3 flex-shrink-0">
                                    <i class="fas fa-sync-alt"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 fw-bold">Regular Updates</h5>
                                    <p class="text-muted mb-0">Constantly updated with the newest apps</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4">
                    <h4 class="mb-4 fw-bold">App Directory</h4>
                    <p class="mb-3">Your one-stop destination for discovering amazing apps.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white social-icon"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h5 class="mb-4 fw-bold">Quick Links</h5>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2"><a href="#categories" class="text-white-50 text-decoration-none"><i class="fas fa-chevron-right me-2 small"></i>Categories</a></li>
                        <li class="mb-2"><a href="#popular-apps" class="text-white-50 text-decoration-none"><i class="fas fa-chevron-right me-2 small"></i>Popular Apps</a></li>
                        <li><a href="#about" class="text-white-50 text-decoration-none"><i class="fas fa-chevron-right me-2 small"></i>About</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h5 class="mb-4 fw-bold">Contact</h5>
                    <ul class="list-unstyled">
                        <li class="mb-3 d-flex">
                            <i class="fas fa-envelope me-3 mt-1 text-primary"></i>
                            <span>info@appdirectory.com</span>
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="fas fa-phone-alt me-3 mt-1 text-primary"></i>
                            <span>+1 (123) 456-7890</span>
                        </li>
                        <li class="d-flex">
                            <i class="fas fa-map-marker-alt me-3 mt-1 text-primary"></i>
                            <span>123 App Street, Digital City</span>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h5 class="mb-4 fw-bold">Newsletter</h5>
                    <p class="mb-3">Subscribe to get updates on new apps and features.</p>
                    <div class="input-group">
                        <input type="email" class="form-control rounded-pill rounded-end" placeholder="Your email">
                        <button class="btn btn-primary rounded-pill rounded-start" type="button">Subscribe</button>
                    </div>
                </div>
            </div>
            <hr class="mt-5 mb-4">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> App Directory. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button class="btn btn-primary rounded-circle shadow back-to-top" id="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Add this modal at the end of the body -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content rounded-4 overflow-hidden border-0 shadow-lg">
                <div class="modal-header border-0 bg-light">
                    <h5 class="modal-title fw-bold" id="categoryModalLabel">Category Apps</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modalAppsContainer" class="row g-4">
                        <div class="col-12 text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Back to top button
        const backToTopButton = document.getElementById('back-to-top');
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.style.display = 'block';
            } else {
                backToTopButton.style.display = 'none';
            }
        });

        backToTopButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Initialize the Bootstrap modal
        let categoryModal;
        document.addEventListener('DOMContentLoaded', function() {
            categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
            
            // Add CSS for hover effects
            const style = document.createElement('style');
            style.textContent = `
                .category-card {
                    transition: transform 0.3s ease, box-shadow 0.3s ease;
                    cursor: pointer;
                }
                .category-card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
                }
                .category-image-overlay {
                    background: rgba(0,0,0,0.2);
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }
                .category-card:hover .category-image-overlay {
                    opacity: 1;
                }
                .category-card:hover .category-icon {
                    opacity: 1 !important;
                }
                .feature-icon {
                    width: 50px;
                    height: 50px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .social-icon {
                    transition: transform 0.3s ease;
                }
                .social-icon:hover {
                    transform: translateY(-3px);
                }
                .back-to-top {
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    width: 50px;
                    height: 50px;
                    display: none;
                    z-index: 99;
                }
                .app-card {
                    transition: transform 0.3s ease, box-shadow 0.3s ease;
                    border-radius: 1rem;
                    overflow: hidden;
                    border: none;
                }
                .app-card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
                }
                .app-img {
                    width: 60px;
                    height: 60px;
                    object-fit: cover;
                    border-radius: 15px;
                }
            `;
            document.head.appendChild(style);
        });

        // Open category modal and load apps
        function openCategoryModal(categoryId, categoryName) {
            // Set modal title
            document.getElementById('categoryModalLabel').textContent = categoryName + ' Apps';
            
            // Show loading spinner
            document.getElementById('modalAppsContainer').innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            // Show the modal
            categoryModal.show();
            
            // Fetch apps for this category
            fetch(`get_category_apps.php?category_id=${categoryId}`)
                .then(response => {
                    console.log('Fetch response status:', response.status);
                    console.log('Response headers:', [...response.headers.entries()]);
                    return response.json();
                })
                .then(data => {
                    console.log('Category apps data:', data);
                    
                    const appsContainer = document.getElementById('modalAppsContainer');
                    appsContainer.innerHTML = '';
                    
                    if (!data.success || data.apps.length === 0) {
                        appsContainer.innerHTML = '<div class="col-12"><div class="alert alert-info rounded-4 shadow-sm"><i class="fas fa-info-circle me-2"></i> No apps available in this category yet.</div></div>';
                        return;
                    }
                    
                    data.apps.forEach(app => {
                        const appCard = document.createElement('div');
                        appCard.className = 'col-md-6 col-lg-4 mb-4';
                        appCard.innerHTML = `
                            <div class="card app-card shadow-sm h-100 animate__animated animate__fadeIn">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="app-icon-wrapper rounded-4 p-2 me-3 bg-light">
                                            <img src="${app.image_url}" class="app-img" alt="${app.name}" onerror="this.src='../../assets/img/default-app.jpg';">
                                        </div>
                                        <div>
                                            <h5 class="card-title fw-bold mb-1">${app.name}</h5>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <i class="fas fa-star text-warning"></i>
                                                    <i class="fas fa-star text-warning"></i>
                                                    <i class="fas fa-star text-warning"></i>
                                                    <i class="fas fa-star text-warning"></i>
                                                    <i class="fas fa-star-half-alt text-warning"></i>
                                                </div>
                                                <small class="text-muted">(4.5)</small>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="card-text mb-4">${app.description}</p>
                                    <div class="d-flex justify-content-between align-items-center mt-auto">
                                        <span class="badge bg-light text-primary rounded-pill px-3 py-2 small">
                                            <i class="fas fa-mobile-alt me-1"></i> App
                                        </span>
                                        <a href="${app.form_url}" class="btn btn-primary rounded-pill px-4" target="_blank">
                                            <i class="fas fa-external-link-alt me-2"></i> Get App
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `;
                        appsContainer.appendChild(appCard);
                    });
                })
                .catch(error => {
                    console.error('Error loading category apps:', error);
                    document.getElementById('modalAppsContainer').innerHTML = 
                        '<div class="col-12"><div class="alert alert-danger rounded-4 shadow-sm"><i class="fas fa-exclamation-circle me-2"></i> Failed to load apps for this category</div></div>';
                });
        }

        // Load popular apps on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded');
            
            fetch('get_popular_apps.php')
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Data received:', data);
                    const container = document.getElementById('popular-apps-container');
                    container.innerHTML = '';
                    
                    if (!data.success || data.apps.length === 0) {
                        container.innerHTML = '<div class="col-12"><div class="alert alert-info rounded-4 shadow-sm"><i class="fas fa-info-circle me-2"></i> No popular apps available at the moment.</div></div>';
                        return;
                    }
                    
                    data.apps.forEach(app => {
                        const appCard = document.createElement('div');
                        appCard.className = 'col-md-6 col-lg-3 mb-4';
                        appCard.innerHTML = `
                            <div class="card app-card shadow-sm h-100 animate__animated animate__fadeIn">
                                <div class="position-relative">
                                    <div class="position-absolute top-0 end-0 m-3">
                                        <span class="badge bg-danger rounded-pill px-3 py-2">
                                            <i class="fas fa-fire me-1"></i> Popular
                                        </span>
                                    </div>
                                    <img src="${app.image_url}" class="card-img-top" 
                                         style="height: 140px; object-fit: cover;" 
                                         alt="${app.name}" 
                                         onerror="this.src='../../assets/img/default-app.jpg';">
                                </div>
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h5 class="card-title fw-bold mb-0">${app.name}</h5>
                                        <div class="app-icon bg-light rounded-circle p-2">
                                            <img src="${app.image_url}" 
                                                 class="rounded-circle" 
                                                 style="width: 30px; height: 30px; object-fit: cover;"
                                                 alt="${app.name}" 
                                                 onerror="this.src='../../assets/img/default-app.jpg';">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star text-warning"></i>
                                        <i class="fas fa-star-half-alt text-warning"></i>
                                        <small class="text-muted ms-1">(4.5)</small>
                                    </div>
                                    <p class="card-text small mb-4">${app.description.substring(0, 100)}${app.description.length > 100 ? '...' : ''}</p>
                                </div>
                                <div class="card-footer bg-white border-0 p-4 pt-0">
                                    <a href="${app.form_url}" class="btn btn-primary w-100 rounded-pill" target="_blank">
                                        <i class="fas fa-external-link-alt me-2"></i> Get App
                                    </a>
                                </div>
                            </div>
                        `;
                        container.appendChild(appCard);
                    });
                })
                .catch(error => {
                    console.error('Error loading popular apps:', error);
                    document.getElementById('popular-apps-container').innerHTML = 
                        '<div class="col-12"><div class="alert alert-danger rounded-4 shadow-sm"><i class="fas fa-exclamation-circle me-2"></i> Failed to load popular apps</div></div>';
                });
        });

        // Update modal apps to use red styling
        function updateModalAppsStyle() {
            // Create a style element
            const styleElement = document.createElement('style');
            styleElement.textContent = `
                .modal-content .app-card .badge.bg-light.text-primary {
                    background-color: rgba(255, 32, 32, 0.1) !important;
                    color: #ff2020 !important;
                }
                
                .modal-content .app-card .btn-primary {
                    background: #ff2020;
                    border-color: #ff2020;
                }
                
                .modal-content .app-card .btn-primary:hover {
                    background: #e60000;
                    border-color: #e60000;
                }
            `;
            document.head.appendChild(styleElement);
        }
        
        // Ensure this runs when the document is loaded
        document.addEventListener('DOMContentLoaded', function() {
            updateModalAppsStyle();
            
            // Update the popular apps card rendering to use red styling
            const originalFetch = window.fetch;
            window.fetch = function() {
                return originalFetch.apply(this, arguments)
                    .then(response => {
                        if (arguments[0].includes('get_popular_apps.php')) {
                            // Store the original json method
                            const originalJson = response.json;
                            // Override the json method
                            response.json = function() {
                                return originalJson.call(this).then(data => {
                                    if (data.success && data.apps.length > 0) {
                                        setTimeout(() => {
                                            // Update all primary buttons to danger
                                            document.querySelectorAll('#popular-apps-container .btn-primary').forEach(btn => {
                                                btn.classList.remove('btn-primary');
                                                btn.classList.add('btn-danger');
                                            });
                                            
                                            // Update all text-primary to text-danger
                                            document.querySelectorAll('#popular-apps-container .text-primary').forEach(el => {
                                                el.classList.remove('text-primary');
                                                el.classList.add('text-danger');
                                            });
                                        }, 100);
                                    }
                                    return data;
                                });
                            }
                        }
                        return response;
                    });
            };
        });
    </script>

    <style>
        /* Categories and Selected Category Apps Sections Styling */
        .categories-section, .category-apps-section {
            font-family: 'Poppins', sans-serif;
        }
        
        .categories-section h2, .category-apps-section h2 {
            letter-spacing: -0.5px;
        }
        
        .categories-section .text-danger, .category-apps-section .text-danger {
            color: #ff2020 !important;
        }
        
        .custom-divider {
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #ff3e3e 0%, #c50000 100%);
            border-radius: 4px;
            margin-bottom: 2rem;
        }
        
        .category-card {
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .category-card:hover {
            transform: translateY(-7px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
        }
        
        .category-img {
            height: 180px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .category-card:hover .category-img {
            transform: scale(1.1);
        }
        
        .category-image-overlay {
            background: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.6));
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .category-card:hover .category-image-overlay {
            opacity: 1;
        }
        
        .category-ribbon {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 2;
            background: #ff2020;
            color: white;
            padding: 3px 10px;
            font-size: 0.75rem;
            border-radius: 30px;
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .bg-danger {
            background-color: #ff2020 !important;
        }
        
        .text-danger {
            color: #ff2020 !important;
        }
        
        .btn-outline-danger {
            border-color: #ff2020;
            color: #ff2020;
        }
        
        .btn-outline-danger:hover {
            background-color: #ff2020;
            color: white;
        }
        
        .badge.bg-light.text-danger {
            background-color: rgba(255, 32, 32, 0.1) !important;
            color: #ff2020 !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .category-card:hover .badge.bg-light.text-danger {
            background-color: rgba(255, 32, 32, 0.2) !important;
        }
        
        @media (max-width: 767px) {
            .category-img {
                height: 150px;
            }
        }

        /* Popular Apps Section Styling */
        .popular-apps-section {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f9f9;
            position: relative;
            overflow: hidden;
        }
        
        .popular-apps-section::before {
            content: '';
            position: absolute;
            top: -150px;
            right: -150px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 32, 32, 0.03);
            z-index: 0;
        }
        
        .popular-apps-section::after {
            content: '';
            position: absolute;
            bottom: -150px;
            left: -150px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 32, 32, 0.05);
            z-index: 0;
        }
        
        .popular-apps-section .container {
            position: relative;
            z-index: 1;
        }
        
        /* Update the modal styling for apps */
        #categoryModal .modal-header {
            background-color: #ff2020;
            color: white;
        }
        
        #categoryModal .modal-title {
            color: white;
        }
        
        #categoryModal .btn-close {
            filter: brightness(0) invert(1);
        }
        
        #categoryModal .modal-footer {
            background-color: #f9f9f9;
        }
        
        #categoryModal .btn-outline-secondary {
            border-color: #ff2020;
            color: #ff2020;
        }
        
        #categoryModal .btn-outline-secondary:hover {
            background-color: #ff2020;
            color: white;
        }
        
        /* Spinner color update */
        .spinner-border.text-primary {
            color: #ff2020 !important;
        }

        /* About Section Styling */
        .about-section {
            font-family: 'Poppins', sans-serif;
            background-color: #fff;
            position: relative;
            overflow: hidden;
        }
        
        .about-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 32, 32, 0.05) 0%, rgba(255, 255, 255, 0) 70%);
            z-index: 0;
        }
        
        .about-section::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 32, 32, 0.05) 0%, rgba(255, 255, 255, 0) 70%);
            z-index: 0;
        }
        
        .about-content {
            position: relative;
            z-index: 1;
        }
        
        .about-icon-wrapper {
            width: 36px;
            height: 36px;
            background-color: rgba(255, 32, 32, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .feature-card {
            transform: translateY(0);
            transition: all 0.4s ease;
            overflow: hidden;
            border-radius: 1rem;
            background: linear-gradient(145deg, #ffffff 0%, #f9f9f9 100%);
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
        }
        
        .feature-item {
            transition: all 0.3s ease;
            padding: 10px;
            border-radius: 0.75rem;
        }
        
        .feature-item:hover {
            background-color: rgba(255, 32, 32, 0.05);
        }
        
        .feature-icon {
            box-shadow: 0 5px 15px rgba(255, 32, 32, 0.2);
            transition: all 0.3s ease;
        }
        
        .feature-item:hover .feature-icon {
            transform: scale(1.1);
        }
    </style>
</body>
</html> 