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
final class bulkactions_deletecourse_task_test extends advanced_testcase {
    /**
     * Test that the delete course task behaves correctly in various scenarios.
     *
     * @dataProvider provider_test_execute
     * @covers \tool_coursebulkactions\task\bulkactions_deletecourse_task::execute
     * @param int $status
     * @param string $action
     * @param bool $deletecourse
     */
    public function test_execute(int $status, string $action, bool $deletecourse): void {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course(['shortname' => 'TESTDELETE']);

        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $manager = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $record = (object)[
            'courseid' => $course->id,
            'action' => $action,
            'status' => $status,
            'shortname' => 'TESTDELETE',
            'fullname' => 'Test Delete Course',
            'usermodified' => $manager->id,
            'timecreated' => time() - (DAYSECS * 8),
            'timemodified' => time() - (DAYSECS * 8),
        ];
        $id = $DB->insert_record('tool_coursebulkactions_queue', $record);

        ob_start();
        $task = \core\task\manager::get_scheduled_task(bulkactions_cron_task::class);
        $task->execute();
        ob_end_clean();

        // Delete the course if we are testing the case where it doesn't exist, to ensure it is gone before we run the task.
        if ($deletecourse) {
            delete_course($course->id, false);
        }
        $queuedtasks = \core\task\manager::get_adhoc_tasks(bulkactions_deletecourse_task::class);
        ob_start();
        foreach ($queuedtasks as $queuedtask) {
            // If the item is Queued this will become pending in the scheduled job, otherwise it won't change.
            $data = $queuedtask->get_custom_data();
            $queuedtask->execute();
        }
        ob_end_clean();

        $recordexists = $DB->record_exists('tool_coursebulkactions_queue', ['id' => $id]);
        $isdeleted = !$DB->record_exists('course', ['id' => $course->id]);
        if ($action != manager::BULKACTION_DELETE) {
            // The action is not one that would have been queued.
            $this->assertCount(0, $queuedtasks);
        } else if ($deletecourse) {
            // This has been dequeued because the course has been deleted.
            $this->assertFalse($recordexists);
        } else {
            $this->assertTrue($recordexists);
            $updatedrecord = $DB->get_record('tool_coursebulkactions_queue', ['id' => $id]);
            if ($status == manager::STATUS_QUEUED) {
                $this->assertEquals(manager::STATUS_COMPLETED, $updatedrecord->status);
                $this->assertTrue($isdeleted);
            } else {
                $this->assertFalse($isdeleted);
                $this->assertEquals($status, $updatedrecord->status);
            }
        }
    }

    /**
     * Data provider for test_execute
     *
     * @return array
     */
    public static function provider_test_execute(): array {
        return [
            'normal case - should process and status updated' => [
                manager::STATUS_QUEUED,
                manager::BULKACTION_DELETE,
                false,
            ],
            'already processing - should skip' => [
                manager::STATUS_PROCESSING,
                manager::BULKACTION_DELETE,
                false,
            ],
            'wrong action - should skip' => [
                manager::STATUS_QUEUED,
                'randoaction',
                false,
            ],
            'course does not exist - should attempt and dequeue' => [
                manager::STATUS_QUEUED,
                manager::BULKACTION_DELETE,
                true,
            ],
            'task is already pending - no new adhoc task created' => [
                manager::STATUS_PENDING,
                manager::BULKACTION_DELETE,
                false,
            ],
            'task has already failed - no new adhoc task created' => [
                manager::STATUS_FAILED,
                manager::BULKACTION_DELETE,
                false,
            ],
        ];
    }
}
