<?php
/**
 * Logout Script (logout.php)
 * Destroys user session and redirects to homepage
 */

// Start session
session_start();

// Destroy all session data
session_destroy();

// Redirect to homepage
header('Location: index.php');
exit;
?>