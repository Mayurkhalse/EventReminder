<?php
// Purpose: User dashboard UI.

require_once __DIR__ . '/../auth/auth_check.php';

$required_role = 'user';
require_once __DIR__ . '/../auth/role_check.php';
require_once __DIR__ . '/../auth/csrf.php';
require_once __DIR__ . '/../config/db.php';

// Fetch user's events
$user_id = $_SESSION['user_id'];
$now = date('Y-m-d H:i:s');

// Upcoming Events
$upcoming_stmt = $conn->prepare("SELECT * FROM events WHERE user_id = ? AND event_date >= ? ORDER BY event_date ASC");
$upcoming_stmt->bind_param("is", $user_id, $now);
$upcoming_stmt->execute();
$upcoming_result = $upcoming_stmt->get_result();

// Past Events
$past_stmt = $conn->prepare("SELECT * FROM events WHERE user_id = ? AND event_date < ? ORDER BY event_date DESC");
$past_stmt->bind_param("is", $user_id, $now);
$past_stmt->execute();
$past_result = $past_stmt->get_result();

// Global Events
$global_stmt = $conn->prepare("SELECT * FROM events WHERE is_global = 1 AND event_date >= ? ORDER BY event_date ASC");
$global_stmt->bind_param("s", $now);
$global_stmt->execute();
$global_result = $global_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Event Reminder</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>EventApp</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item active">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="margin-right:10px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard
            </a>
            <a href="add_event.php" class="nav-item">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="margin-right:10px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Event
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
            <div class="page-title">Overview</div>
            <div class="user-profile">
                <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                <div style="width:36px;height:36px;background:var(--primary-color);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:14px;">
                    <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="content">
            <?php if (isset($_GET['success'])): ?>
                <div class="success">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>
                    <?php 
                        switch($_GET['success']) {
                            case 'event_added': echo "Event added successfully!"; break;
                            case 'event_updated': echo "Event updated successfully!"; break;
                            case 'event_deleted': echo "Event deleted successfully!"; break;
                            default: echo "Operation successful!";
                        }
                    ?>
                    </span>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="error">
                     <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                     <span>
                     <?php 
                        switch($_GET['error']) {
                            case 'delete_failed': echo "Failed to delete the event."; break;
                            default: echo "An error occurred.";
                        }
                    ?>
                    </span>
                </div>
            <?php endif; ?>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="font-size: 1.5rem; font-weight: 700;">My Events</h1>
                <a href="add_event.php" class="btn btn-primary">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    New Event
                </a>
            </div>

            <div class="tab-navigation">
                <button class="tab-link active" data-tab="upcoming">Upcoming</button>
                <button class="tab-link" data-tab="past">Past</button>
                <button class="tab-link" data-tab="global">Global Events</button>
            </div>

            <!-- Upcoming Events -->
            <div id="upcoming" class="tab-content active">
                <div class="event-grid">
                    <?php if ($upcoming_result->num_rows > 0): ?>
                        <?php while($event = $upcoming_result->fetch_assoc()): ?>
                            <div class="event-card">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p class="desc"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                                <div class="event-card-footer">
                                    <div class="event-date">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <?php echo date('M j, Y, g:i A', strtotime($event['event_date'])); ?>
                                    </div>
                                    <div class="event-actions">
                                        <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn-edit">Edit</a>
                                        <form action="delete_event.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                            <?php echo csrf_input(); ?>
                                            <button type="submit" class="btn-delete">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state" style="grid-column: 1 / -1;">
                             <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="margin-bottom:1rem; color: var(--border-color);"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                             <h3>No upcoming events</h3>
                             <p>You don't have any scheduled events. Start by creating one.</p>
                             <a href="add_event.php" class="btn btn-primary">Create Event</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Past Events -->
            <div id="past" class="tab-content">
                <div class="event-grid">
                     <?php if ($past_result->num_rows > 0): ?>
                        <?php while($event = $past_result->fetch_assoc()): ?>
                            <div class="event-card" style="opacity: 0.85;">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p class="desc"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                                <div class="event-card-footer">
                                    <div class="event-date">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <?php echo date('M j, Y, g:i A', strtotime($event['event_date'])); ?>
                                    </div>
                                     <div class="event-actions">
                                        <form action="delete_event.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                            <?php echo csrf_input(); ?>
                                            <button type="submit" class="btn-delete">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state" style="grid-column: 1 / -1;">
                             <p>You have no past events.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Global Events -->
            <div id="global" class="tab-content">
                 <div class="event-grid">
                    <?php if ($global_result->num_rows > 0): ?>
                        <?php while($event = $global_result->fetch_assoc()): ?>
                            <div class="event-card" style="border-left: 4px solid var(--primary-color);">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p class="desc"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                                <div class="event-card-footer">
                                    <div class="event-date">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <?php echo date('M j, Y, g:i A', strtotime($event['event_date'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state" style="grid-column: 1 / -1;">
                            <p>There are no upcoming global events.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>
