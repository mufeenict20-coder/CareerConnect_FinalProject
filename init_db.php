<?php
require_once __DIR__ . '/config/Database.php';

try {
    $db = Database::getConnection();
    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    $db->exec($sql);
    
    echo "<div style='font-family:sans-serif; padding:20px; background:#d4edda; color:#155724; border-radius:8px; margin:20px;'>";
    echo "<h3>✅ Database Initialized Successfully!</h3>";
    echo "<p>The file <code>database/careerconnect.sqlite</code> was created and tables were initialized.</p>";
    echo "<a href='index.php'>Go to Home Page</a>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='font-family:sans-serif; padding:20px; background:#f8d7da; color:#721c24; border-radius:8px; margin:20px;'>";
    echo "<h3>❌ Error Initializing Database</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}