<?php
// Set to show all errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'include/google_sheets_config.php';

echo "<h1>Google Sheets Debugging Tool</h1>";

// Add some basic styling
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .url-test { margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
    .success { background-color: #d4edda; }
    .error { background-color: #f8d7da; }
    pre { background: #f8f9fa; padding: 10px; overflow: auto; max-height: 300px; }
</style>";

// Function to test a URL and display results
function testUrl($urlName, $url) {
    echo "<div class='url-test'>";
    echo "<h3>Testing: $urlName</h3>";
    echo "<p>URL: " . htmlspecialchars($url) . "</p>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    
    // Add some browser-like headers
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.5',
        'Connection: keep-alive',
        'Upgrade-Insecure-Requests: 1'
    ]);
    
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $dataLength = strlen($data);
    
    if ($data !== false && $httpCode == 200 && $dataLength > 0) {
        echo "<div class='success'>";
        echo "<p>✅ Success! HTTP Code: $httpCode</p>";
        echo "<p>Content Type: $contentType</p>";
        echo "<p>Data Length: $dataLength bytes</p>";
        
        // Show a sample of the data
        if ($dataLength > 0) {
            echo "<h4>First 200 characters of data:</h4>";
            echo "<pre>" . htmlspecialchars(substr($data, 0, 200)) . "</pre>";
            
            // If it looks like HTML, check for certain indicators
            if (strpos($contentType, 'html') !== false || strpos($data, '<html') !== false) {
                if (strpos($data, 'Access denied') !== false || strpos($data, 'permission') !== false) {
                    echo "<p>⚠️ Warning: Response may indicate access restrictions.</p>";
                }
                if (strpos($data, 'login') !== false || strpos($data, 'sign in') !== false) {
                    echo "<p>⚠️ Warning: Response may be a login page.</p>";
                }
            }
            
            // Show line count for CSV-like data
            if (strpos($contentType, 'csv') !== false || strpos($contentType, 'text/plain') !== false) {
                $lines = explode("\n", $data);
                $lineCount = count($lines);
                echo "<p>Line count: $lineCount</p>";
                
                if ($lineCount > 1) {
                    echo "<h4>First 3 lines:</h4>";
                    echo "<pre>";
                    for ($i = 0; $i < min(3, $lineCount); $i++) {
                        echo htmlspecialchars($lines[$i]) . "\n";
                    }
                    echo "</pre>";
                }
            }
        }
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<p>❌ Error! HTTP Code: $httpCode</p>";
        if (!empty($error)) {
            echo "<p>cURL Error: " . htmlspecialchars($error) . "</p>";
        }
        if ($dataLength > 0) {
            echo "<h4>First 200 characters of response:</h4>";
            echo "<pre>" . htmlspecialchars(substr($data, 0, 200)) . "</pre>";
        }
        echo "</div>";
    }
    
    curl_close($ch);
    echo "</div>";
}

// URLs to test
$urlsToTest = [
    'CSV URL (Sheet ID + GID)' => GOOGLE_SHEET_CSV_URL,
    'Alternative CSV URL' => 'https://docs.google.com/spreadsheets/d/' . GOOGLE_SHEET_ID . '/export?format=csv',
    'TSV URL' => GOOGLE_SHEET_TSV_URL,
    'PUB URL' => GOOGLE_SHEET_PUB_URL,
    'HTML View URL' => 'https://docs.google.com/spreadsheets/d/' . GOOGLE_SHEET_ID . '/edit#gid=' . GOOGLE_SHEET_GID,
    'Pub HTML URL' => GOOGLE_SHEET_HTML_URL
];

// Test each URL
foreach ($urlsToTest as $name => $url) {
    testUrl($name, $url);
}

// Show configuration info
echo "<h2>Configuration Information</h2>";
echo "<ul>";
echo "<li>Sheet ID: " . htmlspecialchars(GOOGLE_SHEET_ID) . "</li>";
echo "<li>Sheet GID: " . htmlspecialchars(GOOGLE_SHEET_GID) . "</li>";
echo "<li>Sheet Name: " . htmlspecialchars(GOOGLE_SHEET_TAB) . "</li>";
echo "</ul>";

// Important information about Google Sheet settings
echo "<h2>Important Google Sheet Settings</h2>";
echo "<p>For the import to work, your Google Sheet must be:</p>";
echo "<ol>";
echo "<li><strong>Published to the web</strong> - In Google Sheets, go to File → Share → Publish to the web</li>";
echo "<li><strong>Accessible</strong> - Set sharing settings to 'Anyone with the link can view'</li>";
echo "</ol>";
echo "<p>This debugging page can help identify if the issue is related to permissions or access to the Google Sheet.</p>";
?>
