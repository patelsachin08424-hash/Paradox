<?php
/**
 * Admin Manage Users & Role-Based Access Control (RBAC)
 * University Project: Online Healthcare Management System
 */
require_once __DIR__ . '/../config/db.php';
requireRole('admin', 'login.php');

$currentAdminId = $_SESSION['user_id'] ?? 0;
$filterRole = sanitize($_GET['role'] ?? 'all');

// Handle status toggling or password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['target_user_id'])) {
    $targetId = (int)$_POST['target_user_id'];
    $action = $_POST['action'];

    if ($action === 'toggle_status') {
        if ($targetId === $currentAdminId) {
            setFlash('action_msg', 'You cannot deactivate your own logged-in admin account!', 'warning');
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?");
                $stmt->execute([$targetId]);
                setFlash('action_msg', "Account status toggled for User #{$targetId}.", 'success');
                redirect('manage-users.php');
            } catch (Exception $e) {
                setFlash('action_msg', 'Status change failed: ' . $e->getMessage(), 'danger');
            }
        }
    } elseif ($action === 'reset_password') {
        try {
            $defaultHash = password_hash('pulse123', PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$defaultHash, $targetId]);
            setFlash('action_msg', "Password for User #{$targetId} reset to default: 'pulse123'.", 'success');
            redirect('manage-users.php');
        } catch (Exception $e) {
            setFlash('action_msg', 'Reset failed: ' . $e->getMessage(), 'danger');
        }
    } elseif ($action === 'delete_user') {
        if ($targetId === $currentAdminId) {
            setFlash('action_msg', 'You cannot delete yourself!', 'warning');
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$targetId]);
                setFlash('action_msg', "User #{$targetId} deleted from system.", 'warning');
                redirect('manage-users.php');
            } catch (Exception $e) {
                setFlash('action_msg', 'Deletion failed: ' . $e->getMessage(), 'danger');
            }
        }
    }
}

// Fetch all users
$users = [];
try {
    $sql = "SELECT * FROM users WHERE 1=1";
    $params = [];

    if ($filterRole !== 'all') {
        $sql .= " AND role = ?";
        $params[] = $filterRole;
    }

    $sql .= " ORDER BY created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
} catch (Exception $e) {}

$pageTitle = "Manage System Users";
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
                <h5 class="mb-0 fw-bold text-dark">System Authentication & RBAC User Directory</h5>
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

            <!-- Role Filter Pills -->
            <div class="d-flex flex-wrap gap-2 mb-4">
                <a href="manage-users.php?role=all" class="btn btn-sm <?= $filterRole === 'all' ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">All Accounts (<?= count($users) ?>)</a>
                <a href="manage-users.php?role=patient" class="btn btn-sm <?= $filterRole === 'patient' ? 'btn-success' : 'btn-outline-success' ?> rounded-pill px-3">Patients</a>
                <a href="manage-users.php?role=doctor" class="btn btn-sm <?= $filterRole === 'doctor' ? 'btn-info text-white' : 'btn-outline-info' ?> rounded-pill px-3">Doctors</a>
                <a href="manage-users.php?role=admin" class="btn btn-sm <?= $filterRole === 'admin' ? 'btn-danger' : 'btn-outline-danger' ?> rounded-pill px-3">Administrators</a>
            </div>

            <!-- Users Table -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th class="text-end">Account Controls</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><span class="badge bg-light text-dark border">#UID-<?= $u['id'] ?></span></td>
                                        <td class="fw-bold text-dark">
                                            <?= htmlspecialchars($u['username']) ?>
                                            <?php if ($u['id'] == $currentAdminId): ?>
                                                <span class="badge bg-primary-subtle text-primary small ms-1">You</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($u['email']) ?></td>
                                        <td>
                                            <?php if ($u['role'] === 'admin'): ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-shield-fill me-1"></i>Admin</span>
                                            <?php elseif ($u['role'] === 'doctor'): ?>
                                                <span class="badge bg-info-subtle text-info border border-info-subtle"><i class="bi bi-stethoscope me-1"></i>Doctor</span>
                                            <?php else: ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-person me-1"></i>Patient</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= getStatusBadge($u['status']) ?>
                                        </td>
                                        <td class="small text-muted">
                                            <?= date('M d, Y', strtotime($u['created_at'])) ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <!-- Toggle Active/Inactive -->
                                                <form method="POST" action="manage-users.php" class="d-inline">
                                                    <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <button type="submit" class="btn btn-outline-secondary" title="<?= $u['status'] === 'active' ? 'Deactivate' : 'Activate' ?>">
                                                        <i class="bi <?= $u['status'] === 'active' ? 'bi-toggle-on text-success' : 'bi-toggle-off text-muted' ?>"></i>
                                                    </button>
                                                </form>

                                                <!-- Reset Password -->
                                                <form method="POST" action="manage-users.php" class="d-inline" onsubmit="return confirm('Reset password to pulse123 for <?= htmlspecialchars($u['username']) ?>?');">
                                                    <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                                    <input type="hidden" name="action" value="reset_password">
                                                    <button type="submit" class="btn btn-outline-warning" title="Reset Password">
                                                        <i class="bi bi-key-fill text-dark"></i>
                                                    </button>
                                                </form>

                                                <!-- Delete User -->
                                                <?php if ($u['id'] != $currentAdminId): ?>
                                                    <form method="POST" action="manage-users.php" class="d-inline" onsubmit="return confirm('Permanently delete <?= htmlspecialchars($u['username']) ?>?');">
                                                        <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                                        <input type="hidden" name="action" value="delete_user">
                                                        <button type="submit" class="btn btn-outline-danger" title="Delete Account">
                                                            <i class="bi bi-trash3"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">No users found.</td>
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
