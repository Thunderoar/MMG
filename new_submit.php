<?php
require 'include/db_conn.php';

// Retrieve form data
$studentID = $_POST['m_id'];
$phn = $_POST['mobile'];
$fullName = $_POST['fullName'];
$no_ic = $_POST['no_ic'];

// Set timezone and get current date (without time)
date_default_timezone_set("Asia/Kuala_Lumpur");
$joining_date = date("Y-m-d");

// Insert into users table with formatted joining_date
$query = "INSERT INTO attendedstudent(fullName, NoTel, DateofArrival, NoIC) 
          VALUES ('$fullName', '$phn', CURRENT_TIMESTAMP, '$no_ic')";
if (mysqli_query($conn, $query)) {
	                        // Success message
                        echo "<head><script>alert('Student Added Successfully');</script></head></html>";
                        echo "<meta http-equiv='refresh' content='0; url=index.php'>";
} else {
    // User insert failed
    echo "<head><script>alert('Member Addition Failed');</script></head></html>";
    echo "Error: " . mysqli_error($conn);
}

/**
 * Handles errors by rolling back the user insert and displaying a message.
 */
function handleErrorAndRollback($failedAt, $memID)
{
    global $conn;
    echo "<head><script>alert('Member Addition Failed at $failedAt');</script></head></html>";
    echo "Error: " . mysqli_error($conn);

    // Clean up all related records
    $tables = ['login', 'address', 'health_status', 'enrolls_to', 'images', 'users'];
    foreach ($tables as $table) {
        $query = "DELETE FROM $table WHERE userid='$memID'";
        mysqli_query($conn, $query);
    }
}
?>