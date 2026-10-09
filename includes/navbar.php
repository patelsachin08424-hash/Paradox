<?php
/**
 * Navigation Bar Partial
 */
$assetPrefix = isset($assetPrefix) ? $assetPrefix : '';
?>
<!-- Top Info Bar -->
<div class="top-bar d-none d-md-block">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-4">
            <span><i class="bi bi-telephone-fill text-danger me-2"></i> Emergency: <strong>+1 (800) 432-5844</strong></span>
            <span><i class="bi bi-clock-fill text-primary me-2"></i> Mon - Sun: 24/7 Clinical Operations</span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="<?= $assetPrefix ?>setup.php"><i class="bi bi-database-check me-1"></i>DB Setup</a>
            <span class="text-secondary">|</span>
            <a href="#contact"><i class="bi bi-geo-alt-fill me-1"></i>Location</a>
        </div>
    </div>
</div>

<!-- Main Sticky Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= $assetPrefix ?>index.php">
            <div class="brand-pulse-icon">
                <i class="bi bi-heart-pulse-fill"></i>
            </div>
            <span><?= APP_NAME ?></span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" href="<?= $assetPrefix ?>index.php#home">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $assetPrefix ?>index.php#services">Services</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $assetPrefix ?>index.php#doctors">Doctors</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $assetPrefix ?>index.php#about">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $assetPrefix ?>index.php#contact">Contact</a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <?php if (isLoggedIn()): ?>
                    <?php 
                        $dashUrl = $assetPrefix . 'patient/dashboard.php';
                        if ($_SESSION['role'] === 'doctor') $dashUrl = $assetPrefix . 'doctor/dashboard.php';
                        if ($_SESSION['role'] === 'admin') $dashUrl = $assetPrefix . 'admin/dashboard.php';
                    ?>
                    <a href="<?= $dashUrl ?>" class="btn btn-primary rounded-pill px-3">
                        <i class="bi bi-speedometer2 me-1"></i> My Dashboard (<?= ucfirst($_SESSION['role']) ?>)
                    </a>
                    <a href="<?= $assetPrefix ?>logout.php" class="btn btn-outline-danger rounded-pill px-3">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                <?php else: ?>
                    <!-- Portal Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-outline-primary rounded-pill px-3 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-lock me-1"></i> Sign In
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                            <li><h6 class="dropdown-header text-uppercase small text-muted">Choose Your Portal</h6></li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= $assetPrefix ?>patient/login.php">
                                    <i class="bi bi-person-circle text-primary fs-5"></i>
                                    <div>
                                        <strong>Patient Login</strong>
                                        <div class="small text-muted">View records & appointments</div>
                                    </div>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= $assetPrefix ?>doctor/login.php">
                                    <i class="bi bi-stethoscope text-info fs-5"></i>
                                    <div>
                                        <strong>Doctor Portal</strong>
                                        <div class="small text-muted">Consultations & Prescriptions</div>
                                    </div>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= $assetPrefix ?>admin/login.php">
                                    <i class="bi bi-shield-lock-fill text-danger fs-5"></i>
                                    <div>
                                        <strong>Hospital Admin</strong>
                                        <div class="small text-muted">System & User Management</div>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Patient Register Button -->
                    <a href="<?= $assetPrefix ?>patient/register.php" class="btn btn-primary rounded-pill px-3 shadow-sm">
                        <i class="bi bi-person-plus-fill me-1"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
