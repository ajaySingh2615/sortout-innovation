<?php
// ✅ Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Ensure Correct Path for Database Connection
require_once __DIR__ . '/../includes/db_connect.php';

// ✅ Ensure Only Admins & Super Admins Can Access
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'super_admin')) {
    header("Location: ../auth/login.php");
    exit();
}

// ✅ Debugging: Check if session variables are set properly
// Uncomment this line if you need to debug session values
// echo "<pre>"; print_r($_SESSION); echo "</pre>"; exit();

// ✅ Fetch pending clients
$pendingQuery = "SELECT * FROM clients WHERE approval_status = 'pending'";
$pendingResult = mysqli_query($conn, $pendingQuery);

// ✅ Fetch approved clients
$approvedQuery = "SELECT * FROM clients WHERE approval_status = 'approved'";
$approvedResult = mysqli_query($conn, $approvedQuery);

// Get total counts
$pendingCount = $conn->query("SELECT COUNT(*) as count FROM clients WHERE approval_status = 'pending'")->fetch_assoc()['count'];
$approvedCount = $conn->query("SELECT COUNT(*) as count FROM clients WHERE approval_status = 'approved'")->fetch_assoc()['count'];
$rejectedCount = $conn->query("SELECT COUNT(*) as count FROM clients WHERE approval_status = 'rejected'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agency Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            transition: transform 0.3s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 600;
            color: white;
            transition: background-color 0.3s;
        }
        .btn-approve { background-color: #10b981; }
        .btn-approve:hover { background-color: #059669; }
        .btn-reject { background-color: #ef4444; }
        .btn-reject:hover { background-color: #dc2626; }
        .btn-edit { background-color: #3b82f6; }
        .btn-edit:hover { background-color: #2563eb; }
        .btn-delete { background-color: #ef4444; }
        .btn-delete:hover { background-color: #dc2626; }
        .table-container {
            overflow-x: auto;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin: 1.5rem 0;
        }
        .table-header {
            background-color: #f3f4f6;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 600;
            color: #4b5563;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .table-cell {
            padding: 1rem 1.5rem;
            white-space: nowrap;
            font-size: 0.875rem;
            color: #1f2937;
        }
        .pagination-btn {
            padding: 0.25rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #4b5563;
            background-color: white;
            border: 1px solid #d1d5db;
        }
        .pagination-btn:hover {
            background-color: #f3f4f6;
        }
        .pagination-btn.active {
            background-color: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        .alert {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        .alert-error {
            background-color: #fee2e2;
            color: #b91c1c;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Top Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-900">Agency Dashboard</h1>
                </div>
                <div class="flex items-center">
                    <span class="text-gray-700 mr-4">Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
                    <a href="../auth/logout.php" class="btn bg-red-500 hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Alerts -->
        <div id="alertContainer" class="hidden"></div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="dashboard-card">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Pending Approvals</p>
                        <p class="text-2xl font-semibold text-gray-900"><?= $pendingCount ?></p>
                    </div>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-500">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Approved Clients</p>
                        <p class="text-2xl font-semibold text-gray-900"><?= $approvedCount ?></p>
                    </div>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-500">
                        <i class="fas fa-times-circle fa-2x"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Rejected Clients</p>
                        <p class="text-2xl font-semibold text-gray-900"><?= $rejectedCount ?></p>
                    </div>
                </div>
        </div>
</div>

        <!-- Pending Approvals Section -->
    <div class="table-container">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900">Pending Approvals</h2>
                <div class="flex gap-4">
                    <input type="text" id="pendingSearch" placeholder="Search..." class="px-4 py-2 border rounded-lg">
                    <select id="pendingFilter" class="px-4 py-2 border rounded-lg">
                        <option value="">All Types</option>
                        <option value="Artist">Artist</option>
                        <option value="Employee">Employee</option>
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="table-header px-6 py-3">Name</th>
                            <th class="table-header px-6 py-3">Age</th>
                            <th class="table-header px-6 py-3">Gender</th>
                            <th class="table-header px-6 py-3">Professional</th>
                            <th class="table-header px-6 py-3">Category/Role</th>
                            <th class="table-header px-6 py-3">City</th>
                            <th class="table-header px-6 py-3">Followers</th>
                            <th class="table-header px-6 py-3">Experience</th>
                            <th class="table-header px-6 py-3">Languages</th>
                            <th class="table-header px-6 py-3">Image</th>
                            <th class="table-header px-6 py-3">Resume</th>
                            <th class="table-header px-6 py-3">Actions</th>
            </tr>
                    </thead>
                    <tbody id="pendingTableBody">
                        <?php if ($pendingResult && mysqli_num_rows($pendingResult) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($pendingResult)): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="table-cell"><?= htmlspecialchars($row['name']) ?></td>
                                    <td class="table-cell"><?= $row['age'] ?></td>
                                    <td class="table-cell"><?= htmlspecialchars($row['gender']) ?></td>
                                    <td class="table-cell"><?= htmlspecialchars($row['professional']) ?></td>
                                    <td class="table-cell">
                                        <?= $row['professional'] === 'Artist' ? 
                                            htmlspecialchars($row['category']) : 
                                            htmlspecialchars($row['role']) ?>
                                    </td>
                                    <td class="table-cell"><?= htmlspecialchars($row['city']) ?></td>
                                    <td class="table-cell"><?= htmlspecialchars($row['followers']) ?></td>
                                    <td class="table-cell"><?= htmlspecialchars($row['experience']) ?></td>
                                    <td class="table-cell"><?= htmlspecialchars($row['language']) ?></td>
                                    <td class="table-cell">
                                        <img src="<?= htmlspecialchars($row['image_url']) ?>" 
                                             alt="Client Image" 
                                             class="w-12 h-12 rounded-full object-cover">
                                    </td>
                                    <td class="table-cell">
                                        <?php if (!empty($row['resume_url'])): ?>
                                            <a href="<?= htmlspecialchars('../' . $row['resume_url']) ?>" 
                                               target="_blank"
                                               class="btn bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">
                                                <i class="fas fa-file-pdf mr-1"></i> View
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400">No Resume</span>
                                        <?php endif; ?>
                    </td>
                                    <td class="table-cell">
                                        <div class="flex space-x-2">
                                            <button onclick="approveClient(<?= $row['id'] ?>)" 
                                                    class="action-btn bg-green-500 hover:bg-green-600">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button onclick="rejectClient(<?= $row['id'] ?>)" 
                                                    class="action-btn bg-red-500 hover:bg-red-600">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="12" class="table-cell text-center py-8">
                                    <i class="fas fa-inbox text-gray-400 text-4xl mb-2"></i>
                                    <p class="text-gray-500">No pending clients</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
        </table>
                <!-- Add pagination container -->
                <div id="pendingPagination" class="flex justify-center gap-2 mt-4"></div>
            </div>
    </div>

        <!-- Approved Clients Section -->
    <div class="table-container">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900">Approved Clients</h2>
                <div class="flex gap-4">
                    <input type="text" id="approvedSearch" placeholder="Search..." class="px-4 py-2 border rounded-lg">
                    <select id="approvedFilter" class="px-4 py-2 border rounded-lg">
                        <option value="">All Types</option>
                        <option value="Artist">Artist</option>
                        <option value="Employee">Employee</option>
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="table-header px-6 py-3">Name</th>
                            <th class="table-header px-6 py-3">Age</th>
                            <th class="table-header px-6 py-3">Gender</th>
                            <th class="table-header px-6 py-3">Professional</th>
                            <th class="table-header px-6 py-3">Category/Role</th>
                            <th class="table-header px-6 py-3">City</th>
                            <th class="table-header px-6 py-3">Followers</th>
                            <th class="table-header px-6 py-3">Experience</th>
                            <th class="table-header px-6 py-3">Languages</th>
                            <th class="table-header px-6 py-3">Image</th>
                            <th class="table-header px-6 py-3">Resume</th>
                            <th class="table-header px-6 py-3">Actions</th>
            </tr>
                    </thead>
                    <tbody id="approvedTableBody">
                        <?php if ($approvedResult && mysqli_num_rows($approvedResult) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($approvedResult)): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="table-cell"><?= htmlspecialchars($row['name']) ?></td>
                                    <td class="table-cell"><?= $row['age'] ?></td>
                                    <td class="table-cell"><?= htmlspecialchars($row['gender']) ?></td>
                                    <td class="table-cell"><?= htmlspecialchars($row['professional']) ?></td>
                                    <td class="table-cell">
                                        <?= $row['professional'] === 'Artist' ? 
                                            htmlspecialchars($row['category']) : 
                                            htmlspecialchars($row['role']) ?>
                                    </td>
                                    <td class="table-cell"><?= htmlspecialchars($row['city']) ?></td>
                                    <td class="table-cell"><?= htmlspecialchars($row['followers']) ?></td>
                                    <td class="table-cell"><?= htmlspecialchars($row['experience']) ?></td>
                                    <td class="table-cell"><?= htmlspecialchars($row['language']) ?></td>
                                    <td class="table-cell">
                                        <img src="<?= htmlspecialchars($row['image_url']) ?>" 
                                             alt="Client Image" 
                                             class="w-12 h-12 rounded-full object-cover">
                                    </td>
                                    <td class="table-cell">
                                        <?php if (!empty($row['resume_url'])): ?>
                                            <a href="<?= htmlspecialchars('../' . $row['resume_url']) ?>" 
                                               target="_blank"
                                               class="btn bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">
                                                <i class="fas fa-file-pdf mr-1"></i> View
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400">No Resume</span>
                                        <?php endif; ?>
                    </td>
                                    <td class="table-cell">
                                        <div class="flex space-x-2">
                                            <button onclick="editClient(<?= $row['id'] ?>)" 
                                                    class="action-btn bg-blue-500 hover:bg-blue-600">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="deleteClient(<?= $row['id'] ?>)" 
                                                    class="action-btn bg-red-500 hover:bg-red-600">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="12" class="table-cell text-center py-8">
                                    <i class="fas fa-inbox text-gray-400 text-4xl mb-2"></i>
                                    <p class="text-gray-500">No approved clients</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
        </table>
                <!-- Add pagination container -->
                <div id="approvedPagination" class="flex justify-center gap-2 mt-4"></div>
            </div>
        </div>
    </div>

    <script>
        // Show alert function
        function showAlert(message, type = 'success') {
            const alertContainer = document.getElementById('alertContainer');
            alertContainer.innerHTML = `
                <div class="alert ${type === 'success' ? 'alert-success' : 'alert-error'}">
                    <div class="flex items-center">
                        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                        <span>${message}</span>
                    </div>
                </div>
            `;
            alertContainer.classList.remove('hidden');
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                alertContainer.classList.add('hidden');
            }, 5000);
        }

        // Utility functions
        function createPagination(totalPages, currentPage, containerId, onPageChange) {
            const container = document.getElementById(containerId);
            container.innerHTML = '';
            
            // Add previous button
            if (currentPage > 1) {
                const prevButton = document.createElement('button');
                prevButton.className = 'pagination-btn mx-1';
                prevButton.innerHTML = '<i class="fas fa-chevron-left"></i>';
                prevButton.onclick = () => onPageChange(currentPage - 1);
                container.appendChild(prevButton);
            }
            
            // Calculate page range
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);
            
            if (endPage - startPage < 4) {
                startPage = Math.max(1, endPage - 4);
            }
            
            // Add page buttons
            for (let i = startPage; i <= endPage; i++) {
                const button = document.createElement('button');
                button.className = `pagination-btn mx-1 ${i === currentPage ? 'active' : ''}`;
                button.textContent = i;
                button.onclick = () => onPageChange(i);
                container.appendChild(button);
            }
            
            // Add next button
            if (currentPage < totalPages) {
                const nextButton = document.createElement('button');
                nextButton.className = 'pagination-btn mx-1';
                nextButton.innerHTML = '<i class="fas fa-chevron-right"></i>';
                nextButton.onclick = () => onPageChange(currentPage + 1);
                container.appendChild(nextButton);
            }
        }

        // Load clients function
        async function loadClients(status, page = 1, search = '', filter = '') {
            try {
                const response = await fetch(`fetch_clients.php?status=${status}&page=${page}&search=${search}&filter=${filter}`);
                const data = await response.json();
                
                const tbody = document.getElementById(`${status}TableBody`);
                
                if (data.clients.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="12" class="table-cell text-center py-8">
                                <i class="fas fa-info-circle text-gray-400 text-4xl mb-2"></i>
                                <p class="text-gray-500">No ${status} clients found</p>
                            </td>
                        </tr>
                    `;
                    document.getElementById(`${status}Pagination`).innerHTML = '';
                    return;
                }
                
                tbody.innerHTML = '';
                
                data.clients.forEach(client => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-gray-50';
                    
                    // Format image URL correctly
                    let imageUrl = client.image_url;
                    if (!imageUrl.startsWith('http')) {
                        imageUrl = '../uploads/' + imageUrl;
                    }

                    // Format resume URL correctly
                    let resumeHtml = '';
                    if (client.resume_url) {
                        const resumePath = client.resume_url.startsWith('http') ? 
                            client.resume_url : 
                            '../' + client.resume_url;
                        resumeHtml = `
                            <a href="${resumePath}" target="_blank" class="btn bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded">
                                <i class="fas fa-file-pdf mr-1"></i> View
                            </a>
                        `;
                    } else {
                        resumeHtml = `<span class="text-gray-400">No Resume</span>`;
                    }
                    
                    tr.innerHTML = `
                        <td class="table-cell">${client.name}</td>
                        <td class="table-cell">${client.age}</td>
                        <td class="table-cell">${client.gender}</td>
                        <td class="table-cell">${client.professional}</td>
                        <td class="table-cell">${client.professional === 'Artist' ? (client.category || '-') : (client.role || '-')}</td>
                        <td class="table-cell">${client.city || '-'}</td>
                        <td class="table-cell">${client.followers || '-'}</td>
                        <td class="table-cell">${client.experience || '-'}</td>
                        <td class="table-cell">${client.language || '-'}</td>
                        <td class="table-cell">
                            <img src="${imageUrl}" alt="Profile" class="h-12 w-12 rounded-full object-cover">
                        </td>
                        <td class="table-cell">${resumeHtml}</td>
                        <td class="table-cell">
                            ${status === 'pending' ? `
                                <button onclick="approveClient(${client.id})" class="btn btn-approve mr-2">
                                    <i class="fas fa-check mr-1"></i> Approve
                                </button>
                                <button onclick="rejectClient(${client.id})" class="btn btn-reject">
                                    <i class="fas fa-times mr-1"></i> Reject
                                </button>
                            ` : `
                                <button onclick="editClient(${client.id})" class="btn btn-edit mr-2">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </button>
                                <button onclick="deleteClient(${client.id})" class="btn btn-delete">
                                    <i class="fas fa-trash mr-1"></i> Delete
                                </button>
                            `}
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                createPagination(
                    Math.ceil(data.total / 10),
                    page,
                    `${status}Pagination`,
                    (newPage) => loadClients(status, newPage, search, filter)
                );
            } catch (error) {
                console.error('Error loading clients:', error);
                const tbody = document.getElementById(`${status}TableBody`);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="12" class="table-cell text-center py-8">
                            <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-2"></i>
                            <p class="text-red-500">Error loading clients. Please try again.</p>
                        </td>
                    </tr>
                `;
            }
        }

        // Client action functions
        async function approveClient(id) {
            if (!confirm('Are you sure you want to approve this client?')) return;
            
            try {
                const response = await fetch(`approve_client.php?id=${id}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    showAlert(data.message);
                    loadClients('pending');
                    loadClients('approved');
                    
                    // Update counts
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                console.error('Error approving client:', error);
                showAlert('Error approving client. Please try again.', 'error');
            }
        }

        async function rejectClient(id) {
            if (!confirm('Are you sure you want to reject this client?')) return;
            
            try {
                const response = await fetch(`reject_client.php?id=${id}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    showAlert(data.message);
                    loadClients('pending');
                    
                    // Update counts
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                console.error('Error rejecting client:', error);
                showAlert('Error rejecting client. Please try again.', 'error');
            }
        }

        function editClient(id) {
            window.location.href = `edit_client_form.php?id=${id}`;
        }

        async function deleteClient(id) {
            if (!confirm('Are you sure you want to delete this client? This action cannot be undone.')) return;
            
            try {
                const response = await fetch(`delete_client.php?id=${id}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    showAlert(data.message);
                    loadClients('approved');
                    
                    // Update counts
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                console.error('Error deleting client:', error);
                showAlert('Error deleting client. Please try again.', 'error');
            }
        }

        // Search and filter handlers
        let pendingSearchTimeout, approvedSearchTimeout;

        document.getElementById('pendingSearch').addEventListener('input', (e) => {
            clearTimeout(pendingSearchTimeout);
            pendingSearchTimeout = setTimeout(() => {
                loadClients('pending', 1, e.target.value, document.getElementById('pendingFilter').value);
            }, 300);
        });

        document.getElementById('pendingFilter').addEventListener('change', (e) => {
            loadClients('pending', 1, document.getElementById('pendingSearch').value, e.target.value);
        });

        document.getElementById('approvedSearch').addEventListener('input', (e) => {
            clearTimeout(approvedSearchTimeout);
            approvedSearchTimeout = setTimeout(() => {
                loadClients('approved', 1, e.target.value, document.getElementById('approvedFilter').value);
            }, 300);
        });

        document.getElementById('approvedFilter').addEventListener('change', (e) => {
            loadClients('approved', 1, document.getElementById('approvedSearch').value, e.target.value);
        });

        // Initial load
        document.addEventListener('DOMContentLoaded', () => {
            loadClients('pending');
            loadClients('approved');
        });
    </script>
</body>
</html>
