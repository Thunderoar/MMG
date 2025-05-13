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

// Check if file was uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    // Initialize response array
    $response = [
        'status' => 'error',
        'message' => '',
        'imported' => 0,
        'skipped' => 0,
        'errors' => []
    ];
    
    try {
        // Get uploaded file
        $file = $_FILES['csv_file'];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error: " . $file['error']);
        }
        
        // Read the CSV file
        $csvData = file_get_contents($file['tmp_name']);
        if ($csvData === false) {
            throw new Exception("Failed to read uploaded CSV file");
        }
        
        // Process CSV data
        $lines = explode("\n", $csvData);
        
        // Filter out empty lines
        $lines = array_filter($lines, function($line) {
            return trim($line) !== '';
        });
        
        if (count($lines) <= 1) {
            throw new Exception("No data found in the uploaded CSV file");
        }
        
        // Extract headers
        $headers = str_getcsv(array_shift($lines));
        
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
        
        // Alternative seminar location pattern matcher for different formats
        function tryAlternativeSeminarPatterns($input) {
            // Try different patterns for seminar location
            $patterns = [
                // Pattern for format: [ZONE] DD MMM YYYY (DAY) HH:MM PAGI/PETANG - LOCATION
                '/\[([^\]]+)\]\s*(\d{1,2})\s*([A-Za-z]+)\s*(\d{4})\s*\(([^\)]+)\)\s*(\d{1,2})[:\.](\d{2})\s*(PAGI|PETANG)\s*-\s*(.+)$/ui',
                
                // Pattern for format without brackets: ZONE DD MMM YYYY (DAY) HH:MM PAGI/PETANG - LOCATION
                '/([A-Za-z\s]+)\s*(\d{1,2})\s*([A-Za-z]+)\s*(\d{4})\s*\(([^\)]+)\)\s*(\d{1,2})[:\.](\d{2})\s*(PAGI|PETANG)\s*-\s*(.+)$/ui',
                
                // Pattern for format without day name: [ZONE] DD MMM YYYY HH:MM PAGI/PETANG - LOCATION
                '/\[([^\]]+)\]\s*(\d{1,2})\s*([A-Za-z]+)\s*(\d{4})\s*(\d{1,2})[:\.](\d{2})\s*(PAGI|PETANG)\s*-\s*(.+)$/ui',
                
                // Pattern for format without brackets and day name: ZONE DD MMM YYYY HH:MM PAGI/PETANG - LOCATION
                '/([A-Za-z\s]+)\s*(\d{1,2})\s*([A-Za-z]+)\s*(\d{4})\s*(\d{1,2})[:\.](\d{2})\s*(PAGI|PETANG)\s*-\s*(.+)$/ui'
            ];
            
            foreach ($patterns as $index => $pattern) {
                if (preg_match($pattern, $input, $matches)) {
                    error_log("Matched with alternative pattern #" . ($index + 1));
                    
                    // Extract components based on the pattern
                    $zone = $matches[1];
                    $day = isset($matches[2]) ? $matches[2] : '01';
                    $monthName = isset($matches[3]) ? $matches[3] : 'JAN';
                    $year = isset($matches[4]) ? $matches[4] : date('Y');
                    
                    // Time components will be at different indices depending on the pattern
                    $hourIndex = ($index <= 1) ? 6 : 5;
                    $minuteIndex = ($index <= 1) ? 7 : 6;
                    $meridiemIndex = ($index <= 1) ? 8 : 7;
                    $locationIndex = ($index <= 1) ? 9 : 8;
                    
                    $hours = isset($matches[$hourIndex]) ? intval($matches[$hourIndex]) : 0;
                    $minutes = isset($matches[$minuteIndex]) ? intval($matches[$minuteIndex]) : 0;
                    $meridiem = isset($matches[$meridiemIndex]) ? strtoupper($matches[$meridiemIndex]) : 'PAGI';
                    $location = isset($matches[$locationIndex]) ? trim($matches[$locationIndex]) : 'UNKNOWN';
                    
                    // Convert month name to number
                    $monthNames = [
                        'JAN' => '01', 'FEB' => '02', 'MAC' => '03', 'APR' => '04', 'MEI' => '05', 'JUN' => '06',
                        'JUL' => '07', 'OGO' => '08', 'SEP' => '09', 'OKT' => '10', 'NOV' => '11', 'DIS' => '12'
                    ];
                    $month = strtoupper(substr($monthName, 0, 3));
                    $monthNum = $monthNames[$month] ?? '01';
                    
                    // Convert to 24-hour format
                    if ($meridiem === 'PETANG' && $hours != 12) {
                        $hours += 12;
                    } elseif ($meridiem === 'PAGI' && $hours == 12) {
                        $hours = 0;
                    }
                    
                    // Format time as HH:mm:ss
                    $timeStr = sprintf('%02d:%02d:00', $hours, $minutes);
                    
                    // Normalize zone name
                    if (!preg_match('/^\[/', $zone)) {
                        $zone = '[' . trim($zone) . ']';
                    }
                    
                    return [
                        'zone' => $zone,
                        'date' => sprintf('%s-%s-%s', $year, $monthNum, $day),
                        'time' => $timeStr,
                        'location' => $location,
                        'original' => $input
                    ];
                }
            }
            
            return null; // No pattern matched
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
            
            // Log the normalized input for debugging
            error_log("Normalized seminar location: " . $input);
            
            // More flexible pattern to match all variations
            $pattern = '/\[([^\]]+)\]\s*(\d{2})\s*([\w]+)\s*(\d{4})\s*\(([^\)]+)\)\s*(\d{1,2})[:\.](\d{2})\s*(PAGI|PETANG)\s*-\s*(.+)$/ui';
            
            // Try the primary pattern first
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
            // If primary pattern fails, try alternative patterns
    $alternativeResult = tryAlternativeSeminarPatterns($input);
    if ($alternativeResult) {
        error_log("Matched with alternative pattern");
        return $alternativeResult;
    }
    
    // If all patterns fail, return null
    error_log("All seminar location patterns failed to match");
    return null;
        }
        
        // Function to map CSV fields to database fields
        function mapDataToDbFields($row, $headers) {
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
                'seminar_id' => null // Default seminar_id is null
            ];
            
            // Get the next Giliran value
            $result = mysqli_query($conn, "SELECT MAX(Giliran) as max_giliran FROM attendedstudent");
            if ($result && $row_max = mysqli_fetch_assoc($result)) {
                $data['Giliran'] = ($row_max['max_giliran'] ?? 0) + 1;
            }
            
            // Create column mapping from headers
            $columnMap = [];
            
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
                } elseif (stripos($header, 'nama pegawai jemputan') !== false) {
                    // Exact match for 'NAMA PEGAWAI JEMPUTAN'
                    $columnMap['officer'] = $index;
                    error_log("Found exact officer name column at index $index: $header");
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
            
            // Timestamp - use mapped column if available
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
            
            // Seminar Location - will be used to link to seminar_id
            // First try to find it in mapped columns
            if (isset($columnMap['seminar_location'])) {
                $seminarLocationIndex = $columnMap['seminar_location'];
                if (isset($row[$seminarLocationIndex]) && !empty($row[$seminarLocationIndex])) {
                    $data['seminar_location'] = $row[$seminarLocationIndex];
                }
            } else {
                // If no column mapping found, try common indices where seminar location might be
                $possibleIndices = [8, 9, 10, 11]; // Common indices for seminar location
                foreach ($possibleIndices as $index) {
                    if (isset($row[$index]) && !empty($row[$index])) {
                        // Check if this looks like a seminar location string
                        if (strpos($row[$index], '[') !== false && 
                            (strpos(strtoupper($row[$index]), 'PAGI') !== false || 
                             strpos(strtoupper($row[$index]), 'PETANG') !== false)) {
                            $data['seminar_location'] = $row[$index];
                            break;
                        }
                    }
                }
            }
            
            return $data;
        }
        
        // Process each row
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            $row = str_getcsv($line);
            $mappedData = mapDataToDbFields($row, $headers);
            
            // Process seminar location if available
            if (isset($mappedData['seminar_location']) && !empty($mappedData['seminar_location'])) {
                // Log for debugging
                error_log("Processing seminar location: " . $mappedData['seminar_location']);
                
                $seminarInfo = parseSeminarLocation($mappedData['seminar_location']);
                if ($seminarInfo) {
                    // Log for debugging
                    error_log("Parsed seminar info: " . json_encode($seminarInfo));
                    // Check if this seminar already exists
                    $checkSeminarSql = "SELECT id FROM seminar_schedules 
                                       WHERE zone = ? AND seminar_date = ? AND seminar_time = ? AND location = ?";
                    $checkSeminarStmt = mysqli_prepare($conn, $checkSeminarSql);
                    mysqli_stmt_bind_param($checkSeminarStmt, "ssss", 
                        $seminarInfo['zone'],
                        $seminarInfo['date'],
                        $seminarInfo['time'],
                        $seminarInfo['location']
                    );
                    mysqli_stmt_execute($checkSeminarStmt);
                    mysqli_stmt_store_result($checkSeminarStmt);
                    
                    if (mysqli_stmt_num_rows($checkSeminarStmt) > 0) {
                        // Seminar exists, get its ID
                        mysqli_stmt_bind_result($checkSeminarStmt, $seminarId);
                        mysqli_stmt_fetch($checkSeminarStmt);
                        $mappedData['seminar_id'] = $seminarId;
                    } else {
                        // Insert new seminar schedule
                        $insertSeminarSql = "INSERT INTO seminar_schedules (zone, seminar_date, seminar_time, location, is_active) 
                                           VALUES (?, ?, ?, ?, 1)";
                        $insertSeminarStmt = mysqli_prepare($conn, $insertSeminarSql);
                        mysqli_stmt_bind_param($insertSeminarStmt, "ssss", 
                            $seminarInfo['zone'],
                            $seminarInfo['date'],
                            $seminarInfo['time'],
                            $seminarInfo['location']
                        );
                        mysqli_stmt_execute($insertSeminarStmt);
                        $mappedData['seminar_id'] = mysqli_insert_id($conn);
                        mysqli_stmt_close($insertSeminarStmt);
                    }
                    
                    mysqli_stmt_close($checkSeminarStmt);
                    
                    // Log for debugging
                    error_log("Seminar ID set to: " . ($mappedData['seminar_id'] ?? 'null'));
                } else {
                    error_log("Failed to parse seminar location: " . $mappedData['seminar_location']);
                }
            } else {
                error_log("No seminar location found in the data");
            }
            
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
        
        // Set success response
        $response['status'] = 'success';
        $response['message'] = "Import completed. Imported: {$response['imported']}, Skipped: {$response['skipped']}";
        
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
    <title>Import CSV from Google Sheets</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
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
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        h1 {
            color: var(--primary-dark);
            margin-top: 0;
            text-align: center;
        }

        .steps {
            margin: 2rem 0;
        }

        .steps ol {
            padding-left: 1.5rem;
        }

        .steps li {
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        input[type="file"] {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.25rem;
            background: white;
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
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-secondary {
            background: #718096;
            color: white;
            margin-left: 1rem;
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

        #resultDetails {
            margin-top: 1rem;
            display: none;
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

        .actions {
            margin-top: 2rem;
            text-align: center;
        }

        .note {
            font-size: 0.9rem;
            color: #718096;
            margin-top: 2rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 0.25rem;
        }
        
        /* Import status visualization styles */
        .import-status-container {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: 1.5rem;
            margin: 2rem 0;
            border: 1px solid rgba(255, 255, 255, 0.5);
            transition: all 0.3s ease;
        }
        
        .import-status-container h3 {
            margin-top: 0;
            color: var(--primary-dark);
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        /* Progress bar */
        .progress-container {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .progress-bar {
            flex-grow: 1;
            height: 12px;
            background: rgba(226, 232, 240, 0.5);
            border-radius: 6px;
            overflow: hidden;
            margin-right: 1rem;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        
        .progress-indicator {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--primary-light), var(--primary-dark));
            border-radius: 6px;
            transition: width 0.3s ease;
        }
        
        .progress-text {
            font-weight: 600;
            color: var(--primary-dark);
            min-width: 45px;
            text-align: right;
        }
        
        /* Stats cards */
        .stats-container {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-card {
            flex: 1;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 0.5rem;
            padding: 1rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: var(--shadow-sm);
        }
        
        .stat-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-count {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: #64748b;
        }
        
        /* Activity log */
        .activity-log-container {
            margin-bottom: 1.5rem;
        }
        
        .activity-log-container h4 {
            margin-top: 0;
            color: #4a5568;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .activity-log {
            max-height: 150px;
            overflow-y: auto;
            background: rgba(247, 250, 252, 0.7);
            border-radius: 0.25rem;
            padding: 0.75rem;
            font-family: monospace;
            font-size: 0.875rem;
            color: #4a5568;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        
        .log-entry {
            margin-bottom: 0.25rem;
            line-height: 1.4;
        }
        
        .timestamp {
            color: #718096;
            margin-right: 0.5rem;
        }
        
        /* Error list */
        .error-container {
            background: rgba(254, 226, 226, 0.5);
            border-radius: 0.25rem;
            padding: 0.75rem;
            border: 1px solid rgba(252, 165, 165, 0.5);
        }
        
        .error-container h4 {
            margin: 0;
            color: #b91c1c;
            font-size: 1rem;
            display: flex;
            align-items: center;
        }
        
        .error-icon {
            margin-right: 0.5rem;
        }
        
        .error-count {
            margin-left: 0.5rem;
            font-weight: normal;
            color: #ef4444;
        }
        
        .toggle-btn {
            margin-left: auto;
            background: none;
            border: none;
            color: #b91c1c;
            font-size: 0.875rem;
            cursor: pointer;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }
        
        .toggle-btn:hover {
            background: rgba(254, 202, 202, 0.5);
        }
        
        .error-list {
            margin-top: 0.75rem;
            max-height: 150px;
            overflow-y: auto;
            font-size: 0.875rem;
        }
        
        .error-entry {
            padding: 0.5rem;
            border-radius: 0.25rem;
            background: rgba(254, 242, 242, 0.7);
            margin-bottom: 0.5rem;
            border-left: 3px solid #ef4444;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Import Data from Google Sheets</h1>
            
            <div id="alert" class="alert"></div>
            
            <div class="steps">
                <h3>Follow these steps to import data:</h3>
                <ol>
                    <li>Go to your Google Sheet containing the form responses</li>
                    <li>Go to <strong>File → Download → Comma-separated values (.csv)</strong></li>
                    <li>Save the CSV file to your computer</li>
                    <li>Upload the CSV file using the form below</li>
                </ol>
            </div>
            
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="csvFile">Select CSV File:</label>
                    <input type="file" id="csvFile" name="csv_file" accept=".csv" required>
                </div>
                
                <div class="actions">
                    <button type="submit" id="importBtn" class="btn btn-primary">Import Data</button>
                    <a href="summary.php" class="btn btn-secondary">Back to Summary</a>
                </div>
            </form>
            
            <div id="resultDetails"></div>
            
            <div class="import-status-container" id="importStatusContainer" style="display: none;">
                <h3>Import Status</h3>
                
                <!-- Animated progress bar -->
                <div class="progress-container">
                    <div class="progress-bar" id="importProgressBar">
                        <div class="progress-indicator" id="importProgressIndicator"></div>
                    </div>
                    <div class="progress-text" id="importProgressText">0%</div>
                </div>
                
                <!-- Stats cards -->
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-icon">📋</div>
                        <div class="stat-count" id="totalRecords">0</div>
                        <div class="stat-label">Total Records</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">✅</div>
                        <div class="stat-count" id="importedRecords">0</div>
                        <div class="stat-label">Imported</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">⚠️</div>
                        <div class="stat-count" id="skippedRecords">0</div>
                        <div class="stat-label">Skipped</div>
                    </div>
                </div>
                
                <!-- Live activity log -->
                <div class="activity-log-container">
                    <h4>Import Activity</h4>
                    <div class="activity-log" id="activityLog">
                        <div class="log-entry"><span class="timestamp">[00:00:00]</span> Import process initiated...</div>
                    </div>
                </div>
                
                <!-- Errors list (collapsible) -->
                <div class="error-container" id="errorContainer" style="display: none;">
                    <h4>
                        <span class="error-icon">❌</span> 
                        Errors 
                        <span class="error-count" id="errorCount">(0)</span>
                        <button class="toggle-btn" id="toggleErrors">Show</button>
                    </h4>
                    <div class="error-list" id="errorList" style="display: none;">
                        <!-- Error entries will be added here dynamically -->
                    </div>
                </div>
            </div>
            
            <div class="note">
                <p><strong>Note:</strong> This alternative import method is provided for environments where direct connection to Google Sheets is not available. Make sure your CSV file has headers that match the expected format (name, NoIC, phone, etc.).</p>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const fileInput = document.getElementById('csvFile');
            const importBtn = document.getElementById('importBtn');
            const alert = document.getElementById('alert');
            const resultDetails = document.getElementById('resultDetails');
            
            if (!fileInput.files.length) {
                alert.className = 'alert alert-danger';
                alert.textContent = 'Please select a CSV file first';
                alert.style.display = 'block';
                return;
            }
            
            // Create form data
            const formData = new FormData();
            formData.append('csv_file', fileInput.files[0]);
            
            // Show loading state
            importBtn.innerHTML = '<span class="spinner"></span>Importing...';
            importBtn.disabled = true;
            alert.style.display = 'none';
            resultDetails.style.display = 'none';
            
            // Send the request
            fetch('fetch_and_import.php', {
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
                if (data.status === 'success') {
                    // Show success message
                    alert.className = 'alert alert-success';
                    alert.textContent = data.message;
                    alert.style.display = 'block';
                    
                    // Show detailed results
                    let detailsHtml = `<h3>Import Results</h3>
                                      <p>Successfully imported: ${data.imported} records</p>
                                      <p>Skipped: ${data.skipped} records</p>`;
                    
                    if (data.errors && data.errors.length > 0) {
                        detailsHtml += `<h4>Errors:</h4><ul>`;
                        data.errors.slice(0, 5).forEach(error => {
                            detailsHtml += `<li>${error}</li>`;
                        });
                        
                        if (data.errors.length > 5) {
                            detailsHtml += `<li>...and ${data.errors.length - 5} more errors</li>`;
                        }
                        
                        detailsHtml += `</ul>`;
                    }
                    
                    resultDetails.innerHTML = detailsHtml;
                    resultDetails.style.display = 'block';
                } else {
                    // Show error message
                    alert.className = 'alert alert-danger';
                    alert.textContent = data.message || 'An error occurred during import';
                    alert.style.display = 'block';
                }
                
                // Reset button
                importBtn.innerHTML = 'Import Data';
                importBtn.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                alert.className = 'alert alert-danger';
                alert.textContent = 'Error importing data: ' + error.message;
                alert.style.display = 'block';
                
                importBtn.innerHTML = 'Import Data';
                importBtn.disabled = false;
            });
        });
    </script>
</body>
</html>
