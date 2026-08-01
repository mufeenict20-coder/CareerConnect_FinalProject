<?php
// manage_vacancies.php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Auth.php';

Auth::requireRole(['Employer']);
$db = Database::getConnection();

$empId = $_SESSION['employer_id'];

// Handle Job Deletion
if (isset($_GET['delete'])) {
    $delStmt = $db->prepare("DELETE FROM jobs WHERE job_id = :jid AND employer_id = :eid");
    $delStmt->execute([':jid' => (int)$_GET['delete'], ':eid' => $empId]);
}

$stmt = $db->prepare("SELECT j.*, COUNT(a.application_id) as app_count FROM jobs j LEFT JOIN applications a ON j.job_id = a.job_id WHERE j.employer_id = :eid GROUP BY j.job_id ORDER BY j.created_at DESC");
$stmt->execute([':eid' => $empId]);
$vacancies = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-navy"><i class="bi bi-list-task text-gold me-2"></i> Manage My Vacancies</h3>
    <a href="post_job.php" class="btn btn-gold btn-sm">+ Post New Vacancy</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Job Title</th><th>Type</th><th>Location</th><th>Deadline</th><th>Applicants</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($vacancies as $v): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($v['title']) ?></td>
                        <td><span class="badge badge-navy"><?= $v['job_type'] ?></span></td>
                        <td><?= htmlspecialchars($v['location']) ?></td>
                        <td><?= $v['deadline'] ?></td>
                        <td><a href="view_applicants.php?job_id=<?= $v['job_id'] ?>" class="badge badge-gold text-decoration-none"><?= $v['app_count'] ?> Applicants</a></td>
                        <td>
                            <a href="edit_vacancy.php?id=<?= $v['job_id'] ?>" class="btn btn-sm btn-outline-navy"><i class="bi bi-pencil"></i> Edit</a>
                            <a href="manage_vacancies.php?delete=<?= $v['job_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this job post?')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>