<?php
// Purpose: Edit event.

require_once __DIR__ . '/../auth/auth_check.php';

$required_role = 'user';
require_once __DIR__ . '/../auth/role_check.php';
require_once __DIR__ . '/../auth/csrf.php';
require_once __DIR__ . '/../config/db.php';

$event_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$event_id) {
    die('Event ID is required.');
}

// Fetch the event to ensure it belongs to the logged-in user
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $event_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die('Event not found or you do not have permission to edit it.');
}

$event = $result->fetch_assoc();

// Handle form submission for update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];

    if (empty($title) || empty($event_date)) {
        die('Title and event date are required.');
    }
    
    // When an event is edited, reset its reminder flags
    $reminder1_sent = false;
    $reminder2_sent = false;

    // Recalculate reminder dates
    $event_timestamp = strtotime($event_date);
    $reminder1_date = date('Y-m-d H:i:s', $event_timestamp - 86400); // 1 day before
    $reminder2_date = date('Y-m-d H:i:s', $event_timestamp - 3600);   // 1 hour before

    $update_stmt = $conn->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, reminder1_date = ?, reminder2_date = ?, reminder1_sent = ?, reminder2_sent = ? WHERE id = ? AND user_id = ?");
    $update_stmt->bind_param("sssssiiii", $title, $description, $event_date, $reminder1_date, $reminder2_date, $reminder1_sent, $reminder2_sent, $event_id, $user_id);

    if ($update_stmt->execute()) {
        header('Location: dashboard.php?success=event_updated');
        exit();
    } else {
        die('Error updating event. Please try again.');
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <div class="container" style="max-width: 600px;">
        <div class="dashboard-header">
            <h1>Edit Event</h1>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <form action="edit_event.php?id=<?php echo $event['id']; ?>" method="POST">
            <?php echo csrf_input(); ?>
            <div class="form-group">
                <label for="title">Event Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($event['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="event_date">Event Date and Time</label>
                <input type="datetime-local" id="event_date" name="event_date" value="<?php echo date('Y-m-d\TH:i', strtotime($event['event_date'])); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Event</button>
        </form>
    </div>

</body>
</html>
