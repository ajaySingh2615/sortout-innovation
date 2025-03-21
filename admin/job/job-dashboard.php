<?php
require_once '../../auth/auth.php';
require_once '../../includes/db_connect.php';

// Ensure only super admins can access
if ($_SESSION['role'] !== 'super_admin') {
    echo "Access Denied! Only super admins can manage jobs.";
    exit();
}

// Get total job count
$totalJobs = $conn->query("SELECT COUNT(*) as count FROM jobs")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Management Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: radial-gradient(circle, rgba(255,255,255,1) 0%, rgba(240,240,240,1) 100%);
            background-image: url("https://www.transparenttextures.com/patterns/cubes.png");
            background-repeat: repeat;
        }
        .card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .filters-container {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        .table-responsive {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        th {
            background: #d90429;
            color: white;
        }
        .badge {
            font-size: 0.8rem;
            padding: 0.35em 0.65em;
            border-radius: 0.25rem;
        }
        .badge-verified {
            background-color: #198754;
        }
        .badge-pending {
            background-color: #ffc107;
        }
        .badge-high-demand {
            background-color: #dc3545;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg fixed-top bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center text-danger" href="#">
            Job Management Dashboard
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link fw-semibold px-3" href="../dashboard.php">Admin Dashboard</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle fw-semibold px-3" href="#" role="button" data-bs-toggle="dropdown">
                        👤 <?= $_SESSION['username']; ?> (<?= ucfirst($_SESSION['role']); ?>)
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="../../auth/logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Spacing for Navbar -->
<div style="height: 80px;"></div>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Job Management</h1>
        <a href="add-job.php" class="btn btn-success">
            <i class="fas fa-plus"></i> Add New Job
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Total Jobs</h5>
                        <h2 class="mb-0"><?= $totalJobs ?></h2>
                    </div>
                    <i class="fas fa-briefcase fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Verified Jobs</h5>
                        <h2 class="mb-0" id="verified-jobs-count">-</h2>
                    </div>
                    <i class="fas fa-check-circle fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">High Demand</h5>
                        <h2 class="mb-0" id="high-demand-count">-</h2>
                    </div>
                    <i class="fas fa-fire fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="searchInput" class="form-label">Search Jobs</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search by title, company...">
                    <button class="btn btn-outline-secondary" type="button" id="searchButton">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <label for="jobTypeFilter" class="form-label">Job Type</label>
                <select class="form-select" id="jobTypeFilter">
                    <option value="">All Types</option>
                    <option value="Full Time">Full Time</option>
                    <option value="Part Time">Part Time</option>
                    <option value="Contract">Contract</option>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="verifiedFilter" class="form-label">Status</label>
                <select class="form-select" id="verifiedFilter">
                    <option value="">All</option>
                    <option value="1">Verified</option>
                    <option value="0">Pending</option>
                </select>
            </div>
            <div class="col-md-2 mb-3">
                <label class="form-label">&nbsp;</label>
                <button class="btn btn-outline-secondary form-control" id="resetFilters">
                    <i class="fas fa-undo"></i> Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Job Table -->
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Company</th>
                    <th>Location</th>
                    <th>Salary Range</th>
                    <th>Job Type</th>
                    <th>Status</th>
                    <th>Posted</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="jobTableBody">
                <!-- Data will be loaded here via AJAX -->
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="pagination-info">
            <!-- Pagination info will be populated by JavaScript -->
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination" id="pagination">
                <!-- Pagination will be populated by JavaScript -->
            </ul>
        </nav>
    </div>
</div>

<!-- Bootstrap & jQuery -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    // Global variables
    let currentPage = 1;
    let totalPages = 1;
    const limit = 10;
    
    // Load jobs on page load
    $(document).ready(function() {
        loadJobs(currentPage, limit);
        
        // Set up search functionality
        $('#searchInput').on('keyup', function(e) {
            if(e.key === 'Enter') {
                loadJobs(1, limit);
            }
        });
        
        // Search button click
        $('#searchButton').on('click', function() {
            loadJobs(1, limit);
        });
        
        // Set up filters
        $('#jobTypeFilter, #verifiedFilter').on('change', function() {
            loadJobs(1, limit);
        });
        
        // Reset filters
        $('#resetFilters').on('click', function() {
            $('#searchInput').val('');
            $('#jobTypeFilter').val('');
            $('#verifiedFilter').val('');
            loadJobs(1, limit);
        });
    });
    
    // Function to load jobs
    function loadJobs(page, limit) {
        currentPage = page;
        const search = $('#searchInput').val();
        const jobType = $('#jobTypeFilter').val();
        const verified = $('#verifiedFilter').val();
        
        // Show loading indicator
        $('#jobTableBody').html('<tr><td colspan="9" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');
        
        // Fetch jobs data
        $.ajax({
            url: 'fetch-jobs.php',
            type: 'GET',
            data: { 
                page: page, 
                limit: limit,
                search: search,
                job_type: jobType,
                verified: verified
            },
            dataType: 'json',
            success: function(data) {
                // Update statistics
                $('#verified-jobs-count').text(data.stats?.verified || 0);
                $('#high-demand-count').text(data.stats?.high_demand || 0);
                
                // Update table with job data
                let html = '';
                
                if (data.jobs && data.jobs.length > 0) {
                    data.jobs.forEach(job => {
                        const statusBadge = job.is_verified == 1 
                            ? '<span class="badge badge-verified">Verified</span>' 
                            : '<span class="badge badge-pending">Pending</span>';
                            
                        const highDemandBadge = job.high_demand == 1 
                            ? ' <span class="badge badge-high-demand">High Demand</span>' 
                            : '';

                        html += `
                            <tr>
                                <td>${job.id}</td>
                                <td>${job.title}</td>
                                <td>${job.company_name}</td>
                                <td>${job.location}</td>
                                <td>₹${job.min_salary} - ₹${job.max_salary}</td>
                                <td>${job.job_type}</td>
                                <td>${statusBadge}${highDemandBadge}</td>
                                <td>${job.formatted_date || job.created_at}</td>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        <a href="edit-job.php?id=${job.id}" class="btn btn-sm btn-primary me-2" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-success me-2" onclick="toggleVerification(${job.id}, ${job.is_verified})" title="${job.is_verified == 1 ? 'Unverify' : 'Verify'}">
                                            <i class="fas ${job.is_verified == 1 ? 'fa-times-circle' : 'fa-check-circle'}"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteJob(${job.id})" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="9" class="text-center">No jobs found</td></tr>';
                }
                
                $('#jobTableBody').html(html);
                
                // Update pagination
                updatePagination(data.pagination);
                
                // Update pagination info
                if (data.pagination) {
                    const start = (data.pagination.current_page - 1) * data.pagination.limit + 1;
                    const end = Math.min(data.pagination.current_page * data.pagination.limit, data.pagination.total_rows);
                    $('.pagination-info').html(`Showing ${start} to ${end} of ${data.pagination.total_rows} entries`);
                }
            },
            error: function(xhr, status, error) {
                $('#jobTableBody').html('<tr><td colspan="9" class="text-center text-danger">Error loading jobs</td></tr>');
                console.error("AJAX Error:", status, error);
            }
        });
    }
    
    // Function to update pagination
    function updatePagination(pagination) {
        if (!pagination) {
            console.error('Pagination data is missing');
            return;
        }
        
        const totalPages = parseInt(pagination.total_pages) || 1;
        const currentPage = parseInt(pagination.current_page) || 1;
        
        let paginationHtml = '';
        
        // Previous button
        paginationHtml += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="javascript:void(0)" onclick="${currentPage > 1 ? 'loadJobs(' + (currentPage - 1) + ', ' + limit + ')' : ''}" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        `;
        
        // Page numbers
        const maxPages = 5; // Maximum number of page links to show
        let startPage = Math.max(1, currentPage - Math.floor(maxPages / 2));
        let endPage = Math.min(totalPages, startPage + maxPages - 1);
        
        if (endPage - startPage + 1 < maxPages) {
            startPage = Math.max(1, endPage - maxPages + 1);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            paginationHtml += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="loadJobs(${i}, ${limit})">${i}</a>
                </li>
            `;
        }
        
        // Next button
        paginationHtml += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="javascript:void(0)" onclick="${currentPage < totalPages ? 'loadJobs(' + (currentPage + 1) + ', ' + limit + ')' : ''}" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        `;
        
        $('#pagination').html(paginationHtml);
    }
    
    // Function to toggle job verification
    function toggleVerification(jobId, currentStatus) {
        if (confirm(`Are you sure you want to ${currentStatus == 1 ? 'unverify' : 'verify'} this job?`)) {
            $.ajax({
                url: 'verify-job.php',
                type: 'POST',
                data: { 
                    id: jobId,
                    verified: currentStatus == 1 ? 0 : 1
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(`Job ${currentStatus == 1 ? 'unverified' : 'verified'} successfully!`);
                        loadJobs(currentPage, limit);
                    } else {
                        alert('Failed to update job verification status: ' + (response.error || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred while updating the job verification status.');
                    console.error("AJAX Error:", status, error);
                }
            });
        }
    }
    
    // Function to delete job
    function deleteJob(jobId) {
        if (confirm('Are you sure you want to delete this job?')) {
            $.ajax({
                url: 'delete-job.php',
                type: 'POST',
                data: { id: jobId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Job deleted successfully!');
                        loadJobs(currentPage, limit);
                    } else {
                        alert('Failed to delete job: ' + (response.error || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred while deleting the job.');
                    console.error("AJAX Error:", status, error);
                }
            });
        }
    }
</script>

</body>
</html> 