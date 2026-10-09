<?php
/**
 * Flagship Public Homepage
 * Online Healthcare Management System - University Project
 */
require_once __DIR__ . '/config/db.php';

$pageTitle = "Leading Healthcare & Clinical Management Portal";
$assetPrefix = "";

// Fetch featured doctors from database if connected
$featuredDoctors = [];
if ($db_connected && $pdo) {
    try {
        $stmt = $pdo->query("SELECT d.*, u.email FROM doctors d JOIN users u ON d.user_id = u.id WHERE u.status = 'active' LIMIT 6");
        $featuredDoctors = $stmt->fetchAll();
    } catch (Exception $e) {
        // Fallback gracefully
        $featuredDoctors = [];
    }
}

// If database is empty or not yet run, provide fallback doctors for rich viva presentation
if (empty($featuredDoctors)) {
    $featuredDoctors = [
        [
            'full_name' => 'Dr. Robert Smith, MD',
            'specialization' => 'Cardiology',
            'qualification' => 'MD (Cardiology), FACC',
            'room_no' => 'Suite 302',
            'consultation_fee' => '750.00',
            'available_days' => 'Mon - Thu, 09:00 AM - 03:00 PM',
            'bio' => 'Senior Cardiologist with 14+ years experience in preventive cardiology, coronary interventions, and hypertension management.'
        ],
        [
            'full_name' => 'Dr. Elena Rostova, MD',
            'specialization' => 'Neurology',
            'qualification' => 'MD (Neurology), PhD',
            'room_no' => 'Suite 415',
            'consultation_fee' => '800.00',
            'available_days' => 'Tue - Fri, 10:00 AM - 04:00 PM',
            'bio' => 'Specialist in clinical neurophysiology, headache disorders, and neuromuscular rehabilitation with research background at Johns Hopkins.'
        ],
        [
            'full_name' => 'Dr. Marcus Vance, MBBS',
            'specialization' => 'Pediatrics',
            'qualification' => 'MBBS, DCH, FAAP',
            'room_no' => 'Suite 108',
            'consultation_fee' => '550.00',
            'available_days' => 'Mon - Sat, 08:30 AM - 02:00 PM',
            'bio' => 'Dedicated Pediatric specialist passionate about neonatal care, developmental milestones, and childhood immunization programs.'
        ]
    ];
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<!-- DB Connection Notification Banner (if DB needs initial setup) -->
<?php if (!$db_connected): ?>
    <div class="alert alert-warning mb-0 border-0 rounded-0 text-center py-2">
        <div class="container d-flex flex-column flex-md-row justify-content-center align-items-center gap-2">
            <span><i class="bi bi-exclamation-triangle-fill me-1"></i> Database <strong>healthcare_db</strong> is not yet initialized.</span>
            <a href="setup.php" class="btn btn-sm btn-dark rounded-pill fw-semibold">Click here to 1-Click Install Database</a>
        </div>
    </div>
<?php endif; ?>

<!-- Flash Messages -->
<?php 
$flashAuth = getFlash('auth_error') ?? getFlash('auth_success');
if ($flashAuth): 
?>
    <div class="container mt-3">
        <div class="alert alert-<?= $flashAuth['type'] ?> alert-dismissible fade show rounded-3" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i><?= $flashAuth['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
<?php endif; ?>

<!-- 1. HERO SECTION -->
<section id="home" class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="hero-badge">
                    <i class="bi bi-shield-check text-success"></i> 24/7 Board Certified Clinical Specialists
                </div>
                <h1 class="hero-title">
                    Compassionate Care Meets <span>Next-Gen Medical</span> Technology.
                </h1>
                <p class="hero-desc">
                    Experience seamless digital healthcare. Book certified doctors, access electronic health records, request laboratory diagnostics, and manage prescriptions—all in one unified platform.
                </p>
                
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="patient/login.php" class="btn btn-primary btn-lg rounded-pill px-4 py-3 shadow-sm">
                        <i class="bi bi-calendar-check-fill me-2"></i> Book an Appointment
                    </a>
                    <a href="#doctors" class="btn btn-outline-secondary btn-lg rounded-pill px-4 py-3">
                        <i class="bi bi-search me-2"></i> Find Specialists
                    </a>
                </div>

                <!-- Live Metrics Pills -->
                <div class="row g-3 pt-3">
                    <div class="col-sm-4 col-6">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary-subtle text-primary p-2 rounded-3">
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0">15,000+</h5>
                                <small class="text-muted">Patients Healed</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 col-6">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-info-subtle text-info p-2 rounded-3">
                                <i class="bi bi-award-fill fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0">50+</h5>
                                <small class="text-muted">Expert Doctors</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 col-12">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-success-subtle text-success p-2 rounded-3">
                                <i class="bi bi-clock-history fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0">99.8%</h5>
                                <small class="text-muted">On-Time Visits</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Hero Visual Card -->
            <div class="col-lg-5">
                <div class="position-relative">
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">
                        <div class="p-4 bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-white text-primary fw-bold px-3 py-2 rounded-pill">PATIENT PORTAL</span>
                                <span class="small text-white-50"><i class="bi bi-circle-fill text-success me-1"></i> Live Online</span>
                            </div>
                            <h4 class="mt-3 mb-1 fw-bold">Instant Medical Consultations</h4>
                            <p class="small text-white-50 mb-0">Select a specialist and secure your time slot in under 60 seconds.</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="p-3 bg-light rounded-3 mb-3 border">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-primary text-white rounded-circle p-2 px-3">
                                        <i class="bi bi-heart-pulse fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-bold">Need Immediate Attention?</h6>
                                        <small class="text-muted">Emergency triage available 24/7</small>
                                    </div>
                                    <a href="tel:18004325844" class="btn btn-sm btn-danger rounded-pill px-3">Call</a>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <a href="patient/register.php" class="btn btn-primary rounded-3 py-2 fw-semibold">
                                    <i class="bi bi-person-plus me-1"></i> Create Patient Account
                                </a>
                                <a href="patient/login.php" class="btn btn-outline-primary rounded-3 py-2 fw-semibold">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Patient Sign In
                                </a>
                                <div class="text-center mt-2">
                                    <span class="text-muted small">Are you a healthcare provider? </span>
                                    <a href="doctor/login.php" class="small fw-bold text-primary text-decoration-none">Doctor Login</a>
                                    <span class="text-muted small"> | </span>
                                    <a href="admin/login.php" class="small fw-bold text-secondary text-decoration-none">Admin</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. CLINICAL SERVICES SECTION -->
<section id="services" class="py-5 bg-light">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-bold text-uppercase">
                World-Class Specialties
            </span>
            <h2 class="fw-bold display-6 mt-2">Comprehensive Healthcare Solutions</h2>
            <p class="text-muted">Our accredited clinical departments provide cutting-edge diagnostic and therapeutic interventions tailored to individual wellness.</p>
        </div>

        <div class="row g-4">
            <!-- Service 1 -->
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon-wrapper icon-rose">
                        <i class="bi bi-heart-pulse-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Cardiology & Heart Care</h5>
                    <p class="text-muted small mb-3">Comprehensive cardiac screening, ECG interpretation, arrhythmia management, and coronary artery rehabilitation.</p>
                    <a href="patient/login.php" class="text-primary fw-semibold text-decoration-none small">
                        Book Consultation <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Service 2 -->
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon-wrapper icon-teal">
                        <i class="bi bi-activity"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Neurology & Brain Health</h5>
                    <p class="text-muted small mb-3">Advanced neurological assessments, migraine therapeutics, stroke rehabilitation, and spine neurophysiology.</p>
                    <a href="patient/login.php" class="text-primary fw-semibold text-decoration-none small">
                        Book Consultation <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Service 3 -->
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon-wrapper icon-blue">
                        <i class="bi bi-emoji-smile-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Pediatric Care & Immunization</h5>
                    <p class="text-muted small mb-3">Compassionate child wellness checkups, childhood development monitoring, and scheduled vaccination programs.</p>
                    <a href="patient/login.php" class="text-primary fw-semibold text-decoration-none small">
                        Book Consultation <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Service 4 -->
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon-wrapper icon-indigo">
                        <i class="bi bi-clipboard2-pulse-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Diagnostic Pathology & Labs</h5>
                    <p class="text-muted small mb-3">High-precision hematology, biochemistry, digital radiology, and immediate digital report delivery to your portal.</p>
                    <a href="patient/login.php" class="text-primary fw-semibold text-decoration-none small">
                        Book Consultation <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Service 5 -->
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon-wrapper icon-amber">
                        <i class="bi bi-capsule-pill"></i>
                    </div>
                    <h5 class="fw-bold mb-2">24/7 Digital Pharmacy</h5>
                    <p class="text-muted small mb-3">Integrated electronic prescriptions generated directly by attending physicians with transparent dosage schedules.</p>
                    <a href="patient/login.php" class="text-primary fw-semibold text-decoration-none small">
                        Book Consultation <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Service 6 -->
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon-wrapper icon-emerald">
                        <i class="bi bi-hospital-fill"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Emergency & Critical Trauma</h5>
                    <p class="text-muted small mb-3">Around-the-clock resuscitation suites, rapid response ambulance coordination, and board-certified emergency physicians.</p>
                    <a href="tel:18004325844" class="text-danger fw-semibold text-decoration-none small">
                        Emergency Contact <i class="bi bi-telephone"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. DOCTOR DIRECTORY SPOTLIGHT -->
<section id="doctors" class="py-5 bg-white">
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-5">
            <div>
                <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 rounded-pill fw-bold text-uppercase">
                    Medical Team
                </span>
                <h2 class="fw-bold display-6 mt-2 mb-0">Meet Our Certified Specialists</h2>
            </div>
            <a href="patient/login.php" class="btn btn-outline-primary rounded-pill px-4 mt-3 mt-md-0">
                View All Doctors <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($featuredDoctors as $doc): ?>
                <div class="col-lg-4 col-md-6">
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
                                <div><i class="bi bi-clock-fill text-muted me-2"></i><?= htmlspecialchars($doc['available_days']) ?></div>
                            </div>

                            <a href="patient/login.php" class="btn btn-primary w-100 rounded-pill">
                                <i class="bi bi-calendar-plus me-1"></i> Book Appointment
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 4. ABOUT SECTION -->
<section id="about" class="py-5 bg-light">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-bold text-uppercase">
                    About PulseHealth
                </span>
                <h2 class="fw-bold display-6 mt-2 mb-3">Dedicated to Clinical Excellence & Patient Empowerment</h2>
                <p class="text-secondary mb-3">
                    Founded with a visionary commitment to patient-centric healthcare, PulseHealth integrates modern clinical knowledge with robust cloud infrastructure. We bridge the communication gap between patients, consultants, and diagnostic technicians.
                </p>
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="border-start border-3 border-primary ps-3">
                            <h5 class="fw-bold text-dark mb-1">Electronic Records</h5>
                            <p class="text-muted small mb-0">Secure, encrypted diagnostic and prescription history accessible anytime.</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border-start border-3 border-info ps-3">
                            <h5 class="fw-bold text-dark mb-1">Zero Wait Times</h5>
                            <p class="text-muted small mb-0">Structured slot reservation prevents clinic over-crowding.</p>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <span class="fw-semibold small">ISO 9001:2015 Accredited</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <span class="fw-semibold small">Certified Lab Facilities</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-lg rounded-4 p-4 bg-white">
                    <h5 class="fw-bold mb-3"><i class="bi bi-award-fill text-primary me-2"></i>Hospital Performance Highlights</h5>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <h3 class="fw-bold text-primary mb-0">99.4%</h3>
                                <small class="text-muted">Satisfaction Rate</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <h3 class="fw-bold text-info mb-0">24/7</h3>
                                <small class="text-muted">Emergency Dispatch</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <h3 class="fw-bold text-success mb-0">50+</h3>
                                <small class="text-muted">Board Doctors</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <h3 class="fw-bold text-danger mb-0">15k+</h3>
                                <small class="text-muted">Cases Resolved</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 5. CONTACT & LOCATION SECTION -->
<section id="contact" class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 rounded-pill fw-bold text-uppercase">
                Reach Out
            </span>
            <h2 class="fw-bold display-6 mt-2">Get in Touch With Our Medical Team</h2>
            <p class="text-muted">Have inquiries regarding specialized surgeries, outpatient scheduling, or insurance coverage? We are here to assist.</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card border-0 bg-light rounded-4 p-4 h-100">
                    <h5 class="fw-bold text-dark mb-4">Contact Information</h5>
                    
                    <div class="d-flex gap-3 mb-4">
                        <div class="bg-primary text-white rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-geo-alt-fill fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Campus Location</h6>
                            <p class="text-muted small mb-0">742 Evergreen Healthcare Ave, Medical District, NY 10001</p>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mb-4">
                        <div class="bg-danger text-white rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-telephone-inbound-fill fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Emergency Lines</h6>
                            <p class="text-muted small mb-0">+1 (800) 432-5844 (Toll-Free 24/7)</p>
                            <p class="text-muted small mb-0">+1 (555) 019-2834 (Reception)</p>
                        </div>
                    </div>

                    <div class="d-flex gap-3 mb-4">
                        <div class="bg-info text-white rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-envelope-check-fill fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Email Inquiries</h6>
                            <p class="text-muted small mb-0">appointments@pulsehealth.edu</p>
                            <p class="text-muted small mb-0">support@pulsehealth.edu</p>
                        </div>
                    </div>

                    <div class="p-3 bg-white rounded-3 border">
                        <h6 class="fw-bold small mb-1"><i class="bi bi-clock-history me-1 text-primary"></i> Operating Hours:</h6>
                        <div class="small text-muted d-flex justify-content-between">
                            <span>Outpatient Clinics:</span> <strong>08:00 AM - 08:00 PM</strong>
                        </div>
                        <div class="small text-muted d-flex justify-content-between">
                            <span>Trauma & Emergency:</span> <strong class="text-danger">24 Hours / 7 Days</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                    <h5 class="fw-bold mb-3">Send Us a Direct Message</h5>
                    
                    <div id="contactSuccessAlert" class="alert alert-success d-none rounded-3" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> Thank you! Your inquiry has been received. A patient coordinator will respond within 2 business hours.
                    </div>

                    <form id="publicContactForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Your Full Name</label>
                                <input type="text" class="form-control" required placeholder="John Doe">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Email Address</label>
                                <input type="email" class="form-control" required placeholder="john@example.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Contact Phone</label>
                                <input type="tel" class="form-control" required placeholder="+1 (555) 000-0000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Department of Interest</label>
                                <select class="form-select">
                                    <option value="General">General Inquiry</option>
                                    <option value="Cardiology">Cardiology</option>
                                    <option value="Neurology">Neurology</option>
                                    <option value="Pediatrics">Pediatrics</option>
                                    <option value="Billing">Billing & Insurance</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Message / Clinical Inquiry</label>
                                <textarea class="form-control" rows="4" required placeholder="How can our clinical specialists assist you?"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                                    <i class="bi bi-send-fill me-2"></i> Submit Inquiry
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
