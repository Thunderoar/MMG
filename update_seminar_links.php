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

// Initialize counters
$totalRecords = 0;
$updatedRecords = 0;
$failedRecords = 0;
$errors = [];

// Function to parse seminar location from various formats in DateofArrival
function parseSeminarLocation($input) {
    // Normalize the input string
    $input = trim($input);
    $input = preg_replace('/\s+/', ' ', $input); // Normalize spaces
    
    // Extract zone from brackets if present
    $zone = '';
    if (preg_match('/\[([^\]]+)\]/', $input, $zoneMatches)) {
        $zone = $zoneMatches[1];
    } else if (preg_match('/^([A-Z]+)/', $input, $zoneMatches)) {
        // Try to extract zone from beginning of string if no brackets
        $zone = $zoneMatches[1];
    }
    
    // Extract date
    $date = null;
    if (preg_match('/(\d{4}-\d{2}-\d{2})/', $input, $dateMatches)) {
        $date = $dateMatches[1];
    }
    
    // Extract time
    $time = null;
    if (preg_match('/(\d{2}:\d{2}:\d{2})/', $input, $timeMatches)) {
        $time = $timeMatches[1];
    } else if (preg_match('/(\d{1,2})[\.:](\d{2})\s*(PAGI|PETANG)/i', $input, $timeMatches)) {
        // Convert time format from "9.30 PAGI" to "09:30:00"
        $hours = intval($timeMatches[1]);
        $minutes = intval($timeMatches[2]);
        $meridiem = strtoupper($timeMatches[3]);
        
        // Convert to 24-hour format
        if ($meridiem === 'PETANG' && $hours != 12) {
            $hours += 12;
        } else if ($meridiem === 'PAGI' && $hours == 12) {
            $hours = 0;
        }
        
        $time = sprintf('%02d:%02d:00', $hours, $minutes);
    }
    
    // Extract location
    $location = '';
    if (preg_match('/-\s*([^-]+)$/', $input, $locationMatches)) {
        $location = trim($locationMatches[1]);
    }
    
    return [
        'zone' => $zone,
        'date' => $date,
        'time' => $time,
        'location' => $location
    ];
}

// Process if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    // Get all records from attendedstudent with null seminar_id
    $query = "SELECT NoIC, DateofArrival, invited_officer, tempat_temuduga FROM attendedstudent WHERE seminar_id IS NULL";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        $errors[] = "Error fetching records: " . mysqli_error($conn);
    } else {
        while ($row = mysqli_fetch_assoc($result)) {
            $totalRecords++;
            $noIC = $row['NoIC'];
            $dateOfArrival = $row['DateofArrival'];
            $invitedOfficer = $row['invited_officer'];
            $tempatTemuduga = $row['tempat_temuduga'];

            // Try to extract location from tempat_temuduga, DateofArrival, or invited_officer
            $locationString = '';
            
            // First check tempat_temuduga for seminar info
            if (!empty($tempatTemuduga) && $tempatTemuduga !== 'N/A') {
                $locationString = $tempatTemuduga;
            }
            
            // If tempat_temuduga doesn't contain location info, check DateofArrival
            if (empty($locationString) || !preg_match('/\[.*\]/', $locationString)) {
                if (!empty($dateOfArrival)) {
                    $locationString = $dateOfArrival;
                }
            }
            
            // If DateofArrival doesn't contain location info, try invited_officer
            if (empty($locationString) || !preg_match('/\[.*\]/', $locationString)) {
                if (!empty($invitedOfficer) && $invitedOfficer !== 'N/A') {
                    $locationString = $invitedOfficer;
                }
            }

            // Try to parse seminar information from the location string
            $seminarInfo = parseSeminarLocation($locationString);
            
            // If we have enough information to search for a seminar
            if (!empty($seminarInfo['zone']) || !empty($seminarInfo['date']) || !empty($seminarInfo['time']) || !empty($seminarInfo['location'])) {
                // Build query to find matching seminar
                $conditions = [];
                $params = [];
                $types = '';
                
                if (!empty($seminarInfo['zone'])) {
                    $conditions[] = "zone LIKE ?";
                    $params[] = "%" . $seminarInfo['zone'] . "%";
                    $types .= 's';
                }
                
                if (!empty($seminarInfo['date'])) {
                    $conditions[] = "seminar_date = ?";
                    $params[] = $seminarInfo['date'];
                    $types .= 's';
                }
                
                if (!empty($seminarInfo['time'])) {
                    $conditions[] = "seminar_time = ?";
                    $params[] = $seminarInfo['time'];
                    $types .= 's';
                }
                
                if (!empty($seminarInfo['location'])) {
                    $conditions[] = "location LIKE ?";
                    $params[] = "%" . $seminarInfo['location'] . "%";
                    $types .= 's';
                }
                
                if (!empty($conditions)) {
                    $seminarQuery = "SELECT id FROM seminar_schedules WHERE " . implode(" AND ", $conditions);
                    $stmt = mysqli_prepare($conn, $seminarQuery);
                    
                    if ($stmt) {
                        if (!empty($params)) {
                            mysqli_stmt_bind_param($stmt, $types, ...$params);
                        }
                        
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_store_result($stmt);
                        
                        if (mysqli_stmt_num_rows($stmt) > 0) {
                            mysqli_stmt_bind_result($stmt, $seminarId);
                            mysqli_stmt_fetch($stmt);
                            
                            // Update the record with the found seminar_id
                            $updateQuery = "UPDATE attendedstudent SET seminar_id = ? WHERE NoIC = ?";
                            $updateStmt = mysqli_prepare($conn, $updateQuery);
                            
                            if ($updateStmt) {
                                mysqli_stmt_bind_param($updateStmt, "is", $seminarId, $noIC);
                                
                                if (mysqli_stmt_execute($updateStmt)) {
                                    $updatedRecords++;
                                } else {
                                    $failedRecords++;
                                    $errors[] = "Failed to update NoIC: $noIC - " . mysqli_stmt_error($updateStmt);
                                }
                                
                                mysqli_stmt_close($updateStmt);
                            }
                        } else {
                            $failedRecords++;
                            $errors[] = "No matching seminar found for NoIC: $noIC (Zone: {$seminarInfo['zone']}, Date: {$seminarInfo['date']}, Time: {$seminarInfo['time']}, Location: {$seminarInfo['location']})";
                        }
                        
                        mysqli_stmt_close($stmt);
                    }
                } else {
                    $failedRecords++;
                    $errors[] = "Insufficient seminar information for NoIC: $noIC";
                }
            } else {
                $failedRecords++;
                $errors[] = "Could not parse seminar information from: $locationString for NoIC: $noIC";
            }
        }
        
        mysqli_free_result($result);
    }
}

// Get statistics for display
$totalNullQuery = "SELECT COUNT(*) as total FROM attendedstudent WHERE seminar_id IS NULL";
$totalNullResult = mysqli_query($conn, $totalNullQuery);
$totalNullCount = 0;
if ($totalNullResult && $row = mysqli_fetch_assoc($totalNullResult)) {
    $totalNullCount = $row['total'];
}

$totalLinkedQuery = "SELECT COUNT(*) as total FROM attendedstudent WHERE seminar_id IS NOT NULL";
$totalLinkedResult = mysqli_query($conn, $totalLinkedQuery);
$totalLinkedCount = 0;
if ($totalLinkedResult && $row = mysqli_fetch_assoc($totalLinkedResult)) {
    $totalLinkedCount = $row['total'];
}

$totalRecordsQuery = "SELECT COUNT(*) as total FROM attendedstudent";
$totalRecordsResult = mysqli_query($conn, $totalRecordsQuery);
$totalAttendeeCount = 0;
if ($totalRecordsResult && $row = mysqli_fetch_assoc($totalRecordsResult)) {
    $totalAttendeeCount = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Update Seminar Links</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-light: #4895ef;
            --primary-dark: #3f37c9;
            --secondary-color: #4cc9f0;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --radius-lg: 0.75rem;
        }

        body {
            font-family: 'Segoe UI', Roboto, -apple-system, BlinkMacSystemFont, sans-serif;
            color: #1e293b;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            position: relative;
            background-color: #f8fafc;
        }

        body::before,
        body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            z-index: -1;
        }

        body::before {
            top: -5rem;
            right: -5rem;
            width: 40rem;
            height: 40rem;
            background: radial-gradient(circle, rgba(76, 201, 240, 0.1) 0%, rgba(67, 97, 238, 0.05) 50%, rgba(255, 255, 255, 0) 70%);
        }

        body::after {
            bottom: -5rem;
            left: -5rem;
            width: 30rem;
            height: 30rem;
            background: radial-gradient(circle, rgba(76, 201, 240, 0.1) 0%, rgba(67, 97, 238, 0.05) 50%, rgba(255, 255, 255, 0) 70%);
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
        }

        .card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: 2rem;
            border: 1px solid rgba(226, 232, 240, 0.8);
            margin-bottom: 2rem;
        }

        h1, h2, h3 {
            color: var(--primary-dark);
            margin-top: 0;
        }

        h1 {
            font-size: 1.875rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .stats-container {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            flex: 1;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 0.5rem;
            padding: 1rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: var(--shadow-sm);
        }

        .stat-count {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-count.linked {
            color: var(--success-color);
        }

        .stat-count.unlinked {
            color: var(--warning-color);
        }

        .stat-label {
            font-size: 0.875rem;
            color: #64748b;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            border-radius: 0.25rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #475569;
            margin-left: 1rem;
        }

        .btn-secondary:hover {
            background: #cbd5e1;
        }

        .alert {
            padding: 1rem;
            border-radius: 0.25rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border-color: #a7f3d0;
        }

        .alert-warning {
            background-color: #fef3c7;
            color: #92400e;
            border-color: #fde68a;
        }

        .alert-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            border-color: #fecaca;
        }

        .actions {
            margin-top: 2rem;
            text-align: center;
        }

        .error-list {
            margin-top: 1.5rem;
            max-height: 300px;
            overflow-y: auto;
            background: rgba(254, 242, 242, 0.7);
            border-radius: 0.25rem;
            padding: 0.75rem;
            border: 1px solid rgba(252, 165, 165, 0.5);
        }

        .error-entry {
            padding: 0.5rem;
            border-radius: 0.25rem;
            background: rgba(254, 226, 226, 0.5);
            margin-bottom: 0.5rem;
            border-left: 3px solid #ef4444;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Update Seminar Links</h1>
            
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])): ?>
                <?php if ($updatedRecords > 0): ?>
                    <div class="alert alert-success">
                        Successfully updated <?php echo $updatedRecords; ?> records with seminar IDs.
                    </div>
                    <h2>Update Summary</h2>
                    <p>Total records processed: <?php echo $totalRecords; ?></p>
                    <p>Records updated: <?php echo $updatedRecords; ?></p>
                    <p>Records skipped: <?php echo $totalRecords - $updatedRecords; ?></p>
                    <p><strong>Note:</strong> This tool now checks the new <code>tempat_temuduga</code> field first, then falls back to <code>DateofArrival</code> and <code>invited_officer</code> fields.</p>
                <?php endif; ?>
                
                <?php if ($failedRecords > 0): ?>
                    <div class="alert alert-warning">
                        Failed to update <?php echo $failedRecords; ?> records. See details below.
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-count"><?php echo $totalAttendeeCount; ?></div>
                    <div class="stat-label">Total Records</div>
                </div>
                <div class="stat-card">
                    <div class="stat-count linked"><?php echo $totalLinkedCount; ?></div>
                    <div class="stat-label">Linked to Seminars</div>
                </div>
                <div class="stat-card">
                    <div class="stat-count unlinked"><?php echo $totalNullCount; ?></div>
                    <div class="stat-label">Not Linked</div>
                </div>
            </div>
            
            <p>This tool will attempt to link attendee records with their corresponding seminar schedules based on the information in the DateofArrival field.</p>
            
            <form method="post" action="">
                <div class="actions">
                    <button type="submit" name="update" class="btn btn-primary">Update Seminar Links</button>
                    <a href="summary.php" class="btn btn-secondary">Back to Summary</a>
                </div>
            </form>
            
            <?php if (!empty($errors)): ?>
                <h3>Error Details (<?php echo count($errors); ?>)</h3>
                <div class="error-list">
                    <?php foreach ($errors as $error): ?>
                        <div class="error-entry"><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
