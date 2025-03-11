<?php
require '../includes/db_connect.php';

// Set the number of blogs per page
$blogsPerPage = 20;

// Get the current page number from the URL, default is page 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Calculate the OFFSET for SQL
$offset = ($page - 1) * $blogsPerPage;

// Fetch blog posts with LIMIT for pagination
$result = $conn->query("SELECT id, title, content, image_url, created_at FROM blogs ORDER BY created_at DESC LIMIT $blogsPerPage OFFSET $offset");

// Get the total number of blogs
$totalBlogs = $conn->query("SELECT COUNT(*) as count FROM blogs")->fetch_assoc()['count'];
$totalPages = ceil($totalBlogs / $blogsPerPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Latest Blog Posts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <link rel="stylesheet" href="../public/index.css">
</head>
<body class="bg-gray-50">

<!-- ✅ Navbar -->
<nav class="bg-white shadow-md fixed top-0 left-0 w-full z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mobile-navbar md:flex md:items-center md:justify-between h-16"> <!-- ✅ FIXED -->

            <!-- ✅ Logo (Left Side) -->
            <a href="../index.php" class="logo flex items-center">
                <img src="../public/logo.png" alt="Logo" class="h-10 w-auto">
            </a>

            <!-- ✅ Mobile Menu Button (Right Side) -->
            <button id="menu-toggle" class="menu-toggle md:hidden text-gray-600 text-3xl focus:outline-none">
                ☰
            </button>

            <!-- ✅ Desktop Menu (Hidden on Mobile) -->
            <div class="hidden md:flex space-x-6">
                <a href="../index.php" class="text-gray-700 hover:text-red-500 transition font-semibold">Home</a>
                <a href="../pages/about-page/about.html" class="text-gray-700 hover:text-red-500 transition font-semibold">About</a>
                <a href="#blog-section" class="text-gray-700 hover:text-red-500 transition font-semibold">Blogs</a>
                <a href="../pages/our-services-page/service.html" class="text-gray-700 hover:text-red-500 transition font-semibold">Services</a>
                <a href="../pages/contact-page/contact-page.html" class="text-gray-700 hover:text-red-500 transition font-semibold">Contact</a>
            </div>
        </div>
    </div>

    <!-- ✅ Mobile Menu (Dropdown) -->
    <div id="mobile-menu" class="hidden md:hidden bg-white shadow-md w-full py-4 px-6">
        <a href="../index.php" class="block py-2 text-gray-700 hover:text-red-500 transition font-semibold">Home</a>
        <a href="../pages/about-page/about.html" class="block py-2 text-gray-700 hover:text-red-500 transition font-semibold">About</a>
        <a href="#blog-section" class="block py-2 text-gray-700 hover:text-red-500 transition font-semibold">Blogs</a>
        <a href="../pages/our-services-page/service.html" class="block py-2 text-gray-700 hover:text-red-500 transition font-semibold">Services</a>
        <a href="../pages/contact-page/contact-page.html" class="block py-2 text-gray-700 hover:text-red-500 transition font-semibold">Contact</a>
    </div>
</nav>

<!-- ✅ JavaScript for Mobile Menu -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const menuToggle = document.getElementById('menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');

        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    });
</script>


<!-- ✅ Hero Section - Alternative Modern Design -->
<header class="relative w-full h-[550px] md:h-[700px] lg:h-[850px] flex items-center justify-center overflow-hidden px-4">
    
    <!-- ✅ Background with Dual Gradient Overlay -->
    <div class="absolute inset-0">
        <img src="../images/blogging-page/blogging.webp" 
             alt="Unlock Digital Success" 
             class="w-full h-full object-cover brightness-75 transition-transform duration-500 ease-in-out md:hover:scale-110">
        <div class="absolute inset-0 bg-gradient-to-br from-black/80 via-black/50 to-red-900/70"></div>
    </div>

    <!-- ✅ Hero Content Wrapper -->
    <div class="relative z-10 max-w-4xl w-full text-center px-6 py-12">
        
        <!-- ✅ Stylish Text with Gradient & Focus Effect -->
        <h1 class="text-4xl md:text-6xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white via-gray-200 to-gray-500 leading-tight tracking-wide animate-fade-in">
            Unlock Your Digital Potential
        </h1>

        <!-- ✅ Subheading with Increased Readability -->
        <p class="text-lg md:text-xl mt-4 text-gray-300 leading-relaxed animate-fade-up">
            Transform your online presence with expert strategies, powerful insights, and cutting-edge trends in business, technology, and digital marketing.
        </p>

        <!-- ✅ Call to Action Buttons (New Style) -->
        <div class="mt-6 flex flex-wrap justify-center gap-4">
            <a href="#blog-section" 
               class="px-6 py-3 text-lg font-semibold text-white bg-gradient-to-r from-red-500 to-red-700 rounded-lg shadow-md transition-all duration-500 ease-in-out hover:from-red-600 hover:to-red-800 hover:shadow-lg hover:-translate-y-1">
                Start Exploring →
            </a>
            <a href="../pages/contact-page/contact-page.html" 
               class="px-6 py-3 text-lg font-semibold text-gray-900 bg-white rounded-lg shadow-md transition-all duration-500 ease-in-out hover:bg-gray-200 hover:shadow-lg hover:-translate-y-1">
                Get in Touch →
            </a>
        </div>
    </div>

</header>

<!-- ✅ Custom Animations -->
<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fadeIn 1.2s ease-out;
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(50px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-up {
        animation: fadeUp 1.5s ease-out;
    }
</style>

<!-- ✅ Blog Timeline Container -->
<div class="flex justify-center py-12 px-4 md:px-8" id="blog-section">
    <div class="w-full max-w-7xl">
        <h2 class="text-center text-3xl md:text-4xl font-bold text-red-600 mb-12 uppercase tracking-wide font-playfair">
            Latest Blog Posts
        </h2>

        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="flex flex-col md:flex-row items-center md:items-center mb-16 relative w-full">
                
                <!-- ✅ Left Section: Profile Image & Date -->
                <div class="relative flex flex-col items-center justify-center text-center md:w-1/5">
                    <div class="relative w-32 h-32 md:w-36 md:h-36 rounded-full border-[3px] border-gray-500 shadow-md overflow-hidden transition-transform duration-300 hover:scale-105">
                        <img src="<?= $row['image_url']; ?>" alt="Blog Image" class="w-full h-full object-cover">
                    </div>
                    <div class="mt-3">
                        <p class="text-gray-500 text-sm md:text-lg font-roboto tracking-wide"><?= date('d F', strtotime($row['created_at'])); ?></p>
                        <p class="text-gray-800 font-bold text-2xl md:text-3xl font-playfair tracking-wide"><?= date('H:i', strtotime($row['created_at'])); ?></p>
                    </div>
                </div>

                <!-- ✅ Right Section: Speech Bubble Blog Content -->
                <div class="relative bg-white p-6 md:p-10 border border-gray-300 shadow-lg rounded-[50px] mt-6 md:mt-0 md:ml-10 w-full transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 speech-bubble">
                <h3 class="text-xl md:text-3xl font-bold text-gray-800 leading-tight hover:text-red-600 transition duration-200 font-playfair">
    <a href="post.php?id=<?= $row['id']; ?>"><?= htmlspecialchars($row['title']); ?></a>
</h3>
<p class="text-gray-600 mt-3 md:mt-4 font-poppins text-base md:text-lg">
    <?= substr(nl2br(preg_replace('/<a (.*?)>(.*?)<\/a>/i', '<a $1 class="text-red-600 font-semibold hover:text-red-800 transition" target="_blank">$2</a>', htmlspecialchars_decode($row['content']))), 0, 200) . '...'; ?>
</p>

<p class="text-sm md:text-md text-gray-500 mt-3 md:mt-4 font-roboto tracking-wide">
    By Our Experts
</p>


                    <!-- Buttons (Share & Read More) -->
                    <div class="flex flex-col md:flex-row md:items-center justify-end mt-4 md:mt-6 space-y-2 md:space-y-0 md:space-x-4">
                        <a href="post.php?id=<?= $row['id']; ?>" class="px-5 md:px-6 py-2 md:py-3 border-2 border-red-400 text-red-500 font-semibold rounded-md hover:bg-red-700 hover:text-white transition shadow-md">
                            READ MORE
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>

        <!-- ✅ Pagination -->
<div class="bg-gray-900 text-white p-6 rounded-lg shadow-md text-center mt-10">
    <p class="text-sm md:text-lg">
        Showing <span class="font-bold"><?= ($offset + 1); ?></span> to 
        <span class="font-bold"><?= min(($offset + $blogsPerPage), $totalBlogs); ?></span> of 
        <span class="font-bold"><?= $totalBlogs; ?></span> Entries
    </p>

    <!-- ✅ Styled Pagination Buttons -->
    <div class="flex justify-center mt-4 space-x-2">
        <!-- Previous Button -->
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1; ?>" 
               class="px-4 md:px-5 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition">
                Previous
            </a>
        <?php else: ?>
            <span class="px-4 md:px-5 py-2 bg-gray-800 text-gray-500 rounded-lg cursor-not-allowed">
                Previous
            </span>
        <?php endif; ?>

        <!-- Page Numbers -->
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i == $page): ?>
                <span class="px-4 md:px-5 py-2 bg-gray-700 text-white font-bold rounded-lg shadow-md">
                    <?= $i; ?>
                </span>
            <?php else: ?>
                <a href="?page=<?= $i; ?>" 
                   class="px-4 md:px-5 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-500 transition">
                    <?= $i; ?>
                </a>
            <?php endif; ?>
        <?php endfor; ?>

        <!-- Next Button -->
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1; ?>" 
               class="px-4 md:px-5 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition">
                Next
            </a>
        <?php else: ?>
            <span class="px-4 md:px-5 py-2 bg-gray-800 text-gray-500 rounded-lg cursor-not-allowed">
                Next
            </span>
        <?php endif; ?>
    </div>
</div>


    </div>
</div>

<!-- ✅ Footer Section -->
<footer class="bg-black text-white text-center py-6 mt-12">
    <div class="container mx-auto px-4">
        <!-- Footer Navigation Links -->
        <ul class="flex flex-wrap justify-center space-x-4 md:space-x-6 text-sm md:text-base font-medium">
            <li><a href="../index.php" class="hover:text-gray-400 transition">Home</a></li>
            <li><a href="../pages/about-page/about.html" class="hover:text-gray-400 transition">About</a></li>
            <li><a href="../pages/contact-page/contact-page.html" class="hover:text-gray-400 transition">Contact</a></li>
            <li><a href="privacy.php" class="hover:text-gray-400 transition">Privacy Policy</a></li>
        </ul>

        <!-- Divider -->
        <div class="w-full border-t border-gray-700 my-4"></div>

        <!-- Social Media Icons -->
        <div class="flex justify-center space-x-6">
            <a href="https://youtu.be/tw-xk-Pb-zA?si=QMTwuvhEuTegpqDr" target="_blank" class="text-gray-400 hover:text-white transition text-lg">
                <i class="fab fa-youtube"></i>
            </a>
            <a href="https://www.linkedin.com/company/sortout-innovation/" target="_blank" class="text-gray-400 hover:text-white transition text-lg">
                <i class="fab fa-linkedin-in"></i>
            </a>
            <a href="https://www.facebook.com/profile.php?id=61556452066209" target="_blank" class="text-gray-400 hover:text-white transition text-lg">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="https://www.instagram.com/sortout_innovation" target="_blank" class="text-gray-400 hover:text-white transition text-lg">
                <i class="fab fa-instagram"></i>
            </a>
        </div>

        <!-- Divider -->
        <div class="w-full border-t border-gray-700 my-4"></div>

        <!-- Copyright Text -->
        <p class="text-sm md:text-base text-gray-500">&copy; 2025 Sortout Innovation | All Rights Reserved.</p>
    </div>
</footer>

</body>
</html>
