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

    // If reminder dates are in the past, mark them as already sent
    $now_ts = time();
    $r1_sent = (strtotime($reminder1_date) <= $now_ts) ? 1 : 0;
    $r2_sent = (strtotime($reminder2_date) <= $now_ts) ? 1 : 0;

    $is_global = true;

    $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, reminder1_date, reminder2_date, is_global, reminder1_sent, reminder2_sent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssiii", $title, $description, $event_date, $reminder1_date, $reminder2_date, $is_global, $r1_sent, $r2_sent);

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
    <title>Create Global Event - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Admin Panel</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="margin-right:10px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard
            </a>
            <a href="create_event.php" class="nav-item active">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="margin-right:10px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Global Event
            </a>
            <a href="upload_calendar.php" class="nav-item">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="margin-right:10px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Upload PDF
            </a>
            <a href="review_ai_events.php" class="nav-item">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="margin-right:10px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Review AI Events
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="nav-item" style="color: var(--danger-color);">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="margin-right:10px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Sign Out
            </a>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="main-content-wrapper">
        <!-- Top Navbar -->
        <header class="top-navbar">
            <div class="page-title">Global Events</div>
            <div class="user-profile">
                <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                <div style="width:36px;height:36px;background:var(--primary-color);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:14px;">
                    <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="font-size: 1.5rem; font-weight: 700;">Create a Global Event</h1>
                <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
            </div>

            <div class="card">
                <form action="create_event.php" method="POST">
                    <?php echo csrf_input(); ?>
                    
                    <div class="form-group">
                        <label for="title">Event Title <span style="color:var(--danger-color);">*</span></label>
                        <input type="text" id="title" name="title" placeholder="e.g. University Spring Break" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="event_date">Event Date and Time <span style="color:var(--danger-color);">*</span></label>
                        <input type="datetime-local" id="event_date" name="event_date" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" placeholder="Provide event details..."></textarea>
                    </div>
                    
                    <div style="margin-top: 2rem; display: flex; justify-content: flex-end; gap: 1rem;">
                        <a href="dashboard.php" class="btn btn-outline">Cancel</a>
                        <button type="submit" class="btn btn-primary" style="width: auto;">Publish Global Event</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>
