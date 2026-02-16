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

use advanced_testcase;
use tool_coursebulkactions\manager;

/**
 * Tests for Course bulk actions
 *
 * @package    tool_coursebulkactions
 * @category   test
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class bulkactions_cron_task_test extends advanced_testcase {
    /**
     * Test that adhoc tasks are created for queued items and that they are not created when they shouldn't be.
     *
     * @dataProvider provider_test_delete_adhoc_tasks
     * @covers \tool_coursebulkactions\task\bulkactions_cron_task::execute
     * @param int $status
     * @param string $action
     * @param int $daysago
     * @param bool $shouldexist
     */
    public function test_delete_adhoc_tasks(int $status, string $action, int $daysago, bool $shouldexist): void {
        global $DB;
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course(['shortname' => 'TESTCRON']);

        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $manager = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $record = (object)[
            'courseid' => $course->id,
            'action' => $action,
            'status' => $status,
            'shortname' => $course->shortname,
            'fullname' => $course->fullname,
            'usermodified' => $manager->id,
            'timecreated' => time() - (DAYSECS * $daysago) + 1,
            'timemodified' => time() - (DAYSECS * $daysago) + 1,
        ];
        $id = $DB->insert_record('tool_coursebulkactions_queue', $record);

        ob_start();
        $task = \core\task\manager::get_scheduled_task(bulkactions_cron_task::class);
        $task->execute();
        ob_end_clean();

        $queuedtasks = \core\task\manager::get_adhoc_tasks(bulkactions_deletecourse_task::class);
        if ($shouldexist) {
            $queuedtask = reset($queuedtasks);
            $this->assertCount(1, $queuedtasks);
            $this->assertEquals($id, $queuedtask->get_custom_data()->id);
            $this->assertEquals($course->id, $queuedtask->get_custom_data()->courseid);
            $this->assertEquals($course->shortname, $queuedtask->get_custom_data()->shortname);
            $this->assertEquals($course->fullname, $queuedtask->get_custom_data()->fullname);
        } else {
            $this->assertCount(0, $queuedtasks);
        }
    }

    /**
     * Data provider for test_delete_adhoc_tasks
     *
     * @return array
     */
    public static function provider_test_delete_adhoc_tasks(): array {
        return [
            'already being processed' => [
                manager::STATUS_PROCESSING,
                manager::BULKACTION_DELETE,
                8,
                false,
            ],
            'grace period has passed' => [
                manager::STATUS_QUEUED,
                manager::BULKACTION_DELETE,
                8,
                true,
            ],
            'grace period not yet passed' => [
                manager::STATUS_QUEUED,
                manager::BULKACTION_DELETE,
                1,
                false,
            ],
            'random action' => [
                manager::STATUS_QUEUED,
                'randoaction',
                8,
                false,
            ],
        ];
    }

    /**
     * Test limiting pending tasks
     *
     * @return void
     * @covers \tool_coursebulkactions\task\bulkactions_cron_task
     */
    public function test_limit_tasks(): void {
        global $DB;
        $this->resetAfterTest();
        $limit = 10;
        set_config('limitqueueditemsrun', $limit, 'tool_coursebulkactions');
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $manager = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $courses = [];
        for ($x = 0; $x < ($limit + 10); $x++) {
            $course = $this->getDataGenerator()->create_course();
            $courses[$course->id] = $course;
            $record = (object)[
                'courseid' => $course->id,
                'action' => manager::BULKACTION_DELETE,
                'status' => manager::STATUS_QUEUED,
                'shortname' => $course->shortname,
                'fullname' => $course->fullname,
                'usermodified' => $manager->id,
                'timecreated' => time() - (DAYSECS * 8),
                'timemodified' => time() - (DAYSECS * 8),
            ];
            $DB->insert_record('tool_coursebulkactions_queue', $record);
        }

        // Run the scheduled task for the first time, this will add 10 adhoc tasks.
        $scheduledtask = \core\task\manager::get_scheduled_task(bulkactions_cron_task::class);
        ob_start();
        $scheduledtask->execute();
        ob_end_clean();
        $pendingtasks = \core\task\manager::get_adhoc_tasks(bulkactions_deletecourse_task::class);
        $this->assertCount(10, $pendingtasks);

        // Run the scheduled task again, but because the adhoc tasks haven't run, no more are added.
        ob_start();
        $scheduledtask->execute();
        ob_end_clean();
        $pendingtasks = \core\task\manager::get_adhoc_tasks(bulkactions_deletecourse_task::class);
        $this->assertCount(10, $pendingtasks);

        $clock = \core\di::get(\core\clock::class);
        for ($x = 0; $x < 5; $x++) {
            $task = \core\task\manager::get_next_adhoc_task($clock->time());
            ob_start();
            $task->execute();
            ob_end_clean();
            \core\task\manager::adhoc_task_complete($task);
        }
        // There should only be five left.
        $pendingtasks = \core\task\manager::get_adhoc_tasks(bulkactions_deletecourse_task::class);
        $this->assertCount(5, $pendingtasks);

        ob_start();
        $scheduledtask->execute();
        ob_end_clean();

        // Now there should be 10 delete tasks again. With five left queued.
        $pendingtasks = \core\task\manager::get_adhoc_tasks(bulkactions_deletecourse_task::class);
        $this->assertCount(10, $pendingtasks);
        $queuedtasks = $DB->get_records(
            'tool_coursebulkactions_queue',
            ['action' => manager::BULKACTION_DELETE, 'status' => manager::STATUS_QUEUED]
        );
        $this->assertCount(5, $queuedtasks);

        // Run all pending tasks.
        for ($x = 0; $x < count($pendingtasks); $x++) {
            $task = \core\task\manager::get_next_adhoc_task($clock->time());
            ob_start();
            $task->execute();
            ob_end_clean();
            \core\task\manager::adhoc_task_complete($task);
        }

        // Get remaining Queued tasks.
        ob_start();
        $scheduledtask->execute();
        ob_end_clean();
        $pendingtasks = \core\task\manager::get_adhoc_tasks(bulkactions_deletecourse_task::class);
        $this->assertCount(5, $pendingtasks);
        $queuedtasks = $DB->get_records(
            'tool_coursebulkactions_queue',
            ['action' => manager::BULKACTION_DELETE, 'status' => manager::STATUS_QUEUED]
        );
        $this->assertCount(0, $queuedtasks);
        // Run all pending tasks.
        for ($x = 0; $x < count($pendingtasks); $x++) {
            $task = \core\task\manager::get_next_adhoc_task($clock->time());
            ob_start();
            $task->execute();
            ob_end_clean();
            \core\task\manager::adhoc_task_complete($task);
        }
        $pendingtasks = \core\task\manager::get_adhoc_tasks(bulkactions_deletecourse_task::class);
        $this->assertCount(0, $pendingtasks);
    }
}
