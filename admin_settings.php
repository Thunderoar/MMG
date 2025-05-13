<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'include/db_conn.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Get current user's password
    $sql = "SELECT password FROM admin_users WHERE id = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $_SESSION['admin_id'], $current_password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        if ($new_password === $confirm_password) {
            $update_sql = "UPDATE admin_users SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $new_password, $_SESSION['admin_id']);
            
            if ($update_stmt->execute()) {
                $password_success = "Password updated successfully!";
            } else {
                $password_error = "Error updating password.";
            }
        } else {
            $password_error = "New passwords do not match.";
        }
    } else {
        $password_error = "Current password is incorrect.";
    }
}

// Handle settings form submission
if (isset($_POST['update_settings'])) {
    // Validate and sanitize input
    $state_name = strip_tags(trim($_POST['state_name']));
    $system_title = strip_tags(trim($_POST['system_title']));
    $color_theme = strip_tags(trim($_POST['color_theme']));
    
    // Update settings
    $sql = "UPDATE system_settings SET setting_value = ?, updated_by = ? WHERE setting_key = ?";
    $stmt = $conn->prepare($sql);
    
    // Update state name
    $stmt->bind_param("sis", $state_name, $_SESSION['admin_id'], $setting_key);
    $setting_key = 'state_name';
    $stmt->execute();
    
    // Update system title
    $setting_key = 'system_title';
    $stmt->bind_param("sis", $system_title, $_SESSION['admin_id'], $setting_key);
    $stmt->execute();
    
    // Update color theme
    $setting_key = 'color_theme';
    $stmt->bind_param("sis", $color_theme, $_SESSION['admin_id'], $setting_key);
    $stmt->execute();
    
    $settings_success = "Settings updated successfully!";
}

// Get current settings
$settings = [];
$sql = "SELECT setting_key, setting_value FROM system_settings";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TEMUDUGA TVET</title>
    <style>
        :root {
            --primary-color: <?php echo $settings['color_theme'] ?? '#4361ee'; ?>;
            --primary-light: <?php echo adjustBrightness($settings['color_theme'] ?? '#4361ee', 20); ?>;
            --primary-dark: <?php echo adjustBrightness($settings['color_theme'] ?? '#4361ee', -20); ?>;
            --secondary-color: #4cc9f0;
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --radius-lg: 0.75rem;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f0f4ff 0%, #f9fafb 100%);
            min-height: 100vh;
            margin: 0;
            padding: 2rem;
            color: #1f2937;
        }

        .settings-container {
            background: white;
            padding: 2.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            max-width: 800px;
            margin: 2rem auto;
            position: relative;
            overflow: hidden;
        }

        .settings-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .header h1 {
            color: var(--primary-dark);
            margin: 0;
            font-size: 1.875rem;
            font-weight: 600;
        }

        .settings-section {
            background: #f9fafb;
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid #e5e7eb;
        }

        .section-title {
            font-size: 1.25rem;
            color: var(--primary-dark);
            margin-bottom: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #374151;
            font-weight: 500;
        }

        input, select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e5e7eb;
            border-radius: var(--radius-lg);
            font-size: 1rem;
            transition: var(--transition);
            background-color: white;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        button, .button {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: var(--radius-lg);
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            text-align: center;
        }

        button:hover, .button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .logout-btn {
            background: #ef4444;
            background-image: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
        }

        .success {
            background-color: #dcfce7;
            color: #166534;
            padding: 1rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .error {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 1rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .color-group {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        input[type="color"] {
            width: 80px;
            height: 40px;
            padding: 0;
            border: none;
            border-radius: var(--radius-lg);
            cursor: pointer;
        }

        .color-preview {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid #e5e7eb;
            transition: var(--transition);
        }

        #previewText {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: var(--radius-lg);
            background: white;
            border: 1px solid #e5e7eb;
            transition: var(--transition);
        }

        .password-toggle {
            display: flex;
            align-items: center;
            position: relative;
        }

        .password-toggle input {
            flex: 1;
            padding-right: 3rem;
            box-sizing: border-box;
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            padding: 0.25rem;
            font-size: 0.875rem;
        }

        .toggle-password:hover {
            color: var(--primary-color);
        }

        @media (max-width: 640px) {
            body {
                padding: 1rem;
            }

            .settings-container {
                padding: 1.5rem;
            }

            .button-group {
                flex-direction: column;
            }

            button, .button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="settings-container">
        <div class="header">
            <h1>Dashboard</h1>
            <a href="logout.php" class="button logout-btn">Logout</a>
        </div>
        
        <!-- System Settings Section -->
        <div class="settings-section">
            <h2 class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                System Settings
            </h2>
            <?php if (isset($settings_success)): ?>
                <div class="success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <?php echo htmlspecialchars($settings_success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="state_name">State Name</label>
                    <input type="text" id="state_name" name="state_name" 
                           value="<?php echo htmlspecialchars($settings['state_name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="system_title">System Title</label>
                    <input type="text" id="system_title" name="system_title" 
                           value="<?php echo htmlspecialchars($settings['system_title'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="color_theme">Color Theme</label>
                    <div class="color-group">
                        <input type="color" id="color_theme" name="color_theme" 
                               value="<?php echo htmlspecialchars($settings['color_theme'] ?? '#4361ee'); ?>">
                        <div class="color-preview" id="colorPreview"></div>
                    </div>
                    <div id="previewText">
                        Preview: TEMUDUGA TVET <?php echo htmlspecialchars($settings['state_name'] ?? ''); ?>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" name="update_settings">Save Changes</button>
                    <a href="index.php" class="button" style="background: #6b7280; background-image: linear-gradient(135deg, #6b7280 0%, #9ca3af 100%);">Back to Home</a>
                </div>
            </form>
        </div>

        <!-- Password Change Section -->
        <div class="settings-section">
            <h2 class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                Change Password
            </h2>
            <?php if (isset($password_success)): ?>
                <div class="success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <?php echo htmlspecialchars($password_success); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($password_error)): ?>
                <div class="error">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <?php echo htmlspecialchars($password_error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <div class="password-toggle">
                        <input type="password" id="current_password" name="current_password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('current_password')">Show</button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="password-toggle">
                        <input type="password" id="new_password" name="new_password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('new_password')">Show</button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <div class="password-toggle">
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">Show</button>
                    </div>
                </div>

                <button type="submit" name="change_password">Update Password</button>
            </form>
        </div>
    </div>

    <script>
        // Color picker preview
        const colorInput = document.getElementById('color_theme');
        const colorPreview = document.getElementById('colorPreview');
        const previewText = document.getElementById('previewText');

        function updatePreview() {
            const color = colorInput.value;
            colorPreview.style.backgroundColor = color;
            previewText.style.color = color;
        }

        colorInput.addEventListener('input', updatePreview);
        updatePreview();

        // Password visibility toggle
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.nextElementSibling;
            
            if (input.type === 'password') {
                input.type = 'text';
                button.textContent = 'Hide';
            } else {
                input.type = 'password';
                button.textContent = 'Show';
            }
        }
    </script>
</body>
</html>

<?php
function adjustBrightness($hex, $steps) {
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));
    
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}
?>