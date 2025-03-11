<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// ✅ Allow both Admin & Super Admin
if (!in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    echo "Access Denied! Only admins and super admins can access this page.";
    exit();
}
?>
