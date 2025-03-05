<?php
/**
 * Helper functions for NOIC (National Organization Identity Code) handling
 */

/**
 * Cleans and formats a NOIC string by removing all non-numeric characters
 * @param string $noic The NOIC to clean
 * @return string The cleaned NOIC containing only numbers
 */
function cleanNoic($noic) {
    return trim(preg_replace('/[^0-9]/', '', $noic));
}

/**
 * Formats a clean NOIC into display format (000000-00-0000)
 * @param string $noic The clean NOIC (must be 12 digits)
 * @return string The formatted NOIC
 */
function formatNoic($noic) {
    $clean = cleanNoic($noic);
    if (strlen($clean) !== 12) {
        return $noic; // Return original if invalid
    }
    return substr($clean, 0, 6) . '-' . substr($clean, 6, 2) . '-' . substr($clean, 8, 4);
}

/**
 * Validates if a NOIC is in the correct format
 * @param string $noic The NOIC to validate
 * @return bool True if valid, false otherwise
 */
function isValidNoic($noic) {
    $clean = cleanNoic($noic);
    return strlen($clean) === 12 && is_numeric($clean);
}
?>
