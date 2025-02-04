<?php
$host     = "localhost"; // Host name 
$username = "root"; // Mysql username 
$password = ""; // Mysql password 
$db_name  = "karate_club_db"; // Database name

// Connect to server and select database.
$con = mysqli_connect($host, $username, $password, $db_name);

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
	$conn = null;
}
?>

<?php
// Check if the function 'page_protect' already exists to prevent redeclaration error
if (!function_exists('page_protect')) {
    function page_protect()
    {
        // Start session if it isn't already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        global $db;
        
        /* Secure against Session Hijacking by checking user agent */
        if (isset($_SESSION['HTTP_USER_AGENT'])) {
            if ($_SESSION['HTTP_USER_AGENT'] != md5($_SERVER['HTTP_USER_AGENT'])) {
                session_destroy();
                echo "<meta http-equiv='refresh' content='0; url=../login/'>";
                exit();
            }
        }
        
        // Check session variables for authentication
        if (!isset($_SESSION['user_data']) && !isset($_SESSION['logged']) && !isset($_SESSION['auth_level'])) {
            session_destroy();
            echo "<meta http-equiv='refresh' content='0; url=../login/'>";
            exit();
        }
    }
}
?>
