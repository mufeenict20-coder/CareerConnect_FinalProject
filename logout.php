<?php
// logout.php
require_once __DIR__ . '/classes/Auth.php';

// Destroy user session safely
Auth::logout();

// Redirect to login screen
header("Location: login.php");
exit;