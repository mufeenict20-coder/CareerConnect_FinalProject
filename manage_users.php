<?php
// manage_users.php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Auth.php';

// Restrict access strictly to Admin
Auth::requireRole(['Admin']);

$db = Database::getConnection();
$message = '';
$error = '';

// Handle User Deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $deleteId = (int)$_GET['id'];
    
    // Protect Admin from deleting their own active account
    if ($deleteId === $_SESSION['user_id']) {
        $error = "You cannot delete your own active Admin account!";
    } else {
        try {
            $stmt = $db->prepare("DELETE FROM users WHERE user_id = :id");
            $stmt->execute([':id' => $deleteId]);
            $message = "User account successfully deleted.";
        } catch (Exception $e) {
            $error = "Failed to delete user: " . $e->getMessage();
        }
    }
}

// Fetch all system users
$usersStmt = $db->query("
    SELECT u.user_id, u.name, u.email, u.role, u.created_at,
           e.company_name, e.is_verified
    FROM users u
    LEFT JOIN employers e ON u.user_id = e.user_id
    ORDER BY u.created_at DESC
");
$users = $usersStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-gear-wide-connected text-danger"></i> System Administration</h2>
    <a href="dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
</div>

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">All System Users</h5>
        <span class="badge bg-light text-dark">Total: <?= count($users) ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Details / Status</th>
                        <th>Registered Date</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><strong>#<?= $user['user_id'] ?></strong></td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <?php
                                    $badgeClass = match($user['role']) {
                                        'Admin' => 'bg-danger',
                                        'Coordinator' => 'bg-warning text-dark',
                                        'Employer' => 'bg-info text-dark',
                                        'Student' => 'bg-primary',
                                        default => 'bg-secondary'
                                    };
                                ?>
                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($user['role']) ?></span>
                            </td>
                            <td>
                                <?php if ($user['role'] === 'Employer'): ?>
                                    <small class="d-block text-muted"><?= htmlspecialchars($user['company_name'] ?? 'N/A') ?></small>
                                    <?php if ($user['is_verified']): ?>
                                        <span class="badge bg-success-subtle text-success border border-success">Verified</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning">Pending Approval</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><small><?= date('M d, Y', strtotime($user['created_at'])) ?></small></td>
                            <td class="text-center">
                                <?php if ($user['user_id'] !== $_SESSION['user_id']): ?>
                                    <a href="manage_users.php?action=delete&id=<?= $user['user_id'] ?>" 
                                       class="btn btn-outline-danger btn-sm"
                                       onclick="return confirm('Are you sure you want to delete this account? This action cannot be undone.');">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Current Session</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>