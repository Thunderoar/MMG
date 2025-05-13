<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'include/db_conn.php';

// Get userId from session if available
$userId = isset($_SESSION['userId']) ? $_SESSION['userId'] : null;

// Check if the user is approved only if userId is set
$isApproved = null;
if ($userId !== null && $conn) {
    $query = "SELECT hasApproved FROM users WHERE userid = '$userId'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $isApproved = $row['hasApproved'];
    }
}
?>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<ul class="menu">
  <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
  <li><a href="admin_settings.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
  <li><a href="layout.php"><i class="fas fa-th-large"></i> Layout</a></li>
</ul>
