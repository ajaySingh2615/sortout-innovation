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
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        
        .navbar {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }
        
        .page-header {
            background: linear-gradient(90deg, #2c3e50 0%, #4ca1af 100%);
            color: white;
            padding: 40px 0;
        }
        
        .job-header {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            padding: 25px;
            margin-top: -50px;
        }
        
        .job-details-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            padding: 25px;
            height: 100%;
        }
        
        .company-logo-lg {
            width: 80px;
            height: 80px;
            background-color: #f8f9fa;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #2c3e50;
            margin-right: 20px;
        }
        
        .job-title-lg {
            font-weight: 700;
            font-size: 1.6rem;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .company-name-lg {
            color: #7f8c8d;
            font-size: 1.1rem;
            margin-bottom: 15px;
        }
        
        .job-badge {
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 10px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .badge-location {
            background-color: #f1f8fe;
            color: #3498db;
        }
        
        .badge-salary {
            background-color: #f1fef6;
            color: #2ecc71;
        }
        
        .badge-job-type {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .badge-experience {
            background-color: #fef1f8;
            color: #e84393;
        }
        
        .badge-education {
            background-color: #f8f9fa;
            color: #5d6d7e;
        }
        
        .badge-high-demand {
            background-color: #fff3cd;
            color: #fd7e14;
        }
        
        .section-title {
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: #2c3e50;
        }
        
        .info-item {
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        
        .info-value {
            color: #5d6d7e;
            font-size: 0.95rem;
        }
        
        .apply-button {
            margin-top: 20px;
            padding: 12px 25px;
            font-weight: 600;
            border-radius: 8px;
        }
        
        .job-description {
            white-space: pre-line;
            color: #5d6d7e;
            line-height: 1.7;
        }
        
        .similar-job-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            height: 100%;
            overflow: hidden;
            cursor: pointer;
        }
        
        .similar-job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .company-logo-sm {
            width: 50px;
            height: 50px;
            background-color: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #2c3e50;
            margin-right: 15px;
        }
        
        .job-title-sm {
            font-weight: 600;
            font-size: 1rem;
            color: #2c3e50;
            margin-bottom: 3px;
        }
        
        .company-name-sm {
            color: #7f8c8d;
            font-size: 0.85rem;
        }
        
        .similar-job-detail {
            font-size: 0.8rem;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        @media (max-width: 768px) {
            .job-header {
                margin-top: 0;
                margin-bottom: 20px;
            }
            
            .company-logo-lg {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
            
            .job-title-lg {
                font-size: 1.3rem;
            }
            
            .similar-job-card {
                margin-bottom: 15px;
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

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="fw-bold">Job Details</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
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
    <div class="job-header mb-4">
        <div class="row">
            <div class="col-lg-8">
                <div class="d-flex align-items-start">
                    <div class="company-logo-lg">
                        <?= $companyInitial ?>
                    </div>
                    <div>
                        <h1 class="job-title-lg"><?= htmlspecialchars($job['title']) ?></h1>
                        <p class="company-name-lg"><?= htmlspecialchars($job['company_name']) ?></p>
                        
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
            
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <div class="d-flex flex-column h-100 justify-content-between align-items-lg-end">
                    <div class="text-muted">
                        <i class="far fa-clock me-1"></i> Posted <?= $timeAgo ?>
                    </div>
                    
                    <button class="btn btn-primary apply-button mt-3 mt-lg-0">
                        <i class="fas fa-paper-plane me-2"></i> Apply Now
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content - Refactored Section -->
    <div class="row g-4">
        <!-- Left Column - Job Details -->
        <div class="col-lg-8">
            <!-- Job Description Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden">
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
                            <div class="d-flex align-items-center p-3 rounded-3 bg-light mb-3">
                                <i class="fas fa-briefcase text-primary me-3 fs-4"></i>
                                <div>
                                    <div class="info-label">Job Type</div>
                                    <div class="info-value"><?= htmlspecialchars($job['job_type']) ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 rounded-3 bg-light mb-3">
                                <i class="fas fa-tag text-primary me-3 fs-4"></i>
                                <div>
                                    <div class="info-label">Category</div>
                                    <div class="info-value"><?= !empty($job['category']) ? htmlspecialchars($job['category']) : 'Not specified' ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 rounded-3 bg-light mb-3">
                                <i class="fas fa-users text-primary me-3 fs-4"></i>
                                <div>
                                    <div class="info-label">Vacancies</div>
                                    <div class="info-value"><?= $job['vacancies'] ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 rounded-3 bg-light mb-3">
                                <i class="fas fa-calendar-alt text-primary me-3 fs-4"></i>
                                <div>
                                    <div class="info-label">Working Days</div>
                                    <div class="info-value"><?= $job['working_days'] ?> days per week</div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($job['working_hours'])): ?>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 rounded-3 bg-light mb-3">
                                <i class="fas fa-clock text-primary me-3 fs-4"></i>
                                <div>
                                    <div class="info-label">Working Hours</div>
                                    <div class="info-value"><?= htmlspecialchars($job['working_hours']) ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 rounded-3 bg-light mb-3">
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
                        <button class="btn btn-primary apply-button px-5">
                            <i class="fas fa-paper-plane me-2"></i> Apply for this Position
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Similar Jobs Section -->
            <?php if (count($similarJobs) > 0): ?>
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-body p-4">
                    <h2 class="section-title border-bottom pb-3 mb-4">Similar Jobs You Might Like</h2>
                    <div class="row g-3">
                        <?php foreach ($similarJobs as $similarJob): 
                            $similarJobInitial = strtoupper(substr($similarJob['company_name'], 0, 1));
                            $similarJobFormattedMinSalary = number_format($similarJob['min_salary']);
                            $similarJobFormattedMaxSalary = number_format($similarJob['max_salary']);
                        ?>
                        <div class="col-md-6 mb-3">
                            <a href="job-detail.php?id=<?= $similarJob['id'] ?>" class="text-decoration-none">
                                <div class="card border-0 shadow-sm h-100 transition-hover">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="company-logo-sm d-flex align-items-center justify-content-center bg-light rounded-3 flex-shrink-0">
                                                <?= $similarJobInitial ?>
                                            </div>
                                            <div class="ms-3">
                                                <h5 class="job-title-sm mb-1"><?= htmlspecialchars($similarJob['title']) ?></h5>
                                                <p class="company-name-sm text-muted mb-2"><?= htmlspecialchars($similarJob['company_name']) ?></p>
                                                <div class="d-flex flex-wrap gap-2 mt-2">
                                                    <span class="badge bg-light text-dark px-2 py-1 rounded-pill">
                                                        <i class="fas fa-map-marker-alt me-1 text-muted"></i>
                                                        <?= htmlspecialchars($similarJob['location']) ?>
                                                    </span>
                                                    <span class="badge bg-light text-dark px-2 py-1 rounded-pill">
                                                        <i class="fas fa-rupee-sign me-1 text-success"></i>
                                                        <?= $similarJobFormattedMinSalary ?> - <?= $similarJobFormattedMaxSalary ?>
                                                    </span>
                                                    <span class="badge bg-light text-dark px-2 py-1 rounded-pill">
                                                        <i class="fas fa-briefcase me-1 text-primary"></i>
                                                        <?= htmlspecialchars($similarJob['job_type']) ?>
                                                    </span>
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
        
        <!-- Right Column - Company Details & Application Process -->
        <div class="col-lg-4">
            <!-- Company & Job Info -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden">
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
                            <div class="info-value fw-medium text-success">₹<?= $formattedMinSalary ?> - ₹<?= $formattedMaxSalary ?></div>
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
                    
                    <div class="d-flex align-items-center mb-4">
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
            
            <!-- Contact Information & Interview Location -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden">
                <div class="card-body p-4">
                    <h3 class="section-title border-bottom pb-3 mb-4">Contact Information</h3>
                    
                    <?php if (!empty($job['contact_person'])): ?>
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-light rounded-circle p-3 me-3">
                            <i class="fas fa-user text-primary fs-4"></i>
                        </div>
                        <div>
                            <div class="info-label">Contact Person</div>
                            <div class="info-value fw-medium"><?= htmlspecialchars($job['contact_person']) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($job['interview_address'])): ?>
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-light rounded-circle p-3 me-3">
                            <i class="fas fa-map-marked-alt text-primary fs-4"></i>
                        </div>
                        <div>
                            <div class="info-label">Interview Location</div>
                            <div class="info-value fw-medium"><?= nl2br(htmlspecialchars($job['interview_address'])) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <a href="#" class="btn btn-outline-primary w-100 rounded-pill">
                            <i class="fas fa-share-alt me-2"></i> Share This Job
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Application Process -->
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-body p-4">
                    <h3 class="section-title border-bottom pb-3 mb-4">How to Apply</h3>
                    
                    <div class="bg-light p-4 rounded-3 mb-4">
                        <div class="step-item d-flex mb-3">
                            <div class="step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">1</div>
                            <div class="step-text">Click on the "Apply Now" button</div>
                        </div>
                        <div class="step-item d-flex mb-3">
                            <div class="step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">2</div>
                            <div class="step-text">Fill in your personal and professional details</div>
                        </div>
                        <div class="step-item d-flex mb-3">
                            <div class="step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">3</div>
                            <div class="step-text">Upload your resume (PDF format preferred)</div>
                        </div>
                        <div class="step-item d-flex">
                            <div class="step-number bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 30px; height: 30px;">4</div>
                            <div class="step-text">Submit your application</div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info rounded-3 d-flex align-items-center mb-0" role="alert">
                        <i class="fas fa-info-circle fs-4 me-3"></i>
                        <div>Your application will be reviewed within 3-5 business days.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer - Refactored -->
<footer class="bg-dark text-white py-5 mt-5">
    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-4 col-md-6">
                <h5 class="mb-3 fw-bold d-flex align-items-center">
                    <i class="fas fa-briefcase me-2 text-primary"></i> JobConnect
                </h5>
                <p class="mb-3 text-white-50">Connecting talents with opportunities. Find your dream job or the perfect candidate with us.</p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-white btn btn-sm btn-dark"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white btn btn-sm btn-dark"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-white btn btn-sm btn-dark"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="text-white btn btn-sm btn-dark"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-6">
                <h6 class="mb-3 fw-bold">For Candidates</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none hover-text-white">Browse Jobs</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none hover-text-white">Career Resources</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none hover-text-white">Resume Tips</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none hover-text-white">Interview Guide</a></li>
                </ul>
            </div>
            
            <div class="col-lg-2 col-md-6">
                <h6 class="mb-3 fw-bold">For Employers</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none hover-text-white">Post a Job</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none hover-text-white">Browse Candidates</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none hover-text-white">Recruitment Solutions</a></li>
                    <li class="mb-2"><a href="#" class="text-white-50 text-decoration-none hover-text-white">Pricing</a></li>
                </ul>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <h6 class="mb-3 fw-bold">Stay Updated</h6>
                <p class="text-white-50">Subscribe to our newsletter to get the latest jobs and career tips.</p>
                <div class="input-group">
                    <input type="email" class="form-control" placeholder="Your Email">
                    <button class="btn btn-primary" type="button">Subscribe</button>
                </div>
            </div>
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
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .transition-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
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
</body>
</html> 