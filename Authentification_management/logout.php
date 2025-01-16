<?php
//logout.php
session_start();

// Destroy the session
session_unset();
session_destroy();

// Set headers to prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');

// Redirect to the login page
header('Location: login.php');
exit;
?>
