<?php
// Purpose: Sends the uploaded PDF to the Python AI service (FastAPI + PyMuPDF + Gemini),
//          receives structured events as JSON, and stores them as pending for admin review.

require_once __DIR__ . '/../auth/auth_check.php';
$required_role = 'admin';
require_once __DIR__ . '/../auth/role_check.php';

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/ai_config.php';
require_once __DIR__ . '/pending_events.php';

$pdf_file = $_GET['file'] ?? null;
if (!$pdf_file) {
    die("No file specified for processing.");
}

$pdf_file_path = __DIR__ . '/../uploads/' . basename($pdf_file);

if (!file_exists($pdf_file_path)) {
    die("Uploaded PDF file not found on server.");
}

// ── Send PDF to Python AI service via multipart POST ─────────────────────────
$ch = curl_init(AI_API_URL);

$cfile = new CURLFile($pdf_file_path, 'application/pdf', basename($pdf_file));

curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => ['file' => $cfile],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => AI_API_TIMEOUT,
    CURLOPT_HTTPHEADER     => ['Accept: application/json'],
]);

$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err  = curl_error($ch);
curl_close($ch);

// ── Handle connection errors ──────────────────────────────────────────────────
if ($curl_err) {
    die(
        "<h2>AI Service Unreachable</h2>" .
        "<p>Could not connect to the Python AI service at <strong>" . AI_API_URL . "</strong>.</p>" .
        "<p>Make sure it is running: <code>cd python_api &amp;&amp; uvicorn main:app --port 8000</code></p>" .
        "<p>cURL error: " . htmlspecialchars($curl_err) . "</p>" .
        "<p><a href='../admin/dashboard.php'>Back to Dashboard</a></p>"
    );
}

// ── Handle HTTP-level errors from the AI service ─────────────────────────────
if ($http_code !== 200) {
    $error_data = json_decode($response, true);
    $detail     = $error_data['detail'] ?? $response;
    die(
        "<h2>AI Service Error (HTTP $http_code)</h2>" .
        "<p>" . htmlspecialchars($detail) . "</p>" .
        "<p><a href='../admin/dashboard.php'>Back to Dashboard</a></p>"
    );
}

// ── Parse response ────────────────────────────────────────────────────────────
$data = json_decode($response, true);

if (!isset($data['events']) || !is_array($data['events'])) {
    die("AI service returned an unexpected response. Please try again.");
}

// ── Store valid events as pending ─────────────────────────────────────────────
$stored_count = 0;

foreach ($data['events'] as $event) {
    if (empty($event['title']) || empty($event['event_date'])) {
        continue; // skip malformed entries
    }
    storePendingEvent(
        $conn,
        trim($event['title']),
        trim($event['description'] ?? ''),
        $event['event_date'],
        basename($pdf_file)
    );
    $stored_count++;
}

// ── Redirect admin to review page ─────────────────────────────────────────────
header('Location: ../admin/review_ai_events.php?success=processing_complete&count=' . $stored_count);
exit();

