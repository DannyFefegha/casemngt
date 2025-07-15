<?php
// config.php - Database configuration and common functions

// Database configuration - UPDATE THESE WITH YOUR CPANEL DETAILS
$host = 'localhost';                    // Usually 'localhost' for cPanel
$dbname = 'your_database_name';         // Your database name from cPanel
$username = 'your_db_username';         // Your database username
$password = 'your_db_password';         // Your database password

// Create PDO connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Start session
session_start();

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Common functions
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateCaseId() {
    return 'CASE' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

function logActivity($pdo, $case_id, $admin_id, $action, $details = '') {
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (case_id, admin_id, action, details) VALUES (?, ?, ?, ?)");
        $stmt->execute([$case_id, $admin_id, $action, $details]);
    } catch(PDOException $e) {
        // Log error but don't break the application
        error_log("Activity logging failed: " . $e->getMessage());
    }
}

function isAdmin() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function redirectIfNotAdmin() {
    if (!isAdmin()) {
        header("Location: admin_login.php");
        exit;
    }
}

// Time zone setting
date_default_timezone_set('America/New_York');

// Error reporting for development (disable in production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
?>