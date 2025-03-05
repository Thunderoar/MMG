<?php
/**
 * Helper functions for priority handling
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
                case $withWho === 'Ibu / Bapa':
                    return [
                        'priority' => 1,
                        'description' => 'Keutamaan Tinggi (WhatsApp)'
                    ];
                case $withWho === 'Rakan / Saudara':
                    return [
                        'priority' => 2,
                        'description' => 'Keutamaan Sederhana (WhatsApp)'
                    ];
                case $withWho === 'Sendiri' && $canMakeDecision:
                    return [
                        'priority' => 3,
                        'description' => 'Keutamaan Biasa (WhatsApp)'
                    ];
                default: // Sendiri & !canMakeDecision
                    return [
                        'priority' => 4,
                        'description' => 'Keutamaan Rendah (WhatsApp)'
                    ];
            }
        }
        // Walk-in priorities (5-8)
        else {
            switch(true) {
                case $withWho === 'Ibu / Bapa':
                    return [
                        'priority' => 5,
                        'description' => 'Keutamaan Tinggi (Walk-in)'
                    ];
                case $withWho === 'Rakan / Saudara':
                    return [
                        'priority' => 6,
                        'description' => 'Keutamaan Sederhana (Walk-in)'
                    ];
                case $withWho === 'Sendiri' && $canMakeDecision:
                    return [
                        'priority' => 7,
                        'description' => 'Keutamaan Biasa (Walk-in)'
                    ];
                default: // Sendiri & !canMakeDecision
                    return [
                        'priority' => 8,
                        'description' => 'Keutamaan Rendah (Walk-in)'
                    ];
            }
        }
    }
}
?>
