<?php
session_start();
require_once __DIR__ . '/auth/csrf.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Event Reminder</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">

    <div class="auth-container" style="max-width: 450px;">
        <form action="auth/register_action.php" method="POST" class="auth-form" id="register-form">
            <h2>Create an Account</h2>
            <p class="subtitle">Join us to start managing your events</p>

             <?php if(isset($_GET['error'])): ?>
                <div class="error">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span><?php echo htmlspecialchars($_GET['error']); ?></span>
                </div>
            <?php endif; ?>

            <?php echo csrf_input(); ?>

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="John Doe" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Register</button>

            <div class="bottom-link">
                Already have an account? <a href="index.php">Sign in instead</a>
            </div>
        </form>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
