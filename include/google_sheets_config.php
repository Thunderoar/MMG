<?php
/*
 GOOGLE SHEETS INTEGRATION CONFIGURATION
 ======================================
 IMPORTANT: For this integration to work, you must:
 1. Open your Google Sheet
 2. Go to File → Share → Publish to the web
 3. Select the sheet you want to publish (e.g., "Form Responses 1")
 4. Click the "Publish" button
 5. Also click on "Share" in the top-right corner and set to "Anyone with the link can view"
*/

// ========== CONFIGURATION SETTINGS ==========

// Google Sheet ID from the URL
// For https://docs.google.com/spreadsheets/d/1-M25ODG9kRL3Xq5G4x8vMz5wMHdHCxlKf0xOA91Q0qk/edit...
define('GOOGLE_SHEET_ID', '1-M25ODG9kRL3Xq5G4x8vMz5wMHdHCxlKf0xOA91Q0qk');

// The sheet GID from the URL (the number after #gid= in the URL)
// If you're not sure, leave it as 0 (the first sheet)
define('GOOGLE_SHEET_GID', '0');

// ========== EXPORT URLs ==========
// These URLs are for accessing the published sheet data

// Direct published CSV URL (provided by user)
define('GOOGLE_SHEET_CSV_URL', 'https://docs.google.com/spreadsheets/d/e/2PACX-1vRFXImveioEh8Nr-KFR-y2bGb9oHTUxWs8ALh2zJrgCPawZoHhw2MVV_X1OxHDBCAAhbA8cUYjV6pdg/pub?gid=1805915212&single=true&output=csv');

// Backup URL (in case the main one fails)
define('GOOGLE_SHEET_PUB_URL', 'https://docs.google.com/spreadsheets/d/e/2PACX-1vRFXImveioEh8Nr-KFR-y2bGb9oHTUxWs8ALh2zJrgCPawZoHhw2MVV_X1OxHDBCAAhbA8cUYjV6pdg/pub?output=csv');

?>
