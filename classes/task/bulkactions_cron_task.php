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

use core\task\scheduled_task;
use tool_coursebulkactions\manager as bulkactionsmanager;

/**
 * Class bulkactions_cron_task
 *
 * @package    tool_coursebulkactions
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bulkactions_cron_task extends scheduled_task {
    /**
     * Get the name of the task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('bulkactionscrontask', 'tool_coursebulkactions');
    }

    /**
     * Execute the task.
     *
     * @return void
     */
    public function execute() {
        global $DB;
        $graceperiod = get_config('tool_coursebulkactions', 'graceperiod');
        $limit = get_config('tool_coursebulkactions', 'limitqueueditemsrun');
        $params = [
            'action' => bulkactionsmanager::BULKACTION_DELETE,
            'graceperiod' => time() - $graceperiod,
            'status' => bulkactionsmanager::STATUS_QUEUED,
        ];
        // Deleting a course could take a long time, so this prevents unhindered numbers of deletion tasks blocking other things.
        // Check how many adhoc tasks might already be waiting or running.
        $queuedtasks = \core\task\manager::get_adhoc_tasks(bulkactions_deletecourse_task::class);
        $countqueuedtasks = count($queuedtasks);
        // Already at capacity, so bale out.
        if ($countqueuedtasks >= $limit) {
            mtrace("Reached limit of $limit delete tasks.");
            return;
        }
        // Reduce the limit to prevent a build up.
        $newlimit = $limit - $countqueuedtasks;
        if ($countqueuedtasks > 0 && $newlimit >= 1) {
            $limit = $newlimit;
            mtrace("Adjusting limit to $limit delete tasks as $countqueuedtasks are already pending or running");
        }
        // Joining to the course table, ensures the course still exists.
        $select = "SELECT cba.* ";
        $from = " FROM {tool_coursebulkactions_queue} cba
            JOIN {course} c ON c.id = cba.courseid ";
        $where = ' WHERE cba.action = :action AND cba.timecreated <= :graceperiod AND cba.status = :status
            ORDER BY cba.timecreated ASC ';
        $records = $DB->get_records_sql($select . $from . $where, $params, 0, $limit);
        foreach ($records as $record) {
            $task = new bulkactions_deletecourse_task();
            $task->set_custom_data([
                'id' => $record->id,
                'courseid' => $record->courseid,
                'shortname' => $record->shortname,
                'fullname' => $record->fullname,
            ]);
            \core\task\manager::queue_adhoc_task($task, true);
            $DB->update_record('tool_coursebulkactions_queue', [
                'id' => $record->id,
                'status' => bulkactionsmanager::STATUS_PENDING,
                'timemodified' => time(),
            ]);
        }
        mtrace(count($records) . " adhoc course delete tasks were added");
    }
}
