<?php
// Purpose: Handles secure database connection.

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'event_reminder');

// Create a new MySQLi object
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
