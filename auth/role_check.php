<?php
// Purpose: Role enforcement.

// This script should be included after auth_check.php
// It expects a $required_role variable to be set on the page that includes it.

if (!isset($required_role)) {
    // Default to a safe role if not specified, though it should always be specified.
    $required_role = 'user'; 
}

if ($_SESSION['role'] !== $required_role) {
    // If the user does not have the required role, redirect them.
    // For simplicity, we'll redirect to their respective dashboards.
    if ($_SESSION['role'] === 'admin') {
        header('Location: /EventREmainder2/admin/dashboard.php');
    } else {
        header('Location: /EventREmainder2/user/dashboard.php');
    }
    exit();
}
?>
