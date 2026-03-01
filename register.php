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
<body>

    <div class="auth-container">
        <form action="auth/register_action.php" method="POST" class="auth-form" id="register-form">
            <h2>Create a New Account</h2>

             <?php if(isset($_GET['error'])): ?>
                <p class="error"><?php echo htmlspecialchars($_GET['error']); ?></p>
            <?php endif; ?>

            <?php echo csrf_input(); ?>

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn btn-primary">Register</button>

            <p>
                Already have an account? <a href="index.php">Login here</a>
            </p>
        </form>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
