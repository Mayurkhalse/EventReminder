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
<body>

    <div class="auth-container">
        <form action="auth/login_action.php" method="POST" class="auth-form">
            <h2>Login to Your Account</h2>

            <?php if(isset($_GET['error'])): ?>
                <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
            <?php endif; ?>
            <?php if(isset($_GET['success']) && $_GET['success'] == 'registered'): ?>
                <p class="success">Registration successful! Please log in.</p>
            <?php endif; ?>
             <?php if(isset($_GET['message']) && $_GET['message'] == 'logged_out'): ?>
                <p class="success">You have been logged out successfully.</p>
            <?php endif; ?>

            <?php echo csrf_input(); ?>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary">Login</button>

            <p>
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </form>
    </div>

</body>
</html>
