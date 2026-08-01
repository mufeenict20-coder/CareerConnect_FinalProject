<?php
// login.php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Auth.php';

$error = '';
$selectedRole = $_POST['role'] ?? $_GET['role'] ?? 'Student';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        try {
            $db   = Database::getConnection();
            $auth = new Auth($db);

            if ($auth->login($email, $password)) {
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Invalid email or password. Please double-check your credentials.";
            }
        } catch (Exception $e) {
            $error = "System Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in both email and password.";
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center my-4">
    <div class="col-md-5">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-header bg-corporate-hero text-white text-center py-4 rounded-top-4">
                <i class="bi bi-shield-lock-fill display-5 text-sapphire mb-2 d-block" style="color: #38bdf8 !important;"></i>
                <h4 class="fw-bold mb-0">CareerConnect Portal Sign In</h4>
                <p class="small text-white-50 mb-0">Select your account role to continue</p>
            </div>
            
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-navy"><i class="bi bi-person-badge me-1 text-sapphire"></i> Select Portal / Role</label>
                        <select name="role" class="form-select form-select-lg fs-6 shadow-sm border-2" required>
                            <option value="Student" <?= $selectedRole === 'Student' ? 'selected' : '' ?>>🎓 Student Portal</option>
                            <option value="Employer" <?= $selectedRole === 'Employer' ? 'selected' : '' ?>>🏢 Employer Portal</option>
                            <option value="Coordinator" <?= $selectedRole === 'Coordinator' ? 'selected' : '' ?>>🏛️ University Coordinator</option>
                            <option value="Admin" <?= $selectedRole === 'Admin' ? 'selected' : '' ?>>⚙️ System Administrator</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-navy"><i class="bi bi-envelope me-1 text-sapphire"></i> Email Address</label>
                        <input type="email" name="email" class="form-control form-control-lg fs-6" placeholder="name@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-navy"><i class="bi bi-key me-1 text-sapphire"></i> Password</label>
                        <input type="password" name="password" class="form-control form-control-lg fs-6" placeholder="Enter your password" required>
                    </div>

                    <button type="submit" class="btn btn-sapphire btn-lg w-100 rounded-pill shadow-sm mb-3">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Portal
                    </button>
                </form>
            </div>

            <div class="card-footer bg-light text-center py-3 rounded-bottom-4">
                <span class="text-muted small">Don't have an account yet?</span>
                <a href="register.php" class="fw-bold text-sapphire text-decoration-none ms-1">Register Here</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>