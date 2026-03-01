<?php
// Purpose: A temporary script to test the database connection and verify admin user data.

require_once __DIR__ . '/config/db.php';

echo "<h1>Database Connection Test</h1>";

if ($conn->connect_error) {
    echo "<p style='color: red;'>Connection Failed: " . htmlspecialchars($conn->connect_error) . "</p>";
    die();
} else {
    echo "<p style='color: green;'>Connection Successful!</p>";
}

echo "<h2>Admin User Check</h2>";

$email = 'admin@example.com';
$stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    echo "<p style='color: green;'>Admin user found!</p>";
    echo "<pre>";
    print_r($user);
    echo "</pre>";

    echo "<h2>Password Verification Check</h2>";
    $password_to_check = 'adminpassword';
    if (password_verify($password_to_check, $user['password'])) {
        echo "<p style='color: green;'>Password verification successful. The password 'adminpassword' matches the hash in the database.</p>";
    } else {
        echo "<p style='color: red;'>Password verification FAILED. The provided password does not match the hash in the database.</p>";
        
        echo "<h3>New Hash</h3>";
        echo "<p>If you need to update the hash in `database/schema.sql`, here is a new one for 'adminpassword':</p>";
        echo "<code>" . password_hash($password_to_check, PASSWORD_DEFAULT) . "</code>";
    }

} else {
    echo "<p style='color: red;'>Admin user with email 'admin@example.com' was not found in the database.</p>";
    echo "<p>Please ensure you have created the 'event_reminder' database and imported the 'database/schema.sql' file.</p>";
}

$stmt->close();
$conn->close();
