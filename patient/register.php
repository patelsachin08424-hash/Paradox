<?php
/**
 * Patient Registration Script
 * University Project: Online Healthcare Management System
 * 
 * VIVA NOTE:
 * - Validates inputs and enforces email uniqueness.
 * - Uses transaction to ensure both user record and patient clinical profile are created atomically.
 * - Uses password_hash() with PASSWORD_BCRYPT for irreversible password encryption.
 */
require_once __DIR__ . '/../config/db.php';

$error = '';
$success = '';

if (isLoggedIn() && hasRole('patient')) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name         = sanitize($_POST['full_name'] ?? '');
    $username          = sanitize($_POST['username'] ?? '');
    $email             = sanitize($_POST['email'] ?? '');
    $password          = $_POST['password'] ?? '';
    $confirm_password  = $_POST['confirm_password'] ?? '';
    $dob               = sanitize($_POST['dob'] ?? '');
    $gender            = sanitize($_POST['gender'] ?? 'Male');
    $phone             = sanitize($_POST['phone'] ?? '');
    $blood_group       = sanitize($_POST['blood_group'] ?? 'O+');
    $address           = sanitize($_POST['address'] ?? '');
    $emergency_contact = sanitize($_POST['emergency_contact'] ?? '');

    // Validation
    if (empty($full_name) || empty($username) || empty($email) || empty($password) || empty($phone)) {
        $error = 'Please fill in all mandatory fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (!$db_connected) {
        $error = 'Database is currently offline. Please run the setup installer first.';
    } else {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->rowCount() > 0) {
                $error = 'An account with this username or email already exists.';
            } else {
                // Begin database transaction
                $pdo->beginTransaction();

                // 1. Insert User
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmtUser = $pdo->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, 'patient', 'active')");
                $stmtUser->execute([$username, $email, $hash]);
                $userId = $pdo->lastInsertId();

                // 2. Insert Patient Profile
                $stmtPatient = $pdo->prepare("INSERT INTO patients (user_id, full_name, dob, gender, phone, blood_group, address, emergency_contact) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmtPatient->execute([$userId, $full_name, $dob ?: null, $gender, $phone, $blood_group, $address, $emergency_contact]);
                $patientId = $pdo->lastInsertId();

                $pdo->commit();

                // Set session
                $_SESSION['user_id'] = $userId;
                $_SESSION['patient_id'] = $patientId;
                $_SESSION['role'] = 'patient';
                $_SESSION['user_name'] = $full_name;
                $_SESSION['email'] = $email;

                setFlash('welcome', "Welcome to PulseHealth, {$full_name}! Your patient account has been created.", 'success');
                redirect('dashboard.php');
            }
        } catch (Exception $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Registration failed: ' . $e->getMessage();
        }
    }
}

$pageTitle = "Patient Registration";
$assetPrefix = "../";
include __DIR__ . '/../includes/header.php';
?>

<div class="min-vh-100 bg-light d-flex align-items-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-header bg-primary text-white p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="fw-bold mb-1"><i class="bi bi-person-plus-fill me-2"></i>Patient Registration</h4>
                                <p class="text-white-50 small mb-0">Create your digital healthcare passport</p>
                            </div>
                            <a href="../index.php" class="btn btn-sm btn-light text-primary rounded-pill fw-semibold">
                                <i class="bi bi-house-door me-1"></i> Home
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="register.php">
                            <!-- Account Credentials Section -->
                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-shield-lock me-2"></i>Account Security</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Choose Username *</label>
                                    <input type="text" name="username" class="form-control" required placeholder="e.g. john_doe" value="<?= sanitize($_POST['username'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Email Address *</label>
                                    <input type="email" name="email" class="form-control" required placeholder="john@example.com" value="<?= sanitize($_POST['email'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Password * (min 6 chars)</label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="regPass" class="form-control" required placeholder="••••••••">
                                        <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="regPass">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Confirm Password *</label>
                                    <input type="password" name="confirm_password" class="form-control" required placeholder="••••••••">
                                </div>
                            </div>

                            <!-- Personal Details Section -->
                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person-vcard me-2"></i>Personal & Medical Profile</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Full Legal Name *</label>
                                    <input type="text" name="full_name" class="form-control" required placeholder="John Alexander Doe" value="<?= sanitize($_POST['full_name'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Phone Number *</label>
                                    <input type="tel" name="phone" class="form-control" required placeholder="+1 (555) 000-0000" value="<?= sanitize($_POST['phone'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Date of Birth</label>
                                    <input type="date" name="dob" class="form-control" value="<?= sanitize($_POST['dob'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Gender</label>
                                    <select name="gender" class="form-select">
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Blood Group</label>
                                    <select name="blood_group" class="form-select">
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+" selected>O+</option>
                                        <option value="O-">O-</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Residential Address</label>
                                    <input type="text" name="address" class="form-control" placeholder="123 Hospital Way, Apt 4B" value="<?= sanitize($_POST['address'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Emergency Contact (Name & Phone)</label>
                                    <input type="text" name="emergency_contact" class="form-control" placeholder="Mary Doe - +1 555-987-6544" value="<?= sanitize($_POST['emergency_contact'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-bold py-3">
                                    <i class="bi bi-check-circle-fill me-2"></i> Register Patient Account
                                </button>
                            </div>

                            <div class="text-center text-muted small">
                                Already registered? <a href="login.php" class="text-primary fw-bold text-decoration-none">Sign In Here</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
