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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review AI-Generated Events</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h1>Review AI-Generated Events</h1>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <div class="main-content">
            <?php if (isset($_GET['success']) && $_GET['success'] === 'processing_complete'): ?>
                <?php $count = (int)($_GET['count'] ?? 0); ?>
                <div style="background:#d4edda; border:1px solid #c3e6cb; color:#155724; padding:1rem 1.5rem; border-radius:8px; margin-bottom:1.5rem;">
                    ✅ AI extraction complete — <strong><?= $count ?> event<?= $count !== 1 ? 's' : '' ?></strong> found and queued for review below.
                </div>
            <?php endif; ?>
            <?php if ($pending_events_result->num_rows > 0): ?>
                <div class="event-grid">
                    <?php while($event = $pending_events_result->fetch_assoc()): ?>
                        <div class="event-card">
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                            <div class="event-card-footer">
                                <span><strong>Date:</strong> <?php echo date('D, M j, Y', strtotime($event['event_date'])); ?></span>
                            </div>
                            <div class="event-actions" style="margin-top: 1rem; display: flex; gap: 1rem;">
                                <form action="review_ai_events.php" method="POST" style="display: inline;">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="pending_id" value="<?php echo $event['id']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn" style="background: var(--success-color); color: white;">Approve</button>
                                </form>
                                <form action="review_ai_events.php" method="POST" style="display: inline;">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="pending_id" value="<?php echo $event['id']; ?>">
                                    <button type="submit" name="action" value="reject" class="btn" style="background: var(--error-color); color: white;">Reject</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>There are no pending AI-generated events to review.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
