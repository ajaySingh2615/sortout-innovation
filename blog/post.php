<?php
require '../includes/db_connect.php';

if (!isset($_GET['id'])) {
    die("Invalid blog ID.");
}

$blog_id = $_GET['id'];

// Fetch the blog post details
$stmt = $conn->prepare("SELECT title, content, image_url, created_at FROM blogs WHERE id = ?");
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$result = $stmt->get_result();
$blog = $result->fetch_assoc();
$stmt->close();

if (!$blog) {
    die("Blog post not found.");
}

// Fetch related blogs (excluding current blog)
$relatedStmt = $conn->prepare("SELECT id, title, image_url FROM blogs WHERE id != ? ORDER BY created_at DESC LIMIT 6");
$relatedStmt->bind_param("i", $blog_id);
$relatedStmt->execute();
$relatedBlogs = $relatedStmt->get_result();
$relatedStmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($blog['title']); ?></title>

    <!-- ✅ Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- ✅ Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- ✅ AOS for Scroll Animations -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            /* background: linear-gradient(to right, #ccc, #ccc); */
            color: #333;
        }

        /* Responsive Font Sizes */
    h1 {
        font-size: 1.75rem;
    }
    @media (min-width: 768px) {
        h1 {
            font-size: 2.5rem;
        }
    }

    /* Ensure hyperlinks in content are clearly visible */
    .prose a {
        color: #2563eb;
        text-decoration: underline;
        font-weight: 600;
    }
    .prose a:hover {
        color: #1e40af;
    }

    /* Styled Blockquotes */
    .prose blockquote {
        border-left: 4px solid #2563eb;
        padding-left: 1rem;
        font-style: italic;
        color: #374151;
        background-color: #f9fafb;
        padding: 12px;
        border-radius: 6px;
        margin: 1.5rem 0;
    }

    /* Code Blocks & Inline Code */
    .prose pre {
        background-color: #f3f4f6;
        padding: 1rem;
        border-radius: 8px;
        overflow-x: auto;
        font-family: "Courier New", monospace;
        font-size: 0.95rem;
        margin-top: 1.5rem;
    }

    .prose code {
        background-color: #e5e7eb;
        padding: 3px 6px;
        border-radius: 4px;
        font-size: 0.95rem;
        font-family: monospace;
    }

    /* Lists */
    .prose ul {
        padding-left: 1.5rem;
        margin-bottom: 1.5rem;
        list-style-type: disc;
    }

    .prose ol {
        padding-left: 1.5rem;
        margin-bottom: 1.5rem;
        list-style-type: decimal;
    }

    /* Responsive Button Layout */
    .flex-wrap {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
    }

        
    </style>
</head>
<body>

<!-- ✅ Responsive Navbar -->
<nav class="bg-white-600 p-4 shadow-lg px-6 md:px-12">
    <div class="container mx-auto flex justify-between items-center">
        <!-- ✅ Logo -->
        <a href="../index.php" class="text-black text-2xl font-bold flex items-center">
            <img src="../public/logo.png" alt="Logo" class="h-10 mr-2">
        </a>

        <!-- ✅ Mobile Menu Button -->
        <button id="menu-toggle" class="text-black text-2xl md:hidden">
            ☰
        </button>

        <!-- ✅ Navigation Links -->
        <ul id="nav-links" class="hidden md:flex space-x-6 text-black font-medium">
            <li><a href="../index.php" class="hover:text-red-400">Home</a></li>
            <li><a href="../pages/about-page/about.html" class="hover:text-red-400">About</a></li>
            <li><a href="../blog/index.php" class="hover:text-red-400">Blog</a></li>
            <li><a href="../pages/our-services-page/service.html" class="hover:text-red-400">Services</a></li>
            <li><a href="../pages/contact-page/contact-page.html" class="hover:text-red-400">Contact</a></li>

            <!-- <li><a href="add_blog.php" class="hover:text-gray-200">Add Blog</a></li> -->
            
            <!-- ✅ Logout Button -->
            <!-- <li>
                <a href="../auth/logout.php" class="bg-white text-red-600 px-4 py-2 rounded-md hover:bg-gray-200 transition">
                    Logout
                </a>
            </li> -->
        </ul>
    </div>

    <!-- ✅ Mobile Menu (Hidden by Default) -->
    <div id="mobile-menu" class="hidden md:hidden bg-white-700 text-black flex flex-col items-center py-4 space-y-4">
        <a href="../index.php" class="hover:text-gray-200">Home</a>
        <a href="../pages/about-page/about.html" class="hover:text-gray-200">About</a>
        <a href="/blog/index.php" class="hover:text-gray-200">Blog</a>
        <a href="../pages/our-services-page/service.html" class="hover:text-gray-200">Services</a>
        <a href="../pages/contact-page/contact-page.html" class="hover:text-gray-200">Contact</a>
        <!-- <a href="../auth/logout.php" class="bg-white text-red-600 px-4 py-2 rounded-md hover:bg-gray-200 transition">
            🚪 Logout
        </a> -->
    </div>
</nav>

<!-- ✅ Navbar JavaScript -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const menuToggle = document.getElementById("menu-toggle");
    const mobileMenu = document.getElementById("mobile-menu");

    menuToggle.addEventListener("click", function () {
        mobileMenu.classList.toggle("hidden");
    });
});
</script>


<?php
// Get the current page URL dynamically
$page_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>

<!-- ✅ Blog Content Section (Fully Responsive) -->
<div class="max-w-5xl mx-auto mt-8 p-6 md:p-10 bg-white text-gray-900 shadow-lg rounded-xl border border-gray-200" data-aos="fade-up">

    <!-- Blog Title -->
    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 leading-tight border-l-8 border-blue-500 pl-4">
        <?= htmlspecialchars($blog['title']); ?>
    </h1>

    <!-- Blog Metadata -->
    <div class="flex items-center text-gray-600 text-base md:text-lg mt-3 md:mt-4 mb-6">
        <span class="italic">Published on <?= date('F j, Y', strtotime($blog['created_at'])); ?></span>
    </div>

    <!-- Blog Image -->
    <figure class="w-full flex justify-center mb-6">
        <img src="<?= $blog['image_url']; ?>" class="w-full max-w-[700px] md:max-w-[900px] object-cover rounded-lg shadow-md" alt="Blog Image">
        
    </figure>

    <!-- Blog Content (With Responsive Text & Links) -->
    <article class="text-gray-800 text-base md:text-lg leading-7 md:leading-8 tracking-wide font-[Montserrat] prose lg:prose-lg max-w-none">
        <?php
        // ✅ Preserve formatting and ensure hyperlinks are styled
        echo html_entity_decode($blog['content'], ENT_QUOTES, 'UTF-8');
        ?>
    </article>

    <!-- ✅ Social Share Section (Responsive) -->
    <div class="mt-10 p-6 bg-gray-100 rounded-lg text-center shadow">
        <p class="text-gray-700 text-lg font-semibold">Share this article anywhere!</p>

        <!-- Hidden Input to Store the Page URL for Copying -->
        <input type="text" id="blogLink" value="<?= $page_url; ?>" class="hidden">

        <div class="mt-4 flex flex-wrap justify-center gap-3 md:gap-4">
            <!-- Copy Buttons (Now Fully Responsive) -->
            <button onclick="copyLink()" class="px-4 py-2 md:px-5 md:py-3 bg-blue-500 text-white text-sm md:text-lg font-semibold rounded-lg shadow-md hover:bg-blue-600 transition">
                Copy Twitter Link
            </button>

            <button onclick="copyLink()" class="px-4 py-2 md:px-5 md:py-3 bg-blue-700 text-white text-sm md:text-lg font-semibold rounded-lg shadow-md hover:bg-blue-800 transition">
                Copy LinkedIn Link
            </button>

            <button onclick="copyLink()" class="px-4 py-2 md:px-5 md:py-3 bg-blue-600 text-white text-sm md:text-lg font-semibold rounded-lg shadow-md hover:bg-blue-700 transition">
                Copy Facebook Link
            </button>

            <button onclick="copyLink()" class="px-4 py-2 md:px-5 md:py-3 bg-green-500 text-white text-sm md:text-lg font-semibold rounded-lg shadow-md hover:bg-green-600 transition">
                Copy WhatsApp Link
            </button>
        </div>

        <!-- Copy Success Message -->
        <p id="copyMessage" class="text-green-600 font-semibold mt-3 hidden">✅ Link Copied!</p>
    </div>
</div>

<!-- ✅ JavaScript for Copy Functionality -->
<script>
    function copyLink() {
        var copyText = document.getElementById("blogLink");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);

        document.getElementById("copyMessage").classList.remove("hidden");
        setTimeout(() => {
            document.getElementById("copyMessage").classList.add("hidden");
        }, 2000);
    }
</script>


<!-- ✅ Related Blogs Section -->
<h3 class="text-center text-black text-3xl md:text-4xl font-bold mt-12" data-aos="fade-up">🔗 Related Blogs</h3>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 mt-6">
    <div class="flex flex-wrap justify-center gap-6">
        <?php while ($related = $relatedBlogs->fetch_assoc()): ?>
            <a href="post.php?id=<?= $related['id']; ?>" 
                class="group block bg-white rounded-xl overflow-hidden shadow-lg transition-transform duration-300 hover:scale-105 hover:shadow-2xl" 
                data-aos="fade-up">
                
                <!-- Image with Square Aspect Ratio -->
                <div class="relative w-[280px] md:w-[320px] aspect-square overflow-hidden">
                    <img src="<?= $related['image_url']; ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" alt="Blog Image">
                    
                    <!-- Overlay Gradient Effect -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-80"></div>

                    <!-- Category Badge (Optional) -->
                    <span class="absolute top-3 left-3 bg-blue-500 text-white text-xs px-3 py-1 rounded-full shadow">
                        Blog
                    </span>
                </div>

                <!-- Blog Content -->
                <div class="p-5 text-center">
                    <h5 class="text-lg md:text-xl font-semibold text-gray-800 leading-snug group-hover:text-red-500 transition">
                        <?= htmlspecialchars($related['title']); ?>
                    </h5>
                    <p class="text-sm text-gray-500 mt-2">Read More →</p>
                </div>
            </a>
        <?php endwhile; ?>
    </div>
</div>

<!-- ✅ Additional Styling -->
<style>
    /* Responsive Grid */
    @media (max-width: 640px) {
        .flex-wrap {
            flex-direction: column;
            align-items: center;
        }
    }
</style>



<!-- ✅ Back Button -->
<a href="index.php" 
    class="fixed bottom-5 left-5 md:bottom-8 md:left-8 bg-red-600 text-white px-4 py-2 md:px-5 md:py-3 text-sm md:text-base font-semibold rounded-full shadow-lg hover:bg-red-700 hover:scale-105 transition duration-300 flex items-center gap-2">
    
    <!-- Back Icon -->
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 md:w-5 md:h-5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
    </svg>

    Back
</a>


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



<!-- ✅ Tailwind JS & AOS -->
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init();
</script>

</body>
</html>
