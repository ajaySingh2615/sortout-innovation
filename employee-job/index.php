<?php
require_once '../includes/db_connect.php';

// Initialize variables
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';
$selectedJobType = isset($_GET['job_type']) ? $_GET['job_type'] : '';
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
$recordsPerPage = 10;

// Function to get distinct job categories
function getJobCategories($conn) {
    $categories = [];
    $sql = "SELECT DISTINCT category FROM jobs WHERE category IS NOT NULL ORDER BY category";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['category'];
        }
    }
    
    return $categories;
}

// Function to get job count
function getJobsCount($conn, $searchTerm = '', $category = '', $jobType = '') {
    $conditions = ["is_verified = 1"];
    $params = [];
    $types = "";
    
    if (!empty($searchTerm)) {
        $conditions[] = "(title LIKE ? OR company_name LIKE ? OR location LIKE ?)";
        $params[] = "%$searchTerm%";
        $params[] = "%$searchTerm%";
        $params[] = "%$searchTerm%";
        $types .= "sss";
    }
    
    if (!empty($category)) {
        $conditions[] = "category = ?";
        $params[] = $category;
        $types .= "s";
    }
    
    if (!empty($jobType)) {
        $conditions[] = "job_type = ?";
        $params[] = $jobType;
        $types .= "s";
    }
    
    $whereClause = implode(' AND ', $conditions);
    $sql = "SELECT COUNT(*) as count FROM jobs WHERE $whereClause";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row['count'];
}

// Get all job categories
$categories = getJobCategories($conn);

// Get total job count
$totalJobs = getJobsCount($conn, $searchTerm, $selectedCategory, $selectedJobType);

// Calculate total pages
$totalPages = ceil($totalJobs / $recordsPerPage);
if ($currentPage > $totalPages && $totalPages > 0) {
    $currentPage = $totalPages;
}

// Calculate the SQL LIMIT clause
$offset = ($currentPage - 1) * $recordsPerPage;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Listings | Find Your Next Career Opportunity</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        
        .navbar {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }
        
        .hero-section {
            background: linear-gradient(90deg, #2c3e50 0%, #4ca1af 100%);
            color: white;
            padding: 60px 0;
            border-radius: 0 0 100px 0;
        }
        
        .hero-title {
            font-size: 2.5rem;
            font-weight: 700;
        }
        
        .search-box {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            margin-top: -40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .stats-container {
            background-color: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        
        .job-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            height: 100%;
            cursor: pointer;
            overflow: hidden;
            position: relative;
        }
        
        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .job-card-high-demand {
            border-top: 3px solid #f1c40f;
        }
        
        .high-demand-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #f1c40f;
            color: #000;
            font-size: 0.7rem;
            padding: 3px 8px;
            border-radius: 30px;
            font-weight: 600;
        }
        
        .company-logo {
            width: 60px;
            height: 60px;
            background-color: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.5rem;
            color: #2c3e50;
        }
        
        .job-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .company-name {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .job-detail {
            font-size: 0.85rem;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .job-salary {
            font-weight: 600;
            color: #2ecc71;
        }
        
        .job-type {
            display: inline-block;
            background-color: #e3f2fd;
            color: #1976d2;
            font-size: 0.8rem;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .job-type.contract {
            background-color: #fff3e0;
            color: #ff9800;
        }
        
        .job-type.part-time {
            background-color: #e8f5e9;
            color: #4caf50;
        }
        
        .pagination .page-link {
            color: #2c3e50;
            border: none;
            font-weight: 500;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #2c3e50;
            color: white;
        }
        
        .filter-dropdown {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 8px 15px;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .hero-section {
                border-radius: 0;
                padding: 40px 0;
            }
            
            .search-box {
                margin-top: 0;
                padding: 20px;
            }
            
            .job-card {
                margin-bottom: 15px;
            }
            
            .filter-dropdown {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="../index.php">
            <i class="fas fa-briefcase me-2 text-primary"></i> JobConnect
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link fw-semibold" href="../index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold active" href="index.php">Jobs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold" href="#">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold" href="#">Contact</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-section mb-5">
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <h1 class="hero-title">Find Your Dream Job</h1>
                <p class="lead">Discover thousands of job opportunities with all the information you need.</p>
            </div>
        </div>
    </div>
</section>

<!-- Search and Filter Section -->
<div class="container">
    <div class="search-box mb-5">
        <form action="" method="GET" id="searchForm">
            <div class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" placeholder="Search jobs, companies, or locations" name="search" value="<?= htmlspecialchars($searchTerm) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select filter-dropdown" name="category" id="categoryFilter">
                        <option value="">All Categories</option>
                        <?php foreach($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category) ?>" <?= ($selectedCategory == $category) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select filter-dropdown" name="job_type" id="jobTypeFilter">
                        <option value="">All Types</option>
                        <option value="Full Time" <?= ($selectedJobType == 'Full Time') ? 'selected' : '' ?>>Full Time</option>
                        <option value="Part Time" <?= ($selectedJobType == 'Part Time') ? 'selected' : '' ?>>Part Time</option>
                        <option value="Contract" <?= ($selectedJobType == 'Contract') ? 'selected' : '' ?>>Contract</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Find Jobs</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Stats Section -->
    <div class="stats-container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h4 class="mb-0 fw-semibold">Job Listings</h4>
                <p class="text-muted mb-0 small">Showing <?= min($totalJobs, $offset + 1) ?>-<?= min($totalJobs, $offset + $recordsPerPage) ?> of <?= $totalJobs ?> jobs</p>
            </div>
            
            <div class="col-md-6 text-md-end">
                <button class="btn btn-outline-secondary btn-sm me-2" id="resetFilters">
                    <i class="fas fa-redo-alt me-1"></i> Reset Filters
                </button>
                <div class="btn-group" role="group">
                    <a href="#" class="btn btn-outline-secondary btn-sm active">
                        <i class="fas fa-th-large"></i>
                    </a>
                    <a href="#" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-list"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Job Listings -->
    <div class="row g-4 mb-5" id="jobListings">
        <?php
        // Build query to fetch jobs with filtering
        $conditions = ["is_verified = 1"];
        $params = [];
        $types = "";
        
        if (!empty($searchTerm)) {
            $conditions[] = "(title LIKE ? OR company_name LIKE ? OR location LIKE ?)";
            $params[] = "%$searchTerm%";
            $params[] = "%$searchTerm%";
            $params[] = "%$searchTerm%";
            $types .= "sss";
        }
        
        if (!empty($selectedCategory)) {
            $conditions[] = "category = ?";
            $params[] = $selectedCategory;
            $types .= "s";
        }
        
        if (!empty($selectedJobType)) {
            $conditions[] = "job_type = ?";
            $params[] = $selectedJobType;
            $types .= "s";
        }
        
        $whereClause = implode(' AND ', $conditions);
        $sql = "SELECT * FROM jobs WHERE $whereClause ORDER BY high_demand DESC, created_at DESC LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($sql);
        
        // Add limit and offset to parameters
        $params[] = $recordsPerPage;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while($job = $result->fetch_assoc()) {
                $jobTypeClass = strtolower(str_replace(' ', '-', $job['job_type']));
                $formattedMinSalary = number_format($job['min_salary']);
                $formattedMaxSalary = number_format($job['max_salary']);
                
                // Get first letter of company name for logo placeholder
                $companyInitial = strtoupper(substr($job['company_name'], 0, 1));
                
                // Calculate days ago
                $createdDate = new DateTime($job['created_at']);
                $now = new DateTime();
                $interval = $now->diff($createdDate);
                $daysAgo = $interval->days;
                $timeAgo = $daysAgo > 0 ? $daysAgo . " day" . ($daysAgo > 1 ? "s" : "") . " ago" : "Today";
        ?>
        
        <div class="col-md-6 col-lg-4">
            <a href="job-detail.php?id=<?= $job['id'] ?>" class="text-decoration-none">
                <div class="job-card p-4 <?= $job['high_demand'] ? 'job-card-high-demand' : '' ?>">
                    <?php if($job['high_demand']): ?>
                    <span class="high-demand-badge"><i class="fas fa-fire me-1"></i> High Demand</span>
                    <?php endif; ?>
                    
                    <div class="d-flex align-items-start mb-3">
                        <div class="company-logo">
                            <?= $companyInitial ?>
                        </div>
                        <div>
                            <h5 class="job-title"><?= htmlspecialchars($job['title']) ?></h5>
                            <p class="company-name mb-0"><?= htmlspecialchars($job['company_name']) ?></p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <p class="job-detail">
                            <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                            <?= htmlspecialchars($job['location']) ?>
                        </p>
                        <p class="job-detail">
                            <i class="fas fa-briefcase me-2 text-muted"></i>
                            <?= htmlspecialchars($job['experience']) ?>
                        </p>
                        <p class="job-detail job-salary">
                            <i class="fas fa-rupee-sign me-2"></i>
                            <?= $formattedMinSalary ?> - <?= $formattedMaxSalary ?>
                        </p>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="job-type <?= $jobTypeClass ?>"><?= htmlspecialchars($job['job_type']) ?></span>
                        <small class="text-muted"><?= $timeAgo ?></small>
                    </div>
                </div>
            </a>
        </div>
        
        <?php
            }
        } else {
        ?>
        <div class="col-12 text-center py-5">
            <i class="fas fa-search fa-3x mb-3 text-muted"></i>
            <h4>No jobs found</h4>
            <p class="text-muted">Try adjusting your search or filter criteria</p>
            <button class="btn btn-outline-primary" id="clearFilters">Clear all filters</button>
        </div>
        <?php
        }
        $stmt->close();
        ?>
    </div>

    <!-- Pagination -->
    <?php if($totalPages > 1): ?>
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php if($currentPage > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?= $currentPage - 1 ?>&search=<?= urlencode($searchTerm) ?>&category=<?= urlencode($selectedCategory) ?>&job_type=<?= urlencode($selectedJobType) ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
            <?php endif; ?>
            
            <?php
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $startPage + 4);
            
            if ($endPage - $startPage < 4) {
                $startPage = max(1, $endPage - 4);
            }
            
            for ($i = $startPage; $i <= $endPage; $i++):
            ?>
            <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($searchTerm) ?>&category=<?= urlencode($selectedCategory) ?>&job_type=<?= urlencode($selectedJobType) ?>">
                    <?= $i ?>
                </a>
            </li>
            <?php endfor; ?>
            
            <?php if($currentPage < $totalPages): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?= $currentPage + 1 ?>&search=<?= urlencode($searchTerm) ?>&category=<?= urlencode($selectedCategory) ?>&job_type=<?= urlencode($selectedJobType) ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<!-- Footer -->
<footer class="bg-dark text-white py-5 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4 mb-md-0">
                <h5 class="mb-3 fw-bold">JobConnect</h5>
                <p class="mb-3">Connecting talents with opportunities. Find your dream job or the perfect candidate with us.</p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-white"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            
            <div class="col-md-2 mb-4 mb-md-0">
                <h6 class="mb-3 fw-bold">For Candidates</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#" class="text-white text-decoration-none">Browse Jobs</a></li>
                    <li class="mb-2"><a href="#" class="text-white text-decoration-none">Career Resources</a></li>
                    <li class="mb-2"><a href="#" class="text-white text-decoration-none">Resume Tips</a></li>
                    <li class="mb-2"><a href="#" class="text-white text-decoration-none">Interview Guide</a></li>
                </ul>
            </div>
            
            <div class="col-md-2 mb-4 mb-md-0">
                <h6 class="mb-3 fw-bold">For Employers</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#" class="text-white text-decoration-none">Post a Job</a></li>
                    <li class="mb-2"><a href="#" class="text-white text-decoration-none">Browse Candidates</a></li>
                    <li class="mb-2"><a href="#" class="text-white text-decoration-none">Recruitment Solutions</a></li>
                    <li class="mb-2"><a href="#" class="text-white text-decoration-none">Pricing</a></li>
                </ul>
            </div>
            
            <div class="col-md-4">
                <h6 class="mb-3 fw-bold">Stay Updated</h6>
                <p>Subscribe to our newsletter to get the latest jobs and career tips.</p>
                <div class="input-group mb-3">
                    <input type="email" class="form-control" placeholder="Your Email">
                    <button class="btn btn-primary" type="button">Subscribe</button>
                </div>
            </div>
        </div>
    </div>
</footer>

<div class="bg-dark text-white-50 py-3">
    <div class="container text-center">
        <small>&copy; <?= date('Y') ?> JobConnect. All rights reserved.</small>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Reset filters button
        document.getElementById('resetFilters').addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'index.php';
        });
        
        // Clear filters button (on no results page)
        const clearFiltersBtn = document.getElementById('clearFilters');
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = 'index.php';
            });
        }
        
        // Auto-submit form when filters change
        document.getElementById('categoryFilter').addEventListener('change', function() {
            document.getElementById('searchForm').submit();
        });
        
        document.getElementById('jobTypeFilter').addEventListener('change', function() {
            document.getElementById('searchForm').submit();
        });
    });
</script>

</body>
</html> 