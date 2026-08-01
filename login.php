<?php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Auth.php';

$error = '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        $db   = Database::getConnection();
        $auth = new Auth($db);

        if ($auth->login($email, $password)) {
            header('Location: dashboard.php');
            exit;
        }

        $error = 'Invalid email or password. Please try again.';
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center my-5">
    <div class="col-md-5">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-corporate-hero text-white text-center py-4 rounded-top-4">
                <h4 class="fw-bold mb-0"><i class="bi bi-box-arrow-in-right me-2"></i> CareerConnect Sign In</h4>
            </div>
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" minlength="6" required>
                    </div>
                    <button type="submit" class="btn btn-sapphire w-100 rounded-pill">Sign In</button>
                </form>

                <div class="text-center mt-4">
                    <p class="mb-0 text-muted">Don’t have an account? <a href="register.php" class="text-sapphire fw-bold">Register now</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>