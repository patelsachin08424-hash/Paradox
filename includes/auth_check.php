<?php
/**
 * Role-Based Access Control Guard
 * University Project: Online Healthcare Management System
 */
require_once __DIR__ . '/../config/db.php';

function checkAuth($allowedRoles = []) {
    if (!isLoggedIn()) {
        setFlash('auth_error', 'Please log in to access this portal.', 'warning');
        redirect('../index.php#login-modal');
    }

    if (!empty($allowedRoles)) {
        if (!in_array($_SESSION['role'], (array)$allowedRoles)) {
            setFlash('auth_error', 'Unauthorized access! You do not have permission to view this section.', 'danger');
            
            // Redirect user to their own dashboard based on their current role
            if ($_SESSION['role'] === 'admin') {
                redirect('../admin/dashboard.php');
            } elseif ($_SESSION['role'] === 'doctor') {
                redirect('../doctor/dashboard.php');
            } elseif ($_SESSION['role'] === 'patient') {
                redirect('../patient/dashboard.php');
            } else {
                redirect('../index.php');
            }
        }
    }
}
