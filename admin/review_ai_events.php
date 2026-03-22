<?php
// Purpose: AI event moderation.

require_once __DIR__ . '/../auth/auth_check.php';
$required_role = 'admin';
require_once __DIR__ . '/../auth/role_check.php';
require_once __DIR__ . '/../auth/csrf.php';
require_once __DIR__ . '/../config/db.php';

// Handle approval or rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verify_csrf_token();
    $pending_id = $_POST['pending_id'];
    
    if ($_POST['action'] === 'approve') {
        // Fetch the pending event
        $stmt = $conn->prepare("SELECT * FROM ai_pending_events WHERE id = ? AND status = 'pending'");
        $stmt->bind_param("i", $pending_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $pending_event = $result->fetch_assoc();

            // Insert into the main events table as a global event
            $event_timestamp = strtotime($pending_event['event_date']);
            $reminder1_date = date('Y-m-d H:i:s', $event_timestamp - 86400 * 2);
            $reminder2_date = date('Y-m-d H:i:s', $event_timestamp - 86400);
            $is_global = true;

            $insert_stmt = $conn->prepare("INSERT INTO events (title, description, event_date, reminder1_date, reminder2_date, is_global) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssssi", $pending_event['title'], $pending_event['description'], $pending_event['event_date'], $reminder1_date, $reminder2_date, $is_global);
            $insert_stmt->execute();

            // Update the pending event status
            $update_stmt = $conn->prepare("UPDATE ai_pending_events SET status = 'approved' WHERE id = ?");
            $update_stmt->bind_param("i", $pending_id);
            $update_stmt->execute();
        }

    } elseif ($_POST['action'] === 'reject') {
        // Update the pending event status
        $update_stmt = $conn->prepare("UPDATE ai_pending_events SET status = 'rejected' WHERE id = ?");
        $update_stmt->bind_param("i", $pending_id);
        $update_stmt->execute();
    }
    header('Location: review_ai_events.php');
    exit();
}


// Fetch pending events
$pending_events_result = $conn->query("SELECT * FROM ai_pending_events WHERE status = 'pending' ORDER BY extracted_at DESC");
$total_pending = $pending_events_result->num_rows;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review AI Events - Admin Panel</title>
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
            <a href="create_event.php" class="nav-item">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="margin-right:10px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Global Event
            </a>
            <a href="upload_calendar.php" class="nav-item">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="margin-right:10px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Upload PDF
            </a>
            <a href="review_ai_events.php" class="nav-item active">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="margin-right:10px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Review AI Events
                <?php if($total_pending > 0): ?>
                    <span style="background:var(--danger-color);color:#fff;padding:2px 8px;border-radius:12px;font-size:12px;margin-left:auto;"><?php echo $total_pending; ?></span>
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
            <div class="page-title">Moderation Queue</div>
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
                <h1 style="font-size: 1.5rem; font-weight: 700;">Review Pending Events (<?php echo $total_pending; ?>)</h1>
                <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
            </div>

            <?php if (isset($_GET['success']) && $_GET['success'] === 'processing_complete'): ?>
                <?php $count = (int)($_GET['count'] ?? 0); ?>
                <div class="success">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>AI extraction complete — <strong><?= $count ?> event<?= $count !== 1 ? 's' : '' ?></strong> found and queued below.</span>
                </div>
            <?php endif; ?>

            <?php if ($total_pending > 0): ?>
                <div class="event-grid">
                    <?php while($event = $pending_events_result->fetch_assoc()): ?>
                        <div class="event-card" style="border-top: 4px solid var(--secondary-color);">
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p class="desc"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                            
                            <div class="event-card-footer" style="flex-direction: column; align-items: flex-start; gap: 1rem;">
                                <div class="event-date">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <?php echo date('M j, Y', strtotime($event['event_date'])); ?>
                                </div>
                                
                                <div style="display: flex; gap: 0.5rem; width: 100%;">
                                    <form action="review_ai_events.php" method="POST" style="flex: 1;">
                                        <?php echo csrf_input(); ?>
                                        <input type="hidden" name="pending_id" value="<?php echo $event['id']; ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-success" style="width: 100%;">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Approve
                                        </button>
                                    </form>
                                    <form action="review_ai_events.php" method="POST" style="flex: 1;">
                                        <?php echo csrf_input(); ?>
                                        <input type="hidden" name="pending_id" value="<?php echo $event['id']; ?>">
                                        <button type="submit" name="action" value="reject" class="btn btn-danger" style="width: 100%; background: #FEF2F2; color: var(--danger-color); border: 1px solid #FCA5A5;">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="margin-bottom:1rem; color: var(--success-color);"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h3>Inbox zero!</h3>
                    <p>There are no pending AI-generated events to review.</p>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>
