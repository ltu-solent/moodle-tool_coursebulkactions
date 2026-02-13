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

namespace tool_coursebulkactions\external;

use core\context;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Class queue_courses_for_deletion
 *
 * @package    tool_coursebulkactions
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class queue_courses_for_deletion extends external_api {
    /**
     * Function parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseids' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Course ID')
            ),
        ]);
    }

    /**
     * Queue courses for deletion
     *
     * @param int[] $courseids
     * @return array
     */
    public static function execute($courseids) {
        global $DB, $USER;
        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                'courseids' => $courseids,
            ]
        );
        $errors = [];
        foreach ($params['courseids'] as $courseid) {
            if (!has_capability('moodle/course:delete', context\course::instance($courseid))) {
                $errors[] = "No permission to delete course with id $courseid";
                continue;
            }
            if ($DB->record_exists('tool_coursebulkactions_queue', ['courseid' => $courseid, 'action' => 'delete'])) {
                $errors[] = "Course with id $courseid is already queued for deletion";
                continue;
            }
            // We're storing the course fullname and shortname because once the course has been deleted, that info is no longer
            // available.
            $course = get_course($courseid);
            $DB->insert_record('tool_coursebulkactions_queue', [
                'courseid' => $courseid,
                'shortname' => $course->shortname,
                'fullname' => $course->fullname,
                'action' => 'delete',
                'timecreated' => time(),
                'usermodified' => $USER->id,
            ]);
        }
        return [
            'success' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Structure of return result
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the operation was successful'),
            'errors' => new external_multiple_structure(new external_value(PARAM_TEXT, 'Error message'), 'List of errors'),
        ]);
    }
}
