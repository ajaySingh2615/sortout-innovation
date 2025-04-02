<?php
include_once __DIR__ . '/../includes/db_connect.php'; 
include_once __DIR__ . '/../includes/functions.php';

// Get all active categories
$categories = getCategories(true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>App Directory</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .category-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            height: 100%;
        }
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .category-img {
            height: 150px;
            object-fit: cover;
        }
        .app-card {
            transition: transform 0.3s ease;
            cursor: pointer;
            height: 100%;
        }
        .app-card:hover {
            transform: translateY(-5px);
        }
        .app-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 15px;
        }
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: none;
            z-index: 1000;
        }
        .section-title {
            border-left: 5px solid #0d6efd;
            padding-left: 15px;
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

    <!-- Hero Section -->
    <div class="bg-light py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold">Discover Amazing Apps</h1>
                    <p class="lead my-4">Find the perfect apps for your needs. Browse through our curated collection of apps across various categories.</p>
                    <a href="#categories" class="btn btn-primary btn-lg">Explore Categories</a>
                </div>
                <div class="col-lg-6">
                    <!-- <img src="../../assets/img/apps/hero-image.jpg" alt="App Directory" class="img-fluid rounded shadow" onerror="this.src='../../assets/img/default-app.jpg';"> -->
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Section -->
    <section id="categories" class="py-5">
        <div class="container">
            <h2 class="mb-4 section-title">Categories</h2>
            <div class="row g-4">
                <?php foreach ($categories as $category): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="card category-card shadow-sm" data-category-id="<?php echo $category['id']; ?>" onclick="openCategoryModal(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name'], ENT_QUOTES); ?>')">
                        <img src="<?php echo $category['image_url']; ?>" class="card-img-top category-img" alt="<?php echo $category['name']; ?>" onerror="this.src='../../assets/img/default-category.jpg';">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $category['name']; ?></h5>
                            <p class="card-text small text-muted"><?php echo substr($category['description'], 0, 80); ?><?php echo (strlen($category['description']) > 80) ? '...' : ''; ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (count($categories) === 0): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        No categories available at the moment. Please check back later.
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Selected Category Apps -->
    <section id="category-apps" class="py-5 bg-light d-none">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title" id="category-title">Category Apps</h2>
                <button class="btn btn-outline-secondary" onclick="showAllCategories()">
                    <i class="fas fa-arrow-left me-2"></i> Back to Categories
                </button>
            </div>
            <p id="category-description" class="lead mb-4"></p>
            <div class="row g-4" id="apps-container">
                <!-- Apps will be loaded here dynamically -->
            </div>
        </div>
    </section>

    <!-- Popular Apps Section -->
    <section id="popular-apps" class="py-5">
        <div class="container">
            <h2 class="mb-4 section-title">Popular Apps</h2>
            <div class="row g-4" id="popular-apps-container">
                <!-- Popular apps will be loaded here -->
                <div class="col-12 text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5 bg-light">
        <div class="container">
            <h2 class="mb-4 section-title">About Us</h2>
            <div class="row">
                <div class="col-lg-6">
                    <p>Our App Directory provides a curated collection of the best apps across various categories. Whether you're looking for productivity tools, entertainment, social media, or educational apps, we've got you covered.</p>
                    <p>We carefully review each app to ensure it meets our quality standards before adding it to our directory.</p>
                </div>
                <div class="col-lg-6">
                    <h4>Why Use Our Directory?</h4>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item bg-light"><i class="fas fa-check-circle text-success me-2"></i> Curated selection of high-quality apps</li>
                        <li class="list-group-item bg-light"><i class="fas fa-check-circle text-success me-2"></i> Organized by categories for easy browsing</li>
                        <li class="list-group-item bg-light"><i class="fas fa-check-circle text-success me-2"></i> Direct links to app download forms</li>
                        <li class="list-group-item bg-light"><i class="fas fa-check-circle text-success me-2"></i> Regularly updated with new apps</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>App Directory</h5>
                    <p>Your one-stop destination for discovering amazing apps.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#categories" class="text-white-50">Categories</a></li>
                        <li><a href="#popular-apps" class="text-white-50">Popular Apps</a></li>
                        <li><a href="#about" class="text-white-50">About</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope me-2"></i> info@appdirectory.com</li>
                        <li><i class="fas fa-phone me-2"></i> +1 (123) 456-7890</li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> App Directory. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button class="btn btn-primary rounded-circle back-to-top" id="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Add this modal at the end of the body -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">Category Apps</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modalAppsContainer" class="row g-4">
                        <div class="col-12 text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        });

        // Open category modal and load apps
        function openCategoryModal(categoryId, categoryName) {
            // Set modal title
            document.getElementById('categoryModalLabel').textContent = categoryName + ' Apps';
            
            // Show loading spinner
            document.getElementById('modalAppsContainer').innerHTML = `
                <div class="col-12 text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            // Show the modal
            categoryModal.show();
            
            // Fetch apps for this category
            fetch(`get_category_apps.php?category_id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Category data:', data);
                    
                    const appsContainer = document.getElementById('modalAppsContainer');
                    appsContainer.innerHTML = '';
                    
                    if (!data.success || data.apps.length === 0) {
                        appsContainer.innerHTML = '<div class="col-12"><div class="alert alert-info">No apps available in this category yet.</div></div>';
                        return;
                    }
                    
                    data.apps.forEach(app => {
                        const appCard = document.createElement('div');
                        appCard.className = 'col-md-6 col-lg-4';
                        appCard.innerHTML = `
                            <div class="card app-card shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="${app.image_url}" class="app-img me-3" alt="${app.name}" onerror="this.src='../../assets/img/default-app.jpg';">
                                        <h5 class="card-title mb-0">${app.name}</h5>
                                    </div>
                                    <p class="card-text">${app.description}</p>
                                </div>
                                <div class="card-footer bg-white">
                                    <a href="${app.form_url}" class="btn btn-primary w-100" target="_blank">
                                        <i class="fas fa-external-link-alt me-2"></i> Get App
                                    </a>
                                </div>
                            </div>
                        `;
                        appsContainer.appendChild(appCard);
                    });
                })
                .catch(error => {
                    console.error('Error loading category apps:', error);
                    document.getElementById('modalAppsContainer').innerHTML = 
                        '<div class="col-12"><div class="alert alert-danger">Failed to load apps for this category</div></div>';
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
                        container.innerHTML = '<div class="col-12"><div class="alert alert-info">No popular apps available at the moment.</div></div>';
                        return;
                    }
                    
                    data.apps.forEach(app => {
                        const appCard = document.createElement('div');
                        appCard.className = 'col-md-6 col-lg-3';
                        appCard.innerHTML = `
                            <div class="card app-card shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="${app.image_url}" class="app-img me-3" alt="${app.name}" onerror="this.src='../../assets/img/default-app.jpg';">
                                        <h5 class="card-title mb-0">${app.name}</h5>
                                    </div>
                                    <p class="card-text small">${app.description.substring(0, 120)}${app.description.length > 120 ? '...' : ''}</p>
                                </div>
                                <div class="card-footer bg-white">
                                    <a href="${app.form_url}" class="btn btn-outline-primary w-100" target="_blank">
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
                        '<div class="col-12"><div class="alert alert-danger">Failed to load popular apps</div></div>';
                });
        });
    </script>
</body>
</html> 