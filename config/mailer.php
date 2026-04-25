<?php
// Purpose: Email configuration and sending helpers for PHPMailer.

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Returns a configured PHPMailer instance, or null on failure.
function getMailer(): ?PHPMailer {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';   // ← replace with your SMTP host
        $mail->SMTPAuth   = true;
        $mail->Username   = 'cinecode34@gmail.com';   // ← replace with your SMTP username
        $mail->Password   = 'vwrnxemuwyuvntum';           // ← replace with your SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->setFrom('noreply@eventreminder.com', 'Event Reminder App');
        return $mail;
    } catch (Exception $e) {
        error_log("PHPMailer config error: {$mail->ErrorInfo}");
        return null;
    }
}

/**
 * Send a reminder email for a single event to a single user.
 *
 * @param array $event         Row from the events table
 * @param array $user          Associative array with 'name' and 'email' keys
 * @param int   $reminderNumber  1 = "1 day before", 2 = "1 hour before"
 * @return bool  true on success, false on failure
 */
function sendReminderEmail(array $event, array $user, int $reminderNumber): bool {
    $mail = getMailer();
    if (!$mail) return false;

    $timeLabel = ($reminderNumber === 1) ? '1 day' : '1 hour';
    $eventDate = date('l, F j, Y \a\t g:i A', strtotime($event['event_date']));

    try {
        $mail->addAddress($user['email'], $user['name']);
        $mail->isHTML(true);
        $mail->Subject = "Reminder: \"{$event['title']}\" is in {$timeLabel}";
        $mail->Body = "
            <div style='font-family:sans-serif;max-width:600px;margin:auto;'>
                <h2 style='color:#4a90e2;'>⏰ Event Reminder</h2>
                <p>Hi <strong>" . htmlspecialchars($user['name']) . "</strong>,</p>
                <p>This is a reminder that your event is coming up in <strong>{$timeLabel}</strong>:</p>
                <div style='background:#f4f7f6;border-left:4px solid #4a90e2;padding:1rem 1.5rem;border-radius:6px;'>
                    <h3 style='margin:0 0 .5rem;'>" . htmlspecialchars($event['title']) . "</h3>
                    <p style='margin:0;color:#555;'>" . nl2br(htmlspecialchars($event['description'] ?? '')) . "</p>
                    <p style='margin:.75rem 0 0;font-weight:bold;'>📅 {$eventDate}</p>
                </div>
                <p style='color:#999;font-size:.85em;margin-top:2rem;'>
                    You are receiving this because you created this event on Event Reminder App.
                </p>
            </div>
        ";
        $mail->AltBody = "Reminder: \"{$event['title']}\" is in {$timeLabel}.\nDate: {$eventDate}";
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email send failed for event {$event['id']}: {$mail->ErrorInfo}");
        return false;
    }
}

