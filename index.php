<?php
session_start();
require_once __DIR__ . '/auth/csrf.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Event Reminder</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">

    <div class="auth-container">
        <form action="auth/login_action.php" method="POST" class="auth-form">
            <h2>Welcome Back</h2>
            <p class="subtitle">Sign in to manage your events and reminders</p>

            <?php if(isset($_GET['error'])): ?>
                <div class="error">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span><?php echo htmlspecialchars($_GET['error']); ?></span>
                </div>
            <?php endif; ?>
            <?php if(isset($_GET['success']) && $_GET['success'] == 'registered'): ?>
                <div class="success">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Registration successful! Please log in.</span>
                </div>
            <?php endif; ?>
             <?php if(isset($_GET['message']) && $_GET['message'] == 'logged_out'): ?>
                <div class="success">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>You have been logged out successfully.</span>
                </div>
            <?php endif; ?>

            <?php echo csrf_input(); ?>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Sign In</button>

            <div class="bottom-link">
                Don't have an account? <a href="register.php">Create one now</a>
            </div>
        </form>
    </div>

</body>
</html>
