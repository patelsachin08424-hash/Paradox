<?php
/**
 * Admin Manage Patients
 * University Project: Online Healthcare Management System
 */
require_once __DIR__ . '/../config/db.php';
requireRole('admin', 'login.php');

$search = sanitize($_GET['search'] ?? '');

// Handle delete / deactivate action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['patient_id'])) {
    $patientId = (int)$_POST['patient_id'];
    $userId = (int)($_POST['user_id'] ?? 0);

    if ($_POST['action'] === 'delete') {
        try {
            // Deleting user cascades to patient record
            $stmtDel = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmtDel->execute([$userId]);
            setFlash('action_msg', "Patient #PT-{$patientId} and linked user account removed successfully.", 'warning');
            redirect('manage-patients.php');
        } catch (Exception $e) {
            setFlash('action_msg', 'Deletion failed: ' . $e->getMessage(), 'danger');
        }
    }
}

// Fetch patients
$patients = [];
try {
    $sql = "
        SELECT p.*, u.email, u.username, u.status AS user_status,
               (SELECT COUNT(*) FROM appointments a WHERE a.patient_id = p.id) AS appointment_count
        FROM patients p
        JOIN users u ON p.user_id = u.id
    ";
    $params = [];

    if (!empty($search)) {
        $sql .= " WHERE p.full_name LIKE ? OR p.phone LIKE ? OR u.email LIKE ?";
        $params = ["%{$search}%", "%{$search}%", "%{$search}%"];
    }

    $sql .= " ORDER BY p.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $patients = $stmt->fetchAll();
} catch (Exception $e) {
    //
}

$pageTitle = "Manage Patients";
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
                <h5 class="mb-0 fw-bold text-dark">Patient Registry Management</h5>
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

            <!-- Search & Actions Bar -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <form method="GET" action="manage-patients.php" class="row g-3 align-items-center">
                    <div class="col-md-9">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control form-control-lg" placeholder="Search by patient name, contact phone, or email address..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3">
                            <i class="bi bi-funnel me-1"></i> Filter Patients
                        </button>
                    </div>
                </form>
            </div>

            <!-- Patient Table Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white p-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">Registered Patient Profiles (<?= count($patients) ?>)</h6>
                    <a href="../patient/register.php" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                        <i class="bi bi-person-plus me-1"></i> Register New Patient
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Patient Legal Name</th>
                                <th>Contact & Email</th>
                                <th>Blood & Gender</th>
                                <th>Total Visits</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($patients)): ?>
                                <?php foreach ($patients as $p): ?>
                                    <tr>
                                        <td><span class="badge bg-light text-dark border">#PT-<?= $p['id'] ?></span></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($p['full_name']) ?></div>
                                            <small class="text-muted"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars(substr($p['address'] ?? 'No address', 0, 30)) ?></small>
                                        </td>
                                        <td class="small">
                                            <div><i class="bi bi-telephone text-primary me-1"></i><?= htmlspecialchars($p['phone']) ?></div>
                                            <div class="text-muted"><i class="bi bi-envelope text-muted me-1"></i><?= htmlspecialchars($p['email']) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle me-1"><?= htmlspecialchars($p['blood_group']) ?></span>
                                            <small class="text-secondary"><?= htmlspecialchars($p['gender']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info border"><?= $p['appointment_count'] ?> Visits</span>
                                        </td>
                                        <td>
                                            <?= getStatusBadge($p['user_status']) ?>
                                        </td>
                                        <td class="text-end">
                                            <form method="POST" action="manage-patients.php" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently remove patient <?= htmlspecialchars($p['full_name']) ?> and associated records?');">
                                                <input type="hidden" name="patient_id" value="<?= $p['id'] ?>">
                                                <input type="hidden" name="user_id" value="<?= $p['user_id'] ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                    <i class="bi bi-trash3 me-1"></i>Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-people fs-1 d-block mb-3 text-secondary"></i>
                                        No patient records found.
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
