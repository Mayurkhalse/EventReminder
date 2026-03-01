<?php
// Purpose: Handles secure user login and session creation.

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        die('Please fill in all fields.');
    }

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Regenerate session ID to prevent session fixation attacks
            session_regenerate_id(true);

            // Password is correct, start a new session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: /EventREmainder2/admin/dashboard.php');
            } else {
                header('Location: /EventREmainder2/user/dashboard.php');
            }
            exit();
        } else {
            // Invalid password
            header('Location: /EventREmainder2/index.php?error=invalid_credentials');
            exit();
        }
    } else {
        // No user found
        header('Location: /EventREmainder2/index.php?error=invalid_credentials');
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    // Not a POST request
    header('Location: /EventREmainder2/index.php');
    exit();
}
?>
