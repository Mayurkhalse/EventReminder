<?php
// Purpose: PDF upload.

require_once __DIR__ . '/../auth/auth_check.php';
$required_role = 'admin';
require_once __DIR__ . '/../auth/role_check.php';
require_once __DIR__ . '/../auth/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['calendar_pdf'])) {
    verify_csrf_token();
    $target_dir = __DIR__ . "/../uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_file = $target_dir . basename($_FILES["calendar_pdf"]["name"]);
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file is a actual PDF
    if($file_type != "pdf") {
        die("Only PDF files are allowed.");
    }

    // Move the uploaded file
    if (move_uploaded_file($_FILES["calendar_pdf"]["tmp_name"], $target_file)) {
        // File is uploaded, now trigger the AI pipeline
        // We pass the filename to the AI event extractor
        header('Location: ../ai/ai_event_extractor.php?file=' . urlencode(basename($target_file)));
        exit();
    } else {
        die("Sorry, there was an error uploading your file.");
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Academic Calendar</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <div class="container" style="max-width: 600px;">
        <div class="dashboard-header">
            <h1>Upload Academic Calendar (PDF)</h1>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <form action="upload_calendar.php" method="POST" enctype="multipart/form-data">
            <?php echo csrf_input(); ?>
            <div class="form-group">
                <label for="calendar_pdf">Select PDF File</label>
                <input type="file" id="calendar_pdf" name="calendar_pdf" accept=".pdf" required>
            </div>
            <button type="submit" class="btn btn-primary" id="upload-btn">Upload and Process</button>
            <p id="processing-msg" style="display:none; margin-top:1rem; color: var(--subtle-text-color);">
                ⏳ Extracting text and calling Gemini AI… this may take up to 30 seconds.
            </p>
        </form>
         <div class="instructions" style="margin-top: 2rem;">
            <h3>How it Works</h3>
            <p>
                1. Upload an academic calendar in PDF format.
            </p>
            <p>
                2. Our AI will attempt to parse the PDF, extract key dates and event titles.
            </p>
            <p>
                3. Extracted events will be saved as "Pending" for your review.
            </p>
             <p>
                4. You must approve them in the "Review AI Events" section before they become active global events.
            </p>
        </div>
    </div>

    <script>
    document.querySelector('form').addEventListener('submit', function() {
        document.getElementById('upload-btn').disabled = true;
        document.getElementById('upload-btn').textContent = 'Processing…';
        document.getElementById('processing-msg').style.display = 'block';
    });
    </script>
</body>
</html>
