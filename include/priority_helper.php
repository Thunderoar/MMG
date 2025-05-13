<?php
/**
 * Helper functions for priority handling and sorting
 */

if (!function_exists('getPriorityInfo')) {
    /**
     * Get priority information based on entry method, accompaniment and decision-making ability
     * @param string $withWho Who is accompanying the student
     * @param bool $canMakeDecision Whether the student can make decisions
     * @param string $fromWhere Entry method (WhatsApp/Walk-in)
     * @return array Priority information with 'priority' (1-8) and 'description'
     */
    function getPriorityInfo($withWho, $canMakeDecision, $fromWhere) {
        // WhatsApp priorities (1-4)
        if ($fromWhere === 'WhatsApp') {
            switch(true) {
                case $withWho === 'Ibu Bapa':
                    return [
                        'priority' => 1,
                        'description' => 'Tinggi'
                    ];
                case $withWho === 'Rakan / Saudara':
                    return [
                        'priority' => 2,
                        'description' => 'Sederhana'
                    ];
                case $withWho === 'Sendiri' && $canMakeDecision:
                    return [
                        'priority' => 3,
                        'description' => 'Biasa'
                    ];
                default: // Sendiri & !canMakeDecision
                    return [
                        'priority' => 4,
                        'description' => 'Rendah'
                    ];
            }
        }
        // Walk-in priorities (5-8)
        else {
            switch(true) {
                case $withWho === 'Ibu Bapa':
                    return [
                        'priority' => 5,
                        'description' => 'Tinggi'
                    ];
                case $withWho === 'Rakan / Saudara':
                    return [
                        'priority' => 6,
                        'description' => 'Sederhana'
                    ];
                case $withWho === 'Sendiri' && $canMakeDecision:
                    return [
                        'priority' => 7,
                        'description' => 'Biasa'
                    ];
                default: // Sendiri & !canMakeDecision
                    return [
                        'priority' => 8,
                        'description' => 'Rendah'
                    ];
            }
        }
    }
}

/**
 * Sort entries by priority first, then by timestamp
 * @param array $entries Array of entries to sort
 * @param string $timestampField The name of the timestamp field to use for secondary sorting
 * @param string $priorityField The name of the field containing the priority number (1-8)
 * @return array Sorted array of entries
 */
function sortEntriesByPriorityAndTimestamp($entries, $timestampField = 'timestamp', $priorityField = 'priority') {
    // Define a custom comparison function
    usort($entries, function($a, $b) use ($timestampField, $priorityField) {
        // First compare by priority
        if ($a[$priorityField] != $b[$priorityField]) {
            return $a[$priorityField] - $b[$priorityField]; // Ascending order (lower priority number = higher priority)
        }
        
        // If priorities are equal, sort by timestamp
        return strtotime($a[$timestampField]) - strtotime($b[$timestampField]); // Ascending order (older first)
    });
    
    return $entries;
}


?>