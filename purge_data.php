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

// Initialize response array for AJAX requests
$response = [
    'status' => 'error',
    'message' => '',
    'count' => 0
];

// Check if this is an AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $action = $_POST['action'];
        
        // Different purge actions
        switch ($action) {
            // Purge soft-deleted records
            case 'purge_deleted':
                $sql = "DELETE FROM attendedstudent WHERE is_deleted = 1";
                $result = mysqli_query($conn, $sql);
                
                if ($result) {
                    $count = mysqli_affected_rows($conn);
                    $response = [
                        'status' => 'success',
                        'message' => "Successfully purged $count deleted records",
                        'count' => $count
                    ];
                } else {
                    throw new Exception("Database error: " . mysqli_error($conn));
                }
                break;
            
            // Purge all records (dangerous!)
            case 'purge_all':
                // Check for confirmation code to prevent accidental purge
                if (!isset($_POST['confirm_code']) || $_POST['confirm_code'] !== 'PURGE_ALL') {
                    throw new Exception("Invalid confirmation code for purging all records");
                }
                
                $sql = "DELETE FROM attendedstudent";
                $result = mysqli_query($conn, $sql);
                
                if ($result) {
                    $count = mysqli_affected_rows($conn);
                    $response = [
                        'status' => 'success',
                        'message' => "Successfully purged ALL records ($count total)",
                        'count' => $count
                    ];
                } else {
                    throw new Exception("Database error: " . mysqli_error($conn));
                }
                break;
            
            // Purge records by date range
            case 'purge_date_range':
                // Validate date inputs
                if (!isset($_POST['start_date']) || !isset($_POST['end_date'])) {
                    throw new Exception("Start and end dates are required");
                }
                
                $startDate = $_POST['start_date'];
                $endDate = $_POST['end_date'];
                
                // Validate date format
                $startDateObj = DateTime::createFromFormat('Y-m-d', $startDate);
                $endDateObj = DateTime::createFromFormat('Y-m-d', $endDate);
                
                if (!$startDateObj || !$endDateObj) {
                    throw new Exception("Invalid date format. Use YYYY-MM-DD format.");
                }
                
                // Add time to make the end date inclusive
                $endDate = $endDate . ' 23:59:59';
                
                $sql = "DELETE FROM attendedstudent WHERE DateofArrival BETWEEN ? AND ?";
                $stmt = mysqli_prepare($conn, $sql);
                
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ss", $startDate, $endDate);
                    mysqli_stmt_execute($stmt);
                    $count = mysqli_stmt_affected_rows($stmt);
                    
                    $response = [
                        'status' => 'success',
                        'message' => "Successfully purged $count records between $startDate and $endDate",
                        'count' => $count
                    ];
                    
                    mysqli_stmt_close($stmt);
                } else {
                    throw new Exception("Database error: " . mysqli_error($conn));
                }
                break;
            
            // Purge records imported from Google Sheets (based on NoIC pattern)
            case 'purge_imported':
                // This assumes imported records have NoIC generated by our script (numeric 12-digit)
                $sql = "DELETE FROM attendedstudent WHERE NoIC REGEXP '^[0-9]{12}$'";
                $result = mysqli_query($conn, $sql);
                
                if ($result) {
                    $count = mysqli_affected_rows($conn);
                    $response = [
                        'status' => 'success',
                        'message' => "Successfully purged $count imported records",
                        'count' => $count
                    ];
                } else {
                    throw new Exception("Database error: " . mysqli_error($conn));
                }
                break;
                
            // Purge dealt records (records that have been handled)
            case 'purge_dealt':
                $sql = "DELETE FROM attendedstudent WHERE is_dealt = 1";
                $result = mysqli_query($conn, $sql);
                
                if ($result) {
                    $count = mysqli_affected_rows($conn);
                    $response = [
                        'status' => 'success',
                        'message' => "Successfully purged $count dealt records",
                        'count' => $count
                    ];
                } else {
                    throw new Exception("Database error: " . mysqli_error($conn));
                }
                break;
                
            default:
                throw new Exception("Unknown action: $action");
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Purge Data - MMG System</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-light: #4895ef;
            --primary-dark: #3f37c9;
            --secondary-color: #4cc9f0;
            --danger-color: #ef4444;
            --danger-dark: #b91c1c;
            --warning-color: #f59e0b;
            --warning-dark: #d97706;
            --success-color: #10b981;
            --success-dark: #059669;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius-lg: 0.75rem;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', Roboto, -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f0f4ff 0%, #f9fafb 100%);
            color: #2d3748;
            font-size: 16px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            position: relative;
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
            max-width: 1000px;
            margin: 2rem auto;
            padding: 2rem;
        }

        .page-title {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--primary-dark);
        }

        .card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.5);
            transition: var(--transition);
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-5px);
        }

        .card-title {
            margin-top: 0;
            margin-bottom: 1rem;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
        }

        .card-title i {
            margin-right: 0.5rem;
            font-size: 1.25rem;
        }

        .purge-form {
            margin-top: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        input[type="date"],
        input[type="text"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.25rem;
            font-size: 1rem;
            transition: var(--transition);
        }

        input[type="date"]:focus,
        input[type="text"]:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(76, 201, 240, 0.1);
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
            transition: var(--transition);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color), var(--danger-dark));
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning-color), var(--warning-dark));
            color: white;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        .alert {
            padding: 1rem;
            border-radius: 0.25rem;
            margin-bottom: 1rem;
            display: none;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .card-description {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
        }

        .danger-zone {
            border: 1px dashed var(--danger-color);
            padding: 1rem;
            border-radius: 0.25rem;
            margin-top: 1rem;
        }

        .danger-title {
            color: var(--danger-dark);
            margin-top: 0;
        }

        .action-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .action-row {
                flex-direction: column;
            }
            
            .action-row .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }

        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 0.5rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .back-link {
            display: inline-block;
            margin-top: 2rem;
            color: var(--primary-dark);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .back-link:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }

        .icon {
            margin-right: 0.5rem;
        }

        .stats {
            background: rgba(76, 201, 240, 0.1);
            border-radius: 0.25rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .stats p {
            margin: 0.5rem 0;
        }

        .stats strong {
            color: var(--primary-dark);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="page-title">Data Purge Tools</h1>
        
        <div id="alert" class="alert"></div>
        
        <div class="stats card">
            <h2 class="card-title">Database Statistics</h2>
            <?php
            // Get statistics
            $totalRecords = $deletedRecords = $dealtRecords = 0;
            
            $stats = mysqli_query($conn, "SELECT 
                COUNT(*) as total, 
                SUM(is_deleted = 1) as deleted,
                SUM(is_dealt = 1) as dealt
                FROM attendedstudent");
                
            if ($stats && $row = mysqli_fetch_assoc($stats)) {
                $totalRecords = $row['total'] ?? 0;
                $deletedRecords = $row['deleted'] ?? 0;
                $dealtRecords = $row['dealt'] ?? 0;
            }
            ?>
            <p><strong>Total Records:</strong> <?php echo $totalRecords; ?></p>
            <p><strong>Deleted Records:</strong> <?php echo $deletedRecords; ?></p>
            <p><strong>Dealt Records:</strong> <?php echo $dealtRecords; ?></p>
        </div>
        
        <div class="card">
            <h2 class="card-title">Purge Soft-Deleted Records</h2>
            <p class="card-description">This will permanently remove all records that have been soft-deleted (is_deleted = 1). These are records that have been marked as deleted but are still in the database.</p>
            
            <form class="purge-form" id="purgeDeletedForm">
                <input type="hidden" name="action" value="purge_deleted">
                <button type="submit" class="btn btn-danger" id="purgeDeletedBtn">
                    <span class="icon">🗑️</span> Purge Deleted Records
                </button>
            </form>
        </div>
        
        <div class="card">
            <h2 class="card-title">Purge by Date Range</h2>
            <p class="card-description">Permanently remove records that fall within a specific date range. This is useful for cleaning up old data or records from a specific time period.</p>
            
            <form class="purge-form" id="purgeDateRangeForm">
                <input type="hidden" name="action" value="purge_date_range">
                
                <div class="form-group">
                    <label for="start_date">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" required>
                </div>
                
                <div class="form-group">
                    <label for="end_date">End Date:</label>
                    <input type="date" id="end_date" name="end_date" required>
                </div>
                
                <button type="submit" class="btn btn-warning" id="purgeDateRangeBtn">
                    <span class="icon">📅</span> Purge Records in Date Range
                </button>
            </form>
        </div>
        
        <div class="card">
            <h2 class="card-title">Purge Imported Records</h2>
            <p class="card-description">Remove all records that were imported from Google Sheets. This is useful if you want to clean up after testing or if there was an issue with the imported data.</p>
            
            <form class="purge-form" id="purgeImportedForm">
                <input type="hidden" name="action" value="purge_imported">
                <button type="submit" class="btn btn-danger" id="purgeImportedBtn">
                    <span class="icon">📊</span> Purge All Imported Records
                </button>
            </form>
        </div>
        
        <div class="card">
            <h2 class="card-title">Purge Dealt Records</h2>
            <p class="card-description">Remove all records that have been marked as dealt (is_dealt = 1). This is useful for cleaning up records that have already been processed.</p>
            
            <form class="purge-form" id="purgeDealtForm">
                <input type="hidden" name="action" value="purge_dealt">
                <button type="submit" class="btn btn-warning" id="purgeDealtBtn">
                    <span class="icon">✅</span> Purge All Dealt Records
                </button>
            </form>
        </div>
        
        <div class="card">
            <h2 class="card-title" style="color: var(--danger-dark);">Danger Zone</h2>
            <div class="danger-zone">
                <h3 class="danger-title">Purge ALL Records</h3>
                <p class="card-description">This will permanently delete ALL records from the database. This action cannot be undone. Use with extreme caution!</p>
                
                <form class="purge-form" id="purgeAllForm">
                    <input type="hidden" name="action" value="purge_all">
                    
                    <div class="form-group">
                        <label for="confirm_code">Type "PURGE_ALL" to confirm:</label>
                        <input type="text" id="confirm_code" name="confirm_code" required placeholder="Type PURGE_ALL here">
                    </div>
                    
                    <button type="submit" class="btn btn-danger" id="purgeAllBtn">
                        <span class="icon">⚠️</span> Purge ALL Records
                    </button>
                </form>
            </div>
        </div>
        
        <a href="summary.php" class="back-link">
            <span class="icon">←</span> Back to Summary Page
        </a>
    </div>

    <script>
        // Function to handle form submission
        function handleFormSubmit(formId, callback) {
            document.getElementById(formId).addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!confirm("Are you sure you want to proceed with this action? This cannot be undone.")) {
                    return;
                }
                
                const form = this;
                const button = form.querySelector('button[type="submit"]');
                const originalButtonText = button.innerHTML;
                const alert = document.getElementById('alert');
                
                // Show loading state
                button.innerHTML = '<span class="spinner"></span>Processing...';
                button.disabled = true;
                alert.style.display = 'none';
                
                // Create form data
                const formData = new FormData(form);
                
                // Send the request
                fetch('purge_data.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Failed to parse JSON:', text);
                            throw new Error('Invalid response format');
                        }
                    });
                })
                .then(data => {
                    // Show result
                    alert.className = 'alert ' + (data.status === 'success' ? 'alert-success' : 'alert-danger');
                    alert.textContent = data.message;
                    alert.style.display = 'block';
                    
                    // Reset button
                    button.innerHTML = originalButtonText;
                    button.disabled = false;
                    
                    // If successful, update UI or run callback
                    if (data.status === 'success' && typeof callback === 'function') {
                        callback(data);
                    }
                    
                    // Reload the page after a successful purge to update statistics
                    if (data.status === 'success') {
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert.className = 'alert alert-danger';
                    alert.textContent = 'Error: ' + error.message;
                    alert.style.display = 'block';
                    
                    button.innerHTML = originalButtonText;
                    button.disabled = false;
                });
            });
        }
        
        // Initialize all forms
        window.addEventListener('DOMContentLoaded', function() {
            handleFormSubmit('purgeDeletedForm');
            handleFormSubmit('purgeDateRangeForm');
            handleFormSubmit('purgeImportedForm');
            handleFormSubmit('purgeDealtForm');
            handleFormSubmit('purgeAllForm');
        });
    </script>
</body>
</html>
