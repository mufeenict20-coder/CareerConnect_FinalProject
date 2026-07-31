<?php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/User.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getConnection();
    $userModel = new User($db);

    $role     = $_POST['role'] ?? '';
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($role === 'Student') {
        $degree = trim($_POST['degree'] ?? '');
        $gpa    = (float)($_POST['gpa'] ?? 0.0);

        if ($userModel->registerStudent($name, $email, $password, $degree, $gpa)) {
            $message = "Student registration successful! You can now log in.";
        } else {
            $error = "Registration failed. The email address might already be registered.";
        }
    } elseif ($role === 'Employer') {
        $companyName = trim($_POST['company_name'] ?? '');
        $industry    = trim($_POST['industry'] ?? '');
        $website     = trim($_POST['company_website'] ?? '');

        if ($userModel->registerEmployer($name, $email, $password, $companyName, $industry, $website)) {
            $message = "Employer account created! Please wait for Career Coordinator verification before posting jobs.";
        } else {
            $error = "Registration failed. Email might already exist.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-person-plus-fill"></i> Create a CareerConnect Account</h4>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($message) ?> <a href="login.php">Click here to Login</a></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="register.php" id="regForm">
                    <!-- Role Selection -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Registration Role</label>
                        <select name="role" id="roleSelect" class="form-select" required onchange="toggleFormFields()">
                            <option value="">-- Choose Role --</option>
                            <option value="Student">Student (Job Seeker)</option>
                            <option value="Employer">Employer (Company)</option>
                        </select>
                    </div>

                    <!-- Common User Fields -->
                    <div class="mb-3">
                        <label class="form-label">Full Name / Contact Person</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" minlength="6" required>
                    </div>

                    <!-- Student Specific Fields -->
                    <div id="studentFields" style="display: none;" class="p-3 bg-light border rounded mb-3">
                        <h6 class="text-primary"><i class="bi bi-mortarboard-fill"></i> Academic Details</h6>
                        <div class="mb-3">
                            <label class="form-label">Degree Program</label>
                            <input type="text" name="degree" class="form-control" placeholder="e.g., BSc (Hons) in IT">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">GPA (Optional)</label>
                            <input type="number" step="0.01" min="0" max="4.0" name="gpa" class="form-control" placeholder="e.g., 3.50">
                        </div>
                    </div>

                    <!-- Employer Specific Fields -->
                    <div id="employerFields" style="display: none;" class="p-3 bg-light border rounded mb-3">
                        <h6 class="text-primary"><i class="bi bi-building"></i> Company Details</h6>
                        <div class="mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control" placeholder="e.g., TechCorp Solutions">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Industry</label>
                            <input type="text" name="industry" class="form-control" placeholder="e.g., Software Development">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Company Website (Optional)</label>
                            <input type="url" name="company_website" class="form-control" placeholder="https://company.com">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Register Account</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFormFields() {
    const role = document.getElementById('roleSelect').value;
    document.getElementById('studentFields').style.display = (role === 'Student') ? 'block' : 'none';
    document.getElementById('employerFields').style.display = (role === 'Employer') ? 'block' : 'none';
}
</script>

<?php require_once __DIR__ . '/includes/header.php'; ?>