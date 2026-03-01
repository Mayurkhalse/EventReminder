<?php
// Purpose: CSRF protection helpers included on every page that has a form.

function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Renders a hidden <input> — echo this inside every <form>
function csrf_input(): string {
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(generate_csrf_token()) . '">';
}

// Call at the top of every POST handler. Kills the request on failure.
function verify_csrf_token(): void {
    $token = $_POST['csrf_token'] ?? '';
    $stored = $_SESSION['csrf_token'] ?? '';
    if (!$stored || !hash_equals($stored, $token)) {
        http_response_code(403);
        die('Security check failed (invalid CSRF token). Please go back and try again.');
    }
}
