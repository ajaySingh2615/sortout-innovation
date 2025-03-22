<?php
require_once '../includes/db_connect.php';

// Debug output
error_log("Job detail page accessed");

// Check if job ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    error_log("No job ID provided");
    header("Location: index.php");
    exit();
}

$jobId = intval($_GET['id']);
error_log("Requested job ID: " . $jobId);

// Fetch job details
$stmt = $conn->prepare("SELECT * FROM jobs WHERE id = ? AND is_verified = 1");
$stmt->bind_param("i", $jobId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    error_log("Job not found for ID: " . $jobId);
    header("Location: index.php");
    exit();
}

$job = $result->fetch_assoc();
error_log("Job found: " . $job['title']);
$stmt->close();

// Format salary
$formattedMinSalary = number_format($job['min_salary']);
$formattedMaxSalary = number_format($job['max_salary']);

// Calculate days ago
$createdDate = new DateTime($job['created_at']);
$now = new DateTime();
$interval = $now->diff($createdDate);
$daysAgo = $interval->days;
$timeAgo = $daysAgo > 0 ? $daysAgo . " day" . ($daysAgo > 1 ? "s" : "") . " ago" : "Today";

// Get company initial for logo
$companyInitial = strtoupper(substr($job['company_name'], 0, 1));

// Fetch similar jobs (same category or job type)
$similarJobsStmt = $conn->prepare("
    SELECT * FROM jobs 
    WHERE is_verified = 1 
    AND id != ? 
    AND (category = ? OR job_type = ?) 
    ORDER BY created_at DESC 
    LIMIT 3
");
$similarJobsStmt->bind_param("iss", $jobId, $job['category'], $job['job_type']);
$similarJobsStmt->execute();
$similarJobsResult = $similarJobsStmt->get_result();
$similarJobs = [];

while ($similarJob = $similarJobsResult->fetch_assoc()) {
    $similarJobs[] = $similarJob;
}
$similarJobsStmt->close();

// Debug output
error_log("Similar jobs found: " . count($similarJobs));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($job['title']) ?> | <?= htmlspecialchars($job['company_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #FF5E5E;
            --primary-light: #ff8686;
            --primary-lighter: rgba(255, 94, 94, 0.1);
            --primary-lightest: rgba(255, 94, 94, 0.05);
            --primary-shadow: rgba(255, 94, 94, 0.25);
            --text-dark: #333333;
            --text-medium: #666666;
            --text-light: #777777;
            --white: #ffffff;
            --bg-light: #f8f9fa;
            --space-xs: 5px;
            --space-sm: 10px;
            --space-md: 15px;
            --space-lg: 20px;
            --space-xl: 30px;
        }
        
        body {
            background-color: var(--bg-light);
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            line-height: 1.6;
        }
        
        /* General Navbar Styling */
.navbar {
  height: 70px; /* Fixed navbar height */
  position: sticky;
  display: flex;
  align-items: center;
  top: 0;
  width: 100%;
  background: #ffffff;
  z-index: 1000;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  padding: 15px 0;
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.navbar .container {
  max-width: 1200px;
  padding: 0 20px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

/* Logo */
.navbar-logo img {
  width: 200px; /* Increase this for bigger logo */
  height: 80px; /* Maintain aspect ratio */
  max-height: 50px; /* Restrict navbar height */
  object-fit: contain; /* Ensures proper scaling */
}

/* Navbar Links */
.navbar-links ul {
  display: flex;
  gap: 20px;
  list-style: none;
  margin: 0;
  padding: 0;
}

.navbar-links ul li a {
  text-decoration: none;
  font-size: 1rem;
  font-weight: 600;
  color: #333333;
  padding: 10px 15px;
  transition: color 0.3s ease, transform 0.3s ease;
}

.navbar-links ul li a:hover {
  color: #d10000;
  transform: scale(1.05);
}

.navbar-links ul li a.active {
  color: #d10000;
  border-bottom: 2px solid #d10000;
}

/* Call-to-Action (CTA) Button */
.nav-cta {
  background: #d10000;
  color: #ffffff !important;
  padding: 10px 20px;
  border-radius: 50px;
  transition: background 0.3s ease, transform 0.3s ease;
}

.nav-cta:hover {
  background: #a00000;
  transform: translateY(-3px);
}

/* Mobile Menu Button */
.navbar-toggle {
  display: none;
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #333333;
  cursor: pointer;
}

@media (max-width: 991px) {
  .navbar-toggle {
    display: block;
  }

  .navbar-links {
    position: fixed;
    top: 60px;
    right: 0;
    background: #ffffff;
    width: 100%;
    max-width: 300px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    transform: translateX(100%);
    transition: transform 0.3s ease;
    display: block;
  }

  .navbar-links ul {
    flex-direction: column;
    gap: 10px;
    padding: 20px;
  }

  .navbar-links ul li {
    text-align: left;
  }

  .navbar-links.active {
    transform: translateX(0);
  }
}

/* Sticky Background on Scroll */
.navbar.scrolled {
  background: #f8f9fa;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}

/* Responsive Alignment */
@media (max-width: 767px) {
  .navbar .container {
    padding: 0 15px;
  }

  .navbar-links ul li a {
    font-size: 0.9rem;
    padding: 8px 10px;
  }
}

        
        .page-header {
            background: linear-gradient(135deg, #FF5E5E 0%, #ff8686 100%);
            color: white;
            padding: 60px 0;
            position: relative;
            overflow: hidden;
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='rgba(255,255,255,0.1)' fill-rule='evenodd'/%3E%3C/svg%3E") repeat;
            opacity: 0.6;
        }
        
        .page-header h1 {
            font-weight: 800;
            font-size: 2.2rem;
            margin-bottom: 10px;
            position: relative;
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .job-header {
            background-color: var(--white);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin-top: -50px;
            position: relative;
            z-index: 10;
            border-top: 5px solid var(--primary-color);
            transition: all 0.3s ease;
        }
        
        .job-header:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
            transform: translateY(-5px);
        }
        
        .company-logo-lg {
            width: 90px;
            height: 90px;
            background-color: var(--primary-lightest);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            color: var(--primary-color);
            margin-right: 25px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 94, 94, 0.15);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
        }
        
        .company-logo-lg::after {
            content: '';
            position: absolute;
            width: 150%;
            height: 150%;
            background: linear-gradient(135deg, rgba(255, 94, 94, 0.15), rgba(255, 94, 94, 0), rgba(255, 94, 94, 0));
            top: -25%;
            left: -25%;
            transform: rotate(45deg);
        }
        
        .job-title-lg {
            font-weight: 700;
            font-size: 1.8rem;
            color: var(--text-dark);
            margin-bottom: 8px;
            line-height: 1.3;
        }
        
        .company-name-lg {
            color: var(--text-medium);
            font-size: 1.1rem;
            margin-bottom: 16px !important;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        
        .company-name-lg i {
            color: #4CAF50;
            margin-left: 8px;
            font-size: 0.9rem;
        }
        
        .job-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-right: 10px;
            margin-bottom: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .badge-location {
            background-color: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }
        
        .badge-salary {
            background-color: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
        }
        
        .badge-job-type {
            background-color: rgba(155, 89, 182, 0.1);
            color: #9b59b6;
        }
        
        .badge-experience {
            background-color: rgba(230, 126, 34, 0.1);
            color: #e67e22;
        }
        
        .badge-education {
            background-color: rgba(52, 73, 94, 0.1);
            color: #34495e;
        }
        
        .badge-high-demand {
            background-color: var(--primary-lightest);
            color: var(--primary-color);
        }
        
        .card {
            border: none !important;
            border-radius: 16px !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06) !important;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .card:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1) !important;
        }
        
        .section-title {
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 20px;
            font-size: 1.3rem;
        }
        
        .border-bottom {
            border-color: rgba(0, 0, 0, 0.08) !important;
        }
        
        .job-description {
            color: var(--text-medium);
            font-size: 1rem;
            line-height: 1.8;
        }
        
        .info-label {
            color: var(--text-light);
            font-size: 0.85rem;
            margin-bottom: 2px;
        }
        
        .info-value {
            color: var(--text-dark);
            font-size: 1rem;
            font-weight: 500;
        }
        
        .bg-light {
            background-color: var(--bg-light) !important;
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        .bg-primary {
            background-color: var(--primary-color) !important;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, var(--primary-color), var(--primary-light));
            border: none;
            box-shadow: 0 10px 20px var(--primary-shadow);
            padding: 12px 28px;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.7s ease;
            z-index: -1;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 25px var(--primary-shadow);
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .similar-job-card {
            background-color: var(--white);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            height: 100%;
            overflow: hidden;
            position: relative;
            border: 1px solid rgba(0, 0, 0, 0.03);
        }
        
        .similar-job-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            border-color: rgba(255, 94, 94, 0.15);
        }
        
        .similar-job-card.high-demand {
            border-top: none;
        }
        
        .similar-job-card.high-demand::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(to right, var(--primary-color), var(--primary-light));
            border-radius: 3px 3px 0 0;
        }
        
        .company-logo-sm {
            width: 50px;
            height: 50px;
            background-color: var(--primary-lightest);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: var(--primary-color);
            margin-right: 15px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 94, 94, 0.1);
        }
        
        .company-logo-sm::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 94, 94, 0.1), rgba(255, 94, 94, 0), rgba(255, 94, 94, 0));
            top: 0;
            left: 0;
        }
        
        .job-title-sm {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--text-dark);
            margin-bottom: 5px;
            line-height: 1.4;
            transition: color 0.3s ease;
        }
        
        .similar-job-card:hover .job-title-sm {
            color: var(--primary-color);
        }
        
        .company-name-sm {
            color: var(--text-medium);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }
        
        .company-name-sm i {
            color: #4CAF50;
            margin-left: 8px;
            font-size: 0.75rem;
        }
        
        .similar-job-detail {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }
        
        .similar-job-detail i {
            width: 20px;
            text-align: center;
            margin-right: 8px;
            color: var(--primary-color);
            opacity: 0.85;
        }
        
        .transition-hover {
            transition: all 0.3s ease;
        }
        
        .transition-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
        }
        
        .rounded-circle {
            border-radius: 50% !important;
        }
        
        .bg-gradient-primary {
            background: linear-gradient(45deg, var(--primary-color), var(--primary-light));
        }
        
        @media (max-width: 991px) {
            .job-header {
                margin-top: 0;
                margin-bottom: 20px;
            }
        }
        
        @media (max-width: 768px) {
            .page-header {
                padding: 40px 0;
            }
            
            .page-header h1 {
                font-size: 1.8rem;
            }
            
            .company-logo-lg {
                width: 70px;
                height: 70px;
                font-size: 1.8rem;
            }
            
            .job-title-lg {
                font-size: 1.5rem;
            }
            
            .similar-job-card {
                margin-bottom: 20px;
            }
            
            .job-badge {
                padding: 5px 10px;
                font-size: 0.8rem;
                margin-right: 8px;
                margin-bottom: 8px;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animated {
            animation-duration: 0.6s;
            animation-fill-mode: both;
        }

        .fadeInUp {
            animation-name: fadeInUp;
        }

        .delay-1 {
            animation-delay: 0.1s;
        }

        .delay-2 {
            animation-delay: 0.2s;
        }

        .delay-3 {
            animation-delay: 0.3s;
        }

        .delay-4 {
            animation-delay: 0.4s;
        }

        /* Footer Section */
.footer-section {
  background: #0a0a0a;
  color: #fff;
  padding: 60px 20px;
  font-family: Arial, sans-serif;
}

.footer-section h4 {
  color: #ff4b4b;
  margin-bottom: 15px;
  font-size: 1.4rem;
}

/* Footer Links */
.footer-links {
  list-style: none;
  padding: 0;
  font-size: 1rem;
}

.footer-links li {
  margin-bottom: 10px;
}

.footer-links li a {
  text-decoration: none;
  color: #ddd;
  transition: color 0.3s ease;
}

.footer-links li a:hover {
  color: #ff4b4b;
}

/* Social Icons */
.social-icons {
  display: flex;
  gap: 10px;
}

.social-icons a {
  font-size: 1.5rem;
  color: #fff;
  transition: transform 0.3s ease, color 0.3s ease;
}

.social-icons a:hover {
  color: #ff4b4b;
  transform: scale(1.1);
}

/* Footer Bottom */
.footer-bottom {
  text-align: center;
  margin-top: 40px;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.footer-bottom p {
  font-size: 0.9rem;
  color: #aaa;
}

.footer-bottom ul {
  list-style: none;
  padding: 0;
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-top: 10px;
}

.footer-bottom ul li a {
  text-decoration: none;
  color: #ddd;
  font-size: 0.9rem;
  transition: color 0.3s ease;
}

.footer-bottom ul li a:hover {
  color: #ff4b4b;
}

.footer-logo img {
  padding-bottom: 15px;
  width: 180px; /* Adjust width as needed */
  height: auto; /* Maintains aspect ratio */
  max-width: 100%; /* Ensures responsiveness */
  display: block;
  margin: 0 auto; /* Centers the logo */
}

@media (max-width: 768px) {
  .footer-logo img {
    width: 140px; /* Smaller size for mobile */
  }
}

/* Responsive Design */
@media (max-width: 991px) {
  .footer-section .row {
    flex-direction: column;
    text-align: center;
  }

  .social-icons {
    justify-content: center;
  }

  .footer-bottom ul {
    flex-direction: column;
    gap: 10px;
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
          <img
            src="/images/sortoutInnovation-icon/sortout-innovation-only-s.gif"
            alt="SortOut Innovation"
          />
        </a>

        <!-- Navigation Links -->
        <nav class="navbar-links">
          <ul>
            <li><a href="#" class="nav-link">Home</a></li>
            <li>
              <a href="/pages/about-page/about.html" class="nav-link">About</a>
            </li>
            <!-- <li>
              <a href="/pages/portfolio/portfolio.html" class="nav-link"
                >Portfolio</a
              >
            </li> -->
            <li><a href="/employee-job/index.php" class="nav-link">Jobs</a></li>
            <li>
              <a href="/modal_agency.php" class="nav-link"
                >Find Talent</a
              >
            </li>
            <li>
              <a href="/pages/our-services-page/service.html" class="nav-link"
                >Service</a
              >
            </li>
            <li>
              <a href="/pages/contact-page/contact-page.html" class="nav-link"
                >Contact</a
              >
            </li>
            <li>
              <a href="/blog/index.php" class="nav-link"
                >Blog</a
              >
            </li>
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
    </script>


<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="fw-bold mb-2">Job Details</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../index.php" class="text-white text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item"><a href="index.php" class="text-white text-decoration-none">Jobs</a></li>
                        <li class="breadcrumb-item active text-white-50" aria-current="page"><?= htmlspecialchars($job['title']) ?></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<div class="container mb-5">
    <!-- Job Header Card -->
    <div class="job-header mb-4 animated fadeInUp">
        <div class="row">
            <div class="col-lg-8">
                <div class="d-flex align-items-start">
                    <div class="company-logo-lg">
                        <?= $companyInitial ?>
                    </div>
                    <div>
                        <h1 class="job-title-lg"><?= htmlspecialchars($job['title']) ?></h1>
                        <p class="company-name-lg">
                            <?= htmlspecialchars($job['company_name']) ?>
                            <i class="fas fa-check-circle"></i>
                        </p>
                        
                        <div>
                            <span class="job-badge badge-location">
                                <i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($job['location']) ?>
                            </span>
                            
                            <span class="job-badge badge-salary">
                                <i class="fas fa-rupee-sign me-1"></i> <?= $formattedMinSalary ?> - <?= $formattedMaxSalary ?>
                            </span>
                            
                            <span class="job-badge badge-job-type">
                                <i class="fas fa-briefcase me-1"></i> <?= htmlspecialchars($job['job_type']) ?>
                            </span>
                            
                            <span class="job-badge badge-experience">
                                <i class="fas fa-user-clock me-1"></i> <?= htmlspecialchars($job['experience']) ?>
                            </span>
                            
                            <?php if (!empty($job['education_level'])): ?>
                            <span class="job-badge badge-education">
                                <i class="fas fa-graduation-cap me-1"></i> <?= htmlspecialchars($job['education_level']) ?>
                            </span>
                            <?php endif; ?>
                            
                            <?php if ($job['high_demand']): ?>
                            <span class="job-badge badge-high-demand">
                                <i class="fas fa-fire me-1"></i> High Demand
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 d-flex align-items-center justify-content-lg-end mt-4 mt-lg-0">
                <a href="https://docs.google.com/forms/d/e/1FAIpQLSfmHKGO60CrF7M2HuPpb8KhlhazfcsKPo1MF-fqQyFM1aM22A/viewform" target="_blank" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="fas fa-paper-plane"></i> Apply for Job
                </a>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Left Column - Job Details -->
        <div class="col-lg-8">
            <!-- Job Description Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden animated fadeInUp delay-1">
                <div class="card-body p-4">
                    <h2 class="section-title border-bottom pb-3 mb-4">Job Description</h2>
                    <div class="job-description mb-4">
                        <?= nl2br(htmlspecialchars($job['description'])) ?>
                    </div>
                    
                    <?php if (!empty($job['incentives'])): ?>
                    <h3 class="section-title border-bottom pb-3 mb-4 mt-5">Benefits & Incentives</h3>
                    <div class="mb-4">
                        <p class="text-secondary"><?= htmlspecialchars($job['incentives']) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Key Details -->
                    <h3 class="section-title border-bottom pb-3 mb-4 mt-5">Key Details</h3>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 rounded-3 bg-light mb-3 transition-hover">
                                <i class="fas fa-briefcase text-primary me-3 fs-4"></i>
                                <div>
                                    <div class="info-label">Job Type</div>
                                    <div class="info-value"><?= htmlspecialchars($job['job_type']) ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 rounded-3 bg-light mb-3 transition-hover">
                                <i class="fas fa-tag text-primary me-3 fs-4"></i>
                                <div>
                                    <div class="info-label">Category</div>
                                    <div class="info-value"><?= !empty($job['category']) ? htmlspecialchars($job['category']) : 'Not specified' ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 rounded-3 bg-light mb-3 transition-hover">
                                <i class="fas fa-users text-primary me-3 fs-4"></i>
                                <div>
                                    <div class="info-label">Vacancies</div>
                                    <div class="info-value"><?= $job['vacancies'] ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 rounded-3 bg-light mb-3 transition-hover">
                                <i class="fas fa-calendar-alt text-primary me-3 fs-4"></i>
                                <div>
                                    <div class="info-label">Working Days</div>
                                    <div class="info-value"><?= $job['working_days'] ?> days per week</div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($job['working_hours'])): ?>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 rounded-3 bg-light mb-3 transition-hover">
                                <i class="fas fa-clock text-primary me-3 fs-4"></i>
                                <div>
                                    <div class="info-label">Working Hours</div>
                                    <div class="info-value"><?= htmlspecialchars($job['working_hours']) ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 rounded-3 bg-light mb-3 transition-hover">
                                <i class="fas fa-file-contract text-primary me-3 fs-4"></i>
                                <div>
                                    <div class="info-label">Job Status</div>
                                    <div class="info-value"><?= $job['is_contract'] ? 'Contract' : 'Permanent' ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Call to Action Button -->
                    <div class="text-center mt-5">
                        <a href="https://docs.google.com/forms/d/e/1FAIpQLSfmHKGO60CrF7M2HuPpb8KhlhazfcsKPo1MF-fqQyFM1aM22A/viewform" target="_blank" class="btn btn-primary px-5 py-3 fw-bold">
                            <i class="fas fa-paper-plane me-2"></i> Apply for this Job
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Similar Jobs Section -->
            <?php if (count($similarJobs) > 0): ?>
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden animated fadeInUp delay-2">
                <div class="card-body p-4">
                    <h2 class="section-title border-bottom pb-3 mb-4">Similar Jobs</h2>
                    <div class="row g-3">
                        <?php foreach ($similarJobs as $similarJob): 
                            $similarJobInitial = strtoupper(substr($similarJob['company_name'], 0, 1));
                            $createdDate = new DateTime($similarJob['created_at']);
                            $now = new DateTime();
                            $interval = $now->diff($createdDate);
                            $daysAgo = $interval->days;
                            $timeAgo = $daysAgo > 0 ? $daysAgo . " day" . ($daysAgo > 1 ? "s" : "") . " ago" : "Today";
                        ?>
                        <div class="col-md-6 mb-3">
                            <a href="job-detail.php?id=<?= $similarJob['id'] ?>" class="text-decoration-none">
                                <div class="similar-job-card h-100 transition-hover <?= $similarJob['high_demand'] ? 'high-demand' : '' ?>">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="company-logo-sm">
                                                <?= $similarJobInitial ?>
                                            </div>
                                            <div>
                                                <h5 class="job-title-sm mb-1"><?= htmlspecialchars($similarJob['title']) ?></h5>
                                                <p class="company-name-sm mb-2">
                                                    <?= htmlspecialchars($similarJob['company_name']) ?>
                                                    <i class="fas fa-check-circle"></i>
                                                </p>
                                                
                                                <div class="similar-job-detail">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <?= htmlspecialchars($similarJob['location']) ?>
                                                </div>
                                                
                                                <div class="similar-job-detail">
                                                    <i class="fas fa-rupee-sign"></i>
                                                    <?= number_format($similarJob['min_salary']) ?> - <?= number_format($similarJob['max_salary']) ?>
                                                </div>
                                                
                                                <div class="similar-job-detail mb-0">
                                                    <i class="far fa-clock"></i>
                                                    <?= $timeAgo ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Right Column - Sidebar -->
        <div class="col-lg-4">
            <!-- Company & Job Info -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden animated fadeInUp delay-2">
                <div class="card-body p-4">
                    <h3 class="section-title border-bottom pb-3 mb-4">Job Information</h3>
                    
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-light rounded-circle p-3 me-3">
                            <i class="fas fa-building text-primary fs-4"></i>
                        </div>
                        <div>
                            <div class="info-label">Company Name</div>
                            <div class="info-value fw-medium"><?= htmlspecialchars($job['company_name']) ?></div>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-light rounded-circle p-3 me-3">
                            <i class="fas fa-map-marker-alt text-primary fs-4"></i>
                        </div>
                        <div>
                            <div class="info-label">Location</div>
                            <div class="info-value fw-medium"><?= htmlspecialchars($job['location']) ?></div>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-light rounded-circle p-3 me-3">
                            <i class="fas fa-rupee-sign text-primary fs-4"></i>
                        </div>
                        <div>
                            <div class="info-label">Salary Range</div>
                            <div class="info-value fw-medium text-primary">₹<?= $formattedMinSalary ?> - ₹<?= $formattedMaxSalary ?></div>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-light rounded-circle p-3 me-3">
                            <i class="fas fa-user-clock text-primary fs-4"></i>
                        </div>
                        <div>
                            <div class="info-label">Experience Required</div>
                            <div class="info-value fw-medium"><?= htmlspecialchars($job['experience']) ?></div>
                        </div>
                    </div>
                    
                    <?php if (!empty($job['education_level'])): ?>
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-light rounded-circle p-3 me-3">
                            <i class="fas fa-graduation-cap text-primary fs-4"></i>
                        </div>
                        <div>
                            <div class="info-label">Education Level</div>
                            <div class="info-value fw-medium"><?= htmlspecialchars($job['education_level']) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-flex align-items-center">
                        <div class="bg-light rounded-circle p-3 me-3">
                            <i class="far fa-calendar-alt text-primary fs-4"></i>
                        </div>
                        <div>
                            <div class="info-label">Posted On</div>
                            <div class="info-value fw-medium"><?= date('F j, Y', strtotime($job['created_at'])) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- How to Apply -->
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden animated fadeInUp delay-3">
                <div class="card-body p-4">
                    <h3 class="section-title border-bottom pb-3 mb-4">How to Apply</h3>
                    
                    <div class="bg-light p-4 rounded-3 mb-4">
                        <div class="step-item d-flex mb-3">
                            <div class="step-number bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">1</div>
                            <div class="step-text">Click on the "Apply Now" button</div>
                        </div>
                        <div class="step-item d-flex mb-3">
                            <div class="step-number bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">2</div>
                            <div class="step-text">Fill in your personal and professional details</div>
                        </div>
                        <div class="step-item d-flex mb-3">
                            <div class="step-number bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">3</div>
                            <div class="step-text">Upload your resume (PDF format preferred)</div>
                        </div>
                        <div class="step-item d-flex">
                            <div class="step-number bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">4</div>
                            <div class="step-text">Submit your application</div>
                        </div>
                    </div>
                    
                    <a href="https://docs.google.com/forms/d/e/1FAIpQLSfmHKGO60CrF7M2HuPpb8KhlhazfcsKPo1MF-fqQyFM1aM22A/viewform" target="_blank" class="btn btn-primary w-100 py-3">
                        <i class="fas fa-paper-plane me-2"></i> Apply Now
                    </a>
                </div>
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

<div class="bg-dark text-white-50 py-3 border-top border-secondary">
    <div class="container text-center">
        <small>&copy; <?= date('Y') ?> JobConnect. All rights reserved.</small>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<style>
    /* Additional styles to fix overlapping and improve UI */
    .transition-hover {
        transition: all 0.3s ease;
    }
    
    .transition-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
    }
    
    .hover-text-white:hover {
        color: white !important;
    }
    
    .rounded-pill {
        border-radius: 50rem !important;
    }
    
    .rounded-circle {
        border-radius: 50% !important;
    }
    
    .fw-medium {
        font-weight: 500 !important;
    }
    
    .shadow-sm {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    }
    
    /* Fix for mobile responsiveness */
    @media (max-width: 767.98px) {
        .card {
            margin-bottom: 1rem;
        }
        
        .company-logo-sm {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }
        
        .job-title-sm {
            font-size: 0.95rem;
        }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add hover effects for detail boxes
        const detailBoxes = document.querySelectorAll('.transition-hover');
        detailBoxes.forEach(box => {
            box.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 15px 35px rgba(0, 0, 0, 0.1)';
            });
            
            box.addEventListener('mouseleave', function() {
                this.style.transform = '';
                this.style.boxShadow = '';
            });
        });
        
        // Add particle effect to header (similar to the index page)
        const pageHeader = document.querySelector('.page-header');
        if (pageHeader) {
            pageHeader.addEventListener('mousemove', function(e) {
                const shapes = document.querySelectorAll('.page-header::before');
                const x = (e.clientX / window.innerWidth) * 100;
                const y = (e.clientY / window.innerHeight) * 100;
                
                shapes.forEach(shape => {
                    shape.style.opacity = 0.4 + (e.clientY / window.innerHeight) * 0.4;
                    shape.style.transform = `translate(${x / 10}px, ${y / 10}px)`;
                });
            });
        }
    });
</script>
</body>
</html> 