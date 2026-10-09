<?php
/**
 * Doctor Portal Authentication
 * University Project: Online Healthcare Management System
 */
require_once __DIR__ . '/../config/db.php';

$error = '';

if (isLoggedIn() && hasRole('doctor')) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = sanitize($_POST['login_input'] ?? '');
    $password    = $_POST['password'] ?? '';

    if (empty($login_input) || empty($password)) {
        $error = 'Please enter your professional physician email and password.';
    } elseif (!$db_connected) {
        $error = 'Database is currently offline. Please run the setup installer first.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE (email = ? OR username = ?) AND role = 'doctor' LIMIT 1");
            $stmt->execute([$login_input, $login_input]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'active') {
                    $error = 'Doctor account is inactive. Please contact administration.';
                } else {
                    session_regenerate_id(true);

                    // Fetch associated doctor record
                    $stmtDoc = $pdo->prepare("SELECT * FROM doctors WHERE user_id = ? LIMIT 1");
                    $stmtDoc->execute([$user['id']]);
                    $doc = $stmtDoc->fetch();

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['doctor_id'] = $doc ? $doc['id'] : 0;
                    $_SESSION['role'] = 'doctor';
                    $_SESSION['user_name'] = $doc ? $doc['full_name'] : $user['username'];
                    $_SESSION['email'] = $user['email'];

                    setFlash('welcome', "Welcome back, {$_SESSION['user_name']}!", 'success');
                    redirect('dashboard.php');
                }
            } else {
                $error = 'Invalid doctor credentials.';
            }
        } catch (Exception $e) {
            $error = 'Authentication error: ' . $e->getMessage();
        }
    }
}

$pageTitle = "Physician Login";
$assetPrefix = "../";
include __DIR__ . '/../includes/header.php';
?>

<div class="min-vh-100 bg-light d-flex align-items-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-8">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-header bg-dark text-white p-4 text-center">
                        <div class="bg-primary text-white rounded-circle d-inline-flex p-3 mb-2 shadow-sm">
                            <i class="bi bi-stethoscope fs-2"></i>
                        </div>
                        <h4 class="fw-bold mb-1">Physician Portal Login</h4>
                        <p class="text-white-50 small mb-0">Consultations, clinical diagnoses, and electronic prescriptions</p>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Quick Demo Credentials for Viva -->
                        <div class="alert alert-info border-info-subtle small mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="text-primary"><i class="bi bi-info-circle me-1"></i>Examiner Demo Login:</strong>
                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 rounded-pill" onclick="
                                    document.getElementById('docInput').value='doctor.smith@hospital.com';
                                    document.getElementById('docPass').value='doctor123';
                                ">Fill Demo</button>
                            </div>
                            <div>Email: <code>doctor.smith@hospital.com</code></div>
                            <div>Password: <code>doctor123</code></div>
                        </div>

                        <form method="POST" action="login.php">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Doctor Email / Username</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-envelope text-muted"></i></span>
                                    <input type="text" name="login_input" id="docInput" class="form-control" required placeholder="doctor.smith@hospital.com" value="<?= sanitize($_POST['login_input'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-semibold mb-0">Password</label>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-lock text-muted"></i></span>
                                    <input type="password" name="password" id="docPass" class="form-control" required placeholder="••••••••">
                                    <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="docPass">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-bold py-2">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> Enter Doctor Portal
                                </button>
                            </div>

                            <div class="text-center text-muted small">
                                Need account credentials? Contact the Hospital Administrator.
                            </div>

                            <hr class="my-4">

                            <div class="text-center small">
                                <span class="text-muted">Not a physician? </span>
                                <a href="../patient/login.php" class="fw-semibold text-primary text-decoration-none">Patient Sign In</a>
                                <span class="text-muted"> | </span>
                                <a href="../admin/login.php" class="fw-semibold text-danger text-decoration-none">Hospital Admin</a>
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
