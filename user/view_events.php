<?php
// Purpose: Event listing.
// For this application, the primary event view is integrated into the dashboard.
// This file redirects to the dashboard to maintain a single source of truth for event display.

require_once __DIR__ . '/../auth/auth_check.php';

header('Location: dashboard.php');
exit();
