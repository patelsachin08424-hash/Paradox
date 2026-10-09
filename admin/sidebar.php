<?php
/**
 * Admin Portal Sidebar Component
 */
$currentPage = basename($_SERVER['PHP_SELF']);
$adminName = $_SESSION['user_name'] ?? 'System Administrator';
$adminEmail = $_SESSION['email'] ?? 'admin@pulsehealth.edu';
?>
<aside class="portal-sidebar">
    <div class="portal-brand">
        <div class="brand-pulse-icon" style="background: linear-gradient(135deg, #ef4444, #b91c1c);">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <div>
            <h6 class="mb-0 fw-bold text-white"><?= APP_NAME ?></h6>
            <small class="text-danger-emphasis fw-bold">Admin Control Console</small>
        </div>
    </div>

    <div class="portal-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="dashboard.php" class="portal-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard Analytics</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="manage-doctors.php" class="portal-link <?= $currentPage === 'manage-doctors.php' ? 'active' : '' ?>">
                    <i class="bi bi-stethoscope"></i>
                    <span>Manage Doctors</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="manage-patients.php" class="portal-link <?= $currentPage === 'manage-patients.php' ? 'active' : '' ?>">
                    <i class="bi bi-people-fill"></i>
                    <span>Manage Patients</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="manage-appointments.php" class="portal-link <?= $currentPage === 'manage-appointments.php' ? 'active' : '' ?>">
                    <i class="bi bi-calendar3"></i>
                    <span>Manage Appointments</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="manage-users.php" class="portal-link <?= $currentPage === 'manage-users.php' ? 'active' : '' ?>">
                    <i class="bi bi-shield-shaded"></i>
                    <span>Manage System Users</span>
                </a>
            </li>
            <li class="nav-item mt-3 pt-3 border-top border-secondary border-opacity-25">
                <a href="../setup.php" class="portal-link text-warning">
                    <i class="bi bi-database-gear"></i>
                    <span>Database Tools</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../index.php" class="portal-link">
                    <i class="bi bi-house-door-fill"></i>
                    <span>Hospital Website</span>
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
        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
            <i class="bi bi-shield-check"></i>
        </div>
        <div class="overflow-hidden">
            <div class="text-white fw-bold small text-truncate"><?= htmlspecialchars($adminName) ?></div>
            <div class="text-muted small text-truncate"><?= htmlspecialchars($adminEmail) ?></div>
        </div>
    </div>
</aside>
