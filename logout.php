<?php
/**
 * Secure User Logout Script
 * University Project: Online Healthcare Management System
 */
require_once __DIR__ . '/config/db.php';

// Clear session data completely
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Redirect to home with a clean flash notification
session_start();
setFlash('auth_success', 'You have been safely logged out. Have a healthy day!', 'success');
redirect('index.php');
