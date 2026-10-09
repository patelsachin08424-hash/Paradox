<?php
/**
 * Patient Login Script
 * University Project: Online Healthcare Management System
 * 
 * VIVA NOTE:
 * - Uses password_verify() to test against bcrypt hash stored in MySQL.
 * - Enforces role-based check ensuring only 'patient' accounts can log in here.
 * - Session fixation prevention via session_regenerate_id().
 */
require_once __DIR__ . '/../config/db.php';

$error = '';

if (isLoggedIn() && hasRole('patient')) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = sanitize($_POST['login_input'] ?? '');
    $password    = $_POST['password'] ?? '';

    if (empty($login_input) || empty($password)) {
        $error = 'Please enter your email/username and password.';
    } elseif (!$db_connected) {
        $error = 'Database is currently offline. Please run the setup installer first.';
    } else {
        try {
            // Find user by email or username
            $stmt = $pdo->prepare("SELECT * FROM users WHERE (email = ? OR username = ?) AND role = 'patient' LIMIT 1");
            $stmt->execute([$login_input, $login_input]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'active') {
                    $error = 'Your account has been deactivated. Please contact hospital support.';
                } else {
                    // Prevent session fixation
                    session_regenerate_id(true);

                    // Fetch associated patient record
                    $stmtPatient = $pdo->prepare("SELECT * FROM patients WHERE user_id = ? LIMIT 1");
                    $stmtPatient->execute([$user['id']]);
                    $patient = $stmtPatient->fetch();

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['patient_id'] = $patient ? $patient['id'] : 0;
                    $_SESSION['role'] = 'patient';
                    $_SESSION['user_name'] = $patient ? $patient['full_name'] : $user['username'];
                    $_SESSION['email'] = $user['email'];

                    setFlash('welcome', "Welcome back, {$_SESSION['user_name']}!", 'success');
                    redirect('dashboard.php');
                }
            } else {
                $error = 'Invalid email/username or password.';
            }
        } catch (Exception $e) {
            $error = 'Authentication error: ' . $e->getMessage();
        }
    }
}

$pageTitle = "Patient Login";
$assetPrefix = "../";
include __DIR__ . '/../includes/header.php';
?>

<div class="min-vh-100 bg-light d-flex align-items-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-8">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-header bg-primary text-white p-4 text-center">
                        <div class="bg-white text-primary rounded-circle d-inline-flex p-3 mb-2 shadow-sm">
                            <i class="bi bi-person-fill-lock fs-2"></i>
                        </div>
                        <h4 class="fw-bold mb-1">Patient Sign In</h4>
                        <p class="text-white-50 small mb-0">Access your consultations, prescriptions, & records</p>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Quick Demo Credentials Box for University Examiner -->
                        <div class="alert alert-info border-info-subtle small mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="text-primary"><i class="bi bi-info-circle me-1"></i>Viva Demo Login:</strong>
                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 rounded-pill" onclick="
                                    document.getElementById('loginInput').value='patient.john@gmail.com';
                                    document.getElementById('loginPass').value='patient123';
                                ">Fill Demo</button>
                            </div>
                            <div>Email: <code>patient.john@gmail.com</code></div>
                            <div>Password: <code>patient123</code></div>
                        </div>

                        <form method="POST" action="login.php">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Email or Username</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person text-muted"></i></span>
                                    <input type="text" name="login_input" id="loginInput" class="form-control" required placeholder="patient.john@gmail.com" value="<?= sanitize($_POST['login_input'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-semibold mb-0">Password</label>
                                    <a href="#" class="text-muted small text-decoration-none">Forgot?</a>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-lock text-muted"></i></span>
                                    <input type="password" name="password" id="loginPass" class="form-control" required placeholder="••••••••">
                                    <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="loginPass">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-bold py-2">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> Sign In to Portal
                                </button>
                            </div>

                            <div class="text-center text-muted small">
                                Don't have an account yet? <a href="register.php" class="text-primary fw-bold text-decoration-none">Create Patient Profile</a>
                            </div>

                            <hr class="my-4">

                            <div class="text-center small">
                                <span class="text-muted">Medical Staff? </span>
                                <a href="../doctor/login.php" class="fw-semibold text-info text-decoration-none">Doctor Portal</a>
                                <span class="text-muted"> | </span>
                                <a href="../admin/login.php" class="fw-semibold text-danger text-decoration-none">Admin Login</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="../index.php" class="text-secondary text-decoration-none small">
                        <i class="bi bi-arrow-left me-1"></i> Return to Homepage
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
