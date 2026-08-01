<?php
// student_applications.php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Auth.php';

Auth::requireRole(['Student']);
$db = Database::getConnection();

$stmt = $db->prepare("
    SELECT a.*, j.title, j.job_type, e.company_name 
    FROM applications a
    JOIN jobs j ON a.job_id = j.job_id
    JOIN employers e ON j.employer_id = e.employer_id
    WHERE a.student_id = :sid
    ORDER BY a.applied_at DESC
");
$stmt->execute([':sid' => $_SESSION['student_id']]);
$apps = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<h3 class="fw-bold mb-4 text-navy"><i class="bi bi-file-earmark-text text-gold me-2"></i> My Application Statuses</h3>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Company</th><th>Job Title</th><th>Match Score</th><th>Applied Date</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php foreach ($apps as $app): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($app['company_name']) ?></td>
                        <td><?= htmlspecialchars($app['title']) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= $app['match_score'] ?>% Match</span></td>
                        <td><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>
                        <td>
                            <?php
                                $badge = match($app['status']) {
                                    'Accepted' => 'bg-success',
                                    'Shortlisted', 'Interview Scheduled' => 'bg-info text-dark',
                                    'Rejected' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            ?>
                            <span class="badge <?= $badge ?>"><?= $app['status'] ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>