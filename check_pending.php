<?php
require_once __DIR__ . '/config/db.php';

$now = date('Y-m-d H:i:s');
echo "Current Time: $now\n";

// Check pending Reminder 1 (personal)
$stmt = $conn->prepare("
    SELECT e.id, e.title, e.event_date, e.reminder1_date, u.email
    FROM events e
    JOIN users u ON e.user_id = u.id
    WHERE e.reminder1_sent = 0
      AND e.reminder1_date <= ?
      AND e.event_date > ?
      AND e.user_id IS NOT NULL
");
$stmt->bind_param('ss', $now, $now);
$stmt->execute();
$res1 = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo "Pending Reminder 1 (Personal): " . count($res1) . "\n";
foreach($res1 as $r) {
    echo "- ID: {$r['id']}, Title: {$r['title']}, Date: {$r['event_date']}, Reminder: {$r['reminder1_date']}, User: {$r['email']}\n";
}

// Check pending Reminder 2 (personal)
$stmt = $conn->prepare("
    SELECT e.id, e.title, e.event_date, e.reminder2_date, u.email
    FROM events e
    JOIN users u ON e.user_id = u.id
    WHERE e.reminder2_sent = 0
      AND e.reminder2_date <= ?
      AND e.event_date > ?
      AND e.user_id IS NOT NULL
");
$stmt->bind_param('ss', $now, $now);
$stmt->execute();
$res2 = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo "Pending Reminder 2 (Personal): " . count($res2) . "\n";
foreach($res2 as $r) {
    echo "- ID: {$r['id']}, Title: {$r['title']}, Date: {$r['event_date']}, Reminder: {$r['reminder2_date']}, User: {$r['email']}\n";
}

// Check pending global
$stmt = $conn->prepare("
    SELECT id, title, event_date, reminder1_date
    FROM events
    WHERE is_global = 1
      AND reminder1_sent = 0
      AND reminder1_date <= ?
      AND event_date > ?
");
$stmt->bind_param('ss', $now, $now);
$stmt->execute();
$res3 = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo "Pending Global Reminder 1: " . count($res3) . "\n";
foreach($res3 as $r) {
    echo "- ID: {$r['id']}, Title: {$r['title']}, Date: {$r['event_date']}, Reminder: {$r['reminder1_date']}\n";
}

$conn->close();
