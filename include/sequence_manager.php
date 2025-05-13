<?php
require_once __DIR__ . '/db_conn.php';
if (!function_exists('cleanNoic')) {
    require_once __DIR__ . '/noic_helper.php';
}
if (!function_exists('getPriorityInfo')) {
    require_once __DIR__ . '/priority_helper.php';
}

/**
 * Updates the Giliran sequence for all records
 * @return array Result of the update operation
 */
function updateGiliranSequence() {
    global $conn;
    
    try {
        $result = updateSequence($conn);
        return $result;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to update sequence: ' . $e->getMessage(),
            'count' => 0
        ];
    }
}

function updateSequence($conn) {
    // Get all records ordered by priority and arrival date
    $query = "SELECT NoIC, Giliran, DateofArrival, WithWho, canMakeDecision, priority 
              FROM attendedstudent 
              WHERE is_dealt = 0 
              ORDER BY priority ASC, DateofArrival ASC";
    
    $result = $conn->query($query);
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $counter = 1;
    while ($row = $result->fetch_assoc()) {
        $cleanNoic = cleanNoic($row['NoIC']);
        $updateStmt = $conn->prepare("UPDATE attendedstudent SET Giliran = ? WHERE NoIC = ?");
        $updateStmt->bind_param("is", $counter, $cleanNoic);
        $updateStmt->execute();
        $counter++;
    }

    return [
        'success' => true,
        'message' => 'Queue updated based on priority and arrival time',
        'count' => $counter - 1
    ];
}

/**
 * Get the next Giliran number
 * @return int Next Giliran number
 */
function getNextGiliranNumber() {
    global $conn;
    
    $query = "SELECT COALESCE(MAX(Giliran), 0) + 1 as next_number FROM attendedstudent";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    return $row['next_number'];
}

/**
 * Get the display order of undealt records based on priority system
 * @return array Array of NoIC values in display order
 */
function getDisplayOrder() {
    global $conn;
    
    // Get current date in Malaysia timezone
    date_default_timezone_set("Asia/Kuala_Lumpur");
    $today = date('Y-m-d');
    
    // Get the next upcoming seminar date
    $seminarQuery = "SELECT id, zone, seminar_date, seminar_time, location 
                     FROM seminar_schedules 
                     WHERE seminar_date >= ? AND is_active = 1 
                     ORDER BY seminar_date ASC, seminar_time ASC LIMIT 1";
    $stmt = mysqli_prepare($conn, $seminarQuery);
    mysqli_stmt_bind_param($stmt, "s", $today);
    mysqli_stmt_execute($stmt);
    $seminarResult = mysqli_stmt_get_result($stmt);
    $seminarInfo = mysqli_fetch_assoc($seminarResult);
    
    if (!$seminarInfo) {
        return []; // No upcoming seminars found
    }
    
    // Get all undealt records for this seminar
    $sql = "SELECT NoIC, WithWho, canMakeDecision, FromWhere, DateofArrival, priority 
            FROM attendedstudent 
            WHERE is_dealt = 0 AND is_deleted = 0
            AND (seminar_id = ? OR seminar_id IS NULL)  -- Include records with no seminar_id for backward compatibility
            ORDER BY priority ASC, DateofArrival ASC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $seminarInfo['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    // Process the results maintaining priority order
    $entries = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $entries[] = $row['NoIC'];
    }
    
    return $entries;
}
?>