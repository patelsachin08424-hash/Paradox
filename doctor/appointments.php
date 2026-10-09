<?php
/**
 * Doctor Appointments Management
 * University Project: Online Healthcare Management System
 */
require_once __DIR__ . '/../config/db.php';
requireRole('doctor', 'login.php');

$doctorId = $_SESSION['doctor_id'] ?? 0;
$filter = sanitize($_GET['status'] ?? 'all');

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['appointment_id'])) {
    $aptId = (int)$_POST['appointment_id'];
    $newStatus = sanitize($_POST['action']);

    if (in_array($newStatus, ['Confirmed', 'Cancelled', 'Completed'])) {
        try {
            $stmtUp = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?");
            $stmtUp->execute([$newStatus, $aptId, $doctorId]);
            setFlash('action_msg', "Appointment #APT-{$aptId} marked as '{$newStatus}'.", 'success');
            redirect('appointments.php' . ($filter !== 'all' ? '?status=' . $filter : ''));
        } catch (Exception $e) {
            setFlash('action_msg', 'Status update failed: ' . $e->getMessage(), 'danger');
        }
    }
}

// Fetch all appointments for this doctor
$appointments = [];
try {
    $sql = "
        SELECT a.*, p.full_name AS patient_name, p.phone AS patient_phone, p.gender, p.blood_group, p.dob
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        WHERE a.doctor_id = ?
    ";
    $params = [$doctorId];

    if ($filter !== 'all') {
        $sql .= " AND a.status = ?";
        $params[] = ucfirst($filter);
    }

    $sql .= " ORDER BY a.appointment_date DESC, a.appointment_time ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $appointments = $stmt->fetchAll();
} catch (Exception $e) {
    //
}

$pageTitle = "Manage Clinical Visits";
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
                <h5 class="mb-0 fw-bold text-dark">Patient Appointments & Consultation Queue</h5>
            </div>
            <a href="dashboard.php" class="btn btn-sm btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </header>

        <main class="portal-main">
            <?php 
                $action = getFlash('action_msg');
                if ($action):
            ?>
                <div class="alert alert-<?= $action['type'] ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
                    <i class="bi bi-info-circle me-2"></i><?= $action['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Filter Tabs -->
            <div class="d-flex flex-wrap gap-2 mb-4">
                <a href="appointments.php?status=all" class="btn btn-sm <?= $filter === 'all' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">All Visits</a>
                <a href="appointments.php?status=pending" class="btn btn-sm <?= $filter === 'pending' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' ?> rounded-pill px-3">Pending Approval</a>
                <a href="appointments.php?status=confirmed" class="btn btn-sm <?= $filter === 'confirmed' ? 'btn-success' : 'btn-outline-success' ?> rounded-pill px-3">Confirmed</a>
                <a href="appointments.php?status=completed" class="btn btn-sm <?= $filter === 'completed' ? 'btn-info text-white' : 'btn-outline-info' ?> rounded-pill px-3">Completed</a>
                <a href="appointments.php?status=cancelled" class="btn btn-sm <?= $filter === 'cancelled' ? 'btn-danger' : 'btn-outline-danger' ?> rounded-pill px-3">Cancelled</a>
            </div>

            <!-- Appointments List Table -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Patient Demographic</th>
                                <th>Date & Time</th>
                                <th>Chief Complaint</th>
                                <th>Status</th>
                                <th class="text-end">Clinical Actions</th>
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
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($apt['patient_name']) ?></div>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($apt['gender']) ?> &bull; 
                                                Blood: <span class="badge bg-danger-subtle text-danger"><?= htmlspecialchars($apt['blood_group']) ?></span> &bull;
                                                <i class="bi bi-telephone ms-1"></i> <?= htmlspecialchars($apt['patient_phone']) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><i class="bi bi-calendar text-primary me-1"></i><?= date('D, M d, Y', strtotime($apt['appointment_date'])) ?></div>
                                            <small class="text-muted"><i class="bi bi-clock text-muted me-1"></i><?= date('h:i A', strtotime($apt['appointment_time'])) ?></small>
                                        </td>
                                        <td>
                                            <span class="small text-secondary"><?= htmlspecialchars($apt['reason']) ?></span>
                                        </td>
                                        <td>
                                            <?= getStatusBadge($apt['status']) ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <?php if ($apt['status'] === 'Pending'): ?>
                                                    <form method="POST" action="appointments.php" class="d-inline">
                                                        <input type="hidden" name="appointment_id" value="<?= $apt['id'] ?>">
                                                        <input type="hidden" name="action" value="Confirmed">
                                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3" title="Approve">
                                                            <i class="bi bi-check-lg me-1"></i>Approve
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="appointments.php" class="d-inline" onsubmit="return confirm('Reject this appointment?');">
                                                        <input type="hidden" name="appointment_id" value="<?= $apt['id'] ?>">
                                                        <input type="hidden" name="action" value="Cancelled">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Reject">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </form>
                                                <?php elseif ($apt['status'] === 'Confirmed'): ?>
                                                    <form method="POST" action="appointments.php" class="d-inline">
                                                        <input type="hidden" name="appointment_id" value="<?= $apt['id'] ?>">
                                                        <input type="hidden" name="action" value="Completed">
                                                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">
                                                            <i class="bi bi-check2-circle me-1"></i>Finish
                                                        </button>
                                                    </form>
                                                    <a href="create-prescription.php?appointment_id=<?= $apt['id'] ?>&patient_id=<?= $apt['patient_id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-2" title="Prescription">
                                                        <i class="bi bi-capsule"></i>
                                                    </a>
                                                    <a href="add-record.php?patient_id=<?= $apt['patient_id'] ?>" class="btn btn-sm btn-outline-info rounded-pill px-2" title="Add Diagnosis">
                                                        <i class="bi bi-journal-plus"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="patient-history.php?patient_id=<?= $apt['patient_id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                        <i class="bi bi-folder2-open me-1"></i>History
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-3 text-secondary"></i>
                                        No appointments matching this filter.
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
