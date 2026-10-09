<?php
/**
 * Patient Dashboard
 * University Project: Online Healthcare Management System
 */
require_once __DIR__ . '/../config/db.php';
requireRole('patient', 'login.php');

$patientId = $_SESSION['patient_id'] ?? 0;

$upcomingAppointments = [];
$stats = [
    'appointments' => 0,
    'prescriptions' => 0,
    'records' => 0
];

if ($db_connected && $pdo && $patientId > 0) {
    try {
        // Fetch upcoming appointments
        $stmt = $pdo->prepare("
            SELECT a.*, d.full_name AS doctor_name, d.specialization, d.room_no, d.phone AS doctor_phone
            FROM appointments a
            JOIN doctors d ON a.doctor_id = d.id
            WHERE a.patient_id = ?
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
            LIMIT 5
        ");
        $stmt->execute([$patientId]);
        $upcomingAppointments = $stmt->fetchAll();

        // Count stats
        $stmtStats = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id = ?");
        $stmtStats->execute([$patientId]);
        $stats['appointments'] = $stmtStats->fetchColumn();

        $stmtPresc = $pdo->prepare("SELECT COUNT(*) FROM prescriptions WHERE patient_id = ?");
        $stmtPresc->execute([$patientId]);
        $stats['prescriptions'] = $stmtPresc->fetchColumn();

        $stmtRec = $pdo->prepare("SELECT COUNT(*) FROM medical_records WHERE patient_id = ?");
        $stmtRec->execute([$patientId]);
        $stats['records'] = $stmtRec->fetchColumn();

    } catch (Exception $e) {
        // graceful
    }
}

// Handle quick appointment cancel from dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_appointment_id'])) {
    $cancelId = (int)$_POST['cancel_appointment_id'];
    try {
        $stmtCancel = $pdo->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = ? AND patient_id = ?");
        $stmtCancel->execute([$cancelId, $patientId]);
        setFlash('action_msg', 'Appointment #'.$cancelId.' cancelled successfully.', 'warning');
        redirect('dashboard.php');
    } catch (Exception $e) {
        setFlash('action_msg', 'Could not cancel appointment: ' . $e->getMessage(), 'danger');
    }
}

$pageTitle = "Patient Dashboard";
$assetPrefix = "../";
include __DIR__ . '/../includes/header.php';
?>

<div class="portal-wrapper">
    <!-- Patient Sidebar -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="portal-content">
        <!-- Topbar -->
        <header class="portal-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light d-lg-none" id="sidebarToggle" type="button">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <h5 class="mb-0 fw-bold text-dark">Patient Dashboard</h5>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                    <i class="bi bi-check-circle-fill me-1"></i> Patient Account Active
                </span>
                <a href="book-appointment.php" class="btn btn-sm btn-primary rounded-pill px-3">
                    <i class="bi bi-calendar-plus me-1"></i> New Booking
                </a>
            </div>
        </header>

        <!-- Main Body -->
        <main class="portal-main">
            <!-- Flash Message Alerts -->
            <?php 
                $welcomeFlash = getFlash('welcome');
                $actionFlash = getFlash('action_msg');
                if ($welcomeFlash):
            ?>
                <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                    <i class="bi bi-check2-circle me-2"></i><?= $welcomeFlash['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($actionFlash): ?>
                <div class="alert alert-<?= $actionFlash['type'] ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
                    <i class="bi bi-info-circle me-2"></i><?= $actionFlash['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Metrics Cards -->
            <div class="row g-3 mb-4">
                <div class="col-lg-4 col-md-6">
                    <div class="stat-card d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Appointments</span>
                            <h3 class="fw-bold mb-0 mt-1"><?= $stats['appointments'] ?></h3>
                            <small class="text-primary"><a href="appointments.php" class="text-decoration-none">Manage Schedule &rarr;</a></small>
                        </div>
                        <div class="stat-icon-pill icon-teal">
                            <i class="bi bi-calendar2-check-fill"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="stat-card d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Medical Records</span>
                            <h3 class="fw-bold mb-0 mt-1"><?= $stats['records'] ?></h3>
                            <small class="text-primary"><a href="records.php" class="text-decoration-none">Review Diagnoses &rarr;</a></small>
                        </div>
                        <div class="stat-icon-pill icon-blue">
                            <i class="bi bi-clipboard2-pulse-fill"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <div class="stat-card d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Active Prescriptions</span>
                            <h3 class="fw-bold mb-0 mt-1"><?= $stats['prescriptions'] ?></h3>
                            <small class="text-primary"><a href="prescriptions.php" class="text-decoration-none">View Medication Plan &rarr;</a></small>
                        </div>
                        <div class="stat-icon-pill icon-rose">
                            <i class="bi bi-capsule"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Action Banners -->
            <div class="card border-0 bg-primary text-white rounded-4 p-4 mb-4 shadow-sm">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="fw-bold mb-1">Need to consult a Medical Specialist?</h4>
                        <p class="mb-0 text-white-50 small">Schedule visits with our experienced cardiologists, pediatricians, or neurologists in just two clicks.</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="book-appointment.php" class="btn btn-light text-primary fw-bold rounded-pill px-4 py-2 me-2">
                            <i class="bi bi-calendar-plus me-1"></i> Book Now
                        </a>
                        <a href="doctors.php" class="btn btn-outline-light rounded-pill px-3 py-2">
                            <i class="bi bi-search me-1"></i> Doctors
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Appointments Table -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white p-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-calendar3 me-2 text-primary"></i>My Recent Appointments</h6>
                    <a href="appointments.php" class="btn btn-sm btn-outline-primary rounded-pill">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Doctor & Specialty</th>
                                <th>Date & Time</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($upcomingAppointments)): ?>
                                <?php foreach ($upcomingAppointments as $apt): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($apt['doctor_name']) ?></div>
                                            <span class="badge bg-light text-secondary border small"><?= htmlspecialchars($apt['specialization']) ?></span>
                                            <small class="text-muted ms-2"><i class="bi bi-door-open"></i> <?= htmlspecialchars($apt['room_no']) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><i class="bi bi-calendar me-1 text-primary"></i><?= date('M d, Y', strtotime($apt['appointment_date'])) ?></div>
                                            <small class="text-muted"><i class="bi bi-clock me-1"></i><?= date('h:i A', strtotime($apt['appointment_time'])) ?></small>
                                        </td>
                                        <td>
                                            <span class="small text-secondary"><?= htmlspecialchars(substr($apt['reason'], 0, 45)) ?><?= strlen($apt['reason']) > 45 ? '...' : '' ?></span>
                                        </td>
                                        <td>
                                            <?= getStatusBadge($apt['status']) ?>
                                        </td>
                                        <td class="text-end">
                                            <?php if ($apt['status'] === 'Pending' || $apt['status'] === 'Confirmed'): ?>
                                                <form method="POST" action="dashboard.php" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                                    <input type="hidden" name="cancel_appointment_id" value="<?= $apt['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                        <i class="bi bi-x-circle me-1"></i>Cancel
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted small">No action</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-calendar-x fs-2 d-block mb-2 text-secondary"></i>
                                        You have no recorded appointments yet.
                                        <div class="mt-2">
                                            <a href="book-appointment.php" class="btn btn-sm btn-primary rounded-pill">Book Your First Appointment</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
