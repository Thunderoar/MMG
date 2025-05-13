<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'include/db_conn.php';

// Get system settings
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
  <title><?php echo htmlspecialchars($settings['system_title'] . ' ' . $settings['state_name']); ?></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Fonts and Icons -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito+Sans:200,300,400,700,900|Oswald:400,700"> 
  <link rel="stylesheet" href="fonts/icomoon/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <!-- CSS Files -->
  <link rel="stylesheet" href="css/jquery.fancybox.min.css">
  <link rel="stylesheet" href="css/jquery-ui.css">
  <link rel="stylesheet" href="css/owl.carousel.min.css">
  <link rel="stylesheet" href="css/owl.theme.default.min.css">
  <link rel="stylesheet" href="css/animate.css">
  <link rel="stylesheet" href="fonts/flaticon/font/flaticon.css">
  <link rel="stylesheet" href="css/aos.css">
  <link rel="stylesheet" href="css/homepagestyle.css">

  <!-- JS Files -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  <style>
    :root {
      --primary-color: <?php echo $settings['color_theme'] ?? '#4361ee'; ?>;
      --primary-dark: <?php echo adjustBrightness($settings['color_theme'] ?? '#4361ee', -20); ?>;
    }

    /* General Link and Paragraph Styles */
    a:not(.not) {
      color: #a9c9fc !important;
    }
    a:not(.not):hover {
      color: white !important;
    }
    p {
      text-indent: 20px;
    }
    p:hover {
      color: #00358a !important;
    }
    /* Section Styles */
    .site-section {
      background-color: #ffffff; 
      padding: 40px; 
      border-radius: 10px; 
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
    }
    .section-title {
      font-size: 2.5rem; 
      font-weight: bold; 
      color: #343a40; 
      margin-bottom: 20px; 
      text-align: center; 
    }
    /* Image Effects */
    img:not(.logo) {
      transition: transform 0.3s ease, box-shadow 0.3s ease; 
      border-radius: 10px; 
      width: 100%; 
      height: auto; 
    }
    img:not(.logo):hover {
      transform: scale(1.05); 
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); 
    }
    /* Menu Styles */
    .menu a {
      font-size: 1rem;
      color: white !important;
      text-decoration: none;
      padding: 0.5rem 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.3s ease;
    }

    .menu a:hover {
      background: rgba(255, 255, 255, 0.2);
      border-radius: 0.375rem;
    }

    .menu i {
      font-size: 1.2rem;
    }

    .menu li {
      list-style: none;
    }

    .menu {
      display: flex;
      gap: 1rem;
    }
    /* Fixed Header Styles */
    header.site-navbar {
      background-color: var(--primary-color);
      height: 80px;  
      width: 100%;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1000;
      display: flex;
      align-items: center;
      padding: 0 20px;
    }
    header.site-navbar .container {
      width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .site-logo {
      display: flex;
      align-items: center;
    }
    .site-logo img.logo {
      height: 50px;
      margin-right: 10px;
    }
    .site-logo a {
      font-size: 18px;
      color: white !important;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .admin-nav {
      margin-left: auto;
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .admin-nav a {
      color: white !important;
      text-decoration: none;
      padding: 0.5rem 1rem;
      border-radius: 0.375rem;
      transition: all 0.3s ease;
    }

    .admin-nav a:hover {
      background: rgba(255, 255, 255, 0.1);
    }

    .admin-nav .login-btn {
      background: #28a745 !important;
      color: white !important;
      font-weight: bold !important;
      padding: 0.5rem 1rem !important;
      border: none !important;
      border-radius: 0.375rem !important;
      cursor: pointer !important;
      transition: all 0.3s ease !important;
    }

    .admin-nav .login-btn:hover {
      background: #218838 !important;
      transform: translateY(-2px) !important;
      box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3) !important;
    }

    /* Logout Button Styles */
    .logout-btn {
      background: linear-gradient(135deg, #ff6b6b, #ff4757) !important;
      color: white;
      font-weight: bold;
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 0.375rem;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .logout-btn:hover {
      background: linear-gradient(135deg, #ff6b6b, #ff4757) !important;
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(255, 71, 87, 0.3);
    }

    /* Remove body centering so the header stays at the top */
    body {
      font-family: "Lato", Arial, sans-serif;
      margin: 0;
      padding-top: 80px; /* To prevent header overlap with content */
      background-image: linear-gradient(140deg, #e2e2e2, #cdcdcd);
    }
    * {
      box-sizing: border-box;
    }

  </style>
</head>
<body>
  <!-- Fixed Header -->
  <header class="site-navbar">
    <div class="container">
      <div class="site-logo">
        <img src="<?php echo htmlspecialchars($settings['system_logo']); ?>" alt="Logo" class="logo" />
        <a href="index.php">
          <?php echo htmlspecialchars($settings['system_title']); ?>
          <span style="margin-left: 0.5rem; font-weight: 600;">
            <?php echo htmlspecialchars($settings['state_name']); ?>
          </span>
        </a>
      </div>
      <nav class="site-navigation">
        <ul class="menu">
          <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
          <?php if (isset($_SESSION['admin_id'])): ?>
          <li><a href="admin_settings.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
          <li><a href="logout.php" class="logout-btn">Logout</a></li>
          <?php else: ?>
          <li><a href="admin_login.php" class="login-btn">Admin Login</a></li>
          <?php endif; ?>
        </ul>
      </nav>
    </div>
  </header>
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
