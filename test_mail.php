<?php
// Purpose: Test script to verify PHPMailer configuration.

require_once __DIR__ . '/config/mailer.php';

$recipient = '1234mayurk@gmail.com';
$subject = 'Test Email from Event Reminder App';
$body = '<h1>Hello!</h1><p>This is a test email to verify the SMTP configuration.</p>';

echo "Attempting to send a test email to: $recipient...\n";

$mail = getMailer();

if (!$mail) {
    echo "❌ Failed to initialize Mailer. Check config/mailer.php\n";
    exit;
}

try {
    $mail->addAddress($recipient);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $body;
    $mail->AltBody = strip_tags($body);

    $mail->send();
    echo "✅ Email sent successfully!\n";
} catch (Exception $e) {
    echo "❌ Email could not be sent. Mailer Error: {$mail->ErrorInfo}\n";
}
