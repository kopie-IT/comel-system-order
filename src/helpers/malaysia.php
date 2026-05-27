<?php

/**
 * Change Log
 * -----------------------------------
 * Date: 2026-05-26
 * Developer: AI Assistant
 * Version: v1.3.0
 * Description:
 * - Added Malaysian state list helper
 * - Added east_malaysia detection (Sabah/Sarawak/Labuan)
 * - Added state-based postage calculator using settings
 */

if (!function_exists('malaysia_states')) {
    /**
     * Returns the list of Malaysian states + federal territories
     * grouped into Semenanjung (West) and Sabah/Sarawak (East).
     */
    function malaysia_states(): array {
        return [
            'Semenanjung Malaysia' => [
                'Johor',
                'Kedah',
                'Kelantan',
                'Melaka',
                'Negeri Sembilan',
                'Pahang',
                'Perak',
                'Perlis',
                'Pulau Pinang',
                'Selangor',
                'Terengganu',
                'Kuala Lumpur',
                'Putrajaya',
            ],
            'Sabah & Sarawak' => [
                'Sabah',
                'Sarawak',
                'Labuan',
            ],
        ];
    }
}

if (!function_exists('malaysia_states_flat')) {
    /**
     * Returns a flat list of all Malaysian states for validation.
     */
    function malaysia_states_flat(): array {
        $flat = [];
        foreach (malaysia_states() as $group) {
            foreach ($group as $state) {
                $flat[] = $state;
            }
        }
        return $flat;
    }
}

if (!function_exists('is_east_malaysia')) {
    /**
     * Returns true if the given state is Sabah, Sarawak, or Labuan.
     */
    function is_east_malaysia(string $state): bool {
        $east = ['Sabah', 'Sarawak', 'Labuan'];
        return in_array(trim($state), $east, true);
    }
}

if (!function_exists('postage_for_state')) {
    /**
     * Returns the postage fee (RM) for a given Malaysian state based on
     * settings: postage_fee (Semenanjung default) and postage_fee_east
     * (Sabah/Sarawak/Labuan).
     */
    function postage_for_state(string $state, array $settings = []): float {
        if (empty($settings)) {
            $settings = (new Setting())->all();
        }
        $west = (float)($settings['postage_fee']      ?? '7.00');
        $east = (float)($settings['postage_fee_east'] ?? '12.00');
        return is_east_malaysia($state) ? $east : $west;
    }
}
