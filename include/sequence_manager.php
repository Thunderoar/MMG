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
 * Get the display order of undealt records
 * @return array Array of NoIC values in display order
 */
function getDisplayOrder() {
    global $conn;
    
    $sql = "SELECT NoIC FROM attendedstudent 
            WHERE is_dealt = 0 
            ORDER BY 
                CASE FromWhere 
                    WHEN 'WhatsApp' THEN 0 
                    ELSE 1 
                END,
                CASE WithWho
                    WHEN 'Ibu / Bapa' THEN 0
                    WHEN 'Rakan / Saudara' THEN 1
                    ELSE 2
                END,
                CASE 
                    WHEN WithWho = 'Sendiri' AND canMakeDecision = 1 THEN 0
                    WHEN WithWho = 'Sendiri' AND canMakeDecision = 0 THEN 1
                    ELSE 0
                END,
                DateofArrival ASC";
    
    $result = mysqli_query($conn, $sql);
    $order = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $order[] = $row['NoIC'];
    }
    
    return $order;
}
?>