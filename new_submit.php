<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'include/sequence_manager.php';
require_once 'include/error_handler.php';
require_once 'include/db_conn.php';
require_once 'include/noic_helper.php';

// Set timezone
date_default_timezone_set("Asia/Kuala_Lumpur");

// Clean and validate input data
$phn = cleanInput($_POST['mobile']);
$fullName = cleanInput($_POST['fullName']);
$no_ic = cleanNoic($_POST['no_ic']);
$fromWhere = cleanInput(isset($_POST['fav_language']) ? $_POST['fav_language'] : '');
$withWho = cleanInput(isset($_POST['hadir']) ? $_POST['hadir'] : '');
$studentPhone = cleanInput($_POST['student_phone']);
$guardianPhone = cleanInput($_POST['guardian_phone']);
$invitedOfficer = cleanInput($_POST['invited_officer']);

// Validate NOIC
if (!isValidNoic($no_ic)) {
    handleErrorAndRollback('NOIC validation', $no_ic);
    exit;
}

// Determine if student can make decisions
$canMakeDecision = isset($_POST['decision']) && $_POST['decision'] === 'Boleh membuat keputusan sendiri';

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Check if NOIC already exists
    $checkStmt = mysqli_prepare($conn, "SELECT NoIC FROM attendedstudent WHERE NoIC = ?");
    mysqli_stmt_bind_param($checkStmt, "s", $no_ic);
    mysqli_stmt_execute($checkStmt);
    mysqli_stmt_store_result($checkStmt);
    
    if (mysqli_stmt_num_rows($checkStmt) > 0) {
        throw new Exception("NOIC already exists in the system");
    }
    mysqli_stmt_close($checkStmt);
    
    // Get the next Giliran number based on current timestamp
    $nextGiliran = getNextGiliranNumber();
    
    // Get the next upcoming seminar
    $today = date('Y-m-d');
    $seminarQuery = "SELECT id FROM seminar_schedules 
                     WHERE seminar_date >= ? AND is_active = 1 
                     ORDER BY seminar_date ASC, seminar_time ASC LIMIT 1";
    $seminarStmt = mysqli_prepare($conn, $seminarQuery);
    mysqli_stmt_bind_param($seminarStmt, "s", $today);
    mysqli_stmt_execute($seminarStmt);
    $seminarResult = mysqli_stmt_get_result($seminarStmt);
    $seminarRow = mysqli_fetch_assoc($seminarResult);
    $seminarId = $seminarRow ? $seminarRow['id'] : null;
    
    // Insert new record with all form fields including seminar_id
    $query = "INSERT INTO attendedstudent(fullName, NoTel, DateofArrival, NoIC, FromWhere, WithWho, 
                                        Giliran, canMakeDecision, student_phone, guardian_phone, 
                                        invited_officer, seminar_id) 
              VALUES (?, ?, CURRENT_TIMESTAMP, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssissssi", 
        $fullName, $phn, $no_ic, $fromWhere, $withWho, $nextGiliran, 
        $canMakeDecision, $studentPhone, $guardianPhone, $invitedOfficer, $seminarId
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to insert new record: " . mysqli_error($conn));
    }
    mysqli_stmt_close($stmt);
    
    // Update all Giliran numbers to ensure proper sequence
    if (!updateGiliranSequence()) {
        throw new Exception("Failed to update sequence");
    }
    
    mysqli_commit($conn);
    echo "<head>
            <script>
                alert('Student Added Successfully');
                window.location.href = 'index.php';
            </script>
          </head>";
    
} catch (Exception $e) {
    mysqli_rollback($conn);
    handleErrorAndRollback('database operation', $no_ic);
    error_log($e->getMessage());
}

// Helper function to clean input
function cleanInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}
?>