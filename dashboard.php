<?php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Auth.php';

Auth::requireRole(['Student', 'Employer', 'Coordinator', 'Admin']);
$db = Database::getConnection();

$role   = $_SESSION['role'];
$name   = $_SESSION['name'];
$userId = $_SESSION['user_id'];

// Fetch user profile picture
$userStmt = $db->prepare("SELECT profile_pic FROM users WHERE user_id = :uid");
$userStmt->execute([':uid' => $userId]);
$userInfo = $userStmt->fetch();

// Fetch Notifications
$notifStmt = $db->prepare("SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT 5");
$notifStmt->execute([':uid' => $userId]);
$notifications = $notifStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Portal Banner -->
<div class="p-4 bg-corporate-hero rounded-3 shadow mb-4 text-white">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <img src="<?= getProfilePicPath($userInfo['profile_pic'] ?? '') ?>" class="rounded-circle border border-3 border-white shadow" style="width: 70px; height: 70px; object-fit: cover;">
            <div>
                <h2 class="fw-bold mb-0">Welcome, <?= htmlspecialchars($name) ?>!</h2>
                <p class="mb-0 opacity-75">CareerConnect Smart Portal</p>
            </div>
        </div>
        <span class="badge badge-emerald fs-6 px-3 py-2 rounded-pill"><?= htmlspecialchars($role) ?> Portal</span>
    </div>
</div>

<!-- Notifications Drawer -->
<?php if (!empty($notifications)): ?>
    <div class="alert alert-warning shadow-sm border-start border-4 border-warning mb-4">
        <h6 class="fw-bold"><i class="bi bi-bell-fill me-2"></i> Recent Notifications</h6>
        <ul class="mb-0 ps-3">
            <?php foreach ($notifications as $n): ?>
                <li><?= htmlspecialchars($n['message']) ?> <small class="text-muted">(<?= date('M d, g:i a', strtotime($n['created_at'])) ?>)</small></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Dynamic Role Cards -->
<div class="row">
    <?php if ($role === 'Student'): ?>
        <div class="col-md-4 mb-3">
            <a href="index.php" class="text-decoration-none">
                <div class="card h-100 p-4"><h5 class="fw-bold text-navy"><i class="bi bi-search text-sapphire me-2"></i> Browse Opportunities</h5><p class="text-muted mb-0">Apply for job vacancies with CV upload.</p></div>
            </a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="student_applications.php" class="text-decoration-none">
                <div class="card h-100 p-4"><h5 class="fw-bold text-navy"><i class="bi bi-file-earmark-check text-sapphire me-2"></i> Application Status</h5><p class="text-muted mb-0">Track application progress and employer responses.</p></div>
            </a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="student_profile.php" class="text-decoration-none">
                <div class="card h-100 p-4"><h5 class="fw-bold text-navy"><i class="bi bi-person-gear text-sapphire me-2"></i> Profile & Photo</h5><p class="text-muted mb-0">Upload profile photo, update GPA, and skills.</p></div>
            </a>
        </div>

    <?php elseif ($role === 'Employer'): ?>
        <div class="col-md-4 mb-3">
            <a href="post_job.php" class="text-decoration-none">
                <div class="card h-100 p-4"><h5 class="fw-bold text-navy"><i class="bi bi-plus-circle text-sapphire me-2"></i> Post Vacancy</h5><p class="text-muted mb-0">Publish new job or internship listings.</p></div>
            </a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="manage_vacancies.php" class="text-decoration-none">
                <div class="card h-100 p-4"><h5 class="fw-bold text-navy"><i class="bi bi-kanban text-sapphire me-2"></i> Manage Vacancies</h5><p class="text-muted mb-0">Edit active job posts and review applications.</p></div>
            </a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="employer_profile.php" class="text-decoration-none">
                <div class="card h-100 p-4"><h5 class="fw-bold text-navy"><i class="bi bi-building-gear text-sapphire me-2"></i> Company Profile & Logo</h5><p class="text-muted mb-0">Update company logo, details, and website.</p></div>
            </a>
        </div>

    <?php elseif ($role === 'Coordinator'): ?>
        <div class="col-md-6 mb-3">
            <a href="coordinator_panel.php" class="text-decoration-none">
                <div class="card h-100 p-4"><h5 class="fw-bold text-navy"><i class="bi bi-patch-check text-sapphire me-2"></i> Employer Verification & Reports</h5><p class="text-muted mb-0">Approve new employers and export placement analytics.</p></div>
            </a>
        </div>
        <div class="col-md-6 mb-3">
            <a href="user_profile.php" class="text-decoration-none">
                <div class="card h-100 p-4"><h5 class="fw-bold text-navy"><i class="bi bi-person-circle text-sapphire me-2"></i> Profile & Photo</h5><p class="text-muted mb-0">Upload profile picture and update account settings.</p></div>
            </a>
        </div>

    <?php elseif ($role === 'Admin'): ?>
        <div class="col-md-4 mb-3">
            <a href="manage_users.php" class="text-decoration-none">
                <div class="card h-100 p-4"><h5 class="fw-bold text-navy"><i class="bi bi-people text-sapphire me-2"></i> Manage System Users</h5><p class="text-muted mb-0">Control accounts across the platform.</p></div>
            </a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="manage_posts.php" class="text-decoration-none">
                <div class="card h-100 p-4"><h5 class="fw-bold text-navy"><i class="bi bi-shield-x text-sapphire me-2"></i> Content Moderation</h5><p class="text-muted mb-0">Review, edit, or delete inappropriate job posts.</p></div>
            </a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="user_profile.php" class="text-decoration-none">
                <div class="card h-100 p-4"><h5 class="fw-bold text-navy"><i class="bi bi-person-gear text-sapphire me-2"></i> Profile & Photo</h5><p class="text-muted mb-0">Upload profile photo and update admin security details.</p></div>
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>