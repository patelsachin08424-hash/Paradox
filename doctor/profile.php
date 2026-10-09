<?php
/**
 * Doctor Profile Management
 * University Project: Online Healthcare Management System
 */
require_once __DIR__ . '/../config/db.php';
requireRole('doctor', 'login.php');

$doctorId = $_SESSION['doctor_id'] ?? 0;
$userId = $_SESSION['user_id'] ?? 0;
$error = '';
$success = '';

// Handle Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name        = sanitize($_POST['full_name'] ?? '');
    $specialization   = sanitize($_POST['specialization'] ?? '');
    $qualification    = sanitize($_POST['qualification'] ?? '');
    $phone            = sanitize($_POST['phone'] ?? '');
    $room_no          = sanitize($_POST['room_no'] ?? '');
    $consultation_fee = (float)($_POST['consultation_fee'] ?? 500);
    $available_days   = sanitize($_POST['available_days'] ?? '');
    $bio              = sanitize($_POST['bio'] ?? '');

    if (empty($full_name) || empty($phone) || empty($room_no)) {
        $error = 'Name, phone, and clinic room number are required.';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE doctors 
                SET full_name = ?, specialization = ?, qualification = ?, phone = ?, room_no = ?, consultation_fee = ?, available_days = ?, bio = ?
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$full_name, $specialization, $qualification, $phone, $room_no, $consultation_fee, $available_days, $bio, $doctorId, $userId]);
            $_SESSION['user_name'] = $full_name;
            $success = 'Professional physician profile updated successfully!';
        } catch (Exception $e) {
            $error = 'Profile update failed: ' . $e->getMessage();
        }
    }
}

// Fetch current details
$doctor = [];
try {
    $stmt = $pdo->prepare("SELECT d.*, u.email, u.username FROM doctors d JOIN users u ON d.user_id = u.id WHERE d.id = ?");
    $stmt->execute([$doctorId]);
    $doctor = $stmt->fetch();
} catch (Exception $e) {
    //
}

$pageTitle = "Physician Profile";
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
                <h5 class="mb-0 fw-bold text-dark">Physician Credentials & Consultation Settings</h5>
            </div>
            <a href="dashboard.php" class="btn btn-sm btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </header>

        <main class="portal-main">
            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i><?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Summary Card -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 text-center p-4">
                        <div class="d-flex justify-content-center mb-3">
                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center fs-1 shadow-sm" style="width: 100px; height: 100px;">
                                <i class="bi bi-stethoscope"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($doctor['full_name'] ?? 'Doctor') ?></h5>
                        <div class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill mb-3">
                            <?= htmlspecialchars($doctor['specialization'] ?? 'Specialist') ?>
                        </div>
                        <p class="text-muted small mb-3"><?= htmlspecialchars($doctor['qualification'] ?? '') ?></p>

                        <ul class="list-group list-group-flush text-start small">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Office / Room:</span>
                                <strong><?= htmlspecialchars($doctor['room_no'] ?? '') ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Fee:</span>
                                <strong>$<?= htmlspecialchars($doctor['consultation_fee'] ?? '500.00') ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Email:</span>
                                <strong><?= htmlspecialchars($doctor['email'] ?? '') ?></strong>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                        <h5 class="fw-bold mb-4 text-primary"><i class="bi bi-sliders me-2"></i>Edit Clinical Settings</h5>

                        <form method="POST" action="profile.php">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Full Legal & Professional Name *</label>
                                    <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($doctor['full_name'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Specialization Department *</label>
                                    <input type="text" name="specialization" class="form-control" required value="<?= htmlspecialchars($doctor['specialization'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Medical Degree & Qualifications *</label>
                                    <input type="text" name="qualification" class="form-control" required value="<?= htmlspecialchars($doctor['qualification'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Contact Phone Number *</label>
                                    <input type="tel" name="phone" class="form-control" required value="<?= htmlspecialchars($doctor['phone'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Clinic Suite / Room Number *</label>
                                    <input type="text" name="room_no" class="form-control" required value="<?= htmlspecialchars($doctor['room_no'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Consultation Fee ($ USD)</label>
                                    <input type="number" step="0.01" name="consultation_fee" class="form-control" required value="<?= htmlspecialchars($doctor['consultation_fee'] ?? '500.00') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Availability Schedule (Days & Working Hours)</label>
                                    <input type="text" name="available_days" class="form-control" value="<?= htmlspecialchars($doctor['available_days'] ?? 'Mon - Fri, 09:00 AM - 05:00 PM') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Professional Bio & Clinical Philosophy</label>
                                    <textarea name="bio" class="form-control" rows="3"><?= htmlspecialchars($doctor['bio'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                                        <i class="bi bi-save me-1"></i> Update Physician Profile
                                    </button>
                                </div>
                            </div>
                        </form>
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
