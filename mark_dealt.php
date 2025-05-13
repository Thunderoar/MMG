<?php
// mark_dealt.php

// Set the response content type to JSON
header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid request method.'
    ]);
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'include/db_conn.php';

// Verify admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Validate that the noIC parameter is provided
if (!isset($_POST['noIC']) || empty($_POST['noIC'])) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Missing or empty noIC parameter.'
    ]);
    exit;
}

// Clean and validate the NOIC
$noic = cleanNoic($_POST['noIC']);
if (!isValidNoic($noic)) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid NOIC format'
    ]);
    exit;
}

// Database connection parameters
$host     = "localhost";
$username = "root";
$password = "";
$database = "mmg_db";

// Create a new MySQLi connection
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Mark the record as dealt
    $stmt = $conn->prepare("UPDATE attendedstudent SET is_dealt = 1 WHERE NoIC = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $noic);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("No record found with the provided IC number.");
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'status'  => 'success',
        'message' => 'Record marked as dealt successfully.'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    // Close statement and connection
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>
