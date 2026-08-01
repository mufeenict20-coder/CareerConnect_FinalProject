<?php
// edit_vacancy.php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/User.php';

Auth::requireRole(['Employer']);
$db = Database::getConnection();
$userModel = new User($db);

$jobId = (int)($_GET['id'] ?? 0);
$empId = $_SESSION['employer_id'];

$message = ''; $error = '';

// Fetch job details ensuring it belongs to logged-in employer
$stmt = $db->prepare("SELECT * FROM jobs WHERE job_id = :jid AND employer_id = :eid");
$stmt->execute([':jid' => $jobId, ':eid' => $empId]);
$job = $stmt->fetch();

if (!$job) {
    die("Job vacancy not found or permission denied.");
}

// Fetch current skill IDs assigned to this job
$skillStmt = $db->prepare("SELECT skill_id FROM job_skills WHERE job_id = :jid");
$skillStmt->execute([':jid' => $jobId]);
$currentSkills = $skillStmt->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $jobType     = $_POST['job_type'] ?? 'Full-time';
    $location    = trim($_POST['location'] ?? '');
    $salary      = trim($_POST['salary_range'] ?? 'Negotiable');
    $deadline    = $_POST['deadline'] ?? '';
    $selectedSkills = $_POST['skills'] ?? [];

    if (!empty($title) && !empty($description) && !empty($selectedSkills)) {
        try {
            $db->beginTransaction();

            $updateStmt = $db->prepare("
                UPDATE jobs 
                SET title = :title, description = :description, job_type = :job_type, 
                    location = :location, salary_range = :salary, deadline = :deadline
                WHERE job_id = :jid AND employer_id = :eid
            ");
            $updateStmt->execute([
                ':title'       => $title,
                ':description' => $description,
                ':job_type'    => $jobType,
                ':location'    => $location,
                ':salary'      => $salary,
                ':deadline'    => $deadline,
                ':jid'         => $jobId,
                ':eid'         => $empId
            ]);

            // Sync required skills
            $db->prepare("DELETE FROM job_skills WHERE job_id = :jid")->execute([':jid' => $jobId]);
            $insSkill = $db->prepare("INSERT INTO job_skills (job_id, skill_id) VALUES (:jid, :sid)");
            foreach ($selectedSkills as $sid) {
                $insSkill->execute([':jid' => $jobId, ':sid' => (int)$sid]);
            }

            $db->commit();
            $message = "Vacancy updated successfully!";
            
            // Refresh local arrays
            $job['title'] = $title;
            $job['description'] = $description;
            $job['job_type'] = $jobType;
            $job['location'] = $location;
            $job['salary_range'] = $salary;
            $job['deadline'] = $deadline;
            $currentSkills = $selectedSkills;
        } catch (Exception $e) {
            $db->rollBack();
            $error = "Failed to update vacancy: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields and select at least one skill.";
    }
}

$allSkills = $userModel->getAllSkills();
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-corporate-hero text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Job Vacancy</h5>
                <a href="manage_vacancies.php" class="btn btn-sm btn-gold">Back to Vacancies</a>
            </div>
            <div class="card-body">
                <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
                <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Job Title</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($job['title']) ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Employment Type</label>
                            <select name="job_type" class="form-select" required>
                                <option value="Internship" <?= $job['job_type'] === 'Internship' ? 'selected' : '' ?>>Internship</option>
                                <option value="Full-time" <?= $job['job_type'] === 'Full-time' ? 'selected' : '' ?>>Full-time</option>
                                <option value="Part-time" <?= $job['job_type'] === 'Part-time' ? 'selected' : '' ?>>Part-time</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Application Deadline</label>
                            <input type="date" name="deadline" class="form-control" value="<?= htmlspecialchars($job['deadline']) ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($job['location']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Salary Range</label>
                            <input type="text" name="salary_range" class="form-control" value="<?= htmlspecialchars($job['salary_range']) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Job Description</label>
                        <textarea name="description" class="form-control" rows="4" required><?= htmlspecialchars($job['description']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Required Skills (Smart Matching Engine)</label>
                        <div class="row bg-light p-3 border rounded">
                            <?php foreach ($allSkills as $skill): ?>
                                <div class="col-md-4 col-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="skills[]" value="<?= $skill['skill_id'] ?>" 
                                            id="skill_<?= $skill['skill_id'] ?>" <?= in_array($skill['skill_id'], $currentSkills) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="skill_<?= $skill['skill_id'] ?>">
                                            <?= htmlspecialchars($skill['skill_name']) ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-gold w-100">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>