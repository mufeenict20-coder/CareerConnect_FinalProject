<?php
// logout.php
require_once __DIR__ . '/classes/Auth.php';

// Destroy user session
Auth::logout();

// Redirect to login page
header("Location: login.php");
exit;