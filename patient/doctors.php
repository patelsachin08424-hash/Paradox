<?php
/**
 * Doctor Directory with Search & Filter
 * University Project: Online Healthcare Management System
 */
require_once __DIR__ . '/../config/db.php';
requireRole('patient', 'login.php');

$doctors = [];
$specialties = [];

if ($db_connected && $pdo) {
    try {
        $stmt = $pdo->query("SELECT d.*, u.email FROM doctors d JOIN users u ON d.user_id = u.id WHERE u.status = 'active' ORDER BY d.full_name ASC");
        $doctors = $stmt->fetchAll();

        $stmtSpec = $pdo->query("SELECT DISTINCT specialization FROM doctors ORDER BY specialization ASC");
        $specialties = $stmtSpec->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        $doctors = [];
    }
}

$pageTitle = "Find Specialists";
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
                <h5 class="mb-0 fw-bold text-dark">Find Doctors & Specialists</h5>
            </div>
            <a href="book-appointment.php" class="btn btn-sm btn-primary rounded-pill px-3">
                <i class="bi bi-calendar-plus me-1"></i> Book Appointment
            </a>
        </header>

        <main class="portal-main">
            <!-- Search & Filter Controls -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <div class="row g-3 align-items-center">
                    <div class="col-md-7">
                        <label class="form-label small fw-semibold text-muted">Search by Doctor Name or Clinical Specialty</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="doctorSearchInput" class="form-control" placeholder="Search e.g. Dr. Robert or Cardiology...">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold text-muted">Filter by Department</label>
                        <select id="specialtyFilter" class="form-select">
                            <option value="all">All Specialties & Departments</option>
                            <?php foreach ($specialties as $spec): ?>
                                <option value="<?= htmlspecialchars($spec) ?>"><?= htmlspecialchars($spec) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Doctor Cards Grid -->
            <div class="row g-4" id="doctorsGrid">
                <?php if (!empty($doctors)): ?>
                    <?php foreach ($doctors as $doc): ?>
                        <div class="col-lg-4 col-md-6 doctor-item-card" data-name="<?= htmlspecialchars($doc['full_name']) ?>" data-specialty="<?= htmlspecialchars($doc['specialization']) ?>">
                            <div class="doctor-card">
                                <div class="doctor-avatar-box">
                                    <i class="bi bi-person-badge"></i>
                                </div>
                                <div class="p-4 text-center pt-0">
                                    <div class="d-flex justify-content-center">
                                        <div class="doctor-avatar-circle">
                                            <i class="bi bi-stethoscope"></i>
                                        </div>
                                    </div>
                                    <h5 class="fw-bold mt-3 mb-1"><?= htmlspecialchars($doc['full_name']) ?></h5>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill mb-2">
                                        <?= htmlspecialchars($doc['specialization']) ?>
                                    </span>
                                    <p class="small text-muted mb-2"><strong><?= htmlspecialchars($doc['qualification']) ?></strong></p>
                                    <p class="small text-secondary mb-3"><?= htmlspecialchars($doc['bio']) ?></p>

                                    <div class="p-2 bg-light rounded-3 text-start small mb-3">
                                        <div><i class="bi bi-door-open-fill text-primary me-2"></i><strong>Room:</strong> <?= htmlspecialchars($doc['room_no']) ?></div>
                                        <div><i class="bi bi-cash-stack text-success me-2"></i><strong>Fee:</strong> $<?= htmlspecialchars($doc['consultation_fee']) ?></div>
                                        <div><i class="bi bi-telephone text-secondary me-2"></i><?= htmlspecialchars($doc['phone']) ?></div>
                                        <div><i class="bi bi-clock-fill text-muted me-2"></i><?= htmlspecialchars($doc['available_days']) ?></div>
                                    </div>

                                    <a href="book-appointment.php?doctor_id=<?= $doc['id'] ?>" class="btn btn-primary w-100 rounded-pill">
                                        <i class="bi bi-calendar-check me-1"></i> Book with this Doctor
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center py-4 rounded-4">
                            <i class="bi bi-info-circle-fill fs-3 d-block mb-2"></i>
                            No doctors found in the database. Run the database setup script to seed specialist doctors.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
