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

use stdClass;
use tool_coursebulkactions\manager;

/**
 * Tests for Course bulk actions
 *
 * @package    tool_coursebulkactions
 * @category   test
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class bulkactions_cleanup_task_test extends \advanced_testcase {
    /**
     * Test scheduled task
     *
     * @dataProvider execute_provider
     * @covers \tool_coursebulkactions\task\bulkactions_cleanup_task
     * @param string $action
     * @param int $status
     * @param int $timemodified
     * @param bool $deleted
     * @return void
     */
    public function test_execute($action, $status, $timemodified, $deleted): void {
        global $DB;
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course(['shortname' => 'TESTCRON']);

        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $manager = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $retention = (DAYSECS * 90);
        set_config('logretention', $retention, 'tool_coursebulkactions');
        $timecreated = strtotime($timemodified) - HOURSECS;
        $record = (object)[
            'courseid' => $course->id,
            'action' => $action,
            'status' => $status,
            'shortname' => $course->shortname,
            'fullname' => $course->fullname,
            'usermodified' => $manager->id,
            'timecreated' => $timecreated,
            'timemodified' => strtotime($timemodified),
        ];
        $id = $DB->insert_record('tool_coursebulkactions_queue', $record);
        ob_start();
        $task = \core\task\manager::get_scheduled_task(bulkactions_cleanup_task::class);
        $task->execute();
        ob_end_clean();

        $hasbeendeleted = !$DB->record_exists('tool_coursebulkactions_queue', ['id' => $id]);
        $this->assertEquals($deleted, $hasbeendeleted);
    }

    /**
     * Test provider for test_execute
     *
     * @return array
     */
    public static function execute_provider(): array {
        return [
            'Invalid action - deleted' => [
                'invalidaction',
                manager::STATUS_PENDING,
                '-180 days',
                true,
            ],
            'completed 2 days ago - not deleted' => [
                manager::BULKACTION_DELETE,
                manager::STATUS_COMPLETED,
                '-2 days',
                false,
            ],
            'failed 7 days ago - not deleted' => [
                manager::BULKACTION_DELETE,
                manager::STATUS_FAILED,
                '-7 days',
                false,
            ],
            'completed 2 weeks ago - not deleted' => [
                manager::BULKACTION_DELETE,
                manager::STATUS_COMPLETED,
                '-2 weeks',
                false,
            ],
            'completed 180 days ago - deleted' => [
                manager::BULKACTION_DELETE,
                manager::STATUS_COMPLETED,
                '-180 days',
                true,
            ],
            'failed 180 days ago - deleted' => [
                manager::BULKACTION_DELETE,
                manager::STATUS_FAILED,
                '-180 days',
                true,
            ],
            'completed 89 days ago - not deleted' => [
                manager::BULKACTION_DELETE,
                manager::STATUS_COMPLETED,
                '-89 days',
                false,
            ],
            'completed 91 days ago - deleted' => [
                manager::BULKACTION_DELETE,
                manager::STATUS_COMPLETED,
                '-91 days',
                true,
            ],
            'pending 91 days ago - not deleted' => [
                manager::BULKACTION_DELETE,
                manager::STATUS_PENDING,
                '-91 days',
                false,
            ],
            'processing 91 days ago - not deleted' => [
                manager::BULKACTION_DELETE,
                manager::STATUS_PROCESSING,
                '-91 days',
                false,
            ],
            'queued 91 days ago - not deleted' => [
                manager::BULKACTION_DELETE,
                manager::STATUS_QUEUED,
                '-91 days',
                false,
            ],
        ];
    }

    /**
     * No retention limit, so everything is kept
     *
     * @covers \tool_coursebulkactions\task\bulkactions_cleanup_task
     * @dataProvider no_retention_limit_provider
     * @param int $status
     * @param string $timemodified
     * @return void
     */
    public function test_no_retention_limit($status, $timemodified): void {
        global $DB;
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course(['shortname' => 'TESTCRON']);

        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        $manager = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->role_assign($managerrole->id, $manager->id);
        $retention = 0;
        set_config('logretention', $retention, 'tool_coursebulkactions');
        $timecreated = strtotime($timemodified) - HOURSECS;
        $record = (object)[
            'courseid' => $course->id,
            'action' => manager::BULKACTION_DELETE,
            'status' => $status,
            'shortname' => $course->shortname,
            'fullname' => $course->fullname,
            'usermodified' => $manager->id,
            'timecreated' => $timecreated,
            'timemodified' => strtotime($timemodified),
        ];
        $id = $DB->insert_record('tool_coursebulkactions_queue', $record);
        ob_start();
        $task = \core\task\manager::get_scheduled_task(bulkactions_cleanup_task::class);
        $task->execute();
        ob_end_clean();

        $hasbeendeleted = !$DB->record_exists('tool_coursebulkactions_queue', ['id' => $id]);
        $this->assertFalse($hasbeendeleted);
    }

    /**
     * Data provider for no_retention_limit_test
     *
     * @return array
     */
    public static function no_retention_limit_provider(): array {
        return [
            'completed 10 days ago' => [
                manager::STATUS_COMPLETED,
                '-10 days',
            ],
            'completed 60 days ago' => [
                manager::STATUS_COMPLETED,
                '-60 days',
            ],
            'completed 89 days ago' => [
                manager::STATUS_COMPLETED,
                '-89 days',
            ],
            'completed 91 days ago' => [
                manager::STATUS_COMPLETED,
                '-91 days',
            ],
            'failed 10 days ago' => [
                manager::STATUS_FAILED,
                '-10 days',
            ],
            'failed 89 days ago' => [
                manager::STATUS_FAILED,
                '-89 days',
            ],
            'failed 91 days ago' => [
                manager::STATUS_FAILED,
                '-91 days',
            ],
        ];
    }
}
