<?php
require 'include/db_conn.php';
header('Content-Type: application/json');

// Configure error reporting
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$response = ['exists' => false];

try {
    if (!isset($_POST['ic']) || strlen($_POST['ic']) !== 12 || !ctype_digit($_POST['ic'])) {
        throw new InvalidArgumentException('Invalid IC number format');
    }

    // Your database connection here
    $conn = mysqli_connect($host, $username, $password, $db_name);
    
    if (mysqli_connect_errno()) {
        throw new RuntimeException('Database connection failed');
    }

    // Sanitize the input to prevent SQL injection
    $ic = mysqli_real_escape_string($conn, $_POST['ic']);

    $query = "SELECT * FROM attendedstudent WHERE noIC = '$ic'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new RuntimeException('Query error: ' . mysqli_error($conn));
    }

    $response['exists'] = mysqli_num_rows($result) > 0;

    mysqli_free_result($result);
    mysqli_close($conn);

} catch (Exception $e) {
    http_response_code(400);
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
exit;
?>
