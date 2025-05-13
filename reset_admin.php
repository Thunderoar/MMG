<?php
require 'include/db_conn.php';

// Drop and recreate admin user with plain password
$sql = "TRUNCATE TABLE admin_users";
$conn->query($sql);

// Insert admin user with plain text password
$sql = "INSERT INTO admin_users (username, password, email) VALUES ('admin', 'admin123', 'admin@example.com')";
if ($conn->query($sql)) {
    echo "Admin user has been reset successfully.<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
    echo "<a href='admin_login.php'>Go to Login</a>";
} else {
    echo "Error resetting admin user: " . $conn->error;
}
?>