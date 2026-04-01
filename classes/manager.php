<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace tool_coursebulkactions;

/**
 * Class manager
 *
 * @package    tool_coursebulkactions
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @author Mark Sharp <mark.sharp@solent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {
    /**
     * Delete action
     */
    const BULKACTION_DELETE = 'delete';

    /**
     * Queued task
     */
    const STATUS_QUEUED = 0;
    /**
     * Adhoc task is waiting to be processed
     */
    const STATUS_PENDING = 1;
    /**
     * Adhoc task is currently being processed
     */
    const STATUS_PROCESSING = 2;
    /**
     * Adhoc task completed successfully
     */
    const STATUS_COMPLETED = 3;
    /**
     * Adhoc task failed
     */
    const STATUS_FAILED = 4;
    /**
     * Adhoc task deferred because of space issues
     */
    const STATUS_DEFERRED = 5;

    /**
     * Dequeue item
     *
     * @param int $id
     * @return void
     */
    public static function dequeue(int $id): void {
        global $DB;
        $DB->delete_records('tool_coursebulkactions_queue', ['id' => $id]);
    }

    /**
     * Requeue a deferred item
     * @param int $id
     * @return void
     */
    public static function requeue(int $id): void {
        global $DB;
        $record = $DB->get_record('tool_coursebulkactions_queue', ['id' => $id]);
        if ($record && $record->status == self::STATUS_DEFERRED) {
            $record->status = self::STATUS_QUEUED;
            $DB->update_record('tool_coursebulkactions_queue', $record);
        }
    }

    /**
     * Delete saved search
     *
     * @param int $id
     * @return void
     */
    public static function delete_search(int $id): void {
        global $DB;
        $DB->delete_records('tool_coursebulkactions', ['id' => $id]);
    }

    /**
     * Display space warning message
     *
     * @return bool
     */
    public static function has_space_warning(): bool {
        global $CFG;
        $categorybinenabled = get_config('tool_recyclebin', 'categorybinenable');
        if (!$categorybinenabled) {
            return false;
        }
        $space = self::available_space();
        $threshold = $space['threshold'];
        $availablespace = $space['available'];
        return $availablespace < $threshold;
    }

    /**
     * Get available space and threshold for warnings
     * @return array<float, int> Available space and threshold in bytes
     */
    public static function available_space(): array {
        global $CFG;
        $availablespace = function_exists('disk_free_space') ? disk_free_space($CFG->dataroot) : 0;
        return [
            'available' => (float)$availablespace,
            'threshold' => get_config('tool_coursebulkactions', 'spacewarningthreshold') ?? 10737418240, // Default to 10GB.
        ];
    }
}
