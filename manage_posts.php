<?php
// manage_posts.php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Auth.php';

Auth::requireRole(['Admin']);
$db = Database::getConnection();

$message = ''; $error = '';

// Handle Job Deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $jobId = (int)$_GET['id'];
    try {
        $stmt = $db->prepare("DELETE FROM jobs WHERE job_id = :id");
        $stmt->execute([':id' => $jobId]);
        $message = "Inappropriate post successfully removed from the system.";
    } catch (Exception $e) {
        $error = "Failed to delete post: " . $e->getMessage();
    }
}

// Handle Admin Quick Edit Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_job_id'])) {
    $editId    = (int)$_POST['edit_job_id'];
    $title     = trim($_POST['title'] ?? '');
    $desc      = trim($_POST['description'] ?? '');
    $location  = trim($_POST['location'] ?? '');
    $jobType   = $_POST['job_type'] ?? 'Full-time';

    try {
        $stmt = $db->prepare("UPDATE jobs SET title = :title, description = :desc, location = :loc, job_type = :type WHERE job_id = :id");
        $stmt->execute([
            ':title' => $title,
            ':desc'  => $desc,
            ':loc'   => $location,
            ':type'  => $jobType,
            ':id'    => $editId
        ]);
        $message = "Job post updated successfully by Administrator.";
    } catch (Exception $e) {
        $error = "Failed to update post: " . $e->getMessage();
    }
}

// Fetch all jobs across the platform with company details
$jobs = $db->query("
    SELECT j.*, e.company_name, e.industry, u.email AS employer_email 
    FROM jobs j 
    JOIN employers e ON j.employer_id = e.employer_id 
    JOIN users u ON e.user_id = u.user_id
    ORDER BY j.created_at DESC
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-navy mb-0"><i class="bi bi-shield-check text-sapphire me-2"></i> Content Moderation Panel</h2>
    <a href="dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
</div>

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-corporate-hero text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All Employer Job Vacancies (<?= count($jobs) ?> Total)</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($jobs)): ?>
            <div class="p-4 text-center text-muted">No vacancies posted in the system yet.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Company</th>
                            <th>Job Title</th>
                            <th>Type / Location</th>
                            <th>Posted Date</th>
                            <th class="text-center">Moderation Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $j): ?>
                            <tr>
                                <td>
                                    <strong class="text-navy d-block"><?= htmlspecialchars($j['company_name']) ?></strong>
                                    <small class="text-muted"><?= htmlspecialchars($j['employer_email']) ?></small>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($j['title']) ?></span>
                                    <small class="d-block text-muted text-truncate" style="max-width: 280px;"><?= htmlspecialchars($j['description']) ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-sapphire mb-1"><?= htmlspecialchars($j['job_type']) ?></span>
                                    <small class="d-block text-muted"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($j['location']) ?></small>
                                </td>
                                <td><small class="text-muted"><?= date('M d, Y', strtotime($j['created_at'])) ?></small></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill me-1" data-bs-toggle="modal" data-bs-target="#editModal<?= $j['job_id'] ?>">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>
                                    
                                    <a href="manage_posts.php?action=delete&id=<?= $j['job_id'] ?>" 
                                       class="btn btn-sm btn-outline-danger rounded-pill" 
                                       onclick="return confirm('Are you sure you want to permanently delete this job posting?');">
                                        <i class="bi bi-trash"></i> Remove
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- All Modals Placed Outside Table Containers to Avoid Clipping -->
<?php foreach ($jobs as $j): ?>
    <div class="modal fade" id="editModal<?= $j['job_id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $j['job_id'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <form method="POST" action="manage_posts.php">
                    <div class="modal-header bg-navy text-white" style="background-color: var(--navy-900);">
                        <h5 class="modal-title text-white" id="editModalLabel<?= $j['job_id'] ?>"><i class="bi bi-pencil-square me-2"></i> Edit Vacancy Post</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4" style="background-color: #ffffff;">
                        <input type="hidden" name="edit_job_id" value="<?= $j['job_id'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Job Title</label>
                            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($j['title']) ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Employment Type</label>
                                <select name="job_type" class="form-select">
                                    <option value="Internship" <?= $j['job_type'] === 'Internship' ? 'selected' : '' ?>>Internship</option>
                                    <option value="Full-time" <?= $j['job_type'] === 'Full-time' ? 'selected' : '' ?>>Full-time</option>
                                    <option value="Part-time" <?= $j['job_type'] === 'Part-time' ? 'selected' : '' ?>>Part-time</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Location</label>
                                <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($j['location']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Job Description</label>
                            <textarea name="description" class="form-control" rows="5" required><?= htmlspecialchars($j['description']) ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sapphire btn-sm rounded-pill px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>