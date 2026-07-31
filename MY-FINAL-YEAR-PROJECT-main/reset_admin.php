<?php
// reset_admin.php
require_once __DIR__ . '/config/Database.php';

try {
    $db = Database::getConnection();

    // Generate fresh, valid BCRYPT hash for 'admin123'
    $newPasswordHash = password_hash('admin123', PASSWORD_BCRYPT);
    $adminEmail = 'admin@careerconnect.ac.lk';

    // Update existing admin password or insert if admin doesn't exist
    $stmt = $db->prepare("
        INSERT INTO users (name, email, password, role) 
        VALUES ('System Admin', :email, :password, 'Admin')
        ON CONFLICT(email) DO UPDATE SET password = :password
    ");

    $stmt->execute([
        ':email'    => $adminEmail,
        ':password' => $newPasswordHash
    ]);

    echo "<div style='font-family:sans-serif; padding:20px; background:#d4edda; color:#155724; border-radius:8px; margin:20px;'>";
    echo "<h3>✅ Admin Password Successfully Reset!</h3>";
    echo "<p><strong>Email:</strong> admin@careerconnect.ac.lk</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "<p><a href='login.php' style='color:#155724; font-weight:bold;'>Click here to Login Now</a></p>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='font-family:sans-serif; padding:20px; background:#f8d7da; color:#721c24; border-radius:8px; margin:20px;'>";
    echo "<h3>❌ Error Resetting Password</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}