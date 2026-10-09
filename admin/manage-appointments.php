<?php
/**
 * Admin Master Appointments Management
 * University Project: Online Healthcare Management System
 */
require_once __DIR__ . '/../config/db.php';
requireRole('admin', 'login.php');

$filterStatus = sanitize($_GET['status'] ?? 'all');
$filterDoctor = (int)($_GET['doctor_id'] ?? 0);

// Handle Status Changes by Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['appointment_id'])) {
    $aptId = (int)$_POST['appointment_id'];
    $newStatus = sanitize($_POST['action']);

    if (in_array($newStatus, ['Confirmed', 'Cancelled', 'Completed', 'Pending'])) {
        try {
            $stmtUp = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
            $stmtUp->execute([$newStatus, $aptId]);
            setFlash('action_msg', "Appointment #APT-{$aptId} status set to '{$newStatus}'.", 'success');
            redirect('manage-appointments.php');
        } catch (Exception $e) {
            setFlash('action_msg', 'Failed to update: ' . $e->getMessage(), 'danger');
        }
    }
}

// Fetch all doctors for filter dropdown
$doctorsList = [];
try {
    $stmtDocs = $pdo->query("SELECT id, full_name, specialization FROM doctors ORDER BY full_name ASC");
    $doctorsList = $stmtDocs->fetchAll();
} catch (Exception $e) {}

// Fetch filtered appointments
$appointments = [];
try {
    $sql = "
        SELECT a.*, p.full_name AS patient_name, p.phone AS patient_phone,
               d.full_name AS doctor_name, d.specialization, d.room_no
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        JOIN doctors d ON a.doctor_id = d.id
        WHERE 1=1
    ";
    $params = [];

    if ($filterStatus !== 'all') {
        $sql .= " AND a.status = ?";
        $params[] = ucfirst($filterStatus);
    }
    if ($filterDoctor > 0) {
        $sql .= " AND a.doctor_id = ?";
        $params[] = $filterDoctor;
    }

    $sql .= " ORDER BY a.appointment_date DESC, a.appointment_time ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $appointments = $stmt->fetchAll();
} catch (Exception $e) {}

$pageTitle = "Master Appointments Control";
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
                <h5 class="mb-0 fw-bold text-dark">Master Hospital Appointments Scheduler</h5>
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

            <!-- Filter Controls -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <form method="GET" action="manage-appointments.php" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold text-muted">Filter by Status</label>
                        <select name="status" class="form-select">
                            <option value="all" <?= $filterStatus === 'all' ? 'selected' : '' ?>>All Statuses</option>
                            <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="confirmed" <?= $filterStatus === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                            <option value="completed" <?= $filterStatus === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= $filterStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold text-muted">Filter by Doctor</label>
                        <select name="doctor_id" class="form-select">
                            <option value="0">All Attending Doctors</option>
                            <?php foreach ($doctorsList as $doc): ?>
                                <option value="<?= $doc['id'] ?>" <?= $filterDoctor == $doc['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($doc['full_name']) ?> (<?= htmlspecialchars($doc['specialization']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 rounded-3">
                            <i class="bi bi-funnel me-1"></i> Apply
                        </button>
                    </div>
                </form>
            </div>

            <!-- Master Appointments Table -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white p-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">Booked Consultations (<?= count($appointments) ?>)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Patient Name</th>
                                <th>Attending Specialist</th>
                                <th>Scheduled Date & Time</th>
                                <th>Status</th>
                                <th class="text-end">Admin Override Actions</th>
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
                                            <small class="text-muted"><i class="bi bi-telephone"></i> <?= htmlspecialchars($apt['patient_phone']) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($apt['doctor_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($apt['specialization']) ?> &bull; Room <?= htmlspecialchars($apt['room_no']) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= date('M d, Y', strtotime($apt['appointment_date'])) ?></div>
                                            <small class="text-muted"><i class="bi bi-clock me-1"></i><?= date('h:i A', strtotime($apt['appointment_time'])) ?></small>
                                        </td>
                                        <td>
                                            <?= getStatusBadge($apt['status']) ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <?php if ($apt['status'] !== 'Confirmed'): ?>
                                                    <form method="POST" action="manage-appointments.php" class="d-inline">
                                                        <input type="hidden" name="appointment_id" value="<?= $apt['id'] ?>">
                                                        <input type="hidden" name="action" value="Confirmed">
                                                        <button type="submit" class="btn btn-outline-success" title="Confirm"><i class="bi bi-check-lg"></i></button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if ($apt['status'] !== 'Completed'): ?>
                                                    <form method="POST" action="manage-appointments.php" class="d-inline">
                                                        <input type="hidden" name="appointment_id" value="<?= $apt['id'] ?>">
                                                        <input type="hidden" name="action" value="Completed">
                                                        <button type="submit" class="btn btn-outline-primary" title="Complete"><i class="bi bi-check2-all"></i></button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if ($apt['status'] !== 'Cancelled'): ?>
                                                    <form method="POST" action="manage-appointments.php" class="d-inline" onsubmit="return confirm('Cancel this appointment?');">
                                                        <input type="hidden" name="appointment_id" value="<?= $apt['id'] ?>">
                                                        <input type="hidden" name="action" value="Cancelled">
                                                        <button type="submit" class="btn btn-outline-danger" title="Cancel"><i class="bi bi-x-lg"></i></button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-calendar-x fs-1 d-block mb-3 text-secondary"></i>
                                        No appointments matching the selected filters.
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
