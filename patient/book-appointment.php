<?php
/**
 * Book Appointment Script
 * University Project: Online Healthcare Management System
 */
require_once __DIR__ . '/../config/db.php';
requireRole('patient', 'login.php');

$patientId = $_SESSION['patient_id'] ?? 0;
$preSelectedDoctor = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;
$error = '';

// Fetch all active doctors
$doctors = [];
try {
    $stmt = $pdo->query("SELECT d.*, u.status FROM doctors d JOIN users u ON d.user_id = u.id WHERE u.status = 'active' ORDER BY d.full_name ASC");
    $doctors = $stmt->fetchAll();
} catch (Exception $e) {
    $error = 'Error loading doctors: ' . $e->getMessage();
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id        = (int)($_POST['doctor_id'] ?? 0);
    $appointment_date = sanitize($_POST['appointment_date'] ?? '');
    $appointment_time = sanitize($_POST['appointment_time'] ?? '');
    $reason           = sanitize($_POST['reason'] ?? '');

    if (empty($doctor_id) || empty($appointment_date) || empty($appointment_time) || empty($reason)) {
        $error = 'All fields are mandatory to schedule your consultation.';
    } elseif (strtotime($appointment_date) < strtotime(date('Y-m-d'))) {
        $error = 'You cannot book an appointment for a date in the past.';
    } else {
        try {
            // Check for double booking conflict with the same doctor at the same time
            $stmtCheck = $pdo->prepare("SELECT id FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'Cancelled'");
            $stmtCheck->execute([$doctor_id, $appointment_date, $appointment_time]);

            if ($stmtCheck->rowCount() > 0) {
                $error = 'This doctor already has a booked consultation for that specific date & time slot. Please choose another time.';
            } else {
                $stmtInsert = $pdo->prepare("
                    INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, status)
                    VALUES (?, ?, ?, ?, ?, 'Pending')
                ");
                $stmtInsert->execute([$patientId, $doctor_id, $appointment_date, $appointment_time, $reason]);
                $bookingId = $pdo->lastInsertId();

                setFlash('action_msg', "Appointment #APT-{$bookingId} submitted successfully! Your doctor will review and confirm shortly.", 'success');
                redirect('appointments.php');
            }
        } catch (Exception $e) {
            $error = 'Booking failed: ' . $e->getMessage();
        }
    }
}

$pageTitle = "Book Appointment";
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
                <h5 class="mb-0 fw-bold text-dark">Schedule Clinical Appointment</h5>
            </div>
            <a href="appointments.php" class="btn btn-sm btn-outline-secondary rounded-pill">
                <i class="bi bi-calendar2-check me-1"></i> My Appointments
            </a>
        </header>

        <main class="portal-main">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-primary text-white p-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-white text-primary rounded-circle p-3">
                                    <i class="bi bi-calendar-plus fs-3"></i>
                                </div>
                                <div>
                                    <h4 class="fw-bold mb-1">New Appointment Request</h4>
                                    <p class="text-white-50 small mb-0">Select your preferred physician, available day, and visit reason</p>
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

                            <form method="POST" action="book-appointment.php">
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-dark">1. Select Attending Doctor *</label>
                                    <select name="doctor_id" class="form-select form-select-lg" required>
                                        <option value="">-- Choose Specialist / Department --</option>
                                        <?php foreach ($doctors as $d): ?>
                                            <option value="<?= $d['id'] ?>" <?= ($preSelectedDoctor == $d['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($d['full_name']) ?> — <?= htmlspecialchars($d['specialization']) ?> (Fee: $<?= htmlspecialchars($d['consultation_fee']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text small">Fee is payable at the clinic reception on the day of consultation.</div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-dark">2. Preferred Consultation Date *</label>
                                        <input type="date" name="appointment_date" id="appointment_date" class="form-control form-control-lg" required min="<?= date('Y-m-d') ?>" value="<?= sanitize($_POST['appointment_date'] ?? date('Y-m-d', strtotime('+1 day'))) ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-dark">3. Time Slot *</label>
                                        <select name="appointment_time" class="form-select form-select-lg" required>
                                            <option value="09:00:00">09:00 AM (Morning Slot)</option>
                                            <option value="09:45:00">09:45 AM (Morning Slot)</option>
                                            <option value="10:30:00" selected>10:30 AM (Morning Slot)</option>
                                            <option value="11:15:00">11:15 AM (Morning Slot)</option>
                                            <option value="14:00:00">02:00 PM (Afternoon Slot)</option>
                                            <option value="14:45:00">02:45 PM (Afternoon Slot)</option>
                                            <option value="15:30:00">03:30 PM (Evening Slot)</option>
                                            <option value="16:15:00">04:15 PM (Evening Slot)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold text-dark">4. Clinical Reason & Current Symptoms *</label>
                                    <textarea name="reason" class="form-control" rows="4" required placeholder="Describe your symptoms (e.g. chest heaviness, persistent headache, medication follow-up)..."><?= sanitize($_POST['reason'] ?? '') ?></textarea>
                                    <div class="form-text small">This confidential detail aids the doctor in pre-evaluating your case.</div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold py-3">
                                        <i class="bi bi-calendar-check-fill me-2"></i> Confirm Booking Request
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
