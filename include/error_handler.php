<?php
/**
 * Helper functions for error handling and database operations
 */

/**
 * Handle database errors and rollback transactions
 * @param string $operation Description of the operation that failed
 * @param string $identifier Identifier (like NOIC) related to the error
 * @return void
 */
function handleErrorAndRollback($operation, $identifier) {
    global $conn;
    
    // Log the error
    $errorMessage = "Error during $operation for ID: $identifier";
    error_log($errorMessage);
    
    // Display user-friendly error message
    echo "<head>
            <script>
                alert('An error occurred while processing your request. Please try again.');
                window.location.href = 'index.php';
            </script>
          </head>";
}

/**
 * Validate NOIC format
 * @param string $noic NOIC to validate
 * @return bool True if valid, false otherwise
 */
function validateNoIC($noic) {
    // Remove any non-digit characters
    $cleaned = preg_replace('/[^0-9]/', '', $noic);
    return strlen($cleaned) === 12;
}

/**
 * Clean and format input data
 * @param string $input Input to clean
 * @return string Cleaned input
 */
function cleanInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
?>
