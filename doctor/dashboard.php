<?php
/**
 * Doctor Dashboard
 * University Project: Online Healthcare Management System
 */
require_once __DIR__ . '/../config/db.php';
requireRole('doctor', 'login.php');

$doctorId = $_SESSION['doctor_id'] ?? 0;
$todayDate = date('Y-m-d');

$todayAppointments = [];
$stats = [
    'today' => 0,
    'pending' => 0,
    'completed' => 0,
    'prescriptions' => 0
];

if ($db_connected && $pdo && $doctorId > 0) {
    try {
        // Today's appointments
        $stmtToday = $pdo->prepare("
            SELECT a.*, p.full_name AS patient_name, p.phone AS patient_phone, p.blood_group, p.gender
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            WHERE a.doctor_id = ? AND a.appointment_date = ?
            ORDER BY a.appointment_time ASC
        ");
        $stmtToday->execute([$doctorId, $todayDate]);
        $todayAppointments = $stmtToday->fetchAll();

        // Count stats
        $stmtCountToday = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date = ?");
        $stmtCountToday->execute([$doctorId, $todayDate]);
        $stats['today'] = $stmtCountToday->fetchColumn();

        $stmtCountPending = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND status = 'Pending'");
        $stmtCountPending->execute([$doctorId]);
        $stats['pending'] = $stmtCountPending->fetchColumn();

        $stmtCountComp = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND status = 'Completed'");
        $stmtCountComp->execute([$doctorId]);
        $stats['completed'] = $stmtCountComp->fetchColumn();

        $stmtCountPrx = $pdo->prepare("SELECT COUNT(*) FROM prescriptions WHERE doctor_id = ?");
        $stmtCountPrx->execute([$doctorId]);
        $stats['prescriptions'] = $stmtCountPrx->fetchColumn();

    } catch (Exception $e) {
        //
    }
}

// Handle status updates from dashboard (Confirm / Reject / Complete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['appointment_id'])) {
    $aptId = (int)$_POST['appointment_id'];
    $newStatus = sanitize($_POST['action']);

    if (in_array($newStatus, ['Confirmed', 'Cancelled', 'Completed'])) {
        try {
            $stmtUp = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?");
            $stmtUp->execute([$newStatus, $aptId, $doctorId]);
            setFlash('action_msg', "Appointment #APT-{$aptId} status updated to '{$newStatus}'.", 'success');
            redirect('dashboard.php');
        } catch (Exception $e) {
            setFlash('action_msg', 'Failed to update appointment: ' . $e->getMessage(), 'danger');
        }
    }
}

$pageTitle = "Physician Dashboard";
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
                    <h5 class="mb-0 fw-bold text-dark">Clinical Dashboard</h5>
                    <small class="text-muted">Today: <?= date('l, F j, Y') ?></small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="add-record.php" class="btn btn-sm btn-primary rounded-pill px-3">
                    <i class="bi bi-plus-circle me-1"></i> New Record
                </a>
                <a href="create-prescription.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="bi bi-file-earmark-plus me-1"></i> Write e-Rx
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

            <!-- Metrics Overview -->
            <div class="row g-3 mb-4">
                <div class="col-lg-3 col-sm-6">
                    <div class="stat-card d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Today's Visits</span>
                            <h3 class="fw-bold mb-0 mt-1"><?= $stats['today'] ?></h3>
                        </div>
                        <div class="stat-icon-pill icon-teal">
                            <i class="bi bi-calendar-check-fill"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <div class="stat-card d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Pending Review</span>
                            <h3 class="fw-bold mb-0 mt-1 text-warning"><?= $stats['pending'] ?></h3>
                        </div>
                        <div class="stat-icon-pill icon-amber">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <div class="stat-card d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Completed Consults</span>
                            <h3 class="fw-bold mb-0 mt-1 text-success"><?= $stats['completed'] ?></h3>
                        </div>
                        <div class="stat-icon-pill icon-emerald">
                            <i class="bi bi-check2-all"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <div class="stat-card d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold text-uppercase">Prescriptions</span>
                            <h3 class="fw-bold mb-0 mt-1"><?= $stats['prescriptions'] ?></h3>
                        </div>
                        <div class="stat-icon-pill icon-rose">
                            <i class="bi bi-capsule"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Schedule Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white p-3 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock me-2 text-primary"></i>Today's Patient Schedule (<?= date('M d, Y') ?>)</h6>
                    </div>
                    <a href="appointments.php" class="btn btn-sm btn-outline-primary rounded-pill">View All Dates</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Patient Info</th>
                                <th>Reported Symptoms</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($todayAppointments)): ?>
                                <?php foreach ($todayAppointments as $apt): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><i class="bi bi-clock text-primary me-1"></i><?= date('h:i A', strtotime($apt['appointment_time'])) ?></div>
                                            <small class="text-muted">#APT-<?= $apt['id'] ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($apt['patient_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($apt['gender']) ?> &bull; Blood: <span class="badge bg-danger-subtle text-danger"><?= htmlspecialchars($apt['blood_group']) ?></span></small>
                                        </td>
                                        <td>
                                            <span class="small text-secondary"><?= htmlspecialchars($apt['reason']) ?></span>
                                        </td>
                                        <td>
                                            <?= getStatusBadge($apt['status']) ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <?php if ($apt['status'] === 'Pending'): ?>
                                                    <form method="POST" action="dashboard.php" class="d-inline">
                                                        <input type="hidden" name="appointment_id" value="<?= $apt['id'] ?>">
                                                        <input type="hidden" name="action" value="Confirmed">
                                                        <button type="submit" class="btn btn-success" title="Accept"><i class="bi bi-check-lg"></i> Confirm</button>
                                                    </form>
                                                    <form method="POST" action="dashboard.php" class="d-inline" onsubmit="return confirm('Reject this appointment?');">
                                                        <input type="hidden" name="appointment_id" value="<?= $apt['id'] ?>">
                                                        <input type="hidden" name="action" value="Cancelled">
                                                        <button type="submit" class="btn btn-outline-danger" title="Reject"><i class="bi bi-x-lg"></i></button>
                                                    </form>
                                                <?php elseif ($apt['status'] === 'Confirmed'): ?>
                                                    <form method="POST" action="dashboard.php" class="d-inline">
                                                        <input type="hidden" name="appointment_id" value="<?= $apt['id'] ?>">
                                                        <input type="hidden" name="action" value="Completed">
                                                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle"></i> Mark Done</button>
                                                    </form>
                                                    <a href="create-prescription.php?appointment_id=<?= $apt['id'] ?>&patient_id=<?= $apt['patient_id'] ?>" class="btn btn-outline-primary" title="Prescribe">
                                                        <i class="bi bi-capsule"></i>
                                                    </a>
                                                    <a href="add-record.php?patient_id=<?= $apt['patient_id'] ?>" class="btn btn-outline-info" title="Add Record">
                                                        <i class="bi bi-journal-plus"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border">Completed</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-calendar-check fs-2 d-block mb-2 text-secondary"></i>
                                        No consultations booked for today.
                                        <div class="mt-2">
                                            <a href="appointments.php" class="btn btn-sm btn-outline-primary rounded-pill">View Upcoming Schedule</a>
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
