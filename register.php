<?php
// register.php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/User.php';

$error = ''; $success = '';
$selectedRole = $_POST['role'] ?? 'Student';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'Student';

    if (!empty($name) && !empty($email) && !empty($password)) {
        $db   = Database::getConnection();
        $userModel = new User($db);

        // Check if email already exists
        $check = $db->prepare("SELECT user_id FROM users WHERE LOWER(email) = LOWER(:email)");
        $check->execute([':email' => $email]);
        if ($check->fetch()) {
            $error = "This email is already registered. Please sign in instead.";
        } else {
            if ($role === 'Student') {
                $degree = trim($_POST['degree'] ?? 'BSc in IT');
                $gpa    = (float)($_POST['gpa'] ?? 0.0);
                if ($userModel->registerStudent($name, $email, $password, $degree, $gpa)) {
                    $success = "Student account created successfully! You can now log in.";
                } else { $error = "Failed to create student account."; }

            } elseif ($role === 'Employer') {
                $company = trim($_POST['company_name'] ?? '');
                $industry = trim($_POST['industry'] ?? 'Information Technology');
                $website  = trim($_POST['company_website'] ?? '');
                if ($userModel->registerEmployer($name, $email, $password, $company, $industry, $website)) {
                    $success = "Employer account created! Pending Coordinator verification.";
                } else { $error = "Failed to create employer account."; }

            } elseif (in_array($role, ['Coordinator', 'Admin'], true)) {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
                if ($stmt->execute([':name' => $name, ':email' => strtolower($email), ':password' => $hashedPassword, ':role' => $role])) {
                    $success = $role . " account created successfully!";
                } else { $error = "Failed to create " . $role . " account."; }
            }
        }
    } else {
        $error = "Please complete all required fields.";
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center my-4">
    <div class="col-md-6">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-header bg-corporate-hero text-white text-center py-4 rounded-top-4">
                <i class="bi bi-person-plus-fill display-5 text-sapphire mb-2 d-block" style="color: #38bdf8 !important;"></i>
                <h4 class="fw-bold mb-0">Create Your CareerConnect Account</h4>
            </div>

            <div class="card-body p-4">
                <?php if ($error): ?><div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                <?php if ($success): ?><div class="alert alert-success text-center"><?= htmlspecialchars($success) ?> <a href="login.php" class="fw-bold">Sign In Here</a></div><?php endif; ?>

                <form method="POST" action="register.php">
                    <div class="mb-3">
                        <label class="form-label fw-bold">I am registering as:</label>
                        <select name="role" id="roleSelect" class="form-select form-select-lg fs-6" onchange="toggleRoleFields()" required>
                            <option value="Student" <?= $selectedRole === 'Student' ? 'selected' : '' ?>>🎓 Student</option>
                            <option value="Employer" <?= $selectedRole === 'Employer' ? 'selected' : '' ?>>🏢 Employer / Company</option>
                            <option value="Coordinator" <?= $selectedRole === 'Coordinator' ? 'selected' : '' ?>>🏛️ University Coordinator</option>
                            <option value="Admin" <?= $selectedRole === 'Admin' ? 'selected' : '' ?>>⚙️ Administrator</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <!-- Dynamic Student Fields -->
                    <div id="studentFields">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Degree Program</label>
                                <input type="text" name="degree" class="form-control" value="BSc (Hons) in IT">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Current GPA</label>
                                <input type="number" step="0.01" name="gpa" class="form-control" value="3.50">
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Employer Fields -->
                    <div id="employerFields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Company Name</label>
                            <input type="text" name="company_name" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Industry Field</label>
                            <input type="text" name="industry" class="form-control" value="Information Technology">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Company Website</label>
                            <input type="url" name="company_website" class="form-control" placeholder="https://company.com">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-sapphire btn-lg w-100 rounded-pill mt-2">Register Account</button>
                </form>
            </div>

            <div class="card-footer bg-light text-center py-3 rounded-bottom-4">
                <span class="text-muted small">Already have an account?</span>
                <a href="login.php" class="fw-bold text-sapphire text-decoration-none ms-1">Sign In</a>
            </div>
        </div>
    </div>
</div>

<script>
function toggleRoleFields() {
    var role = document.getElementById('roleSelect').value;
    document.getElementById('studentFields').style.display = (role === 'Student') ? 'block' : 'none';
    document.getElementById('employerFields').style.display = (role === 'Employer') ? 'block' : 'none';
}
toggleRoleFields();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>