<?php
/**
 * Patient Appointments Management
 * University Project: Online Healthcare Management System
 */
require_once __DIR__ . '/../config/db.php';
requireRole('patient', 'login.php');

$patientId = $_SESSION['patient_id'] ?? 0;
$filter = sanitize($_GET['status'] ?? 'all');

// Handle Cancel Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $cancelId = (int)$_POST['cancel_id'];
    try {
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = ? AND patient_id = ?");
        $stmt->execute([$cancelId, $patientId]);
        setFlash('action_msg', "Appointment #APT-{$cancelId} was cancelled successfully.", 'warning');
        redirect('appointments.php');
    } catch (Exception $e) {
        setFlash('action_msg', 'Failed to cancel appointment: ' . $e->getMessage(), 'danger');
    }
}

// Fetch Appointments
$appointments = [];
try {
    $sql = "
        SELECT a.*, d.full_name AS doctor_name, d.specialization, d.room_no, d.consultation_fee, d.phone AS doctor_phone
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        WHERE a.patient_id = ?
    ";
    $params = [$patientId];

    if ($filter !== 'all') {
        $sql .= " AND a.status = ?";
        $params[] = ucfirst($filter);
    }

    $sql .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $appointments = $stmt->fetchAll();
} catch (Exception $e) {
    //
}

$pageTitle = "My Appointments";
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
                <h5 class="mb-0 fw-bold text-dark">My Appointments</h5>
            </div>
            <a href="book-appointment.php" class="btn btn-sm btn-primary rounded-pill px-3">
                <i class="bi bi-plus-circle me-1"></i> Book New
            </a>
        </header>

        <main class="portal-main">
            <?php 
                $flashMsg = getFlash('action_msg');
                if ($flashMsg):
            ?>
                <div class="alert alert-<?= $flashMsg['type'] ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
                    <i class="bi bi-info-circle me-2"></i><?= $flashMsg['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Status Filter Tabs -->
            <div class="d-flex flex-wrap gap-2 mb-4">
                <a href="appointments.php?status=all" class="btn btn-sm <?= $filter === 'all' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">All</a>
                <a href="appointments.php?status=pending" class="btn btn-sm <?= $filter === 'pending' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' ?> rounded-pill px-3">Pending</a>
                <a href="appointments.php?status=confirmed" class="btn btn-sm <?= $filter === 'confirmed' ? 'btn-success' : 'btn-outline-success' ?> rounded-pill px-3">Confirmed</a>
                <a href="appointments.php?status=completed" class="btn btn-sm <?= $filter === 'completed' ? 'btn-info text-white' : 'btn-outline-info' ?> rounded-pill px-3">Completed</a>
                <a href="appointments.php?status=cancelled" class="btn btn-sm <?= $filter === 'cancelled' ? 'btn-danger' : 'btn-outline-danger' ?> rounded-pill px-3">Cancelled</a>
            </div>

            <!-- Appointments Table Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Specialist</th>
                                <th>Date & Time</th>
                                <th>Clinical Reason</th>
                                <th>Status</th>
                                <th class="text-end">Manage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($appointments)): ?>
                                <?php foreach ($appointments as $apt): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-light text-dark border">#APT-<?= $apt['id'] ?></span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($apt['doctor_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($apt['specialization']) ?> &bull; Room: <?= htmlspecialchars($apt['room_no']) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><i class="bi bi-calendar-event text-primary me-1"></i><?= date('D, M d, Y', strtotime($apt['appointment_date'])) ?></div>
                                            <small class="text-muted"><i class="bi bi-clock text-muted me-1"></i><?= date('h:i A', strtotime($apt['appointment_time'])) ?></small>
                                        </td>
                                        <td>
                                            <span class="small text-secondary"><?= htmlspecialchars($apt['reason']) ?></span>
                                        </td>
                                        <td>
                                            <?= getStatusBadge($apt['status']) ?>
                                        </td>
                                        <td class="text-end">
                                            <?php if ($apt['status'] === 'Pending' || $apt['status'] === 'Confirmed'): ?>
                                                <form method="POST" action="appointments.php" class="d-inline" onsubmit="return confirm('Do you wish to cancel Appointment #APT-<?= $apt['id'] ?>?');">
                                                    <input type="hidden" name="cancel_id" value="<?= $apt['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                        <i class="bi bi-x-lg me-1"></i>Cancel
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border">Completed / Void</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-calendar-x fs-1 d-block mb-3 text-secondary"></i>
                                        No appointments found for the selected filter.
                                        <div class="mt-2">
                                            <a href="book-appointment.php" class="btn btn-sm btn-primary rounded-pill">Book an Appointment</a>
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
