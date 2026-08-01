<?php
// coordinator_panel.php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Auth.php';

Auth::requireRole(['Coordinator']);
$db = Database::getConnection();

$message = ''; $error = '';

// Handle Verification Toggles
if (isset($_GET['action']) && isset($_GET['emp_id'])) {
    $empId  = (int)$_GET['emp_id'];
    $action = $_GET['action'];

    if ($action === 'verify') {
        $db->prepare("UPDATE employers SET is_verified = 1 WHERE employer_id = :eid")->execute([':eid' => $empId]);
        $message = "Employer successfully verified!";
    } elseif ($action === 'revoke') {
        $db->prepare("UPDATE employers SET is_verified = 0 WHERE employer_id = :eid")->execute([':eid' => $empId]);
        $message = "Employer verification revoked.";
    }
}

// Statistics Overview
$totalStudents  = $db->query("SELECT COUNT(*) FROM students")->fetchColumn();
$verifiedEmps   = $db->query("SELECT COUNT(*) FROM employers WHERE is_verified = 1")->fetchColumn();
$pendingEmps    = $db->query("SELECT COUNT(*) FROM employers WHERE is_verified = 0")->fetchColumn();
$totalJobs      = $db->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
$totalApps      = $db->query("SELECT COUNT(*) FROM applications")->fetchColumn();

// Datasets
$allEmployers = $db->query("
    SELECT e.*, u.name AS contact_name, u.email 
    FROM employers e 
    JOIN users u ON e.user_id = u.user_id 
    ORDER BY e.is_verified ASC, e.employer_id DESC
")->fetchAll();

$allJobs = $db->query("
    SELECT j.*, e.company_name 
    FROM jobs j 
    JOIN employers e ON j.employer_id = e.employer_id 
    ORDER BY j.created_at DESC
")->fetchAll();

$allApplications = $db->query("
    SELECT a.*, u.name AS student_name, s.degree, j.title AS job_title, e.company_name 
    FROM applications a
    JOIN students s ON a.student_id = s.student_id
    JOIN users u ON s.user_id = u.user_id
    JOIN jobs j ON a.job_id = j.job_id
    JOIN employers e ON j.employer_id = e.employer_id
    ORDER BY a.applied_at DESC
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Title & Action Export Buttons -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-navy mb-0"><i class="bi bi-patch-check-fill text-gold me-2"></i> University Career Coordinator Workspace</h2>
    <div>
        <a href="export_reports.php?type=print" target="_blank" class="btn btn-navy btn-sm me-2"><i class="bi bi-printer me-1"></i> Print / PDF Summary</a>
        <a href="export_reports.php?type=csv" class="btn btn-gold btn-sm"><i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel Data</a>
    </div>
</div>

<?php if ($message): ?><div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

<!-- Metric Counters -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card p-3 shadow-sm text-center border-start border-4 border-primary">
            <h6 class="text-muted mb-1">Total Students</h6>
            <h3 class="fw-bold text-navy mb-0"><?= $totalStudents ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 shadow-sm text-center border-start border-4 border-success">
            <h6 class="text-muted mb-1">Verified Employers</h6>
            <h3 class="fw-bold text-success mb-0"><?= $verifiedEmps ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 shadow-sm text-center border-start border-4 border-warning">
            <h6 class="text-muted mb-1">Pending Approval</h6>
            <h3 class="fw-bold text-gold mb-0"><?= $pendingEmps ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 shadow-sm text-center border-start border-4 border-info">
            <h6 class="text-muted mb-1">Total Applications</h6>
            <h3 class="fw-bold text-navy mb-0"><?= $totalApps ?></h3>
        </div>
    </div>
</div>

<!-- Navigation Tabs -->
<ul class="nav nav-tabs fw-bold mb-3" id="coordTab" role="tablist">
    <li class="nav-item">
        <button class="nav-link active text-navy" id="employers-tab" data-bs-toggle="tab" data-bs-target="#employers" type="button"><i class="bi bi-building me-1"></i> Employer Verification (<?= count($allEmployers) ?>)</button>
    </li>
    <li class="nav-item">
        <button class="nav-link text-navy" id="jobs-tab" data-bs-toggle="tab" data-bs-target="#jobs" type="button"><i class="bi bi-briefcase me-1"></i> Active Vacancies (<?= count($allJobs) ?>)</button>
    </li>
    <li class="nav-item">
        <button class="nav-link text-navy" id="apps-tab" data-bs-toggle="tab" data-bs-target="#apps" type="button"><i class="bi bi-journal-text me-1"></i> Student Applications (<?= count($allApplications) ?>)</button>
    </li>
</ul>

<div class="tab-content" id="coordTabContent">
    <!-- TAB 1: Employer Verification Management -->
    <div class="tab-pane fade show active" id="employers" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Company</th><th>Industry</th><th>Contact Person</th><th>Email</th><th>Status</th><th class="text-center">Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allEmployers as $e): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($e['company_name']) ?></td>
                                <td><?= htmlspecialchars($e['industry']) ?></td>
                                <td><?= htmlspecialchars($e['contact_name']) ?></td>
                                <td><?= htmlspecialchars($e['email']) ?></td>
                                <td>
                                    <?php if ($e['is_verified']): ?>
                                        <span class="badge bg-success">Verified</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Pending Approval</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($e['is_verified']): ?>
                                        <a href="coordinator_panel.php?action=revoke&emp_id=<?= $e['employer_id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Revoke verification for this company?')">Revoke Access</a>
                                    <?php else: ?>
                                        <a href="coordinator_panel.php?action=verify&emp_id=<?= $e['employer_id'] ?>" class="btn btn-gold btn-sm"><i class="bi bi-check-lg"></i> Approve & Verify</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: Vacancy Monitoring -->
    <div class="tab-pane fade" id="jobs" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Job Title</th><th>Company</th><th>Type</th><th>Location</th><th>Deadline</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allJobs as $j): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($j['title']) ?></td>
                                <td><?= htmlspecialchars($j['company_name']) ?></td>
                                <td><span class="badge badge-navy"><?= $j['job_type'] ?></span></td>
                                <td><?= htmlspecialchars($j['location']) ?></td>
                                <td><?= $j['deadline'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 3: All Student Applications -->
    <div class="tab-pane fade" id="apps" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Student Name</th><th>Degree</th><th>Applied Job</th><th>Company</th><th>Match Score</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allApplications as $app): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($app['student_name']) ?></td>
                                <td><?= htmlspecialchars($app['degree']) ?></td>
                                <td><?= htmlspecialchars($app['job_title']) ?></td>
                                <td><?= htmlspecialchars($app['company_name']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= $app['match_score'] ?>%</span></td>
                                <td><span class="badge bg-info text-dark"><?= $app['status'] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>