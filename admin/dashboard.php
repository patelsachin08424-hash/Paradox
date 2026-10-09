<?php
/**
 * Admin Dashboard & System Analytics
 * University Project: Online Healthcare Management System
 */
require_once __DIR__ . '/../config/db.php';
requireRole('admin', 'login.php');

$stats = [
    'patients' => 0,
    'doctors' => 0,
    'appointments' => 0,
    'users' => 0,
    'prescriptions' => 0,
    'records' => 0
];

$latestAppointments = [];
$latestPatients = [];

if ($db_connected && $pdo) {
    try {
        $stats['patients'] = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
        $stats['doctors'] = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
        $stats['appointments'] = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
        $stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['prescriptions'] = $pdo->query("SELECT COUNT(*) FROM prescriptions")->fetchColumn();
        $stats['records'] = $pdo->query("SELECT COUNT(*) FROM medical_records")->fetchColumn();

        // Latest Appointments
        $stmtApt = $pdo->query("
            SELECT a.*, p.full_name AS patient_name, d.full_name AS doctor_name, d.specialization
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            JOIN doctors d ON a.doctor_id = d.id
            ORDER BY a.created_at DESC
            LIMIT 5
        ");
        $latestAppointments = $stmtApt->fetchAll();

        // Latest Patients
        $stmtPat = $pdo->query("
            SELECT p.*, u.email, u.status AS user_status
            FROM patients p
            JOIN users u ON p.user_id = u.id
            ORDER BY p.created_at DESC
            LIMIT 5
        ");
        $latestPatients = $stmtPat->fetchAll();

    } catch (Exception $e) {
        //
    }
}

$pageTitle = "Hospital Admin Dashboard";
$assetPrefix = "../";
include __DIR__ . '/../includes/header.php';
?>

<div class="portal-wrapper">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="portal-content">
        <header class="portal-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light d-lg-none" id="sidebarToggle" type="button">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <div>
                    <h5 class="mb-0 fw-bold text-dark">Executive Operations Console</h5>
                    <small class="text-muted">Master control panel for PulseHealth hospital system</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="manage-doctors.php" class="btn btn-sm btn-primary rounded-pill px-3">
                    <i class="bi bi-person-plus-fill me-1"></i> Add Doctor
                </a>
                <a href="manage-appointments.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-calendar3 me-1"></i> Master Schedule
                </a>
            </div>
        </header>

        <main class="portal-main">
            <?php 
                $welcome = getFlash('welcome');
                $action = getFlash('action_msg');
                if ($welcome):
            ?>
                <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                    <i class="bi bi-check2-circle me-2"></i><?= $welcome['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($action): ?>
                <div class="alert alert-<?= $action['type'] ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
                    <i class="bi bi-info-circle me-2"></i><?= $action['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Metric Cards -->
            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-sm-6">
                    <div class="stat-card d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Patients Registered</span>
                            <h3 class="fw-bold mb-0 mt-1"><?= $stats['patients'] ?></h3>
                            <small><a href="manage-patients.php" class="text-primary text-decoration-none">Manage Registry &rarr;</a></small>
                        </div>
                        <div class="stat-icon-pill icon-teal">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="stat-card d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Active Doctors</span>
                            <h3 class="fw-bold mb-0 mt-1 text-info"><?= $stats['doctors'] ?></h3>
                            <small><a href="manage-doctors.php" class="text-info text-decoration-none">View Faculty &rarr;</a></small>
                        </div>
                        <div class="stat-icon-pill icon-blue">
                            <i class="bi bi-stethoscope"></i>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="stat-card d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Appointments</span>
                            <h3 class="fw-bold mb-0 mt-1 text-warning"><?= $stats['appointments'] ?></h3>
                            <small><a href="manage-appointments.php" class="text-warning text-decoration-none">View All Visits &rarr;</a></small>
                        </div>
                        <div class="stat-icon-pill icon-amber">
                            <i class="bi bi-calendar-range-fill"></i>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6">
                    <div class="stat-card d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">System Users</span>
                            <h3 class="fw-bold mb-0 mt-1 text-danger"><?= $stats['users'] ?></h3>
                            <small><a href="manage-users.php" class="text-danger text-decoration-none">RBAC Access &rarr;</a></small>
                        </div>
                        <div class="stat-icon-pill icon-rose">
                            <i class="bi bi-shield-shaded"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two-Column Tables Grid -->
            <div class="row g-4">
                <!-- Master Appointments Stream -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <div class="card-header bg-white p-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-calendar3 me-2 text-primary"></i>Recent Appointment Bookings</h6>
                            <a href="manage-appointments.php" class="btn btn-sm btn-outline-primary rounded-pill">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Date & Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($latestAppointments)): ?>
                                        <?php foreach ($latestAppointments as $a): ?>
                                            <tr>
                                                <td class="fw-semibold text-dark"><?= htmlspecialchars($a['patient_name']) ?></td>
                                                <td>
                                                    <div class="small fw-bold"><?= htmlspecialchars($a['doctor_name']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($a['specialization']) ?></small>
                                                </td>
                                                <td class="small">
                                                    <div><?= date('M d, Y', strtotime($a['appointment_date'])) ?></div>
                                                    <div class="text-muted"><?= date('h:i A', strtotime($a['appointment_time'])) ?></div>
                                                </td>
                                                <td><?= getStatusBadge($a['status']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-center py-4 text-muted">No appointments recorded yet.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Patient Registrations -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                        <div class="card-header bg-white p-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-check-fill me-2 text-success"></i>Recent Patient Signups</h6>
                            <a href="manage-patients.php" class="btn btn-sm btn-outline-success rounded-pill">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Contact</th>
                                        <th>Blood</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($latestPatients)): ?>
                                        <?php foreach ($latestPatients as $p): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($p['full_name']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($p['email']) ?></small>
                                                </td>
                                                <td class="small"><?= htmlspecialchars($p['phone']) ?></td>
                                                <td><span class="badge bg-danger-subtle text-danger"><?= htmlspecialchars($p['blood_group']) ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="3" class="text-center py-4 text-muted">No patient accounts found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
