<?php
// Purpose: Queries due reminders and sends emails via PHPMailer.
// Trigger: Admin button on dashboard, or future CRON: php send_reminders.php

$is_cli = (php_sapi_name() === 'cli');

if (!$is_cli) {
    require_once __DIR__ . '/auth/auth_check.php';
    $required_role = 'admin';
    require_once __DIR__ . '/auth/role_check.php';
    require_once __DIR__ . '/auth/csrf.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: admin/dashboard.php');
        exit();
    }
    verify_csrf_token();
}

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/mailer.php';

$now   = date('Y-m-d H:i:s');
$sent  = 0;
$failed = 0;

// ── Helper: send and flag ────────────────────────────────────────────────────
function processReminder(mysqli $conn, array $event, array $user, int $num,
                          string $flagCol, int &$sent, int &$failed): void {
    if (sendReminderEmail($event, $user, $num)) {
        $upd = $conn->prepare("UPDATE events SET {$flagCol} = 1 WHERE id = ?");
        $upd->bind_param('i', $event['id']);
        $upd->execute();
        $sent++;
    } else {
        $failed++;
    }
}

// ── Reminder 1 (1 day before) – personal events ──────────────────────────────
$stmt = $conn->prepare("
    SELECT e.*, u.name AS user_name, u.email AS user_email
    FROM events e
    JOIN users u ON e.user_id = u.id
    WHERE e.reminder1_sent = 0
      AND e.reminder1_date <= ?
      AND e.event_date    >  ?
      AND e.user_id IS NOT NULL
");
$stmt->bind_param('ss', $now, $now);
$stmt->execute();
foreach ($stmt->get_result() as $event) {
    processReminder($conn, $event,
        ['name' => $event['user_name'], 'email' => $event['user_email']],
        1, 'reminder1_sent', $sent, $failed);
}

// ── Reminder 2 (1 hour before) – personal events ─────────────────────────────
$stmt = $conn->prepare("
    SELECT e.*, u.name AS user_name, u.email AS user_email
    FROM events e
    JOIN users u ON e.user_id = u.id
    WHERE e.reminder2_sent = 0
      AND e.reminder2_date <= ?
      AND e.event_date    >  ?
      AND e.user_id IS NOT NULL
");
$stmt->bind_param('ss', $now, $now);
$stmt->execute();
foreach ($stmt->get_result() as $event) {
    processReminder($conn, $event,
        ['name' => $event['user_name'], 'email' => $event['user_email']],
        2, 'reminder2_sent', $sent, $failed);
}

// ── Reminder 1 – global events (send to every user) ──────────────────────────
$stmt = $conn->prepare("
    SELECT * FROM events
    WHERE is_global = 1
      AND reminder1_sent = 0
      AND reminder1_date <= ?
      AND event_date    >  ?
");
$stmt->bind_param('ss', $now, $now);
$stmt->execute();
$global_r1 = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (!empty($global_r1)) {
    $users_result = $conn->query("SELECT name, email FROM users WHERE role = 'user'");
    $all_users = $users_result->fetch_all(MYSQLI_ASSOC);

    foreach ($global_r1 as $event) {
        $any_sent = false;
        foreach ($all_users as $user) {
            if (sendReminderEmail($event, $user, 1)) { $any_sent = true; $sent++; }
            else $failed++;
        }
        if ($any_sent) {
            $upd = $conn->prepare("UPDATE events SET reminder1_sent = 1 WHERE id = ?");
            $upd->bind_param('i', $event['id']);
            $upd->execute();
        }
    }
}

// ── Reminder 2 – global events ───────────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT * FROM events
    WHERE is_global = 1
      AND reminder2_sent = 0
      AND reminder2_date <= ?
      AND event_date    >  ?
");
$stmt->bind_param('ss', $now, $now);
$stmt->execute();
$global_r2 = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (!empty($global_r2)) {
    if (!isset($all_users)) {
        $all_users = $conn->query("SELECT name, email FROM users WHERE role = 'user'")->fetch_all(MYSQLI_ASSOC);
    }
    foreach ($global_r2 as $event) {
        $any_sent = false;
        foreach ($all_users as $user) {
            if (sendReminderEmail($event, $user, 2)) { $any_sent = true; $sent++; }
            else $failed++;
        }
        if ($any_sent) {
            $upd = $conn->prepare("UPDATE events SET reminder2_sent = 1 WHERE id = ?");
            $upd->bind_param('i', $event['id']);
            $upd->execute();
        }
    }
}

// ── Output ────────────────────────────────────────────────────────────────────
if ($is_cli) {
    echo "Done. Sent: {$sent}, Failed: {$failed}\n";
} else {
    header("Location: admin/dashboard.php?reminders_sent={$sent}&reminders_failed={$failed}");
    exit();
}
