<?php
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

// Validate NOIC
if (!isValidNoic($no_ic)) {
    handleErrorAndRollback('NOIC validation', $no_ic);
    exit;
}

// Handle checkbox values for WithWho
$withWho = [];
if(isset($_POST['ibubapa']) && $_POST['ibubapa'] == 'IbuBapa') $withWho[] = 'Ibu / Bapa';
if(isset($_POST['rakanatausaudara']) && $_POST['rakanatausaudara'] == 'rakanatausaudara') $withWho[] = 'Rakan / Saudara';
if(isset($_POST['sendiri']) && $_POST['sendiri'] == 'Sendiri') $withWho[] = 'Sendiri';

// Combine WithWho array into a string
$withWhoString = !empty($withWho) ? implode(', ', $withWho) : 'Sendiri';

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
    
    // Insert new record
    $query = "INSERT INTO attendedstudent(fullName, NoTel, DateofArrival, NoIC, FromWhere, WithWho, Giliran, canMakeDecision) 
              VALUES (?, ?, CURRENT_TIMESTAMP, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssssii", $fullName, $phn, $no_ic, $fromWhere, $withWhoString, $nextGiliran, $canMakeDecision);
    
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
?>