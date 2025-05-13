<?php
// Simple script to add the tempat_temuduga column to the attendedstudent table
require_once 'include/db_conn.php';

// Check if the column already exists
$checkQuery = "SHOW COLUMNS FROM `attendedstudent` LIKE 'tempat_temuduga'";
$checkResult = mysqli_query($conn, $checkQuery);

if (mysqli_num_rows($checkResult) > 0) {
    echo "Column 'tempat_temuduga' already exists.";
} else {
    // Add the column
    $alterQuery = "ALTER TABLE `attendedstudent` 
                  ADD COLUMN `tempat_temuduga` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL 
                  AFTER `invited_officer`";
    
    if (mysqli_query($conn, $alterQuery)) {
        echo "Successfully added 'tempat_temuduga' column to the attendedstudent table.";
    } else {
        echo "Error adding column: " . mysqli_error($conn);
    }
}
?>
