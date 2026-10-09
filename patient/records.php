<?php
/**
 * Patient Medical Records & Diagnostic History
 * University Project: Online Healthcare Management System
 */
require_once __DIR__ . '/../config/db.php';
requireRole('patient', 'login.php');

$patientId = $_SESSION['patient_id'] ?? 0;
$records = [];

try {
    $stmt = $pdo->prepare("
        SELECT mr.*, d.full_name AS doctor_name, d.specialization, d.qualification
        FROM medical_records mr
        JOIN doctors d ON mr.doctor_id = d.id
        WHERE mr.patient_id = ?
        ORDER BY mr.recorded_at DESC
    ");
    $stmt->execute([$patientId]);
    $records = $stmt->fetchAll();
} catch (Exception $e) {
    //
}

$pageTitle = "My Medical Records";
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
                <h5 class="mb-0 fw-bold text-dark">Medical & Diagnostic Records</h5>
            </div>
            <a href="dashboard.php" class="btn btn-sm btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </header>

        <main class="portal-main">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="fw-bold text-dark mb-1">Clinical History</h5>
                    <p class="text-muted small mb-0">Authorized diagnoses recorded by PulseHealth attending specialists</p>
                </div>
                <button class="btn btn-outline-primary btn-sm rounded-pill" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Print History
                </button>
            </div>

            <?php if (!empty($records)): ?>
                <div class="row g-4">
                    <?php foreach ($records as $rec): ?>
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                <div class="card-header bg-white p-4 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-primary-subtle text-primary rounded-3 p-3">
                                            <i class="bi bi-clipboard2-pulse fs-3"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($rec['diagnosis']) ?></h5>
                                            <div class="small text-muted">
                                                <span>Attending Physician: <strong><?= htmlspecialchars($rec['doctor_name']) ?></strong> (<?= htmlspecialchars($rec['specialization']) ?>)</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                                        <i class="bi bi-clock-history me-1 text-primary"></i> <?= date('M d, Y - h:i A', strtotime($rec['recorded_at'])) ?>
                                    </div>
                                </div>

                                <div class="card-body p-4">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <h6 class="fw-bold text-secondary small text-uppercase mb-2"><i class="bi bi-thermometer-half me-1"></i>Symptoms Reported:</h6>
                                            <div class="p-3 bg-light rounded-3 small text-dark">
                                                <?= nl2br(htmlspecialchars($rec['symptoms'])) ?>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <h6 class="fw-bold text-secondary small text-uppercase mb-2"><i class="bi bi-file-earmark-medical me-1"></i>Diagnostic & Lab Test Results:</h6>
                                            <div class="p-3 bg-light rounded-3 small text-dark">
                                                <?= !empty($rec['test_results']) ? nl2br(htmlspecialchars($rec['test_results'])) : '<em class="text-muted">No lab tests conducted for this consultation.</em>' ?>
                                            </div>
                                        </div>

                                        <?php if (!empty($rec['notes'])): ?>
                                            <div class="col-12 mt-3">
                                                <h6 class="fw-bold text-secondary small text-uppercase mb-2"><i class="bi bi-journal-text me-1"></i>Physician Advice & Lifestyle Notes:</h6>
                                                <div class="p-3 bg-light-subtle border rounded-3 small text-dark">
                                                    <?= nl2br(htmlspecialchars($rec['notes'])) ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                    <i class="bi bi-folder-x fs-1 text-muted mb-3"></i>
                    <h5 class="fw-bold">No Medical Records Available</h5>
                    <p class="text-muted small">Your physician diagnostic notes and pathology test results will appear here after consultation.</p>
                    <div>
                        <a href="book-appointment.php" class="btn btn-primary rounded-pill btn-sm px-4">Book Consultation</a>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
