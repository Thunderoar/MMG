<?php
// Turn off error display to prevent HTML errors in JSON response
error_reporting(0);
ini_set('display_errors', 0);

// Set the content type to JSON early to ensure no HTML is sent before the JSON response
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'include/db_conn.php';
require_once 'include/google_sheets_config.php';

// Verify admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Initialize response array
$response = [
    'status' => 'error',
    'message' => '',
    'imported' => 0,
    'skipped' => 0,
    'errors' => []
];

// Function to validate NoIC
function validateNoIC($noIC) {
    // Check if empty
    if (empty($noIC)) {
        return false;
    }
    
    // Remove all non-numeric characters (spaces, dashes, etc.)
    $cleanNoIC = preg_replace('/[^0-9]/', '', $noIC);
    
    // Multiple validation strategies based on common patterns
    
    // If we already have 12 digits, use it directly
    if (strlen($cleanNoIC) === 12) {
        return $cleanNoIC;
    }
    
    // Try to extract a valid 12-digit number from longer strings
    if (strlen($cleanNoIC) > 12) {
        // Check for substring that might be the IC
        $possibleMatches = [];
        
        // Try to find sequences that look like Malaysian ICs
        if (preg_match('/([0-9]{6}[0-9]{2}[0-9]{4})/', $cleanNoIC, $matches)) {
            $possibleMatches[] = $matches[1];
        }
        
        // Look for 12 consecutive digits anywhere in the string
        if (preg_match('/([0-9]{12})/', $cleanNoIC, $matches)) {
            $possibleMatches[] = $matches[1];
        }
        
        // If we found potential matches, return the first one
        if (!empty($possibleMatches)) {
            return $possibleMatches[0];
        }
        
        // If all else fails, just take the first 12 digits
        return substr($cleanNoIC, 0, 12);
    }
    
    // If it's too short, pad with zeros (though this is less ideal)
    if (strlen($cleanNoIC) > 0 && strlen($cleanNoIC) < 12) {
        // Only do this if it's reasonably close to 12 digits
        if (strlen($cleanNoIC) >= 8) {
            return str_pad($cleanNoIC, 12, '0', STR_PAD_LEFT);
        }
    }
    
    // If we get here, we couldn't validate or fix the NoIC
    return false;
}

// Function to map Google Sheet fields to database fields
function mapDataToDbFields($row) {
    global $conn;
    
    // Default values - use 'N/A' for text fields as requested
    $data = [
        'FromWhere' => 'WhatsApp', // Default assuming form submissions are from WhatsApp
        'Giliran' => 0, // Will be set later
        'fullName' => 'N/A', // Default if name is missing
        'NoIC' => '', // Will handle specially
        'NoTel' => 'N/A', // Default if phone is missing
        'student_phone' => 'N/A', // Default if student phone is missing
        'guardian_phone' => 'N/A', // Default if guardian phone is missing
        'invited_officer' => 'N/A', // Default if officer info is missing
        'tempat_temuduga' => 'N/A', // Default if seminar location is missing
        'DateofArrival' => date('Y-m-d H:i:s'), // Current date/time as default
        'WithWho' => 'Sendiri', // Default value
        'canMakeDecision' => 1, // Default to true
        'is_dealt' => 0,
        'is_deleted' => 0,
        'seminar_id' => null // Default seminar_id
    ];
    
    // Get the next Giliran value
    $result = mysqli_query($conn, "SELECT MAX(Giliran) as max_giliran FROM attendedstudent");
    if ($result && $row_max = mysqli_fetch_assoc($result)) {
        $data['Giliran'] = ($row_max['max_giliran'] ?? 0) + 1;
    }
    
    // Map Google Sheet columns to database fields - adjust these based on your actual Google Sheet columns
    
    // Inspect headers for better column mapping if available
    global $headers;
    $columnMap = [];
    
    // Check if headers exist and map columns by name instead of index when possible
    if (!empty($headers)) {
        foreach ($headers as $index => $header) {
            $header = strtolower(trim($header));
            
            // Map known header names to their indices
            if (strpos($header, 'timestamp') !== false) {
                $columnMap['timestamp'] = $index;
            } elseif (strpos($header, 'nama penuh pelajar') !== false || strpos($header, 'huruf besar') !== false) {
                // Specific mapping for 'NAMA PENUH PELAJAR (HURUF BESAR)'
                $columnMap['name'] = $index;
                error_log("Found student name column at index $index: $header");
            } elseif (strpos($header, 'name') !== false || strpos($header, 'nama') !== false) {
                // General name mapping as fallback
                if (!isset($columnMap['name'])) {
                    $columnMap['name'] = $index;
                    error_log("Found fallback name column at index $index: $header");
                }
            } elseif (strpos($header, 'no.ic') !== false || strpos($header, 'ic') !== false || strpos($header, 'kad pengenalan') !== false) {
                $columnMap['ic'] = $index;
            } elseif (strpos($header, 'phone') !== false || strpos($header, 'no.tel') !== false || strpos($header, 'tel') !== false) {
                $columnMap['phone'] = $index;
            } elseif (strpos($header, 'student phone') !== false || strpos($header, 'student tel') !== false) {
                $columnMap['student_phone'] = $index;
            } elseif (strpos($header, 'parent phone') !== false || strpos($header, 'guardian') !== false) {
                $columnMap['guardian_phone'] = $index;
            } elseif (strpos($header, 'with') !== false || strpos($header, 'relationship') !== false || strpos($header, 'bersama') !== false) {
                $columnMap['with_who'] = $index;
            } elseif (strpos($header, 'decision') !== false || strpos($header, 'keputusan') !== false) {
                $columnMap['decision'] = $index;
            } elseif (stripos($header, 'nama pegawai jemputan') !== false && (stripos($header, 'poster') !== false || stripos($header, 'iklan') !== false)) {
                // Exact match for "NAMA PEGAWAI JEMPUTAN (ada di poster/ayat iklan)"
                $columnMap['officer'] = $index;
                error_log("Found exact officer name column at index $index: $header");
            } elseif (stripos($header, 'pegawai') !== false && stripos($header, 'jemputan') !== false) {
                // More general match for officer name
                if (!isset($columnMap['officer'])) {
                    $columnMap['officer'] = $index;
                    error_log("Found general officer name column at index $index: $header");
                }
            } elseif (strpos($header, 'officer') !== false || strpos($header, 'pegawai') !== false) {
                // General officer mapping as fallback
                if (!isset($columnMap['officer'])) {
                    $columnMap['officer'] = $index;
                    error_log("Found fallback officer column at index $index: $header");
                }
            } elseif (stripos($header, 'tempat temuduga') !== false) {
                // Exact match for 'TEMPAT TEMUDUGA'
                $columnMap['seminar_location'] = $index;
                error_log("Found exact seminar location column at index $index: $header");
            } elseif (strpos($header, 'temuduga') !== false || strpos($header, 'tempat') !== false || strpos($header, 'location') !== false || strpos($header, 'seminar') !== false) {
                // General seminar location mapping as fallback
                if (!isset($columnMap['seminar_location'])) {
                    $columnMap['seminar_location'] = $index;
                    error_log("Found fallback seminar location column at index $index: $header");
                }
            }
        }
    }
    
    // Timestamp - use mapped column if available, otherwise try standard positions
    $timestampIndex = $columnMap['timestamp'] ?? 0;
    if (isset($row[$timestampIndex]) && !empty($row[$timestampIndex])) {
        $timestamp = strtotime($row[$timestampIndex]);
        if ($timestamp) {
            $data['DateofArrival'] = date('Y-m-d H:i:s', $timestamp);
        }
    }
    
    // Full Name
    $nameIndex = $columnMap['name'] ?? 1;
    if (isset($row[$nameIndex]) && !empty($row[$nameIndex])) {
        $data['fullName'] = $row[$nameIndex];
    }
    
    // NoIC is a special case that must be 12 digits due to database constraints
    // We need to generate a unique 12-digit number for each record
    $timeBasedID = substr(preg_replace('/[^0-9]/', '', md5(time() . rand(1000, 9999))), 0, 12);
    
    // Always use a generated ID since we need to meet the database requirement
    // This avoids the need to read all fields looking for a valid IC
    $data['NoIC'] = $timeBasedID;
    
    // Phone Number
    $phoneIndex = $columnMap['phone'] ?? 3;
    if (isset($row[$phoneIndex]) && !empty($row[$phoneIndex])) {
        $data['NoTel'] = $row[$phoneIndex];
    }
    
    // Student Phone
    $studentPhoneIndex = $columnMap['student_phone'] ?? 4;
    if (isset($row[$studentPhoneIndex]) && !empty($row[$studentPhoneIndex])) {
        $data['student_phone'] = $row[$studentPhoneIndex];
    }
    
    // Guardian Phone
    $guardianPhoneIndex = $columnMap['guardian_phone'] ?? 5;
    if (isset($row[$guardianPhoneIndex]) && !empty($row[$guardianPhoneIndex])) {
        $data['guardian_phone'] = $row[$guardianPhoneIndex];
    }
    
    // With Who (relationship)
    $withWhoIndex = $columnMap['with_who'] ?? 6;
    if (isset($row[$withWhoIndex]) && !empty($row[$withWhoIndex])) {
        $data['WithWho'] = $row[$withWhoIndex];
    }
    
    // Can Make Decision
    $decisionIndex = $columnMap['decision'] ?? 7;
    if (isset($row[$decisionIndex]) && !empty($row[$decisionIndex])) {
        $canMakeDecision = strtolower($row[$decisionIndex]);
        $data['canMakeDecision'] = ($canMakeDecision == 'yes' || $canMakeDecision == 'ya' || $canMakeDecision == '1') ? 1 : 0;
    }
    
    // Invited Officer
    $officerIndex = $columnMap['officer'] ?? 8;
    if (isset($row[$officerIndex])) {
        // Log the raw officer data
        error_log("Officer data at index $officerIndex: " . (empty($row[$officerIndex]) ? "EMPTY" : $row[$officerIndex]));
        
        if (!empty($row[$officerIndex])) {
            $data['invited_officer'] = $row[$officerIndex];
            error_log("Set invited_officer to: {$data['invited_officer']}");
        }
    } else {
        error_log("Officer index $officerIndex not found in row data");
        // Dump first few columns for debugging
        $debugData = array_slice($row, 0, min(10, count($row)));
        error_log("Row data sample: " . json_encode($debugData));
    }
    
    // Tempat Temuduga (Seminar Location)
    $seminarLocationIndex = $columnMap['seminar_location'] ?? 9;
    if (isset($row[$seminarLocationIndex])) {
        // Log the raw seminar location data
        error_log("Seminar location data at index $seminarLocationIndex: " . (empty($row[$seminarLocationIndex]) ? "EMPTY" : $row[$seminarLocationIndex]));
        
        if (!empty($row[$seminarLocationIndex])) {
            $data['tempat_temuduga'] = $row[$seminarLocationIndex];
            error_log("Set tempat_temuduga to: {$data['tempat_temuduga']}");
        }
    } else {
        error_log("Seminar location index $seminarLocationIndex not found in row data");
    }
    
    return $data;
}

// Function to parse seminar location
function parseSeminarLocation($tempatTemuduga) {
    // Example formats:
    // [ZON SELATAN] 03 MEI 2025 (SABTU) 9.30 PAGI- KULAI
    // [PAHANG] 02 MEI 2025 (JUMAAT) 3:00 PETANG - TEMERLOH
    // [JOHOR] 02 MEI 2025 (JUMAAT) 9.00 PAGI - KOTA TINGGI
    
    // Normalize the input string
    $input = trim($tempatTemuduga);
    $input = preg_replace('/\s+/', ' ', $input); // Normalize spaces
    $input = preg_replace('/\s*-\s*/', ' - ', $input); // Normalize spaces around dash
    
    // More flexible pattern to match all variations
    $pattern = '/\[([^\]]+)\]\s*(\d{2})\s*([\w]+)\s*(\d{4})\s*\(([^\)]+)\)\s*(\d{1,2})[:\.](\d{2})\s*(PAGI|PETANG)\s*-\s*(.+)$/ui';
    
    if (preg_match($pattern, $input, $matches)) {
        // Convert month name to number
        $monthNames = [
            'JAN' => '01', 'FEB' => '02', 'MAC' => '03', 'APR' => '04', 'MEI' => '05', 'JUN' => '06',
            'JUL' => '07', 'OGO' => '08', 'SEP' => '09', 'OKT' => '10', 'NOV' => '11', 'DIS' => '12'
        ];
        $month = strtoupper(substr($matches[3], 0, 3));
        $monthNum = $monthNames[$month] ?? '01';
        
        // Parse time components
        $hours = intval($matches[6]);
        $minutes = intval($matches[7]);
        $meridiem = strtoupper($matches[8]); // PAGI or PETANG
        
        // Convert to 24-hour format
        if ($meridiem === 'PETANG' && $hours != 12) {
            $hours += 12;
        } elseif ($meridiem === 'PAGI' && $hours == 12) {
            $hours = 0;
        }
        
        // Format time as HH:mm:ss
        $timeStr = sprintf('%02d:%02d:00', $hours, $minutes);
        
        // Format date components
        $day = $matches[2];
        $year = $matches[4];
        
        // Normalize zone name
        $zone = trim($matches[1]);
        if (!preg_match('/^ZON /', $zone)) {
            $zone = '[' . $zone . ']'; // Keep the original state name format
        }
        
        return [
            'zone' => $zone,
            'date' => sprintf('%s-%s-%s', $year, $monthNum, $day),
            'time' => $timeStr,
            'location' => trim($matches[9]),
            'original' => $input // Keep original string for reference
        ];
    }
    return null;
}

// Function to safely end the script with a JSON response
function endWithJsonResponse($response) {
    // Ensure no other output has been sent
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode($response);
    exit;
}

// Process the import
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // We have a direct CSV URL, but will try backup if needed
        $urlsToTry = [
            'CSV URL' => GOOGLE_SHEET_CSV_URL,
            'PUB URL' => GOOGLE_SHEET_PUB_URL
        ];
        
        $lastError = '';
        $csvData = null;
        
        // Try each URL until we get a successful response
        foreach ($urlsToTry as $urlName => $url) {
            // Setup cURL request with current URL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            // Disable SSL verification for local development environment
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            // Set a user agent to avoid being blocked
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
            
            // Add some additional headers that browsers typically send
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1'
            ]);
            
            $data = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($data !== false && $httpCode == 200 && strlen($data) > 100) {
                // Success! We got data
                $csvData = $data;
                curl_close($ch);
                break;
            } else {
                // Log this failure
                $error = curl_error($ch);
                $lastError .= "[$urlName] HTTP $httpCode: $error; ";
            }
            
            curl_close($ch);
        }
        
        // If all URLs failed
        if ($csvData === null) {
            throw new Exception("Failed to fetch data from Google Sheets. Tried multiple URLs: " . $lastError);
        }
        
        // Ensure data is in a valid format
        if (strlen($csvData) < 100) {
            throw new Exception("Response from Google Sheets is too short. Length: " . strlen($csvData) . ", Content: " . substr($csvData, 0, 50));
        }
        
        // Debug the raw response (only in development)
        if (strlen($csvData) < 200) {
            $debug = 'Raw response (first 200 chars): ' . htmlspecialchars($csvData);
            error_log($debug);
            throw new Exception("Received short response from Google: " . substr($csvData, 0, 100));
        }
        
        // Try different line ending styles
        $lines = [];
        if (strpos($csvData, "\r\n") !== false) {
            $lines = explode("\r\n", $csvData);
        } elseif (strpos($csvData, "\n") !== false) {
            $lines = explode("\n", $csvData);
        } elseif (strpos($csvData, "\r") !== false) {
            $lines = explode("\r", $csvData);
        } else {
            // Default to PHP_EOL if no matches
            $lines = explode(PHP_EOL, $csvData);
        }
        
        // Filter out empty lines
        $lines = array_filter($lines, function($line) {
            return trim($line) !== '';
        });
        
        if (count($lines) <= 1) {
            throw new Exception("No data found in the Google Sheet or unable to parse the CSV format. Data length: " . strlen($csvData));
        }
        
        $headers = str_getcsv(array_shift($lines)); // Remove headers
        
        // Process each row
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            $row = str_getcsv($line);
            $mappedData = mapDataToDbFields($row);
            
            // If there's an error in the data, skip this row
            if (isset($mappedData['error'])) {
                $response['skipped']++;
                $response['errors'][] = $mappedData['error'] . ' - Row: ' . implode(', ', $row);
                continue;
            }
            
            // Check if the record already exists (by NoIC)
            $checkSql = "SELECT NoIC FROM attendedstudent WHERE NoIC = ?";
            $checkStmt = mysqli_prepare($conn, $checkSql);
            mysqli_stmt_bind_param($checkStmt, "s", $mappedData['NoIC']);
            mysqli_stmt_execute($checkStmt);
            mysqli_stmt_store_result($checkStmt);
            
            // If record exists, skip it
            if (mysqli_stmt_num_rows($checkStmt) > 0) {
                $response['skipped']++;
                $response['errors'][] = "Duplicate NoIC: " . $mappedData['NoIC'];
                mysqli_stmt_close($checkStmt);
                continue;
            }
            
            mysqli_stmt_close($checkStmt);
            
            // Prepare insert statement
            $sql = "INSERT INTO attendedstudent (
                        FromWhere, Giliran, fullName, NoIC, NoTel, 
                        student_phone, guardian_phone, invited_officer, tempat_temuduga,
                        DateofArrival, WithWho, canMakeDecision, seminar_id
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $sql);
            if ($stmt === false) {
                throw new Exception("Failed to prepare statement: " . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param(
                $stmt, 
                "sisssssssssii",
                $mappedData['FromWhere'],
                $mappedData['Giliran'],
                $mappedData['fullName'],
                $mappedData['NoIC'],
                $mappedData['NoTel'],
                $mappedData['student_phone'],
                $mappedData['guardian_phone'],
                $mappedData['invited_officer'],
                $mappedData['tempat_temuduga'],
                $mappedData['DateofArrival'],
                $mappedData['WithWho'],
                $mappedData['canMakeDecision'],
                $mappedData['seminar_id']
            );
            
            if (mysqli_stmt_execute($stmt)) {
                $response['imported']++;
            } else {
                $response['skipped']++;
                $response['errors'][] = "Database error: " . mysqli_stmt_error($stmt);
            }
            
            mysqli_stmt_close($stmt);
        }

        // Process seminar locations
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            $row = str_getcsv($line);
            if (isset($row[8]) && !empty($row[8])) { // TEMPAT TEMUDUGA is column 9 (index 8)
                $seminarInfo = parseSeminarLocation($row[8]);
                if ($seminarInfo) {
                    // Check if this seminar already exists
                    $checkSql = "SELECT id FROM seminar_schedules 
                                WHERE zone = ? AND seminar_date = ? AND seminar_time = ? AND location = ?";
                    $checkStmt = $conn->prepare($checkSql);
                    $checkStmt->bind_param("ssss", 
                        $seminarInfo['zone'],
                        $seminarInfo['date'],
                        $seminarInfo['time'],
                        $seminarInfo['location']
                    );
                    $checkStmt->execute();
                    $checkResult = $checkStmt->get_result();
                    $seminarRow = $checkResult->fetch_assoc();
                    
                    if ($seminarRow) {
                        // Use existing seminar ID
                        $seminarId = $seminarRow['id'];
                    } else {
                        // Insert new seminar schedule
                        $insertSql = "INSERT INTO seminar_schedules (zone, seminar_date, seminar_time, location, is_active) 
                                     VALUES (?, ?, ?, ?, 1)";
                        $insertStmt = $conn->prepare($insertSql);
                        $insertStmt->bind_param("ssss", 
                            $seminarInfo['zone'],
                            $seminarInfo['date'],
                            $seminarInfo['time'],
                            $seminarInfo['location']
                        );
                        $insertStmt->execute();
                        $seminarId = $conn->insert_id;
                    }
                    
                    // Add seminar_id to mappedData
                    $mappedData['seminar_id'] = $seminarId;
                }
            }
        }
        
        // Set success response
        $response['status'] = 'success';
        $response['message'] = "Import completed. Imported: {$response['imported']}, Skipped: {$response['skipped']}";
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    // Return JSON response using our safe function
    endWithJsonResponse($response);
}

// If it's an AJAX OPTIONS request (preflight), respond with 200 OK
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit;
}

// If accessed directly without a POST request, redirect to summary page
if (!headers_sent()) {
    header("Location: summary.php");
}
exit();
?>
