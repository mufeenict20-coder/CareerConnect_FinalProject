<?php
// apply.php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/MatchingEngine.php';

Auth::requireRole(['Student']);
$db = Database::getConnection();

$jobId = (int)($_GET['job_id'] ?? 0);
$studentId = $_SESSION['student_id'];
$message = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
        $fileTmp  = $_FILES['cv']['tmp_name'];
        $fileName = $_FILES['cv']['name'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileExt === 'pdf') {
            $uploadDir = __DIR__ . '/uploads/cvs/';
            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

            $newFileName = 'CV_Student_' . $studentId . '_' . time() . '.pdf';
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmp, $destPath)) {
                // Update CV path in students table
                $stmtCV = $db->prepare("UPDATE students SET cv_path = :cv WHERE student_id = :sid");
                $stmtCV->execute([':cv' => $newFileName, ':sid' => $studentId]);

                // Calculate Match Score
                $engine = new MatchingEngine($db);
                $matchResult = $engine->calculateMatchScore($studentId, $jobId);
                $score = $matchResult['score'];

                try {
                    // Insert Application
                    $appStmt = $db->prepare("INSERT INTO applications (student_id, job_id, match_score, status) VALUES (:sid, :jid, :score, 'Pending')");
                    $appStmt->execute([':sid' => $studentId, ':jid' => $jobId, ':score' => $score]);

                    // Notify Employer
                    $empStmt = $db->prepare("SELECT u.user_id, j.title FROM jobs j JOIN employers e ON j.employer_id = e.employer_id JOIN users u ON e.user_id = u.user_id WHERE j.job_id = :jid");
                    $empStmt->execute([':jid' => $jobId]);
                    $empInfo = $empStmt->fetch();

                    if ($empInfo) {
                        $notifMsg = "New application received for '" . $empInfo['title'] . "'";
                        $notifStmt = $db->prepare("INSERT INTO notifications (user_id, message) VALUES (:uid, :msg)");
                        $notifStmt->execute([':uid' => $empInfo['user_id'], ':msg' => $notifMsg]);
                    }

                    $message = "Application submitted successfully with CV!";
                } catch (PDOException $e) {
                    $error = "You have already applied for this vacancy.";
                }
            } else { $error = "Failed to save uploaded CV file."; }
        } else { $error = "Only PDF files are allowed."; }
    } else { $error = "Please upload your CV in PDF format."; }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-corporate-hero text-white"><h5 class="mb-0">Submit Job Application</h5></div>
            <div class="card-body">
                <?php if ($message): ?><div class="alert alert-success"><?= $message ?> <a href="student_applications.php">View Application Status</a></div><?php endif; ?>
                <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Upload CV (PDF format only)</label>
                        <input type="file" name="cv" class="form-control" accept=".pdf" required>
                    </div>
                    <button type="submit" class="btn btn-gold w-100">Submit Application</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>