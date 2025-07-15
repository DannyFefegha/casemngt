<?php
// admin_logout.php - Admin logout functionality
require_once 'config.php';

// Log the logout activity if admin is logged in
if (isAdmin()) {
    logActivity($pdo, null, $_SESSION['admin_id'], 'Admin Logout', 'Admin logged out');
}

// Destroy the session
session_destroy();

// Clear any remember me cookies if they exist
if (isset($_COOKIE['admin_remember'])) {
    setcookie('admin_remember', '', time() - 3600, '/');
}

// Redirect to login page with a success message
header("Location: admin_login.php?message=logout_success");
exit;
?>