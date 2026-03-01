<?php
// Purpose: Store pending AI events.

function storePendingEvent($conn, $title, $description, $event_date, $source_file) {
    if (!$conn) {
        return; // Or handle the error appropriately
    }

    $stmt = $conn->prepare(
        "INSERT INTO ai_pending_events (title, description, event_date, source_file) VALUES (?, ?, ?, ?)"
    );

    if (!$stmt) {
        // Handle prepare statement error
        error_log('Prepare statement failed: ' . $conn->error);
        return;
    }

    $stmt->bind_param("ssss", $title, $description, $event_date, $source_file);

    if (!$stmt->execute()) {
        // Handle execution error
        error_log('Execute failed: ' . $stmt->error);
    }

    $stmt->close();
}
