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
    <title>Upload Academic Calendar - Admin Panel</title>
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
            <a href="upload_calendar.php" class="nav-item active">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="margin-right:10px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Upload PDF
            </a>
            <a href="review_ai_events.php" class="nav-item">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="margin-right:10px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Review AI Events
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
            <div class="page-title">AI Automation Pipeline</div>
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
                <h1 style="font-size: 1.5rem; font-weight: 700;">Upload Academic Calendar (PDF)</h1>
                <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
            </div>

            <div class="stats-grid">
                <!-- Upload form -->
                <div class="card" style="box-shadow: var(--shadow); border-color: var(--primary-color);">
                    <form action="upload_calendar.php" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_input(); ?>
                        <div class="form-group">
                            <label for="calendar_pdf">Select PDF File</label>
                            <input type="file" id="calendar_pdf" name="calendar_pdf" accept=".pdf" required 
                                   style="padding: 1rem; border: 2px dashed var(--border-color); background: #fafafa; cursor: pointer; border-radius: var(--border-radius-lg);">
                        </div>
                        <button type="submit" class="btn btn-primary" id="upload-btn" style="width: 100%; margin-top: 1rem; padding: 0.875rem;">
                            Upload and Process AI Pipeline
                        </button>
                        <p id="processing-msg" style="display:none; margin-top:1.5rem; color: var(--subtle-text-color); text-align: center; font-weight: 500;">
                            ⏳ Extracting text and parsing with ML... please wait up to 30 seconds.
                        </p>
                    </form>
                </div>
                
                <!-- Info block -->
                <div class="card" style="background-color: #F8FAFC; border: none;">
                    <h3 style="margin-bottom: 1rem; color: var(--primary-color);">How the AI Pipeline Works</h3>
                    <ul style="color: var(--subtle-text-color); margin-left: 1.25rem; font-size: 0.95rem;">
                        <li style="margin-bottom: 0.75rem;"><strong>Upload:</strong> Submit an academic calendar in PDF format.</li>
                        <li style="margin-bottom: 0.75rem;"><strong>Extraction:</strong> The NLP engine will parse key dates and event titles automatically.</li>
                        <li style="margin-bottom: 0.75rem;"><strong>Review Queue:</strong> Extracted events are routed to a "Pending" staging table.</li>
                        <li><strong>Approval:</strong> You must approve them in the "Review AI Events" section before they become active.</li>
                    </ul>
                </div>
            </div>

        </main>
    </div>
</div>

<script>
document.querySelector('form').addEventListener('submit', function() {
    var btn = document.getElementById('upload-btn');
    btn.disabled = true;
    btn.textContent = 'Processing Document...';
    btn.style.opacity = '0.7';
    document.getElementById('processing-msg').style.display = 'block';
});
</script>
<script src="../assets/js/script.js"></script>
</body>
</html>
