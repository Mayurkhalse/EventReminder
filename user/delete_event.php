<?php
// Purpose: Event deletion.

require_once __DIR__ . '/../auth/auth_check.php';

$required_role = 'user';
require_once __DIR__ . '/../auth/role_check.php';
require_once __DIR__ . '/../auth/csrf.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit();
}

verify_csrf_token();

$event_id = $_POST['event_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$event_id) {
    die('Event ID is required.');
}

$stmt = $conn->prepare("DELETE FROM events WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $event_id, $user_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        header('Location: dashboard.php?success=event_deleted');
    } else {
        // This could mean the event was not in the past, or didn't belong to the user
        header('Location: dashboard.php?error=delete_failed');
    }
} else {
    die('Error deleting event.');
}

$stmt->close();
$conn->close();
exit();
