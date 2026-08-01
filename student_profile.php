<?php
// student_profile.php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/upload_avatar.php';

Auth::requireRole(['Student']);
$db = Database::getConnection();
$userModel = new User($db);

$studentId = $_SESSION['student_id'];
$userId    = $_SESSION['user_id'];
$message   = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['name'] ?? '');
    $degree = trim($_POST['degree'] ?? '');
    $gpa    = (float)($_POST['gpa'] ?? 0.0);
    $selectedSkills = $_POST['skills'] ?? [];

    try {
        $db->beginTransaction();

        // 1. Update Base User
        $uStmt = $db->prepare("UPDATE users SET name = :name WHERE user_id = :uid");
        $uStmt->execute([':name' => $name, ':uid' => $userId]);
        $_SESSION['name'] = $name;

        // 2. Process Profile Picture Upload
        handleProfilePicUpload($db, $userId);

        // 3. Update Student Record
        $sStmt = $db->prepare("UPDATE students SET degree = :degree, gpa = :gpa WHERE student_id = :sid");
        $sStmt->execute([':degree' => $degree, ':gpa' => $gpa, ':sid' => $studentId]);

        // 4. Update Skills
        $userModel->updateStudentSkills($studentId, $selectedSkills);

        $db->commit();
        $message = "Profile and photo updated successfully!";
    } catch (Exception $e) {
        if ($db->inTransaction()) { $db->rollBack(); }
        $error = "Error updating profile: " . $e->getMessage();
    }
}

// Fetch Profile Data
$stmt = $db->prepare("SELECT u.name, u.email, u.profile_pic, s.degree, s.gpa FROM users u JOIN students s ON u.user_id = s.user_id WHERE s.student_id = :sid");
$stmt->execute([':sid' => $studentId]);
$profile = $stmt->fetch();

$skillStmt = $db->prepare("SELECT skill_id FROM student_skills WHERE student_id = :sid");
$skillStmt->execute([':sid' => $studentId]);
$currentSkills = $skillStmt->fetchAll(PDO::FETCH_COLUMN);

$allSkills = $userModel->getAllSkills();
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-corporate-hero text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i> Student Profile Management</h5>
            </div>
            <div class="card-body">
                <?php if ($message): ?><div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
                <?php if ($error): ?><div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="text-center mb-4">
                        <img src="<?= getProfilePicPath($profile['profile_pic']) ?>" class="rounded-circle shadow border border-3 border-primary mb-2" style="width: 110px; height: 110px; object-fit: cover;">
                        <div>
                            <label class="btn btn-sm btn-outline-primary rounded-pill mt-1">
                                <i class="bi bi-camera-fill me-1"></i> Upload Profile Picture
                                <input type="file" name="profile_pic" accept="image/*" style="display: none;">
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($profile['name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Address</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($profile['email'] ?? '') ?>" disabled>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Degree Program</label>
                            <input type="text" name="degree" class="form-control" value="<?= htmlspecialchars($profile['degree'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">GPA</label>
                            <input type="number" step="0.01" min="0" max="4.0" name="gpa" class="form-control" value="<?= htmlspecialchars($profile['gpa'] ?? 0) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Your Technical Skills</label>
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

                    <button type="submit" class="btn btn-sapphire w-100 rounded-pill">Save Profile Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>