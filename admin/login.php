<?php
/**
 * Admin Authentication Script
 * University Project: Online Healthcare Management System
 */
require_once __DIR__ . '/../config/db.php';

$error = '';

if (isLoggedIn() && hasRole('admin')) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = sanitize($_POST['login_input'] ?? '');
    $password    = $_POST['password'] ?? '';

    if (empty($login_input) || empty($password)) {
        $error = 'Please enter administrator username/email and security credentials.';
    } elseif (!$db_connected) {
        $error = 'Database is currently offline. Please run the setup installer first.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE (email = ? OR username = ?) AND role = 'admin' LIMIT 1");
            $stmt->execute([$login_input, $login_input]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'active') {
                    $error = 'This administrative account is disabled.';
                } else {
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = 'admin';
                    $_SESSION['user_name'] = 'Hospital Admin (' . $user['username'] . ')';
                    $_SESSION['email'] = $user['email'];

                    setFlash('welcome', "Welcome to Hospital Administrative Headquarters.", 'success');
                    redirect('dashboard.php');
                }
            } else {
                $error = 'Invalid administrator credentials.';
            }
        } catch (Exception $e) {
            $error = 'Authentication error: ' . $e->getMessage();
        }
    }
}

$pageTitle = "Hospital Admin Sign In";
$assetPrefix = "../";
include __DIR__ . '/../includes/header.php';
?>

<div class="min-vh-100 bg-light d-flex align-items-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-8">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-header bg-danger text-white p-4 text-center">
                        <div class="bg-white text-danger rounded-circle d-inline-flex p-3 mb-2 shadow-sm">
                            <i class="bi bi-shield-lock-fill fs-2"></i>
                        </div>
                        <h4 class="fw-bold mb-1">Hospital Administration</h4>
                        <p class="text-white-50 small mb-0">Master Control Console & Operations</p>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Quick Demo Credentials for Viva -->
                        <div class="alert alert-danger-subtle border-danger-subtle small mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="text-danger"><i class="bi bi-shield-shaded me-1"></i>Examiner Demo Admin:</strong>
                                <button type="button" class="btn btn-xs btn-outline-danger py-0 px-2 rounded-pill" onclick="
                                    document.getElementById('adminInput').value='admin@hospital.com';
                                    document.getElementById('adminPass').value='admin123';
                                ">Fill Demo</button>
                            </div>
                            <div>Email: <code>admin@hospital.com</code></div>
                            <div>Password: <code>admin123</code></div>
                        </div>

                        <form method="POST" action="login.php">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Admin Username or Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-person-fill-gear text-muted"></i></span>
                                    <input type="text" name="login_input" id="adminInput" class="form-control" required placeholder="admin@hospital.com" value="<?= sanitize($_POST['login_input'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-semibold">Security Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-key text-muted"></i></span>
                                    <input type="password" name="password" id="adminPass" class="form-control" required placeholder="••••••••">
                                    <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="adminPass">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-danger btn-lg rounded-3 fw-bold py-2">
                                    <i class="bi bi-shield-check me-2"></i> Authenticate Admin
                                </button>
                            </div>

                            <hr class="my-4">

                            <div class="text-center small">
                                <span class="text-muted">Standard portals: </span>
                                <a href="../patient/login.php" class="fw-semibold text-primary text-decoration-none">Patient Login</a>
                                <span class="text-muted"> | </span>
                                <a href="../doctor/login.php" class="fw-semibold text-info text-decoration-none">Doctor Login</a>
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
