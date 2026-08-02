<?php
// update_db.php
require_once __DIR__ . '/config/Database.php';

try {
    $db = Database::getConnection();
    
    // Add profile_pic column if it doesn't exist
    $db->exec("ALTER TABLE users ADD COLUMN profile_pic TEXT DEFAULT NULL;");

    // Add notifications table if it does not exist
    $db->exec("CREATE TABLE IF NOT EXISTS notifications (
        notification_id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        message TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    );");
    
    // Create uploads folder
    $uploadDir = __DIR__ . '/uploads/profiles/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    echo "<div style='font-family:sans-serif; padding:20px; background:#d1fae5; color:#065f46; border-radius:8px;'>";
    echo "<h3>✅ Database & Storage Directory Updated Successfully!</h3>";
    echo "<p>Profile picture field and notifications table are now available. You can now delete this script or go to <a href='dashboard.php'>Dashboard</a>.</p>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='font-family:sans-serif; padding:20px; background:#fef2f2; color:#991b1b; border-radius:8px;'>";
    echo "<h3>Notice:</h3> <p>" . $e->getMessage() . "</p>";
    echo "<p><a href='dashboard.php'>Go to Dashboard</a></p>";
    echo "</div>";
}