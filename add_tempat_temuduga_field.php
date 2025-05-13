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

// Check if the field already exists
$checkFieldQuery = "SHOW COLUMNS FROM `attendedstudent` LIKE 'tempat_temuduga'";
$checkResult = mysqli_query($conn, $checkFieldQuery);

$message = '';
$success = false;

if (mysqli_num_rows($checkResult) > 0) {
    $message = "The 'tempat_temuduga' field already exists in the attendedstudent table.";
    $success = true;
} else {
    // Add the new field
    $alterQuery = "ALTER TABLE `attendedstudent` 
                   ADD COLUMN `tempat_temuduga` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL 
                   AFTER `invited_officer`";
    
    if (mysqli_query($conn, $alterQuery)) {
        $message = "Successfully added 'tempat_temuduga' field to the attendedstudent table.";
        $success = true;
    } else {
        $message = "Error adding field: " . mysqli_error($conn);
        $success = false;
    }
}

// Process if form is submitted to update existing records
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_existing'])) {
    // Update existing records where invited_officer might contain seminar location data
    $updateQuery = "UPDATE `attendedstudent` 
                    SET `tempat_temuduga` = `invited_officer` 
                    WHERE `tempat_temuduga` IS NULL AND `invited_officer` IS NOT NULL 
                    AND (`invited_officer` LIKE '%[%]%' OR `invited_officer` LIKE '%PAGI%' OR `invited_officer` LIKE '%PETANG%')";
    
    if (mysqli_query($conn, $updateQuery)) {
        $affectedRows = mysqli_affected_rows($conn);
        $message .= "<br>Updated $affectedRows existing records with tempat_temuduga data.";
    } else {
        $message .= "<br>Error updating existing records: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Tempat Temuduga Field</title>
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

        .actions {
            margin-top: 2rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Add Tempat Temuduga Field</h1>
            
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo $message; ?>
            </div>
            
            <p>This tool adds a new 'tempat_temuduga' field to the attendedstudent table to correctly store the seminar location data from Google Sheets.</p>
            
            <form method="post" action="">
                <div class="actions">
                    <button type="submit" name="update_existing" class="btn btn-primary">Update Existing Records</button>
                    <a href="summary.php" class="btn btn-secondary">Back to Summary</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
