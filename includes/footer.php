<?php
/**
 * Global Footer Partial
 */
$assetPrefix = isset($assetPrefix) ? $assetPrefix : '';
?>
    <!-- Public Footer -->
    <footer class="bg-dark text-white pt-5 pb-4 mt-auto border-top border-secondary border-opacity-25">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="brand-pulse-icon">
                            <i class="bi bi-heart-pulse-fill"></i>
                        </div>
                        <span class="fs-4 fw-bold text-white"><?= APP_NAME ?></span>
                    </div>
                    <p class="text-secondary small pe-lg-4">
                        Next-generation healthcare management ecosystem connecting patients, clinical specialists, and hospital administration with seamless digital appointments and real-time records.
                    </p>
                    <div class="d-flex gap-3 text-secondary">
                        <a href="#" class="text-secondary fs-5 hover-primary"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-secondary fs-5 hover-primary"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="text-secondary fs-5 hover-primary"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="text-secondary fs-5 hover-primary"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="text-white fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled small text-secondary">
                        <li class="mb-2"><a href="<?= $assetPrefix ?>index.php#home" class="text-secondary text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="<?= $assetPrefix ?>index.php#services" class="text-secondary text-decoration-none">Services</a></li>
                        <li class="mb-2"><a href="<?= $assetPrefix ?>index.php#doctors" class="text-secondary text-decoration-none">Specialists</a></li>
                        <li class="mb-2"><a href="<?= $assetPrefix ?>index.php#about" class="text-secondary text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="<?= $assetPrefix ?>index.php#contact" class="text-secondary text-decoration-none">Emergency Contact</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="text-white fw-bold mb-3">Access Portals</h6>
                    <ul class="list-unstyled small text-secondary">
                        <li class="mb-2"><a href="<?= $assetPrefix ?>patient/login.php" class="text-secondary text-decoration-none">Patient Portal</a></li>
                        <li class="mb-2"><a href="<?= $assetPrefix ?>doctor/login.php" class="text-secondary text-decoration-none">Doctor Portal</a></li>
                        <li class="mb-2"><a href="<?= $assetPrefix ?>admin/login.php" class="text-secondary text-decoration-none">Hospital Admin</a></li>
                        <li class="mb-2"><a href="<?= $assetPrefix ?>patient/register.php" class="text-secondary text-decoration-none">New Patient Sign Up</a></li>
                        <li class="mb-2"><a href="<?= $assetPrefix ?>setup.php" class="text-warning text-decoration-none"><i class="bi bi-gear-fill me-1"></i>Database Setup</a></li>
                    </ul>
                </div>

                <div class="col-lg-4 col-md-6">
                    <h6 class="text-white fw-bold mb-3">Emergency Care 24/7</h6>
                    <p class="text-secondary small mb-2"><i class="bi bi-telephone-fill text-danger me-2"></i> +1 (800) 432-5844 (Toll Free)</p>
                    <p class="text-secondary small mb-2"><i class="bi bi-envelope-fill text-primary me-2"></i> emergency@pulsehealth-hospital.edu</p>
                    <p class="text-secondary small"><i class="bi bi-geo-alt-fill text-danger me-2"></i> 742 Evergreen Healthcare Ave, Medical District, NY</p>
                </div>
            </div>

            <hr class="border-secondary opacity-25 my-4">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-secondary">
                <p class="mb-0">&copy; <?= date('Y') ?> <?= APP_NAME ?>. Developed for University Examination & Demonstration.</p>
                <div class="d-flex gap-3 mt-2 mt-md-0">
                    <span class="text-success-emphasis"><i class="bi bi-shield-check me-1"></i> HIPAA Compliant Security</span>
                    <a href="<?= $assetPrefix ?>setup.php" class="badge bg-primary text-white text-decoration-none">Installer</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Application JS -->
    <script src="<?= $assetPrefix ?>assets/js/main.js"></script>
</body>
</html>
