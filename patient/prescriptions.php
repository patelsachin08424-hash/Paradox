<?php
/**
 * Patient Prescriptions View
 * University Project: Online Healthcare Management System
 */
require_once __DIR__ . '/../config/db.php';
requireRole('patient', 'login.php');

$patientId = $_SESSION['patient_id'] ?? 0;
$prescriptions = [];

try {
    $stmt = $pdo->prepare("
        SELECT pr.*, d.full_name AS doctor_name, d.specialization, d.qualification, d.room_no, d.phone AS doctor_phone,
               p.full_name AS patient_name, p.dob, p.gender, p.blood_group
        FROM prescriptions pr
        JOIN doctors d ON pr.doctor_id = d.id
        JOIN patients p ON pr.patient_id = p.id
        WHERE pr.patient_id = ?
        ORDER BY pr.prescribed_at DESC
    ");
    $stmt->execute([$patientId]);
    $prescriptions = $stmt->fetchAll();
} catch (Exception $e) {
    //
}

$pageTitle = "My Prescriptions";
$assetPrefix = "../";
include __DIR__ . '/../includes/header.php';
?>

<div class="portal-wrapper">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="portal-content">
        <header class="portal-topbar no-print">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-light d-lg-none" id="sidebarToggle" type="button">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <h5 class="mb-0 fw-bold text-dark">My Digital Prescriptions</h5>
            </div>
            <a href="dashboard.php" class="btn btn-sm btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </header>

        <main class="portal-main">
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <div>
                    <h5 class="fw-bold text-dark mb-1">Electronic Prescriptions (e-Rx)</h5>
                    <p class="text-muted small mb-0">Authorized medication schedules and dosing directions</p>
                </div>
            </div>

            <?php if (!empty($prescriptions)): ?>
                <div class="row g-4">
                    <?php foreach ($prescriptions as $pr): ?>
                        <div class="col-12">
                            <div class="prescription-sheet mb-4">
                                <!-- Print Trigger in top right -->
                                <div class="position-absolute top-0 end-0 m-4 no-print">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="window.print()">
                                        <i class="bi bi-printer-fill me-1"></i> Print e-Rx
                                    </button>
                                </div>

                                <!-- Prescription Hospital Header -->
                                <div class="prescription-header">
                                    <div class="row align-items-center">
                                        <div class="col-sm-8">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="brand-pulse-icon">
                                                    <i class="bi bi-heart-pulse-fill"></i>
                                                </div>
                                                <div>
                                                    <h4 class="fw-bold text-dark mb-0"><?= APP_NAME ?> Clinical Center</h4>
                                                    <small class="text-muted">742 Evergreen Healthcare Ave, Medical District &bull; Phone: +1 800-432-5844</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 text-sm-end mt-2 mt-sm-0">
                                            <div class="badge bg-light text-dark border px-3 py-2">
                                                Rx ID: #PRX-<?= str_pad($pr['id'], 5, '0', STR_PAD_LEFT) ?>
                                            </div>
                                            <div class="small text-muted mt-1">
                                                Date: <?= date('M d, Y', strtotime($pr['prescribed_at'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Doctor & Patient Meta Table -->
                                <div class="row g-3 p-3 bg-light rounded-3 mb-4 small">
                                    <div class="col-md-6 border-end-md">
                                        <div><strong>Prescribing Physician:</strong> <?= htmlspecialchars($pr['doctor_name']) ?></div>
                                        <div><strong>Credentials:</strong> <?= htmlspecialchars($pr['qualification']) ?> (<?= htmlspecialchars($pr['specialization']) ?>)</div>
                                        <div><strong>Clinic Office:</strong> <?= htmlspecialchars($pr['room_no']) ?> &bull; <?= htmlspecialchars($pr['doctor_phone']) ?></div>
                                    </div>
                                    <div class="col-md-6 ps-md-4">
                                        <div><strong>Patient Name:</strong> <?= htmlspecialchars($pr['patient_name']) ?></div>
                                        <div><strong>Blood Group:</strong> <?= htmlspecialchars($pr['blood_group']) ?> &bull; <strong>Gender:</strong> <?= htmlspecialchars($pr['gender']) ?></div>
                                        <div><strong>Date of Issue:</strong> <?= date('F j, Y - g:i A', strtotime($pr['prescribed_at'])) ?></div>
                                    </div>
                                </div>

                                <!-- The Rx Symbol & Medication Body -->
                                <div class="mb-4">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <span class="rx-symbol">℞</span>
                                        <span class="fw-bold fs-5 text-dark">Prescribed Medication & Dosage Schedule</span>
                                    </div>

                                    <div class="card border border-light-subtle rounded-3 p-3 mb-3">
                                        <h6 class="fw-bold text-primary small text-uppercase mb-2">Medications:</h6>
                                        <div class="p-2 fw-semibold text-dark">
                                            <?= nl2br(htmlspecialchars($pr['medicines'])) ?>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="card border border-light-subtle rounded-3 p-3 h-100">
                                                <h6 class="fw-bold text-secondary small text-uppercase mb-2"><i class="bi bi-stopwatch me-1"></i>Dosage Frequency:</h6>
                                                <div class="small text-dark">
                                                    <?= nl2br(htmlspecialchars($pr['dosage'])) ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card border border-light-subtle rounded-3 p-3 h-100">
                                                <h6 class="fw-bold text-secondary small text-uppercase mb-2"><i class="bi bi-info-circle me-1"></i>Pharmacist / Dietary Directions:</h6>
                                                <div class="small text-dark">
                                                    <?= !empty($pr['instructions']) ? nl2br(htmlspecialchars($pr['instructions'])) : '<em class="text-muted">Take as directed with water.</em>' ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Signature Footer -->
                                <div class="pt-4 border-top d-flex justify-content-between align-items-end mt-4">
                                    <small class="text-muted">PulseHealth Digital Signature Verification &bull; Valid across all partner pharmacies</small>
                                    <div class="text-center">
                                        <div class="fw-bold text-primary font-monospace" style="font-family: cursive !important; font-size: 1.2rem;">
                                            <?= htmlspecialchars($pr['doctor_name']) ?>
                                        </div>
                                        <div class="border-top border-dark pt-1 small text-muted">Authorized Medical Practitioner</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                    <i class="bi bi-capsule fs-1 text-muted mb-3"></i>
                    <h5 class="fw-bold">No Active Prescriptions</h5>
                    <p class="text-muted small">Prescriptions generated by your physician during your clinical appointments will appear here.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
