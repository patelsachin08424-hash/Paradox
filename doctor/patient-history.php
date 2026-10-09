<?php
/**
 * Doctor View Patient Information & Complete Clinical History
 * University Project: Online Healthcare Management System
 */
require_once __DIR__ . '/../config/db.php';
requireRole('doctor', 'login.php');

$doctorId = $_SESSION['doctor_id'] ?? 0;
$selectedPatientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;

$patients = [];
$patient = null;
$records = [];
$prescriptions = [];
$appointments = [];

try {
    // All patients list for quick selection
    $stmtAll = $pdo->query("SELECT id, full_name, phone, blood_group, gender FROM patients ORDER BY full_name ASC");
    $patients = $stmtAll->fetchAll();

    if ($selectedPatientId > 0) {
        // Patient details
        $stmtP = $pdo->prepare("SELECT p.*, u.email FROM patients p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
        $stmtP->execute([$selectedPatientId]);
        $patient = $stmtP->fetch();

        // Medical records
        $stmtR = $pdo->prepare("
            SELECT mr.*, d.full_name AS doctor_name, d.specialization 
            FROM medical_records mr 
            JOIN doctors d ON mr.doctor_id = d.id 
            WHERE mr.patient_id = ? 
            ORDER BY mr.recorded_at DESC
        ");
        $stmtR->execute([$selectedPatientId]);
        $records = $stmtR->fetchAll();

        // Prescriptions
        $stmtPr = $pdo->prepare("
            SELECT pr.*, d.full_name AS doctor_name 
            FROM prescriptions pr 
            JOIN doctors d ON pr.doctor_id = d.id 
            WHERE pr.patient_id = ? 
            ORDER BY pr.prescribed_at DESC
        ");
        $stmtPr->execute([$selectedPatientId]);
        $prescriptions = $stmtPr->fetchAll();

        // Appointments history
        $stmtApt = $pdo->prepare("
            SELECT a.*, d.full_name AS doctor_name 
            FROM appointments a 
            JOIN doctors d ON a.doctor_id = d.id 
            WHERE a.patient_id = ? 
            ORDER BY a.appointment_date DESC
        ");
        $stmtApt->execute([$selectedPatientId]);
        $appointments = $stmtApt->fetchAll();
    }
} catch (Exception $e) {
    //
}

$pageTitle = "Patient Clinical History";
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
                <h5 class="mb-0 fw-bold text-dark">Patient Medical Background & Records</h5>
            </div>
            <a href="dashboard.php" class="btn btn-sm btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </header>

        <main class="portal-main">
            <!-- Patient Selector Dropdown -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <form method="GET" action="patient-history.php" class="row g-3 align-items-end">
                    <div class="col-md-9">
                        <label class="form-label small fw-semibold text-muted">Select Patient to Inspect Clinical Records:</label>
                        <select name="patient_id" class="form-select form-select-lg" onchange="this.form.submit()">
                            <option value="">-- Choose Patient Profile --</option>
                            <?php foreach ($patients as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= ($selectedPatientId == $p['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['full_name']) ?> (<?= htmlspecialchars($p['gender']) ?>, Blood: <?= htmlspecialchars($p['blood_group']) ?>) &bull; <?= htmlspecialchars($p['phone']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3">
                            <i class="bi bi-search me-1"></i> Load Records
                        </button>
                    </div>
                </form>
            </div>

            <?php if ($patient): ?>
                <!-- Patient Profile Summary -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                    <div class="row align-items-center g-3">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fs-2" style="width: 70px; height: 70px;">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div>
                                    <h4 class="fw-bold mb-1"><?= htmlspecialchars($patient['full_name']) ?></h4>
                                    <div class="d-flex flex-wrap gap-2 text-muted small">
                                        <span><i class="bi bi-calendar me-1"></i>DOB: <?= htmlspecialchars($patient['dob'] ?? 'N/A') ?></span>
                                        <span>&bull;</span>
                                        <span>Gender: <?= htmlspecialchars($patient['gender']) ?></span>
                                        <span>&bull;</span>
                                        <span>Blood: <strong class="badge bg-danger-subtle text-danger"><?= htmlspecialchars($patient['blood_group']) ?></strong></span>
                                        <span>&bull;</span>
                                        <span><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($patient['phone']) ?></span>
                                    </div>
                                    <div class="small text-secondary mt-1">
                                        <i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($patient['address'] ?? 'No residential address on file') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <a href="add-record.php?patient_id=<?= $patient['id'] ?>" class="btn btn-sm btn-primary rounded-pill px-3 me-1">
                                <i class="bi bi-plus-circle me-1"></i> Add Record
                            </a>
                            <a href="create-prescription.php?patient_id=<?= $patient['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="bi bi-capsule me-1"></i> Prescribe
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tabs for Records, Prescriptions, Visits -->
                <ul class="nav nav-pills mb-4" id="historyTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active rounded-pill px-4" id="records-tab" data-bs-toggle="pill" data-bs-target="#records-pane">
                            <i class="bi bi-clipboard2-pulse me-1"></i> Diagnostic Records (<?= count($records) ?>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link rounded-pill px-4" id="prescriptions-tab" data-bs-toggle="pill" data-bs-target="#prescriptions-pane">
                            <i class="bi bi-capsule me-1"></i> Prescriptions (<?= count($prescriptions) ?>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link rounded-pill px-4" id="visits-tab" data-bs-toggle="pill" data-bs-target="#visits-pane">
                            <i class="bi bi-calendar3 me-1"></i> Visit History (<?= count($appointments) ?>)
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="historyTabContent">
                    <!-- Tab 1: Diagnostic Records -->
                    <div class="tab-pane fade show active" id="records-pane">
                        <?php if (!empty($records)): ?>
                            <div class="row g-3">
                                <?php foreach ($records as $r): ?>
                                    <div class="col-12">
                                        <div class="card border-0 shadow-sm rounded-4 p-4">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($r['diagnosis']) ?></h5>
                                                    <small class="text-muted">Recorded by <strong><?= htmlspecialchars($r['doctor_name']) ?></strong> (<?= htmlspecialchars($r['specialization']) ?>)</small>
                                                </div>
                                                <span class="badge bg-light text-dark border px-3 py-2">
                                                    <?= date('M d, Y - h:i A', strtotime($r['recorded_at'])) ?>
                                                </span>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="p-3 bg-light rounded-3 small">
                                                        <strong>Symptoms:</strong><br>
                                                        <?= nl2br(htmlspecialchars($r['symptoms'])) ?>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="p-3 bg-light rounded-3 small">
                                                        <strong>Lab Observations:</strong><br>
                                                        <?= !empty($r['test_results']) ? nl2br(htmlspecialchars($r['test_results'])) : '<em class="text-muted">None noted.</em>' ?>
                                                    </div>
                                                </div>
                                                <?php if (!empty($r['notes'])): ?>
                                                    <div class="col-12">
                                                        <div class="p-3 bg-light-subtle border rounded-3 small">
                                                            <strong>Clinical Advice:</strong> <?= htmlspecialchars($r['notes']) ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="card border-0 shadow-sm rounded-4 p-4 text-center text-muted">
                                No diagnostic records found for this patient.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tab 2: Prescriptions -->
                    <div class="tab-pane fade" id="prescriptions-pane">
                        <?php if (!empty($prescriptions)): ?>
                            <div class="row g-3">
                                <?php foreach ($prescriptions as $pr): ?>
                                    <div class="col-12">
                                        <div class="card border-0 shadow-sm rounded-4 p-4">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <h6 class="fw-bold text-primary mb-1">Rx #PRX-<?= str_pad($pr['id'], 4, '0', STR_PAD_LEFT) ?></h6>
                                                    <small class="text-muted">Prescribed by <strong><?= htmlspecialchars($pr['doctor_name']) ?></strong></small>
                                                </div>
                                                <span class="badge bg-light text-dark border"><?= date('M d, Y', strtotime($pr['prescribed_at'])) ?></span>
                                            </div>
                                            <div class="p-3 bg-light rounded-3 small mb-2">
                                                <strong>Medications:</strong><br>
                                                <?= nl2br(htmlspecialchars($pr['medicines'])) ?>
                                            </div>
                                            <div class="p-3 bg-light-subtle border rounded-3 small">
                                                <strong>Dosage & Instructions:</strong><br>
                                                <?= nl2br(htmlspecialchars($pr['dosage'])) ?> &bull; <?= htmlspecialchars($pr['instructions']) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="card border-0 shadow-sm rounded-4 p-4 text-center text-muted">
                                No prescriptions issued yet for this patient.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tab 3: Appointments -->
                    <div class="tab-pane fade" id="visits-pane">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date & Time</th>
                                            <th>Doctor</th>
                                            <th>Reason</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($appointments)): ?>
                                            <?php foreach ($appointments as $a): ?>
                                                <tr>
                                                    <td><?= date('M d, Y', strtotime($a['appointment_date'])) ?> &bull; <?= date('h:i A', strtotime($a['appointment_time'])) ?></td>
                                                    <td><?= htmlspecialchars($a['doctor_name']) ?></td>
                                                    <td><?= htmlspecialchars($a['reason']) ?></td>
                                                    <td><?= getStatusBadge($a['status']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" class="text-center py-3 text-muted">No appointments recorded.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
                    <i class="bi bi-person-bounding-box fs-1 d-block mb-3 text-secondary"></i>
                    <h5>Select a Patient Above</h5>
                    <p class="small">Choose any registered patient from the dropdown list to inspect their clinical background, previous prescriptions, and diagnostic records.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
