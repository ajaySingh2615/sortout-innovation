<?php
require '../includes/db_connect.php';

// Get all categories
$sql = "SELECT * FROM artist_categories ORDER BY name";
$result = $conn->query($sql);
$categories = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artist Job Opportunities</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <header class="navbar">
      <div class="container">
        <!-- Logo -->
        <a href="/" class="navbar-logo">
          <img
            src="/images/sortoutInnovation-icon/sortout-innovation-only-s.gif"
            alt="SortOut Innovation"
          />
        </a>

        <!-- Navigation Links -->
        <nav class="navbar-links">
          <ul>
            <li><a href="#" class="nav-link">Home</a></li>
            <li><a href="/pages/about-page/about.html" class="nav-link">About</a></li>
            <li class="dropdown">
              <a href="#" class="nav-link">Career <i class="fas fa-chevron-down"></i></a>
              <div class="dropdown-menu">
                <a href="/employee-job/index.php" class="dropdown-item">Employee Jobs</a>
                <a href="/artist-job/index.php" class="dropdown-item">Artist Jobs</a>
              </div>
            </li>
            <li><a href="/modal_agency.php" class="nav-link">Find Talent</a></li>
            <li><a href="/pages/our-services-page/service.html" class="nav-link">Service</a></li>
            <li><a href="/pages/contact-page/contact-page.html" class="nav-link">Contact</a></li>
            <li><a href="/blog/index.php" class="nav-link">Blog</a></li>
          </ul>
        </nav>

        <!-- Mobile Menu Button -->
        <button class="navbar-toggle" aria-label="Toggle navigation">
          <i class="fas fa-bars"></i>
        </button>
      </div>
    </header>

    <script>
      // Mobile Menu Toggle
      document.addEventListener('DOMContentLoaded', function() {
          const navbarToggle = document.querySelector(".navbar-toggle");
          const navbarLinks = document.querySelector(".navbar-links");
          const dropdowns = document.querySelectorAll(".dropdown");

          // Toggle mobile menu
          navbarToggle.addEventListener("click", () => {
              navbarLinks.classList.toggle("active");
          });

          // Handle dropdowns on mobile
          dropdowns.forEach(dropdown => {
              const link = dropdown.querySelector("a");
              link.addEventListener("click", (e) => {
                  if (window.innerWidth <= 991) {
                      e.preventDefault();
                      dropdown.classList.toggle("active");
                  }
              });
          });

          // Close mobile menu when clicking outside
          document.addEventListener("click", (e) => {
              if (!navbarLinks.contains(e.target) && !navbarToggle.contains(e.target)) {
                  navbarLinks.classList.remove("active");
                  dropdowns.forEach(dropdown => dropdown.classList.remove("active"));
              }
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

    <!-- Hero Section -->
    <section class="hero-section loading">
        <div class="hero-particles" id="particles-js"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <span class="hero-badge">Creative Opportunities</span>
                    <h1 class="hero-title">Find Your <span class="highlight">Perfect</span> Creative Career</h1>
                    <p class="hero-subtitle">Connect with leading brands and apply for exciting freelance opportunities that showcase your artistic talent</p>
                    <div class="hero-buttons">
                        <a href="#categories" class="btn btn-primary btn-lg">
                            <span>Explore Categories</span>
                        </a>
                        <a href="../auth/login.php" class="btn btn-outline-light btn-lg">
                            <span>Sign Up</span> <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                    <div class="hero-stats">
                        <div class="stat-item">
                            <span class="stat-number">15+</span>
                            <span class="stat-label">Creative Fields</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">100+</span>
                            <span class="stat-label">Applications</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">24/7</span>
                            <span class="stat-label">Support</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 d-lg-block">
                    <div class="hero-image-container">
                        <img src="https://sortoutinnovation.com/images/services-imgs/infulener_s.png" alt="Artist Job Opportunities" class="hero-image">
                        <div class="floating-element elem-1">
                            <i class="fas fa-paint-brush"></i>
                        </div>
                        <div class="floating-element elem-2">
                            <i class="fas fa-camera"></i>
                        </div>
                        <div class="floating-element elem-3">
                            <i class="fas fa-music"></i>
                        </div>
                        <div class="floating-element elem-4">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section id="categories" class="section-padding">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title">Browse Categories</h2>
                    <p class="section-subtitle">Explore our diverse range of creative opportunities and find the perfect match for your artistic talents</p>
                </div>
            </div>

            <div class="row categories-container">
                <?php if (count($categories) > 0): ?>
                    <?php foreach ($categories as $category): ?>
                        <div class="col-md-4 col-lg-3 mb-4">
                            <div class="category-card" data-category-id="<?php echo $category['id']; ?>" data-category-name="<?php echo htmlspecialchars($category['name']); ?>">
                                <div class="card-icon">
                                    <i class="<?php echo $category['icon'] ?: 'fas fa-paint-brush'; ?>"></i>
                                </div>
                                <h5><?php echo htmlspecialchars($category['name']); ?></h5>
                                <p><?php echo (strlen($category['description']) > 80) ? substr(htmlspecialchars($category['description']), 0, 80) . '...' : htmlspecialchars($category['description']); ?></p>
                                
                                <?php
                                // Get count of apps in this category
                                $appCountQuery = "SELECT COUNT(*) as count FROM artist_app_categories WHERE category_id = " . $category['id'];
                                $appCountResult = $conn->query($appCountQuery);
                                $appCount = 0;
                                if ($appCountResult && $appCountResult->num_rows > 0) {
                                    $appCount = $appCountResult->fetch_assoc()['count'];
                                }
                                ?>
                                
                                <div class="category-count">
                                    <?php echo $appCount; ?> Application<?php echo $appCount != 1 ? 's' : ''; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center p-5 shadow-sm border-0 rounded-3">
                            <div class="empty-icon mb-3"><i class="fas fa-folder-open"></i></div>
                            <h4 class="fw-bold">No Categories Available</h4>
                            <p class="text-muted mb-0">Please check back later as we're constantly adding new creative opportunities.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (count($categories) > 0): ?>
            <!-- <div class="row mt-5">
                <div class="col-12 text-center">
                    <p class="text-muted">Don't see what you're looking for?</p>
                    <a href="../contact.php" class="btn btn-outline-danger">
                        <i class="fas fa-envelope me-2"></i> Contact Us
                    </a>
                </div>
            </div> -->
            <?php endif; ?>
        </div>
    </section>

    <!-- Contact Us Section -->
    <section id="contact-us" class="contact-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10 text-center">
                    <div class="contact-card">
                        <div class="contact-card-icon">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                        <h3>Don't see what you're looking for?</h3>
                        <p class="contact-text">We're constantly adding new opportunities for artists. Let us know what you're interested in and we'll help you find the perfect match.</p>
                        <div class="contact-info">
                            <a href="mailto:info@sortoutinnovation.com" class="contact-email">info@sortoutinnovation.com</a>
                        </div>
                        <div class="contact-actions">
                            <a href="mailto:info@sortoutinnovation.com" class="btn btn-primary contact-btn">
                                <i class="fas fa-paper-plane me-2"></i>Contact Us
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- App Selection Modal -->
    <div class="modal fade" id="appModal" tabindex="-1" aria-labelledby="appModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appModalLabel">Select an App</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row" id="appList">
                        <!-- Apps will be loaded here via AJAX -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    
    <footer class="footer-section">
      <div class="container">
        <div class="row">
          <!-- Column 1: Company Info -->
          <div class="col-lg-3 col-md-6">
            <div class="footer-logo">
              <img
                src="/images/sortoutInnovation-icon/Sortout innovation.jpg"
                alt="SortOut Innovation"
              />
              <p class="text-center">
                Empowering businesses with top-notch solutions in digital, IT,
                and business services.
              </p>
            </div>
          </div>

          <!-- Column 2: Quick Links -->
          <div class="col-lg-2 col-md-6">
            <h4>Quick Links</h4>
            <ul class="footer-links">
              <li><a href="index.html">Home</a></li>
              <li><a href="/pages/about-page/about.html">About Us</a></li>
              <li>
                <a href="/pages/contact-page/contact-page.html">Contact</a>
              </li>
              <li>
                <a href="/pages/career.html">Careers</a>
              </li>
              <li>
                <a href="/pages/our-services-page/service.html">Services</a>
              </li>
              <li>
                <a href="/blog/index.php">Blogs</a>
              </li>
              <li>
                <a href="/auth/register.php">Register</a>
              </li>
              <li>
                <a href="/modal_agency.php">talent</a>
              </li>
            </ul>
          </div>

          <!-- Column 3: Our Services -->
          <div class="col-lg-2 col-md-6">
            <h4>Our Services</h4>
            <ul class="footer-links">
              <li>
                <a href="/pages/services/socialMediaInfluencers.html"
                  >Digital Marketing</a
                >
              </li>
              <li><a href="/pages/services/itServices.html">IT Support</a></li>
              <li><a href="/pages/services/caServices.html">CA Services</a></li>
              <li><a href="/pages/services/hrServices.html">HR Services</a></li>
              <li>
                <a href="/pages/services/courierServices.html"
                  >Courier Services</a
                >
              </li>
              <li>
                <a href="/pages/services/shipping.html"
                  >Shipping & Fulfillment</a
                >
              </li>
              <li>
                <a href="/pages/services/stationeryServices.html"
                  >Stationery Services</a
                >
              </li>
              <li>
                <a href="/pages/services/propertyServices.html"
                  >Real Estate & Property</a
                >
              </li>
              <li>
                <a href="/pages/services/event-managementServices.html"
                  >Event Management</a
                >
              </li>
              <li>
                <a href="/pages/services/designAndCreative.html"
                  >Design & Creative</a
                >
              </li>
              <li>
                <a href="/pages/services/designAndCreative.html"
                  >Web & App Development</a
                >
              </li>
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
                <a href="mailto:info@sortoutinnovation.com"
                  >info@sortoutinnovation.com</a
                >
              </li>
              <li>
                <i class="fas fa-map-marker-alt"></i> Spaze i-Tech Park,
                Gurugram, India
              </li>
            </ul>
          </div>

          <!-- Column 5: Social Media -->
          <div class="col-lg-2 col-md-6">
            <h4>Follow Us</h4>
            <div class="social-icons">
              <a href="https://www.facebook.com/profile.php?id=61556452066209"
                ><i class="fab fa-facebook"></i
              ></a>
              <a href="https://youtu.be/tw-xk-Pb-zA?si=QMTwuvhEuTegpqDr"
                ><i class="fab fa-youtube"></i
              ></a>
              <a href="https://www.linkedin.com/company/sortout-innovation/"
                ><i class="fab fa-linkedin"></i
              ></a>
              <a href="https://www.instagram.com/sortoutinnovation"
                ><i class="fab fa-instagram"></i
              ></a>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Initialize particles.js
        document.addEventListener('DOMContentLoaded', function() {
            // Remove loading class from hero section
            setTimeout(function() {
                document.querySelector('.hero-section').classList.remove('loading');
            }, 300);
            
            particlesJS('particles-js', {
                "particles": {
                    "number": {
                        "value": 80,
                        "density": {
                            "enable": true,
                            "value_area": 800
                        }
                    },
                    "color": {
                        "value": "#ffffff"
                    },
                    "shape": {
                        "type": "circle",
                        "stroke": {
                            "width": 0,
                            "color": "#000000"
                        }
                    },
                    "opacity": {
                        "value": 0.3,
                        "random": true,
                        "anim": {
                            "enable": false,
                            "speed": 1,
                            "opacity_min": 0.1,
                            "sync": false
                        }
                    },
                    "size": {
                        "value": 3,
                        "random": true,
                        "anim": {
                            "enable": false,
                            "speed": 40,
                            "size_min": 0.1,
                            "sync": false
                        }
                    },
                    "line_linked": {
                        "enable": true,
                        "distance": 150,
                        "color": "#ffffff",
                        "opacity": 0.2,
                        "width": 1
                    },
                    "move": {
                        "enable": true,
                        "speed": 2,
                        "direction": "none",
                        "random": false,
                        "straight": false,
                        "out_mode": "out",
                        "bounce": false,
                        "attract": {
                            "enable": false,
                            "rotateX": 600,
                            "rotateY": 1200
                        }
                    }
                },
                "interactivity": {
                    "detect_on": "canvas",
                    "events": {
                        "onhover": {
                            "enable": true,
                            "mode": "grab"
                        },
                        "onclick": {
                            "enable": true,
                            "mode": "push"
                        },
                        "resize": true
                    },
                    "modes": {
                        "grab": {
                            "distance": 140,
                            "line_linked": {
                                "opacity": 0.5
                            }
                        },
                        "push": {
                            "particles_nb": 4
                        }
                    }
                },
                "retina_detect": true
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?> 