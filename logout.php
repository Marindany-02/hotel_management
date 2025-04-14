<?php
session_start();
session_unset();      // Clear all session variables
session_destroy();    // Destroy the session
session_start(); // Start a new session to set flash message
$_SESSION['flash'] = ['message' => 'You have been logged out successfully.', 'type' => 'success'];
header("Location: login.php"); // Redirect to login page
exit();
