<?php
/**
 * Global Header Partial
 * Includes HTML head, fonts, Bootstrap 5.3 CDN, and custom CSS.
 */
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config/db.php';
}
$pageTitle = isset($pageTitle) ? $pageTitle . ' | ' . APP_NAME : APP_NAME . ' - Modern Healthcare System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Online Healthcare Management System - Clinical portal for patients, doctors, and hospital administrators.">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Custom Healthcare Styling -->
    <link rel="stylesheet" href="<?= isset($assetPrefix) ? $assetPrefix : '' ?>assets/css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">
