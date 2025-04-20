<?php
// Initialize the session
session_start();

// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
if (session_destroy()) {
     // Redirect to login page
    header("Location: ../login.php"); // Change "login.php" to your actual login page
    exit;
}
else{
    echo "Failed to logout"; // Optional Error message.
}
?>