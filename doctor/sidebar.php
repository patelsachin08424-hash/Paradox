<?php
/**
 * Doctor Portal Sidebar Component
 */
$currentPage = basename($_SERVER['PHP_SELF']);
$doctorName = $_SESSION['user_name'] ?? 'Doctor';
$doctorEmail = $_SESSION['email'] ?? 'doctor@pulsehealth.edu';
?>
<aside class="portal-sidebar">
    <div class="portal-brand">
        <div class="brand-pulse-icon">
            <i class="bi bi-heart-pulse-fill"></i>
        </div>
        <div>
            <h6 class="mb-0 fw-bold text-white"><?= APP_NAME ?></h6>
            <small class="text-white-50">Physician Portal</small>
        </div>
    </div>

    <div class="portal-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="dashboard.php" class="portal-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="appointments.php" class="portal-link <?= $currentPage === 'appointments.php' ? 'active' : '' ?>">
                    <i class="bi bi-calendar3-range-fill"></i>
                    <span>Manage Visits</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="add-record.php" class="portal-link <?= $currentPage === 'add-record.php' ? 'active' : '' ?>">
                    <i class="bi bi-clipboard2-plus-fill"></i>
                    <span>Add Medical Record</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="create-prescription.php" class="portal-link <?= $currentPage === 'create-prescription.php' ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-medical-fill"></i>
                    <span>Create Prescription</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="patient-history.php" class="portal-link <?= $currentPage === 'patient-history.php' ? 'active' : '' ?>">
                    <i class="bi bi-people-fill"></i>
                    <span>Patient History</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="profile.php" class="portal-link <?= $currentPage === 'profile.php' ? 'active' : '' ?>">
                    <i class="bi bi-person-badge-fill"></i>
                    <span>Physician Profile</span>
                </a>
            </li>
            <li class="nav-item mt-3 pt-3 border-top border-secondary border-opacity-25">
                <a href="../index.php" class="portal-link">
                    <i class="bi bi-house-door-fill"></i>
                    <span>Hospital Home</span>
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
        <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
            <i class="bi bi-stethoscope"></i>
        </div>
        <div class="overflow-hidden">
            <div class="text-white fw-bold small text-truncate"><?= htmlspecialchars($doctorName) ?></div>
            <div class="text-muted small text-truncate"><?= htmlspecialchars($doctorEmail) ?></div>
        </div>
    </div>
</aside>
