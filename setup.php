<?php
/**
 * Automated Database Setup & Seed Script
 * Online Healthcare Management System - University Project
 */

require_once __DIR__ . '/config/db.php';

$message = '';
$status_type = '';
$executed_queries = 0;

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'install') {
    try {
        // Connect to MySQL server without database selected
        $rootPdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $sqlFile = __DIR__ . '/database/schema.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("Database schema file not found at " . $sqlFile);
        }

        $sqlContent = file_get_contents($sqlFile);

        // Execute queries
        $rootPdo->exec($sqlContent);
        $message = "Database 'healthcare_db' initialized successfully! All tables created and demo accounts seeded.";
        $status_type = "success";
    } catch (Exception $e) {
        $message = "Installation Error: " . $e->getMessage();
        $status_type = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup | <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light d-flex align-items-center min-vh-100 py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-9">
                <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-primary text-white p-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white text-primary rounded-3 p-3 shadow-sm">
                                <i class="bi bi-database-fill-gear fs-3"></i>
                            </div>
                            <div>
                                <h3 class="mb-1 fw-bold"><?= APP_NAME ?> Database Setup</h3>
                                <p class="mb-0 text-white-50 small">Automated Schema Migrator & University Viva Demo Seeder</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-<?= $status_type ?> alert-dismissible fade show rounded-3 p-3 mb-4" role="alert">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi <?= $status_type === 'success' ? 'bi-check-circle-fill text-success fs-4' : 'bi-exclamation-triangle-fill text-danger fs-4' ?>"></i>
                                    <div><strong><?= $status_type === 'success' ? 'Setup Complete!' : 'Error!' ?></strong> <?= $message ?></div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <h5 class="fw-bold text-dark mb-2">Setup Instructions</h5>
                            <p class="text-muted small">
                                This automated script executes <code>database/schema.sql</code>, creating all 6 relational tables
                                (<strong>users, patients, doctors, appointments, medical_records, prescriptions</strong>) and populates realistic demo accounts.
                            </p>
                        </div>

                        <div class="bg-white border rounded-3 p-3 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold text-secondary small">MySQL Connection Status:</span>
                                <?php if ($db_connected): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2"><i class="bi bi-check-circle me-1"></i> Connected to healthcare_db</span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2"><i class="bi bi-exclamation-circle me-1"></i> Not Initialized or Server Stopped</span>
                                <?php endif; ?>
                            </div>
                            <div class="small text-muted">
                                <div><strong>Host:</strong> <?= DB_HOST ?>:<?= DB_PORT ?></div>
                                <div><strong>User:</strong> <?= DB_USER ?> (default XAMPP)</div>
                            </div>
                        </div>

                        <div class="card bg-light border-0 rounded-3 p-3 mb-4">
                            <h6 class="fw-bold text-primary mb-2"><i class="bi bi-key-fill me-1"></i> Pre-Configured Viva Demo Accounts</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless small mb-0">
                                    <thead>
                                        <tr class="text-muted">
                                            <th>Role</th>
                                            <th>Email</th>
                                            <th>Password</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="badge bg-danger-subtle text-danger">Admin</span></td>
                                            <td><code>admin@hospital.com</code></td>
                                            <td><code>admin123</code></td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-info-subtle text-info">Doctor</span></td>
                                            <td><code>doctor.smith@hospital.com</code></td>
                                            <td><code>doctor123</code></td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-success-subtle text-success">Patient</span></td>
                                            <td><code>patient.john@gmail.com</code></td>
                                            <td><code>patient123</code></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <form method="POST" action="setup.php" class="d-grid gap-2">
                            <input type="hidden" name="action" value="install">
                            <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-bold py-3 shadow-sm">
                                <i class="bi bi-play-circle-fill me-2"></i> Initialize Database Now
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary rounded-3 py-2">
                                <i class="bi bi-arrow-left me-1"></i> Return to Homepage
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
