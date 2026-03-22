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
    <title>Admin Dashboard - Event Reminder</title>
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
            <a href="dashboard.php" class="nav-item active">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="margin-right:10px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard
            </a>
            <a href="create_event.php" class="nav-item">
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
                <?php if($pending_ai_events > 0): ?>
                    <span style="background:var(--danger-color);color:#fff;padding:2px 8px;border-radius:12px;font-size:12px;margin-left:auto;"><?php echo $pending_ai_events; ?></span>
                <?php endif; ?>
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
            <div class="page-title">Admin Overview</div>
            <div class="user-profile">
                <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                <div style="width:36px;height:36px;background:var(--primary-color);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:14px;">
                    <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="content">
            <?php if (isset($_GET['reminders_sent'])): ?>
                <?php $rs = (int)$_GET['reminders_sent']; $rf = (int)($_GET['reminders_failed'] ?? 0); ?>
                <div class="success">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Reminders sent: <strong><?= $rs ?></strong><?= $rf ? " &nbsp;|&nbsp; Failed: <strong>{$rf}</strong> (check SMTP config)" : '' ?></span>
                </div>
            <?php endif; ?>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="font-size: 1.5rem; font-weight: 700;">System Statistics</h1>
                <form action="../send_reminders.php" method="POST" style="margin: 0;">
                     <?php echo csrf_input(); ?>
                     <button type="submit" class="btn btn-outline">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        Trigger Reminders Job
                    </button>
                 </form>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <p class="value" style="color: var(--primary-color);"><?php echo $total_users; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Events</h3>
                    <p class="value" style="color: var(--success-color);"><?php echo $total_events; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Pending AI Events</h3>
                    <p class="value" style="color: <?php echo $pending_ai_events > 0 ? 'var(--danger-color)' : 'var(--text-color)'; ?>;"><?php echo $pending_ai_events; ?></p>
                </div>
            </div>

            <h2 style="margin-top: 3rem; margin-bottom: 1.5rem; font-size: 1.25rem;">Quick Actions</h2>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                 <a href="create_event.php" class="btn btn-primary">Create Global Event</a>
                 <a href="upload_calendar.php" class="btn btn-outline">Upload Academic Calendar</a>
                 <a href="review_ai_events.php" class="btn btn-outline">Review AI Events</a>
            </div>

        </main>
    </div>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>
