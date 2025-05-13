<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure no output before this point
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', 'C:\\wamp64\\logs\\php_ic_check_errors.log');

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Log incoming request details
error_log('Incoming request: ' . print_r($_POST, true));
error_log('Raw input: ' . file_get_contents('php://input'));

require_once 'include/noic_helper.php';

try {
    // Database configuration
    $host = "localhost";
    $username = "root";
    $password = "";
    $db_name = "mmg_db";

    // Create connection
    $conn = new mysqli($host, $username, $password, $db_name);

    // Check connection
    if ($conn->connect_error) {
        error_log('Database connection error: ' . $conn->connect_error);
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Check if no_ic is set in POST request
    if (!isset($_POST['no_ic'])) {
        error_log('No IC number provided in request');
        throw new Exception("No IC number provided");
    }

    // Clean and validate the IC number
    $no_ic = cleanNoic($_POST['no_ic']);
    if (!isValidNoic($no_ic)) {
        error_log('Invalid IC number format: ' . $no_ic);
        throw new Exception("Invalid IC number format");
    }

    // Prepare statement with cleaned data
    $stmt = $conn->prepare("SELECT * FROM attendedstudent WHERE NoIC = ?");
    if (!$stmt) {
        error_log('Prepare statement error: ' . $conn->error);
        throw new Exception("Database error");
    }
    
    $stmt->bind_param("s", $no_ic);
    
    if (!$stmt->execute()) {
        error_log('Execute statement failed: ' . $stmt->error);
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    error_log('Query result: ' . print_r($row, true));

    // Send response
    echo json_encode([
        'exists' => ($row !== null),
        'status' => 'success',
        'data' => $row ? [
            'fullName' => $row['fullName'],
            'noic_display' => formatNoic($row['NoIC']),
            'giliran' => $row['Giliran'],
            'is_dealt' => (bool)$row['is_dealt']
        ] : null
    ]);

} catch (Exception $e) {
    // Send error response
    error_log('Exception caught: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage(),
        'status' => 'error'
    ]);
}

// Close connections
if (isset($stmt)) $stmt->close();
if (isset($conn)) $conn->close();