<?php
// employer_profile.php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/upload_avatar.php';

Auth::requireRole(['Employer']);
$db = Database::getConnection();

$empId  = $_SESSION['employer_id'];
$userId = $_SESSION['user_id'];
$message = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactName = trim($_POST['name'] ?? '');
    $companyName = trim($_POST['company_name'] ?? '');
    $industry    = trim($_POST['industry'] ?? '');
    $website     = trim($_POST['company_website'] ?? '');

    try {
        $db->beginTransaction();

        // 1. Update Contact Name
        $uStmt = $db->prepare("UPDATE users SET name = :name WHERE user_id = :uid");
        $uStmt->execute([':name' => $contactName, ':uid' => $userId]);
        $_SESSION['name'] = $contactName;

        // 2. Handle Logo Upload
        handleProfilePicUpload($db, $userId);

        // 3. Update Employer Details
        $eStmt = $db->prepare("UPDATE employers SET company_name = :cname, industry = :ind, company_website = :web WHERE employer_id = :eid");
        $eStmt->execute([
            ':cname' => $companyName,
            ':ind'   => $industry,
            ':web'   => $website ? $website : null,
            ':eid'   => $empId
        ]);

        $db->commit();
        $message = "Company profile & logo updated successfully!";
    } catch (Exception $e) {
        if ($db->inTransaction()) { $db->rollBack(); }
        $error = "Error updating company profile: " . $e->getMessage();
    }
}

// Fetch Employer Details
$stmt = $db->prepare("SELECT u.name, u.email, u.profile_pic, e.company_name, e.industry, e.company_website, e.is_verified FROM users u JOIN employers e ON u.user_id = e.user_id WHERE e.employer_id = :eid");
$stmt->execute([':eid' => $empId]);
$emp = $stmt->fetch();

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-corporate-hero text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-building me-2"></i> Company Profile Management</h5>
                <?php if ($emp['is_verified']): ?>
                    <span class="badge bg-success">Verified Company</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark">Pending Verification</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($message): ?><div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
                <?php if ($error): ?><div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="text-center mb-4">
                        <img src="<?= getProfilePicPath($emp['profile_pic']) ?>" class="rounded-circle shadow border border-3 border-primary mb-2" style="width: 110px; height: 110px; object-fit: cover;">
                        <div>
                            <label class="btn btn-sm btn-outline-primary rounded-pill mt-1">
                                <i class="bi bi-camera-fill me-1"></i> Change Company Logo
                                <input type="file" name="profile_pic" accept="image/*" style="display: none;">
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Contact Person Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($emp['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Account Email</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($emp['email']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Company Name</label>
                        <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($emp['company_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Industry Field</label>
                        <input type="text" name="industry" class="form-control" value="<?= htmlspecialchars($emp['industry']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Company Website URL</label>
                        <input type="url" name="company_website" class="form-control" value="<?= htmlspecialchars($emp['company_website'] ?? '') ?>" placeholder="https://company.com">
                    </div>

                    <button type="submit" class="btn btn-sapphire w-100 rounded-pill">Save Profile</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>