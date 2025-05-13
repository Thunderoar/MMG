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

// Get statistics
$totalAttended = $pendingAttended = 0;
$attendResult = mysqli_query($conn, "SELECT COUNT(*) as total, SUM(is_dealt = 0) as pending FROM attendedstudent");
if ($attendResult) {
    $row = mysqli_fetch_assoc($attendResult);
    $totalAttended = $row['total'] ?? 0;
    $pendingAttended = $row['pending'] ?? 0;
}
$recentAttended = mysqli_query($conn, "SELECT fullName, NoIC, NoIC_Display, DateofArrival, FromWhere, WithWho, is_dealt, is_deleted FROM attendedstudent WHERE is_deleted = 0 ORDER BY DateofArrival DESC LIMIT 10");
$deletedAttended = mysqli_query($conn, "SELECT fullName, NoIC, NoIC_Display, DateofArrival, FromWhere, WithWho, is_dealt, is_deleted FROM attendedstudent WHERE is_deleted = 1 ORDER BY DateofArrival DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>MMG Dashboard Summary</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dashMain.css">
    <link rel="stylesheet" href="css/dashboard/sidebar.css">
    <style>
        /* Reset previous button styles */
        :root {
            --primary-color: #4361ee;
            --primary-light: #4895ef;
            --primary-dark: #3f37c9;
            --secondary-color: #4cc9f0;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius-lg: 0.75rem;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', Roboto, -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f0f4ff 0%, #f9fafb 100%);
            color: var(--text-color);
            font-size: 16px;
            line-height: 1.5;
        }

        .main-content {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .table-section {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
        }

        .table-section table {
            width: 100% !important; /* Ensure table fits within the container */
            table-layout: fixed !important; /* Prevent columns from expanding beyond container */
            overflow-wrap: break-word !important; /* Break long text to prevent overflow */
        }

        .table-section th {
            background: var(--primary-light) !important;
            color: white !important;
            padding: 0.75rem !important;
            font-weight: 700 !important;
            font-size: 1.2rem !important;
        }

        .table-section td {
            white-space: normal !important; /* Allow text wrapping */
            word-wrap: break-word !important; /* Break long words */
            padding: 8px 12px !important; /* Adjust padding for better spacing */
            border-bottom: 1px solid #eee !important;
            font-size: 1.1rem !important;
        }

        .table-section tr:hover {
            background: rgba(67, 97, 238, 0.05);
        }

        .status-badge {
            padding: 0.35rem 0.75rem !important;
            border-radius: 9999px !important;
            font-size: 1rem !important;
            font-weight: 600 !important;
            display: inline-block !important;
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-dealt { background: #d1fae5; color: #065f46; }
        .status-deleted { background: #ffc080; color: #663300; }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 2rem;  /* Increased padding */
            font-size: 1.25rem;  /* Increased font size */
            font-weight: 600;
            color: white;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: var(--radius-lg);
            cursor: pointer;
            text-decoration: none !important; /* Remove underline and prevent it from being added */
            transition: var(--transition);
            box-shadow: var(--shadow-md);
            min-width: 180px; /* Ensure minimum width */
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .action-btn:active {
            transform: translateY(0);
            box-shadow: var(--shadow-sm);
        }

        .action-btn:focus {
            outline: 2px solid rgba(67, 97, 238, 0.3);
            outline-offset: 2px;
        }

        /* Remove page container styling since nav is gone */
        .page-container {
            padding-left: 0;
        }
        
        .page-container .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Add subtle background gradient from index.php */
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
            background: radial-gradient(circle, rgba(67, 97, 238, 0.1) 0%, rgba(67, 97, 238, 0.05) 30%, transparent 70%);
        }
        
        body::after {
            bottom: -8rem;
            left: -5rem;
            width: 30rem;
            height: 30rem;
            background: radial-gradient(circle, rgba(76, 201, 240, 0.1) 0%, rgba(76, 201, 240, 0.05) 40%, transparent 70%);
        }

        .container {
            width: 100% !important; /* Use full width of the viewport */
            max-width: none !important; /* Remove maximum width constraint */
            margin: 0 !important; /* Remove side margins */
            padding: 20px !important; /* Keep consistent padding */
        }

        .tab-container {
            margin-bottom: 2rem;
        }

        .tab-buttons {
            display: flex;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 1.5rem;
        }

        .tab-btn {
            padding: 1rem 2rem !important;
            background: none !important;
            border: none !important;
            border-bottom: 2px solid transparent !important;
            cursor: pointer !important;
            font-size: 1.25rem !important;
            color: #718096 !important;
            transition: all 0.3s ease !important;
            font-weight: 600 !important;
        }

        .tab-btn:hover {
            color: #2d3748;
        }

        .tab-btn.active {
            color: #2563eb !important;
            border-bottom: 2px solid #2563eb !important;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .stat-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            text-align: center;
        }

        .stat-number {
            font-size: 2.25rem;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .table-section {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 1rem;
            padding: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        table {
            width: auto !important; /* Smart fit the table width */
            table-layout: auto !important; /* Allow columns to adjust based on content */
        }

        th, td {
            white-space: nowrap !important; /* Prevent wrapping of text */
            padding: 5px 10px !important; /* Adjust padding for compactness */
        }

        th {
            font-weight: 600;
            color: #4a5568;
            background: rgba(249, 250, 251, 0.7);
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-dealt { background: #d1fae5; color: #065f46; }
        .status-deleted { background: #ffc080; color: #663300; }

        .action-btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 1rem 2rem !important;
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            border-radius: 0.5rem !important;
            transition: all 0.2s ease !important;
            cursor: pointer !important;
            text-decoration: none !important;
            min-width: 150px !important;
            text-align: center !important;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .delete-btn {
            background: #fed7d7 !important;
            color: #c53030 !important;
            border: none !important;
            min-width: 100px !important;
            font-size: 1.1rem !important;
            padding: 0.5rem 0.75rem !important;
        }

        .delete-btn:hover {
            background: #feb2b2;
        }

        .action-btn, .tab-btn {
            padding: 5px 10px !important; /* Reduced padding with !important */
            font-size: 12px !important; /* Reduced font size with !important */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="table-section">
            <h1 style="margin-bottom: 2rem; font-size: 1.875rem; font-weight: bold; color: #2d3748;">Attendance Summary</h1>

            <div class="stat-cards">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalAttended; ?></div>
                    <div>Total Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $pendingAttended; ?></div>
                    <div>Pending Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalAttended - $pendingAttended; ?></div>
                    <div>Dealt Students</div>
                </div>
            </div>

            <div style="margin-bottom: 2rem;">
                <!-- Primary Actions First -->
                <a href="dashboard/user/new_entry.php" class="action-btn">Add New Student</a>
                
                <!-- Import Data Dropdown (Secondary Action) -->
                <div class="import-dropdown" style="display: inline-block; position: relative; margin-left: 1rem;">
                    <button id="importBtn" class="action-btn" style="background: linear-gradient(135deg, rgba(76, 201, 240, 0.8), rgba(67, 97, 238, 0.8)); border: none; cursor: pointer;">
                        <span style="margin-right: 8px;">📊</span> Import Data <span style="margin-left: 5px;">▼</span>
                    </button>
                    <div class="import-dropdown-content" style="display: none; position: absolute; min-width: 250px; z-index: 999; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 0.5rem; box-shadow: var(--shadow-md); padding: 0.5rem 0; border: 1px solid rgba(255, 255, 255, 0.5); margin-top: 0.25rem; right: 0;">
                        <a href="fetch_and_import.php" class="dropdown-item" style="display: block; padding: 0.75rem 1rem; text-decoration: none; color: #2d3748; transition: all 0.2s; font-weight: 500;">
                            <span style="margin-right: 8px;">📁</span> Upload CSV File
                        </a>
                        <button id="importSheetsBtn" class="dropdown-item" style="display: block; width: 100%; text-align: left; background: none; border: none; padding: 0.75rem 1rem; cursor: pointer; color: #2d3748; transition: all 0.2s; font-weight: 500;">
                            <span style="margin-right: 8px;">📈</span> Direct from Google Sheets
                        </button>
                    </div>
                </div>
                
                <!-- Navigation (Secondary Action) -->
                <a href="index.php" class="action-btn" style="margin-left: 1rem;">Back to Dashboard</a>
                
                <!-- Destructive Action Last -->
                <a href="purge_data.php" class="action-btn" style="margin-left: 1rem; background: linear-gradient(135deg, rgba(239, 68, 68, 0.8), rgba(185, 28, 28, 0.8)); border: none;">
                    <span style="margin-right: 8px;">🗑️</span> Purge Data
                </a>
            </div>

            <div class="tab-container">
                <div class="tab-buttons">
                    <button class="tab-btn active" data-tab="active">Active Records</button>
                    <button class="tab-btn" data-tab="deleted">Deleted Records</button>
                </div>

                <div class="tab-content active" id="active">
                    <div class="table-section">
                        <h2 style="margin-bottom: 1.5rem;">Recent Attended Students</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>NoIC Display</th>
                                    <th>Date of Arrival</th>
                                    <th>From Where</th>
                                    <th>With Who</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($recentAttended)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['fullName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['NoIC_Display']); ?></td>
                                    <td><?php echo date('d M Y H:i', strtotime($row['DateofArrival'])); ?></td>
                                    <td>
                                        <?php if($row['FromWhere'] === 'WhatsApp'): ?>
                                            <img src="image/wsimg.png" alt="WhatsApp" style="width:20px; height:20px; vertical-align: middle;"> WhatsApp
                                        <?php else: ?>
                                            <img src="image/berjalan.webp" alt="Walk-In" style="width:20px; height:20px; vertical-align: middle;"> Walk-In
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['WithWho']); ?></td>
                                    <td>
                                        <?php if ($row['is_dealt'] == 0) { echo '<span class="status-badge status-pending">Pending</span>'; } else { echo '<span class="status-badge status-dealt">Dealt</span>'; } ?>
                                    </td>
                                    <td>
                                        <button type="button" class="action-btn delete-btn" onclick="softDeleteRecord('<?php echo htmlspecialchars($row['NoIC']); ?>')">Delete</button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-content" id="deleted">
                    <div class="table-section">
                        <h2 style="margin-bottom: 1.5rem;">Deleted Records</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>NoIC Display</th>
                                    <th>Date of Arrival</th>
                                    <th>From Where</th>
                                    <th>With Who</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($deletedAttended)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['fullName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['NoIC_Display']); ?></td>
                                    <td><?php echo date('d M Y H:i', strtotime($row['DateofArrival'])); ?></td>
                                    <td>
                                        <?php if($row['FromWhere'] === 'WhatsApp'): ?>
                                            <img src="image/wsimg.png" alt="WhatsApp" style="width:20px; height:20px; vertical-align: middle;"> WhatsApp
                                        <?php else: ?>
                                            <img src="image/berjalan.webp" alt="Walk-In" style="width:20px; height:20px; vertical-align: middle;"> Walk-In
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['WithWho']); ?></td>
                                    <td>
                                        <span class="status-badge status-deleted">Deleted</span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        // Tab switching logic
        document.querySelectorAll('.tab-btn').forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons and content
                document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

                // Add active class to clicked button and corresponding content
                button.classList.add('active');
                const tabId = button.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Dropdown menu functionality
        document.getElementById('importBtn').addEventListener('click', function(e) {
            e.stopPropagation();
            document.querySelector('.import-dropdown-content').style.display = 
                document.querySelector('.import-dropdown-content').style.display === 'block' ? 'none' : 'block';
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.matches('#importBtn') && !e.target.closest('.import-dropdown-content')) {
                document.querySelector('.import-dropdown-content').style.display = 'none';
            }
        });
        
        // Hover effect for dropdown items
        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('mouseover', function() {
                this.style.backgroundColor = 'rgba(76, 201, 240, 0.1)';
            });
            item.addEventListener('mouseout', function() {
                this.style.backgroundColor = 'transparent';
            });
        });
        
        // Google Sheets Import functionality
        document.getElementById('importSheetsBtn').addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('.import-dropdown-content').style.display = 'none';
            if (confirm('Are you sure you want to import data from Google Sheets?')) {
                // Show loading state
                const originalText = this.innerHTML;
                this.innerHTML = '<span class="spinner" style="display: inline-block; width: 20px; height: 20px; border: 3px solid rgba(255,255,255,.3); border-radius: 50%; border-top-color: white; animation: spin 1s ease-in-out infinite;"></span> Importing...';
                this.disabled = true;
                
                // Add the spinner animation
                document.head.insertAdjacentHTML('beforeend', `
                    <style>
                        @keyframes spin {
                            to { transform: rotate(360deg); }
                        }
                    </style>
                `);
                
                // Send the request to the import script
                fetch('import_from_sheets.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.text().then(text => {
                        // First check if the response is empty
                        if (!text) {
                            throw new Error('Empty response received');
                        }
                        
                        try {
                            // Try to parse as JSON
                            return JSON.parse(text);
                        } catch (e) {
                            // If parsing fails, throw error with the text content
                            console.error('Failed to parse JSON:', text);
                            throw new Error('Invalid JSON response: ' + e.message);
                        }
                    });
                })
                .then(data => {
                    if (data.status === 'success') {
                        // Create a detailed result message
                        let resultMessage = `Import successful!\n\nImported: ${data.imported} records\nSkipped: ${data.skipped} records`;
                        
                        if (data.errors && data.errors.length > 0) {
                            resultMessage += '\n\nErrors:\n' + data.errors.slice(0, 5).join('\n');
                            
                            if (data.errors.length > 5) {
                                resultMessage += `\n...and ${data.errors.length - 5} more errors.`;
                            }
                        }
                        
                        alert(resultMessage);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                        this.innerHTML = originalText;
                        this.disabled = false;
                    }
                })
                .catch(error => {
                    alert('Error importing from Google Sheets');
                    console.error('Error:', error);
                    this.innerHTML = originalText;
                    this.disabled = false;
                });
            }
        });

        function softDeleteRecord(noIC) {
            if (confirm('Are you sure you want to soft delete this record?')) {
                fetch('soft_delete.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'noIC=' + encodeURIComponent(noIC)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error soft deleting record');
                    console.error('Error:', error);
                });
            }
        }
    </script>
</body>
</html>
