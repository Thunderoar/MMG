<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'include/db_conn.php';

// Verify admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_POST['noIC'])) {
    $noIC = mysqli_real_escape_string($conn, $_POST['noIC']);
    $query = "UPDATE attendedstudent SET is_deleted = 1 WHERE NoIC = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $noIC);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Record soft deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to soft delete record']);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No IC provided']);
}
?>
