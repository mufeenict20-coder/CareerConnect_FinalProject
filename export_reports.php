<?php
// export_reports.php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Auth.php';

Auth::requireRole(['Coordinator', 'Admin']);
$db = Database::getConnection();

$type = $_GET['type'] ?? '';

if ($type === 'csv') {
    // Export Applications to CSV / Excel readable format
    $stmt = $db->query("
        SELECT a.application_id, u.name AS student_name, s.degree, 
               j.title AS job_title, e.company_name, a.match_score, a.status, a.applied_at
        FROM applications a
        JOIN students s ON a.student_id = s.student_id
        JOIN users u ON s.user_id = u.user_id
        JOIN jobs j ON a.job_id = j.job_id
        JOIN employers e ON j.employer_id = e.employer_id
        ORDER BY a.applied_at DESC
    ");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=CareerConnect_Applications_Report_' . date('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['App ID', 'Student Name', 'Degree Program', 'Job Title', 'Company Name', 'Match Score (%)', 'Status', 'Applied Date']);

    foreach ($data as $row) {
        fputcsv($output, [
            $row['application_id'],
            $row['student_name'],
            $row['degree'],
            $row['job_title'],
            $row['company_name'],
            $row['match_score'] . '%',
            $row['status'],
            $row['applied_at']
        ]);
    }
    fclose($output);
    exit;
} elseif ($type === 'print') {
    // Print-ready Summary Report
    $totalStudents  = $db->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $totalEmployers = $db->query("SELECT COUNT(*) FROM employers WHERE is_verified = 1")->fetchColumn();
    $totalJobs      = $db->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
    $totalApps      = $db->query("SELECT COUNT(*) FROM applications")->fetchColumn();

    $apps = $db->query("
        SELECT u.name AS student_name, j.title AS job_title, e.company_name, a.status, a.applied_at
        FROM applications a
        JOIN students s ON a.student_id = s.student_id
        JOIN users u ON s.user_id = u.user_id
        JOIN jobs j ON a.job_id = j.job_id
        JOIN employers e ON j.employer_id = e.employer_id
        ORDER BY a.applied_at DESC
    ")->fetchAll();

    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>University Placement Summary Report</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>body { padding: 30px; font-family: Arial, sans-serif; }</style>
    </head>
    <body onload="window.print()">
        <div class="text-center mb-4 border-bottom pb-3">
            <h2>CareerConnect — University Placement Summary Report</h2>
            <p class="text-muted">Generated on: <?= date('F j, Y, g:i a') ?></p>
        </div>

        <div class="row text-center mb-4">
            <div class="col-3"><h5>Students: <?= $totalStudents ?></h5></div>
            <div class="col-3"><h5>Employers: <?= $totalEmployers ?></h5></div>
            <div class="col-3"><h5>Vacancies: <?= $totalJobs ?></h5></div>
            <div class="col-3"><h5>Applications: <?= $totalApps ?></h5></div>
        </div>

        <table class="table table-bordered">
            <thead class="table-dark">
                <tr><th>Student</th><th>Job Title</th><th>Company</th><th>Status</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php foreach ($apps as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['student_name']) ?></td>
                        <td><?= htmlspecialchars($a['job_title']) ?></td>
                        <td><?= htmlspecialchars($a['company_name']) ?></td>
                        <td><?= htmlspecialchars($a['status']) ?></td>
                        <td><?= $a['applied_at'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </body>
    </html>
    <?php
    exit;
}