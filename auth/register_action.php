<?php
// Purpose: Handles user registration processing.

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        die('Please fill all fields.');
    }

    if ($password !== $confirm_password) {
        die('Passwords do not match.');
    }

    if (strlen($password) < 6) {
        die('Password must be at least 6 characters long.');
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        die('Email already in use.');
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashed_password);

    if ($stmt->execute()) {
        // Registration successful
        header('Location: /EventREmainder2/index.php?success=registered');
        exit();
    } else {
        // Registration failed
        die('Registration failed. Please try again.');
    }

    $stmt->close();
    $conn->close();
} else {
    // Not a POST request
    header('Location: /EventREmainder2/register.php');
    exit();
}
?>
