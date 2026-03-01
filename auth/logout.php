<?php
// Purpose: Session termination.

session_start();

// Unset all of the session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the login page
header('Location: /EventREmainder2/index.php?message=logged_out');
exit();
?>
