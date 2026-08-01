<?php
// verify_me.php
require_once __DIR__ . '/config/Database.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Employer') {
    die("Please log in as an Employer first!");
}

try {
    $db = Database::getConnection();
    
    // Set all registered employers to verified = 1
    $stmt = $db->prepare("UPDATE employers SET is_verified = 1 WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);

    $_SESSION['is_verified'] = 1;

    echo "<div style='font-family:sans-serif; padding:20px; background:#d4edda; color:#155724; border-radius:8px; margin:20px;'>";
    echo "<h3>✅ Employer Account Verified Successfully!</h3>";
    echo "<p>You can now create and publish vacancies.</p>";
    echo "<p><a href='post_job.php' style='color:#155724; font-weight:bold;'>Go to Post Vacancy Page</a></p>";
    echo "</div>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}