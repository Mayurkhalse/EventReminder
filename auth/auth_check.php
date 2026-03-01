<?php
// Purpose: Handles secure user login and session creation.

session_start();

// If the user is not logged in, redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header('Location: /EventREmainder2/index.php');
    exit();
}
?>
