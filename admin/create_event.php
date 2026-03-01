<?php
// Purpose: Global event creation.

require_once __DIR__ . '/../auth/auth_check.php';

$required_role = 'admin';
require_once __DIR__ . '/../auth/role_check.php';
require_once __DIR__ . '/../auth/csrf.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];

    if (empty($title) || empty($event_date)) {
        die('Title and event date are required.');
    }

    // Reminder dates calculation
    $event_timestamp = strtotime($event_date);
    $reminder1_date = date('Y-m-d H:i:s', $event_timestamp - 86400 * 2); // 2 days before for global events
    $reminder2_date = date('Y-m-d H:i:s', $event_timestamp - 86400);   // 1 day before

    $is_global = true;

    $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, reminder1_date, reminder2_date, is_global) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $title, $description, $event_date, $reminder1_date, $reminder2_date, $is_global);

    if ($stmt->execute()) {
        header('Location: dashboard.php?success=global_event_added');
        exit();
    } else {
        die('Error adding global event.');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Global Event</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <div class="container" style="max-width: 600px;">
        <div class="dashboard-header">
            <h1>Create a Global Event</h1>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <form action="create_event.php" method="POST">
            <?php echo csrf_input(); ?>
            <div class="form-group">
                <label for="title">Event Title</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label for="event_date">Event Date and Time</label>
                <input type="datetime-local" id="event_date" name="event_date" required>
            </div>
            <button type="submit" class="btn btn-primary">Create Global Event</button>
        </form>
    </div>

</body>
</html>
