<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'super_admin')) {
    header("Location: ../auth/login.php");
    exit();
}

// Get client ID from URL
$client_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$client_id) {
    header("Location: model_agency_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Client</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Client</h1>
            
            <div id="errorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"></div>
            <div id="successMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"></div>

            <form id="editClientForm" enctype="multipart/form-data">
                <input type="hidden" id="clientId" name="id">
                <input type="hidden" id="professional" name="professional">
                
                <!-- Basic Information -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="name">Name</label>
                        <input type="text" id="name" name="name" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="age">Age</label>
                        <input type="number" id="age" name="age" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" required>
                    </div>
                </div>

                <!-- Phone and Gender -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="gender">Gender</label>
                        <select id="gender" name="gender" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>

                <!-- City and Language -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="city">City</label>
                        <input type="text" id="city" name="city" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="language">Languages</label>
                        <input type="text" id="language" name="language" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" required>
                    </div>
                </div>

                <!-- Artist Fields -->
                <div id="artistFields" class="hidden">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="category">Category</label>
                            <select id="category" name="category" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                                <option value="">Select Category</option>
                                <option value="Live Streaming Host">Live Streaming Host</option>
                                <option value="YouTubers">YouTubers</option>
                                <option value="Social Media Influencers">Social Media Influencers</option>
                                <option value="Hollywood Artist">Hollywood Artist</option>
                                <option value="Mobile/PC Gamers">Mobile/PC Gamers</option>
                                <option value="Short Video Creators">Short Video Creators</option>
                                <option value="Podcast Hosts">Podcast Hosts</option>
                                <option value="Lifestyle Bloggers/Vloggers">Lifestyle Bloggers/Vloggers</option>
                                <option value="Fitness Influencers">Fitness Influencers</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="followers">Followers</label>
                            <input type="text" id="followers" name="followers" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Employee Fields -->
                <div id="employeeFields" class="hidden">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="role">Role</label>
                            <input type="text" id="role" name="role" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="experience">Experience</label>
                            <input type="text" id="experience" name="experience" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Resume Upload (Only for Employees) -->
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="resume">Resume</label>
                        <div class="flex items-center space-x-4">
                            <a id="currentResume" href="#" target="_blank" class="hidden bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                                <i class="fas fa-file-pdf mr-2"></i>View Current Resume
                            </a>
                            <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Accepted formats: PDF, DOC, DOCX (Max size: 5MB)</p>
                    </div>
                </div>

                <!-- Image Upload -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="image">Profile Image</label>
                    <div class="flex items-center space-x-4">
                        <img id="currentImage" src="" alt="Current Image" class="w-16 h-16 rounded-full object-cover">
                        <input type="file" id="image" name="image" accept="image/*" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-4">
                    <a href="model_agency_dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white p-4 rounded-lg shadow-lg flex items-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mr-3"></div>
            <p class="text-gray-700">Saving changes...</p>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm w-full mx-4">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <i class="fas fa-check text-green-500 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Success!</h3>
                <p class="text-sm text-gray-500 mb-6" id="successModalMessage"></p>
                <button onclick="redirectToDashboard()" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                    Back to Dashboard
                </button>
            </div>
        </div>
    </div>

    <script>
        // Fetch and populate client data
        async function fetchClientData() {
            const urlParams = new URLSearchParams(window.location.search);
            const clientId = urlParams.get('id');
            
            try {
                const response = await fetch(`edit_client.php?id=${clientId}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    const client = data.client;
                    
                    // Set client ID and professional type
                    document.getElementById('clientId').value = client.id;
                    document.getElementById('professional').value = client.professional;
                    
                    // Populate basic fields
                    document.getElementById('name').value = client.name;
                    document.getElementById('age').value = client.age;
                    document.getElementById('phone').value = client.phone;
                    document.getElementById('gender').value = client.gender;
                    document.getElementById('city').value = client.city;
                    document.getElementById('language').value = client.language;
                    
                    // Show appropriate fields based on professional type
                    if (client.professional === 'Artist') {
                        document.getElementById('artistFields').classList.remove('hidden');
                        document.getElementById('employeeFields').classList.add('hidden');
                        document.getElementById('category').value = client.category;
                        document.getElementById('followers').value = client.followers;
                    } else {
                        document.getElementById('employeeFields').classList.remove('hidden');
                        document.getElementById('artistFields').classList.add('hidden');
                        document.getElementById('role').value = client.role;
                        document.getElementById('experience').value = client.experience;
                        
                        // Show current resume if exists
                        if (client.resume_url) {
                            const resumeLink = document.getElementById('currentResume');
                            resumeLink.classList.remove('hidden');
                            
                            // Get the base URL of the website
                            const baseUrl = window.location.origin;
                            
                            // Construct the resume URL
                            const resumeUrl = client.resume_url.startsWith('http') ? 
                                client.resume_url : 
                                `${baseUrl}/${client.resume_url.replace(/^\.\.\//, '')}`;
                            
                            resumeLink.href = resumeUrl;
                        }
                    }
                    
                    // Show current image
                    // Get the base URL of the website
                    const baseUrl = window.location.origin;
                    const imageUrl = client.image_url.startsWith('http') ? 
                        client.image_url : 
                        `${baseUrl}/${client.image_url.replace(/^\.\.\//, '')}`;
                    document.getElementById('currentImage').src = imageUrl;
                    
                } else {
                    showError('Failed to load client data');
                }
            } catch (error) {
                showError('Error loading client data');
                console.error('Error:', error);
            }
        }

        // Handle form submission
        document.getElementById('editClientForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Show loading overlay
            document.getElementById('loadingOverlay').classList.remove('hidden');
            
            try {
                const formData = new FormData(e.target);
                const response = await fetch('edit_client.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                // Hide loading overlay
                document.getElementById('loadingOverlay').classList.add('hidden');
                
                if (data.status === 'success') {
                    // Show success modal
                    document.getElementById('successModalMessage').textContent = data.message;
                    document.getElementById('successModal').classList.remove('hidden');
                } else {
                    showError(data.message);
                }
            } catch (error) {
                // Hide loading overlay
                document.getElementById('loadingOverlay').classList.add('hidden');
                showError('Error updating client');
                console.error('Error:', error);
            }
        });

        function redirectToDashboard() {
            window.location.href = 'model_agency_dashboard.php';
        }

        // Helper functions for showing messages
        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.classList.remove('hidden');
            setTimeout(() => errorDiv.classList.add('hidden'), 5000);
        }

        // Load client data when page loads
        document.addEventListener('DOMContentLoaded', fetchClientData);
    </script>
</body>
</html> 