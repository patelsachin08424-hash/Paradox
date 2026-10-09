<?php
/**
 * Doctor Create Prescription Script
 * University Project: Online Healthcare Management System
 */
require_once __DIR__ . '/../config/db.php';
requireRole('doctor', 'login.php');

$doctorId = $_SESSION['doctor_id'] ?? 0;
$selectedAppointmentId = isset($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : null;
$selectedPatientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;
$error = '';

// If appointment ID was provided but patient ID was not, fetch patient ID from appointment
if ($selectedAppointmentId && !$selectedPatientId) {
    try {
        $stmtApt = $pdo->prepare("SELECT patient_id FROM appointments WHERE id = ?");
        $stmtApt->execute([$selectedAppointmentId]);
        $selectedPatientId = (int)$stmtApt->fetchColumn();
    } catch (Exception $e) {}
}

// Fetch all registered patients
$patients = [];
try {
    $stmt = $pdo->query("SELECT id, full_name, phone, blood_group, gender FROM patients ORDER BY full_name ASC");
    $patients = $stmt->fetchAll();
} catch (Exception $e) {}

// Handle Prescription Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id     = (int)($_POST['patient_id'] ?? 0);
    $appointment_id = !empty($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : null;
    $medicines      = sanitize($_POST['medicines'] ?? '');
    $dosage         = sanitize($_POST['dosage'] ?? '');
    $instructions   = sanitize($_POST['instructions'] ?? '');

    if (empty($patient_id) || empty($medicines) || empty($dosage)) {
        $error = 'Patient selection, medication names, and dosage schedules are required.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO prescriptions (appointment_id, patient_id, doctor_id, medicines, dosage, instructions)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$appointment_id, $patient_id, $doctorId, $medicines, $dosage, $instructions]);
            $prescriptionId = $pdo->lastInsertId();

            setFlash('action_msg', "Prescription #PRX-{$prescriptionId} issued successfully.", 'success');
            redirect("patient-history.php?patient_id={$patient_id}");
        } catch (Exception $e) {
            $error = 'Failed to generate prescription: ' . $e->getMessage();
        }
    }
}

$pageTitle = "Create Electronic Prescription";
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
                <h5 class="mb-0 fw-bold text-dark">Electronic Prescription Generator (e-Rx)</h5>
            </div>
            <a href="dashboard.php" class="btn btn-sm btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </header>

        <main class="portal-main">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-primary text-white p-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-white text-primary rounded-circle p-3">
                                    <i class="bi bi-capsule fs-3"></i>
                                </div>
                                <div>
                                    <h4 class="fw-bold mb-1">Prescribe Medication</h4>
                                    <p class="text-white-50 small mb-0">Generate verifiable electronic prescriptions with pharmacy instructions</p>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-4 p-md-5">
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="create-prescription.php">
                                <?php if ($selectedAppointmentId): ?>
                                    <input type="hidden" name="appointment_id" value="<?= $selectedAppointmentId ?>">
                                    <div class="alert alert-light border small mb-4">
                                        <i class="bi bi-link-45deg text-primary me-1"></i> Linking prescription to Appointment <strong>#APT-<?= $selectedAppointmentId ?></strong>
                                    </div>
                                <?php endif; ?>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-dark">Select Patient *</label>
                                    <select name="patient_id" class="form-select form-select-lg" required>
                                        <option value="">-- Choose Patient --</option>
                                        <?php foreach ($patients as $p): ?>
                                            <option value="<?= $p['id'] ?>" <?= ($selectedPatientId == $p['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($p['full_name']) ?> (<?= htmlspecialchars($p['gender']) ?>, Blood: <?= htmlspecialchars($p['blood_group']) ?>) &bull; <?= htmlspecialchars($p['phone']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-dark">Prescribed Drugs & Dosages *</label>
                                    <textarea name="medicines" class="form-control" rows="4" required placeholder="1. Amoxicillin 500mg capsules&#10;2. Paracetamol 650mg tablets&#10;3. Cetirizine 10mg tablets"><?= sanitize($_POST['medicines'] ?? '') ?></textarea>
                                    <div class="form-text small">Enter each drug name, pharmaceutical strength, and unit per line.</div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-dark">Dosage Schedule & Intake Frequency *</label>
                                    <textarea name="dosage" class="form-control" rows="3" required placeholder="Amoxicillin: 1 capsule three times daily after meals for 7 days.&#10;Paracetamol: 1 tablet as needed for temperature above 100°F."><?= sanitize($_POST['dosage'] ?? '') ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-dark">Pharmacist Warnings & Patient Dietary Instructions</label>
                                    <textarea name="instructions" class="form-control" rows="3" placeholder="Take with plenty of water. Complete full course of antibiotics even if feeling better. Avoid alcohol during treatment."><?= sanitize($_POST['instructions'] ?? '') ?></textarea>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold py-3">
                                        <i class="bi bi-file-earmark-check-fill me-2"></i> Issue Electronic Prescription
                                    </button>
                                </div>
                            </form>
                        </div>
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
