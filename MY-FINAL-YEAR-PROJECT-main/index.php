<?php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/includes/header.php';

$db = Database::getConnection();
$stmt = $db->query("
    SELECT j.*, e.company_name, e.industry, u.profile_pic 
    FROM jobs j 
    JOIN employers e ON j.employer_id = e.employer_id 
    JOIN users u ON e.user_id = u.user_id
    WHERE j.deadline >= DATE('now') AND e.is_verified = 1
    ORDER BY j.created_at DESC
");
$jobs = $stmt->fetchAll();
?>

<!-- Custom CSS override for the Cyan Accent -->
<style>
    .text-cyan {
        color: #fbbf24 !important; /* Academic Gold */
    }
    .btn-cyan {
        background-color: #d97706;
        color: #ffffff;
        border: none;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(217, 119, 6, 0.3);
    }
    .btn-cyan:hover {
        background-color: #b45309;
        color: #ffffff;
    }
</style>

<!-- Hero Banner -->
<div class="p-5 mb-5 bg-corporate-hero shadow-lg">
  <div class="container-fluid py-2">
    <h1 class="display-5 fw-bold mb-3">Find Your Next <span class="text-cyan">Internship</span> or <span class="text-cyan">Career</span> Role</h1>
    <p class="col-md-9 fs-5 text-white fw-normal opacity-90">CareerConnect uses smart skill matching to seamlessly connect university talent with verified industry employers.</p>
    <div class="mt-4">
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a class="btn btn-cyan btn-lg me-2 rounded-pill px-4" href="register.php"><i class="bi bi-rocket-takeoff me-1"></i> Get Started Today</a>
            <a class="btn btn-outline-light btn-lg rounded-pill px-4" href="login.php">Sign In</a>
        <?php else: ?>
            <a class="btn btn-cyan btn-lg me-2 rounded-pill px-4" href="dashboard.php"><i class="bi bi-speedometer2 me-1"></i> Go to Dashboard</a>
        <?php endif; ?>
    </div>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-navy mb-0"><i class="bi bi-briefcase-fill text-sapphire me-2"></i> Active Opportunities</h3>
    <span class="badge badge-sapphire px-3 py-2 fs-6 rounded-pill"><?= count($jobs) ?> Positions Available</span>
</div>

<div class="row">
    <?php if (empty($jobs)): ?>
        <div class="col-12"><div class="alert alert-info shadow-sm">No active vacancies posted yet. Check back soon!</div></div>
    <?php else: ?>
        <?php foreach ($jobs as $job): ?>
            <div class="col-md-4 mb-4">
                <div class="card vacancy-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="<?= getProfilePicPath($job['profile_pic']) ?>" class="rounded-circle border me-2" style="width: 45px; height: 45px; object-fit: cover;">
                            <div>
                                <h6 class="mb-0 text-sapphire fw-bold"><?= htmlspecialchars($job['company_name']) ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($job['industry']) ?></small>
                            </div>
                            <span class="badge badge-sapphire ms-auto"><?= htmlspecialchars($job['job_type']) ?></span>
                        </div>

                        <h5 class="card-title fw-bold text-navy mb-2"><?= htmlspecialchars($job['title']) ?></h5>
                        <p class="card-text text-muted text-truncate"><?= htmlspecialchars($job['description']) ?></p>
                        <small class="text-muted d-block mb-2"><i class="bi bi-geo-alt-fill text-sapphire me-1"></i> <?= htmlspecialchars($job['location']) ?></small>
                    </div>
                    <div class="card-footer bg-transparent border-0 pb-3">
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Student'): ?>
                            <a href="apply.php?job_id=<?= $job['job_id'] ?>" class="btn btn-sapphire btn-sm w-100 rounded-pill"><i class="bi bi-send-fill me-1"></i> Apply Now</a>
                        <?php elseif (!isset($_SESSION['user_id'])): ?>
                            <a href="login.php" class="btn btn-outline-sapphire btn-sm w-100 rounded-pill">Login to View & Apply</a>
                        <?php else: ?>
                            <span class="badge badge-sapphire w-100 py-2 rounded-pill"><?= htmlspecialchars($_SESSION['role']) ?> View Only</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>