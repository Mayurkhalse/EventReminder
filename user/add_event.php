<?php
// Purpose: Create event.

require_once __DIR__ . '/../auth/auth_check.php';

$required_role = 'user';
require_once __DIR__ . '/../auth/role_check.php';
require_once __DIR__ . '/../auth/csrf.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];
    
    // Server-side validation
    if (empty($title) || empty($event_date)) {
        die('Title and event date are required.');
    }

    // Reminder dates calculation (e.g., 1 day and 1 hour before)
    $event_timestamp = strtotime($event_date);
    $reminder1_date = date('Y-m-d H:i:s', $event_timestamp - 86400); // 1 day before
    $reminder2_date = date('Y-m-d H:i:s', $event_timestamp - 3600);   // 1 hour before


    $stmt = $conn->prepare("INSERT INTO events (user_id, title, description, event_date, reminder1_date, reminder2_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $user_id, $title, $description, $event_date, $reminder1_date, $reminder2_date);

    if ($stmt->execute()) {
        header('Location: dashboard.php?success=event_added');
        exit();
    } else {
        die('Error adding event. Please try again.');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Event</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <div class="container" style="max-width: 600px;">
         <div class="dashboard-header">
            <h1>Add a New Event</h1>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <form action="add_event.php" method="POST">
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
            <button type="submit" class="btn btn-primary">Add Event</button>
        </form>
    </div>

</body>
</html>
