<?php
/**
 * Patient Profile Management
 * University Project: Online Healthcare Management System
 */
require_once __DIR__ . '/../config/db.php';
requireRole('patient', 'login.php');

$patientId = $_SESSION['patient_id'] ?? 0;
$userId = $_SESSION['user_id'] ?? 0;
$error = '';
$success = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name         = sanitize($_POST['full_name'] ?? '');
    $dob               = sanitize($_POST['dob'] ?? '');
    $gender            = sanitize($_POST['gender'] ?? 'Male');
    $phone             = sanitize($_POST['phone'] ?? '');
    $blood_group       = sanitize($_POST['blood_group'] ?? 'O+');
    $address           = sanitize($_POST['address'] ?? '');
    $emergency_contact = sanitize($_POST['emergency_contact'] ?? '');

    if (empty($full_name) || empty($phone)) {
        $error = 'Full name and phone number are required.';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE patients 
                SET full_name = ?, dob = ?, gender = ?, phone = ?, blood_group = ?, address = ?, emergency_contact = ? 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$full_name, $dob ?: null, $gender, $phone, $blood_group, $address, $emergency_contact, $patientId, $userId]);
            $_SESSION['user_name'] = $full_name;
            $success = 'Profile updated successfully!';
        } catch (Exception $e) {
            $error = 'Update failed: ' . $e->getMessage();
        }
    }
}

// Fetch current patient details
$patient = [];
try {
    $stmt = $pdo->prepare("SELECT p.*, u.email, u.username FROM patients p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
    $stmt->execute([$patientId]);
    $patient = $stmt->fetch();
} catch (Exception $e) {
    $error = 'Could not load patient profile: ' . $e->getMessage();
}

$pageTitle = "My Profile";
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
                <h5 class="mb-0 fw-bold text-dark">Patient Profile</h5>
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
                <!-- Profile Summary Card -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 text-center p-4">
                        <div class="d-flex justify-content-center mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fs-1 shadow-sm" style="width: 100px; height: 100px;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($patient['full_name'] ?? 'Patient') ?></h5>
                        <p class="text-muted small mb-3">Patient ID: #PT-<?= str_pad($patientId, 4, '0', STR_PAD_LEFT) ?></p>
                        
                        <div class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill mb-3">
                            Blood Group: <strong><?= htmlspecialchars($patient['blood_group'] ?? 'O+') ?></strong>
                        </div>

                        <ul class="list-group list-group-flush text-start small">
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Username:</span>
                                <strong><?= htmlspecialchars($patient['username'] ?? '') ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Email:</span>
                                <strong><?= htmlspecialchars($patient['email'] ?? '') ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span class="text-muted">Account Role:</span>
                                <span class="badge bg-success-subtle text-success">Verified Patient</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Editable Details Form -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                        <h5 class="fw-bold mb-4 text-primary"><i class="bi bi-pencil-square me-2"></i>Edit Medical & Contact Details</h5>

                        <form method="POST" action="profile.php">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Full Legal Name *</label>
                                    <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($patient['full_name'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Phone Number *</label>
                                    <input type="tel" name="phone" class="form-control" required value="<?= htmlspecialchars($patient['phone'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Date of Birth</label>
                                    <input type="date" name="dob" class="form-control" value="<?= htmlspecialchars($patient['dob'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Gender</label>
                                    <select name="gender" class="form-select">
                                        <option value="Male" <?= ($patient['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                                        <option value="Female" <?= ($patient['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                                        <option value="Other" <?= ($patient['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Blood Group</label>
                                    <select name="blood_group" class="form-select">
                                        <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg): ?>
                                            <option value="<?= $bg ?>" <?= ($patient['blood_group'] ?? '') === $bg ? 'selected' : '' ?>><?= $bg ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Residential Address</label>
                                    <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($patient['address'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Emergency Contact (Name, Relationship, Phone)</label>
                                    <input type="text" name="emergency_contact" class="form-control" value="<?= htmlspecialchars($patient['emergency_contact'] ?? '') ?>">
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                                        <i class="bi bi-save me-1"></i> Save Profile Changes
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
