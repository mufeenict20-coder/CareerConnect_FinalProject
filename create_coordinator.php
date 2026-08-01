<?php
// create_coordinator.php
require_once __DIR__ . '/config/Database.php';

try {
    $db = Database::getConnection();

    $name     = 'University Coordinator';
    $email    = 'coordinator@careerconnect.ac.lk';
    $password = password_hash('coord123', PASSWORD_BCRYPT);
    $role     = 'Coordinator';

    $stmt = $db->prepare("
        INSERT INTO users (name, email, password, role) 
        VALUES (:name, :email, :password, :role)
        ON CONFLICT(email) DO UPDATE SET role = 'Coordinator', password = :password
    ");

    $stmt->execute([
        ':name'     => $name,
        ':email'    => $email,
        ':password' => $password,
        ':role'     => $role
    ]);

    echo "<div style='font-family:sans-serif; padding:20px; background:#d4edda; color:#155724; border-radius:8px; margin:20px;'>";
    echo "<h3>✅ Coordinator Account Ready!</h3>";
    echo "<p><strong>Email:</strong> coordinator@careerconnect.ac.lk</p>";
    echo "<p><strong>Password:</strong> coord123</p>";
    echo "<p><a href='login.php' style='color:#155724; font-weight:bold;'>Click here to Login</a></p>";
    echo "</div>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}