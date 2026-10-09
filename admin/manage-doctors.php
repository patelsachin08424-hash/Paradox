<?php
/**
 * Admin Manage Doctors & Add Specialists
 * University Project: Online Healthcare Management System
 */
require_once __DIR__ . '/../config/db.php';
requireRole('admin', 'login.php');

$error = '';
$success = '';

// Handle Adding New Doctor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_doctor') {
    $full_name        = sanitize($_POST['full_name'] ?? '');
    $username         = sanitize($_POST['username'] ?? '');
    $email            = sanitize($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? 'doctor123';
    $specialization   = sanitize($_POST['specialization'] ?? '');
    $qualification    = sanitize($_POST['qualification'] ?? '');
    $phone            = sanitize($_POST['phone'] ?? '');
    $room_no          = sanitize($_POST['room_no'] ?? '');
    $consultation_fee = (float)($_POST['consultation_fee'] ?? 500);
    $available_days   = sanitize($_POST['available_days'] ?? 'Mon - Fri, 09:00 AM - 05:00 PM');
    $bio              = sanitize($_POST['bio'] ?? '');

    if (empty($full_name) || empty($username) || empty($email) || empty($specialization) || empty($room_no)) {
        $error = 'All primary clinical and authentication fields are required.';
    } else {
        try {
            // Check username/email uniqueness
            $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmtCheck->execute([$username, $email]);

            if ($stmtCheck->rowCount() > 0) {
                $error = 'A user with this username or email already exists in the system.';
            } else {
                $pdo->beginTransaction();

                // 1. Insert User
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmtU = $pdo->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, 'doctor', 'active')");
                $stmtU->execute([$username, $email, $hash]);
                $userId = $pdo->lastInsertId();

                // 2. Insert Doctor
                $stmtD = $pdo->prepare("
                    INSERT INTO doctors (user_id, full_name, specialization, qualification, phone, room_no, consultation_fee, bio, available_days)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmtD->execute([$userId, $full_name, $specialization, $qualification, $phone, $room_no, $consultation_fee, $bio, $available_days]);

                $pdo->commit();
                setFlash('action_msg', "Specialist {$full_name} ({$specialization}) registered successfully!", 'success');
                redirect('manage-doctors.php');
            }
        } catch (Exception $e) {
            if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
            $error = 'Failed to register doctor: ' . $e->getMessage();
        }
    }
}

// Handle Delete Doctor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_doctor') {
    $userId = (int)$_POST['user_id'];
    try {
        $stmtDel = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmtDel->execute([$userId]);
        setFlash('action_msg', 'Physician account removed from hospital roster.', 'warning');
        redirect('manage-doctors.php');
    } catch (Exception $e) {
        setFlash('action_msg', 'Deletion error: ' . $e->getMessage(), 'danger');
    }
}

// Fetch all doctors
$doctors = [];
try {
    $stmt = $pdo->query("
        SELECT d.*, u.email, u.username, u.status AS user_status,
               (SELECT COUNT(*) FROM appointments a WHERE a.doctor_id = d.id) AS appointment_count
        FROM doctors d
        JOIN users u ON d.user_id = u.id
        ORDER BY d.full_name ASC
    ");
    $doctors = $stmt->fetchAll();
} catch (Exception $e) {
    //
}

$pageTitle = "Manage Doctors";
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
                <h5 class="mb-0 fw-bold text-dark">Physician Roster & Faculty Management</h5>
            </div>
            <button class="btn btn-sm btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addDoctorModal">
                <i class="bi bi-person-plus-fill me-1"></i> Register New Doctor
            </button>
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

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Doctors Table Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white p-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">Active Specialists (<?= count($doctors) ?>)</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Doctor Details</th>
                                <th>Department / Specialty</th>
                                <th>Clinic Suite</th>
                                <th>Fee</th>
                                <th>Schedule</th>
                                <th>Total Visits</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($doctors)): ?>
                                <?php foreach ($doctors as $d): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($d['full_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($d['qualification']) ?> &bull; <?= htmlspecialchars($d['email']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill">
                                                <?= htmlspecialchars($d['specialization']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><i class="bi bi-door-open me-1"></i><?= htmlspecialchars($d['room_no']) ?></span>
                                        </td>
                                        <td class="fw-semibold text-success">
                                            $<?= htmlspecialchars($d['consultation_fee']) ?>
                                        </td>
                                        <td class="small text-muted">
                                            <?= htmlspecialchars($d['available_days']) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info border"><?= $d['appointment_count'] ?> Visits</span>
                                        </td>
                                        <td class="text-end">
                                            <form method="POST" action="manage-doctors.php" class="d-inline" onsubmit="return confirm('Remove Dr. <?= htmlspecialchars($d['full_name']) ?> from the system?');">
                                                <input type="hidden" name="user_id" value="<?= $d['user_id'] ?>">
                                                <input type="hidden" name="action" value="delete_doctor">
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                    <i class="bi bi-trash3 me-1"></i>Remove
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-stethoscope fs-1 d-block mb-3 text-secondary"></i>
                                        No doctors listed in the database.
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

<!-- Modal: Register New Doctor -->
<div class="modal fade" id="addDoctorModal" tabindex="-1" aria-labelledby="addDoctorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header bg-primary text-white p-4">
                <h5 class="modal-title fw-bold" id="addDoctorModalLabel"><i class="bi bi-person-plus-fill me-2"></i>Register New Medical Specialist</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="manage-doctors.php">
                <input type="hidden" name="action" value="add_doctor">
                <div class="modal-body p-4">
                    <h6 class="fw-bold text-primary mb-3">1. Login Credentials</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Username *</label>
                            <input type="text" name="username" class="form-control" required placeholder="dr_william">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Address *</label>
                            <input type="email" name="email" class="form-control" required placeholder="william@pulsehealth.edu">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Initial Password</label>
                            <input type="password" name="password" class="form-control" required value="doctor123">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Contact Phone *</label>
                            <input type="tel" name="phone" class="form-control" required placeholder="+1 (555) 000-0000">
                        </div>
                    </div>

                    <h6 class="fw-bold text-primary mb-3">2. Clinical Specialization & Clinic Info</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Doctor Full Name with Title *</label>
                            <input type="text" name="full_name" class="form-control" required placeholder="Dr. William Osler, MD">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Specialization / Department *</label>
                            <select name="specialization" class="form-select" required>
                                <option value="Cardiology">Cardiology</option>
                                <option value="Neurology">Neurology</option>
                                <option value="Pediatrics">Pediatrics</option>
                                <option value="Orthopedics">Orthopedics</option>
                                <option value="Dermatology">Dermatology</option>
                                <option value="General Medicine">General Medicine</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Academic Qualifications *</label>
                            <input type="text" name="qualification" class="form-control" required placeholder="MD, MRCP, FACC">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Clinic Suite / Room *</label>
                            <input type="text" name="room_no" class="form-control" required placeholder="Suite 204">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Consultation Fee ($)</label>
                            <input type="number" step="0.01" name="consultation_fee" class="form-control" required value="600.00">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Availability Schedule</label>
                            <input type="text" name="available_days" class="form-control" value="Mon - Fri, 09:00 AM - 04:00 PM">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Clinical Bio & Background</label>
                            <textarea name="bio" class="form-control" rows="2" placeholder="Summary of clinical achievements and specialty experience..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="bi bi-check-circle-fill me-1"></i> Create Doctor Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
