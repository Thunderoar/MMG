<?php
session_start();
require 'include/db_conn.php';

// Check if user is already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_settings.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $sql = "SELECT id, username, password FROM admin_users WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        
        // Update last login time
        $update_sql = "UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $user['id']);
        $update_stmt->execute();
        
        header("Location: admin_settings.php");
        exit();
    }
    $error = "Invalid username or password";
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
            --primary-color: #4361ee;
            --primary-light: #4895ef;
            --primary-dark: #3f37c9;
            --secondary-color: #4cc9f0;
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --radius-lg: 0.75rem;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f0f4ff 0%, #f9fafb 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .login-container {
            background: white;
            padding: 2rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .logo {
            width: 120px;
            height: auto;
            margin-bottom: 1.5rem;
        }

        h1 {
            color: var(--primary-dark);
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #374151;
        }

        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: var(--radius-lg);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        button {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: var(--radius-lg);
            cursor: pointer;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .error {
            color: #ef4444;
            margin-top: 1rem;
            font-size: 0.875rem;
        }

        .back-link {
            display: inline-block;
            margin-top: 1rem;
            color: var(--primary-color);
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="image/magmalogo.png" alt="Logo" class="logo">
        <h1>Dashboard</h1>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <a href="index.php" class="back-link">Back to Home</a>
    </div>
</body>
</html>