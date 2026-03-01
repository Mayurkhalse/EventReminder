<?php
// Purpose: Admin overview.

require_once __DIR__ . '/../auth/auth_check.php';

$required_role = 'admin';
require_once __DIR__ . '/../auth/role_check.php';
require_once __DIR__ . '/../auth/csrf.php';
require_once __DIR__ . '/../config/db.php';

// Fetch stats
$total_users_result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
$total_users = $total_users_result->fetch_assoc()['count'];

$total_events_result = $conn->query("SELECT COUNT(*) as count FROM events");
$total_events = $total_events_result->fetch_assoc()['count'];

$pending_ai_events_result = $conn->query("SELECT COUNT(*) as count FROM ai_pending_events WHERE status = 'pending'");
$pending_ai_events = $pending_ai_events_result->fetch_assoc()['count'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <div class="container">
        <div class="dashboard-header">
            <h1>Admin Dashboard</h1>
            <a href="../auth/logout.php" class="btn logout-btn">Logout</a>
        </div>

        <div class="main-content">
            <?php if (isset($_GET['reminders_sent'])): ?>
                <?php $rs = (int)$_GET['reminders_sent']; $rf = (int)($_GET['reminders_failed'] ?? 0); ?>
                <div style="background:#d4edda;border:1px solid #c3e6cb;color:#155724;padding:1rem 1.5rem;border-radius:8px;margin-bottom:1.5rem;">
                    ✅ Reminders sent: <strong><?= $rs ?></strong><?= $rf ? " &nbsp;|&nbsp; ❌ Failed: <strong>{$rf}</strong> (check SMTP config)" : '' ?>
                </div>
            <?php endif; ?>
            <h2>System Overview</h2>
            <div class="event-grid">
                <div class="event-card">
                    <h3>Total Users</h3>
                    <p style="font-size: 2rem; font-weight: bold;"><?php echo $total_users; ?></p>
                </div>
                <div class="event-card">
                    <h3>Total Events</h3>
                    <p style="font-size: 2rem; font-weight: bold;"><?php echo $total_events; ?></p>
                </div>
                <div class="event-card">
                    <h3>Pending AI Events</h3>
                    <p style="font-size: 2rem; font-weight: bold;"><?php echo $pending_ai_events; ?></p>
                </div>
            </div>

            <h2 style="margin-top: 3rem;">Admin Actions</h2>
            <div class="admin-actions">
                 <a href="create_event.php" class="btn btn-primary">Create Global Event</a>
                 <a href="upload_calendar.php" class="btn btn-primary">Upload Academic Calendar</a>
                 <a href="review_ai_events.php" class="btn btn-primary">Review AI Events</a>
                 <form action="../send_reminders.php" method="POST" style="display:inline;">
                     <?php echo csrf_input(); ?>
                     <button type="submit" class="btn btn-primary" style="background:var(--secondary-color);">⏰ Send Pending Reminders</button>
                 </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
