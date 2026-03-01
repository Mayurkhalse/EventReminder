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
    <title>User Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <div class="container">
        <div class="dashboard-header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
            <a href="../auth/logout.php" class="btn logout-btn">Logout</a>
        </div>

        <div class="main-content">
            <a href="add_event.php" class="btn btn-primary" style="margin-bottom: 2rem;">Add New Event</a>
            
            <div class="tab-navigation">
                <button class="tab-link active" data-tab="upcoming">My Upcoming Events</button>
                <button class="tab-link" data-tab="past">My Past Events</button>
                <button class="tab-link" data-tab="global">Global Events</button>
            </div>

            <!-- Upcoming Events -->
            <div id="upcoming" class="tab-content active">
                <h2>My Upcoming Events</h2>
                <div class="event-grid">
                    <?php if ($upcoming_result->num_rows > 0): ?>
                        <?php while($event = $upcoming_result->fetch_assoc()): ?>
                            <div class="event-card">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                                <div class="event-card-footer">
                                    <span><?php echo date('D, M j, Y, g:i A', strtotime($event['event_date'])); ?></span>
                                    <div class="event-actions">
                                        <a href="edit_event.php?id=<?php echo $event['id']; ?>">Edit</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>You have no upcoming events.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Past Events -->
            <div id="past" class="tab-content">
                <h2>My Past Events</h2>
                <div class="event-grid">
                     <?php if ($past_result->num_rows > 0): ?>
                        <?php while($event = $past_result->fetch_assoc()): ?>
                            <div class="event-card">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                                <div class="event-card-footer">
                                    <span><?php echo date('D, M j, Y, g:i A', strtotime($event['event_date'])); ?></span>
                                     <div class="event-actions">
                                        <form action="delete_event.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                            <?php echo csrf_input(); ?>
                                            <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--error-color);font-weight:600;padding:0;">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>You have no past events.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Global Events -->
            <div id="global" class="tab-content">
                <h2>Upcoming Global Events</h2>
                 <div class="event-grid">
                    <?php if ($global_result->num_rows > 0): ?>
                        <?php while($event = $global_result->fetch_assoc()): ?>
                            <div class="event-card">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                                <div class="event-card-footer">
                                    <span><?php echo date('D, M j, Y, g:i A', strtotime($event['event_date'])); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>There are no upcoming global events.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
