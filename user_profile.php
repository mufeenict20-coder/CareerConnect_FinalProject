<?php
// user_profile.php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/upload_avatar.php';

Auth::requireRole(['Coordinator', 'Admin']);
$db = Database::getConnection();

$userId  = $_SESSION['user_id'];
$message = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $db->beginTransaction();

        // 1. Update User Name
        if (!empty($name)) {
            $stmt = $db->prepare("UPDATE users SET name = :name WHERE user_id = :uid");
            $stmt->execute([':name' => $name, ':uid' => $userId]);
            $_SESSION['name'] = $name;
        }

        // 2. Handle Password Change if provided
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $pStmt  = $db->prepare("UPDATE users SET password = :pass WHERE user_id = :uid");
            $pStmt->execute([':pass' => $hashed, ':uid' => $userId]);
        }

        // 3. Handle Profile Photo Upload
        handleProfilePicUpload($db, $userId);

        $db->commit();
        $message = "Profile details and photo updated successfully!";
    } catch (Exception $e) {
        if ($db->inTransaction()) { $db->rollBack(); }
        $error = "Error updating profile: " . $e->getMessage();
    }
}

// Fetch user data
$stmt = $db->prepare("SELECT name, email, role, profile_pic FROM users WHERE user_id = :uid");
$stmt->execute([':uid' => $userId]);
$user = $stmt->fetch();

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-corporate-hero text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i> Account Profile Management</h5>
                <span class="badge badge-sapphire"><?= htmlspecialchars($user['role']) ?></span>
            </div>
            <div class="card-body p-4">
                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="text-center mb-4">
                        <img src="<?= getProfilePicPath($user['profile_pic']) ?>" class="rounded-circle shadow border border-3 border-primary mb-2" style="width: 110px; height: 110px; object-fit: cover;">
                        <div>
                            <label class="btn btn-sm btn-outline-primary rounded-pill mt-1">
                                <i class="bi bi-camera-fill me-1"></i> Upload Profile Picture
                                <input type="file" name="profile_pic" accept="image/*" style="display: none;">
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Address</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                        <small class="text-muted">Account email is managed by system administration.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Change Password <small class="text-muted fw-normal">(Leave blank to keep current)</small></label>
                        <input type="password" name="password" class="form-control" placeholder="Enter new password">
                    </div>

                    <button type="submit" class="btn btn-sapphire w-100 rounded-pill">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>