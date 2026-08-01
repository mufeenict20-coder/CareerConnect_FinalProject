<?php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/Job.php';
require_once __DIR__ . '/classes/User.php';

Auth::requireRole(['Employer']);

$db = Database::getConnection();
$jobModel  = new Job($db);
$userModel = new User($db);

$message = '';
$error = '';

// Block posting if employer is unverified
if (empty($_SESSION['is_verified'])) {
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="alert alert-warning"><h4><i class="bi bi-clock-history"></i> Account Pending Verification</h4>';
    echo '<p>Your company profile is currently being reviewed by a University Career Coordinator. You will be able to post vacancies once approved.</p></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $jobType     = $_POST['job_type'] ?? 'Full-time';
    $location    = trim($_POST['location'] ?? '');
    $salary      = trim($_POST['salary_range'] ?? 'Negotiable');
    $deadline    = $_POST['deadline'] ?? '';
    $selectedSkills = $_POST['skills'] ?? [];

    if (!empty($title) && !empty($description) && !empty($selectedSkills)) {
        if ($jobModel->createJob($_SESSION['employer_id'], $title, $description, $jobType, $location, $salary, $deadline, $selectedSkills)) {
            $message = "Job vacancy successfully posted!";
        } else {
            $error = "Failed to create job posting. Please try again.";
        }
    } else {
        $error = "Please fill in all required fields and select at least one required skill.";
    }
}

$allSkills = $userModel->getAllSkills();
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="bi bi-plus-circle-fill"></i> Post a New Job or Internship</h4>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($message) ?> <a href="dashboard.php">View Dashboards</a></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="post_job.php">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Job Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g., Associate Web Developer / Intern" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Employment Type</label>
                            <select name="job_type" class="form-select" required>
                                <option value="Internship">Internship</option>
                                <option value="Full-time">Full-time</option>
                                <option value="Part-time">Part-time</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Application Deadline</label>
                            <input type="date" name="deadline" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g., Colombo / Remote" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Salary Range</label>
                            <input type="text" name="salary_range" class="form-control" placeholder="e.g., LKR 40,000/month or Negotiable">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Job Description</label>
                        <textarea name="description" class="form-control" rows="4" required placeholder="Describe responsibilities and candidate profile expectations..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Required Candidate Skills (Used for Smart Matching Engine)</label>
                        <div class="row bg-light p-3 border rounded">
                            <?php foreach ($allSkills as $skill): ?>
                                <div class="col-md-4 col-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="skills[]" value="<?= $skill['skill_id'] ?>" id="skill_<?= $skill['skill_id'] ?>">
                                        <label class="form-check-label" for="skill_<?= $skill['skill_id'] ?>">
                                            <?= htmlspecialchars($skill['skill_name']) ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Publish Vacancy</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>