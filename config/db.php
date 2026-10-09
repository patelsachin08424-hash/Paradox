<?php
/**
 * Database Configuration and Core Utilities
 * University Project: Online Healthcare Management System
 * 
 * VIVA NOTE:
 * - Uses PHP Data Objects (PDO) for secure, prepared SQL execution.
 * - Emulated prepares disabled to ensure actual database-level parameterized queries.
 * - Centralizes session start, CSRF, flash messaging, and Role-Based Access Control (RBAC).
 */

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials (Standard XAMPP / MariaDB configuration)
define('DB_HOST', 'sql111.infinityfree.com');
define('DB_USER', 'if0_42934623');
define('DB_PASS', 'YOUR_HOSTING_PASSWORD');
define('DB_NAME', 'if0_42934623_pulsehealth');
define('DB_PORT', '3306');

// Application configuration
define('APP_NAME', 'PulseHealth');
define('APP_TAGLINE', 'Next-Gen Clinical & Patient Management Portal');
define('BASE_URL', '/'); // Adaptable for local root or subfolder

$pdo = null;
$db_connected = false;
$db_error = null;

try {
    // Attempt connection with UTF-8 charset
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    $db_connected = true;
} catch (PDOException $e) {
    // If the database does not exist or MySQL server isn't running
    $db_error = $e->getMessage();
    $db_connected = false;
}

/**
 * Sanitize User Input against Cross-Site Scripting (XSS)
 * VIVA NOTE: htmlspecialchars escapes HTML entities before rendering.
 */
function sanitize($data) {
    if (is_null($data)) return '';
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Safe redirect helper
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Set a dismissible flash message in session
 */
function setFlash($key, $message, $type = 'info') {
    $_SESSION['flash'][$key] = [
        'message' => $message,
        'type'    => $type // 'success', 'danger', 'warning', 'info'
    ];
}

/**
 * Retrieve and clear flash message
 */
function getFlash($key) {
    if (isset($_SESSION['flash'][$key])) {
        $flash = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $flash;
    }
    return null;
}

/**
 * Check if a user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if the logged-in user matches the expected role
 */
function hasRole($role) {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Guard protected pages against unauthorized access
 */
function requireRole($role, $loginUrl = '../index.php') {
    if (!isLoggedIn() || !hasRole($role)) {
        setFlash('auth_error', 'Access denied. Please sign in with the appropriate credentials.', 'danger');
        redirect($loginUrl);
    }
}

/**
 * Return human-friendly status badge markup
 */
function getStatusBadge($status) {
    switch (strtolower($status)) {
        case 'confirmed':
            return '<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle me-1"></i>Confirmed</span>';
        case 'pending':
            return '<span class="badge bg-warning-subtle text-warning border border-warning-subtle"><i class="bi bi-clock me-1"></i>Pending</span>';
        case 'completed':
            return '<span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bi bi-check2-all me-1"></i>Completed</span>';
        case 'cancelled':
            return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-x-circle me-1"></i>Cancelled</span>';
        case 'active':
            return '<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-dot"></i>Active</span>';
        case 'inactive':
            return '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"><i class="bi bi-dot"></i>Inactive</span>';
        default:
            return '<span class="badge bg-secondary-subtle text-secondary">' . htmlspecialchars($status) . '</span>';
    }
}
