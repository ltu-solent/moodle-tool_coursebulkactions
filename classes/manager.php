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
     * Delete saved search
     *
     * @param int $id
     * @return void
     */
    public static function delete_search(int $id): void {
        global $DB;
        $DB->delete_records('tool_coursebulkactions', ['id' => $id]);
    }
}
