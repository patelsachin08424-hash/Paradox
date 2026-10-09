<?php
/**
 * Doctor Add Medical Record Script
 * University Project: Online Healthcare Management System
 */
require_once __DIR__ . '/../config/db.php';
requireRole('doctor', 'login.php');

$doctorId = $_SESSION['doctor_id'] ?? 0;
$selectedPatientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;
$error = '';
$success = '';

// Fetch all registered patients
$patients = [];
try {
    $stmt = $pdo->query("SELECT id, full_name, phone, blood_group, gender FROM patients ORDER BY full_name ASC");
    $patients = $stmt->fetchAll();
} catch (Exception $e) {
    //
}

// Handle Record Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id   = (int)($_POST['patient_id'] ?? 0);
    $diagnosis    = sanitize($_POST['diagnosis'] ?? '');
    $symptoms     = sanitize($_POST['symptoms'] ?? '');
    $test_results = sanitize($_POST['test_results'] ?? '');
    $notes        = sanitize($_POST['notes'] ?? '');

    if (empty($patient_id) || empty($diagnosis) || empty($symptoms)) {
        $error = 'Patient selection, primary diagnosis, and recorded symptoms are required.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO medical_records (patient_id, doctor_id, diagnosis, symptoms, test_results, notes)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$patient_id, $doctorId, $diagnosis, $symptoms, $test_results, $notes]);
            $recordId = $pdo->lastInsertId();

            setFlash('action_msg', "Clinical Diagnostic Record #MR-{$recordId} saved to patient profile.", 'success');
            redirect("patient-history.php?patient_id={$patient_id}");
        } catch (Exception $e) {
            $error = 'Could not save medical record: ' . $e->getMessage();
        }
    }
}

$pageTitle = "Add Medical Record";
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
                <h5 class="mb-0 fw-bold text-dark">Document Medical Record</h5>
            </div>
            <a href="patient-history.php" class="btn btn-sm btn-outline-secondary rounded-pill">
                <i class="bi bi-folder2-open me-1"></i> Patient History
            </a>
        </header>

        <main class="portal-main">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-dark text-white p-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-info text-white rounded-circle p-3">
                                    <i class="bi bi-clipboard2-plus fs-3"></i>
                                </div>
                                <div>
                                    <h4 class="fw-bold mb-1">New Clinical Diagnostic Entry</h4>
                                    <p class="text-white-50 small mb-0">Record formal medical observations, diagnostic tests, and advice</p>
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

                            <form method="POST" action="add-record.php">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-dark">Select Patient *</label>
                                    <select name="patient_id" class="form-select form-select-lg" required>
                                        <option value="">-- Choose Registered Patient --</option>
                                        <?php foreach ($patients as $p): ?>
                                            <option value="<?= $p['id'] ?>" <?= ($selectedPatientId == $p['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($p['full_name']) ?> (<?= htmlspecialchars($p['gender']) ?>, Blood: <?= htmlspecialchars($p['blood_group']) ?>) &bull; <?= htmlspecialchars($p['phone']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-dark">Primary Diagnosis / Clinical Condition *</label>
                                    <input type="text" name="diagnosis" class="form-control form-control-lg" required placeholder="e.g. Acute Bronchitis, Type 2 Diabetes Mellitus, Migraine" value="<?= sanitize($_POST['diagnosis'] ?? '') ?>">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-dark">Presenting Symptoms & Clinical History *</label>
                                    <textarea name="symptoms" class="form-control" rows="3" required placeholder="Detail the symptoms described by the patient during physical examination..."><?= sanitize($_POST['symptoms'] ?? '') ?></textarea>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-dark">Laboratory / Diagnostic Test Observations</label>
                                    <textarea name="test_results" class="form-control" rows="3" placeholder="e.g. Blood Pressure: 128/84 mmHg, Fasting Blood Glucose: 98 mg/dL, ECG: Normal Sinus Rhythm..."><?= sanitize($_POST['test_results'] ?? '') ?></textarea>
                                    <div class="form-text small">Include vital signs, imaging findings, or blood panel values if relevant.</div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-dark">Physician Recommendations & Follow-Up Notes</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="e.g. Advised hydration, stress management, follow-up consult in 3 weeks..."><?= sanitize($_POST['notes'] ?? '') ?></textarea>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold py-3">
                                        <i class="bi bi-check-circle-fill me-2"></i> Save Official Medical Record
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
