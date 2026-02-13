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

namespace tool_coursebulkactions\task;

use core\task\adhoc_task;
use Exception;
use tool_coursebulkactions\manager;

/**
 * Class bulkactions_deletecourse_task
 *
 * @package    tool_coursebulkactions
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bulkactions_deletecourse_task extends adhoc_task {
    #[\Override]
    public function get_name() {
        return get_string('deletecoursetask', 'tool_coursebulkactions');
    }
    /**
     * Execute the task.
     *
     * @return void
     */
    public function execute() {
        global $DB;
        $id = $this->get_custom_data()->id;
        $record = $DB->get_record('tool_coursebulkactions_queue', ['id' => $id, 'action' => manager::BULKACTION_DELETE]);
        if (!$record) {
            mtrace("No record found for this item: $id");
            return;
        }
        $record->status = manager::STATUS_PROCESSING;
        $record->timemodified = time();
        $DB->update_record('tool_coursebulkactions_queue', $record);
        $course = $DB->get_record('course', ['id' => $record->courseid]);
        if (!$course) {
            mtrace("Course {$record->courseid} not found for this item: $id");
            $DB->delete_records('tool_coursebulkactions_queue', ['id' => $record->id]);
            return;
        }
        mtrace("Beginning deletion of {$course->shortname}");
        // Store the delete output in the task trace for logging purposes.
        $trace = '';
        try {
            ob_start();
            $result = delete_course($course->id, true);
            $record->status = manager::STATUS_COMPLETED;
            if (!$result) {
                $record->status = manager::STATUS_FAILED;
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $record->status = manager::STATUS_FAILED;
        } finally {
            $trace = ob_get_contents();
            ob_end_clean();
        }

        $trace = clean_param($trace, PARAM_NOTAGS);
        $trace = format_text($trace, FORMAT_PLAIN);
        mtrace($trace);
        $record->timemodified = time();

        $DB->update_record('tool_coursebulkactions_queue', $record);
    }

    #[\Override]
    public function retry_until_success(): bool {
        return false;
    }
}
