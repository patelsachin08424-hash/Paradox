<?php
/**
 * Patient Portal Sidebar Component
 */
$currentPage = basename($_SERVER['PHP_SELF']);
$patientName = $_SESSION['user_name'] ?? 'Patient User';
$patientEmail = $_SESSION['email'] ?? 'patient@pulsehealth.edu';
?>
<aside class="portal-sidebar">
    <div class="portal-brand">
        <div class="brand-pulse-icon">
            <i class="bi bi-heart-pulse-fill"></i>
        </div>
        <div>
            <h6 class="mb-0 fw-bold text-white"><?= APP_NAME ?></h6>
            <small class="text-white-50">Patient Portal</small>
        </div>
    </div>

    <div class="portal-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="dashboard.php" class="portal-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="book-appointment.php" class="portal-link <?= $currentPage === 'book-appointment.php' ? 'active' : '' ?>">
                    <i class="bi bi-calendar-plus-fill"></i>
                    <span>Book Appointment</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="appointments.php" class="portal-link <?= $currentPage === 'appointments.php' ? 'active' : '' ?>">
                    <i class="bi bi-calendar2-check-fill"></i>
                    <span>My Appointments</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="doctors.php" class="portal-link <?= $currentPage === 'doctors.php' ? 'active' : '' ?>">
                    <i class="bi bi-person-badge-fill"></i>
                    <span>Find Doctors</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="records.php" class="portal-link <?= $currentPage === 'records.php' ? 'active' : '' ?>">
                    <i class="bi bi-folder2-open"></i>
                    <span>Medical Records</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="prescriptions.php" class="portal-link <?= $currentPage === 'prescriptions.php' ? 'active' : '' ?>">
                    <i class="bi bi-capsule"></i>
                    <span>Prescriptions</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="profile.php" class="portal-link <?= $currentPage === 'profile.php' ? 'active' : '' ?>">
                    <i class="bi bi-person-gear"></i>
                    <span>My Profile</span>
                </a>
            </li>
            <li class="nav-item mt-3 pt-3 border-top border-secondary border-opacity-25">
                <a href="../index.php" class="portal-link">
                    <i class="bi bi-house-door-fill"></i>
                    <span>Return to Home</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../logout.php" class="portal-link text-danger">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Log Out</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="portal-user-profile d-flex align-items-center gap-3">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
            <i class="bi bi-person"></i>
        </div>
        <div class="overflow-hidden">
            <div class="text-white fw-bold small text-truncate"><?= htmlspecialchars($patientName) ?></div>
            <div class="text-muted small text-truncate"><?= htmlspecialchars($patientEmail) ?></div>
        </div>
    </div>
</aside>
