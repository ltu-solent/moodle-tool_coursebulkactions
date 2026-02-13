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

use core\exception\moodle_exception;
use core_external\external_api;
use core_external\external_description;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use core_external\external_warnings;

/**
 * Class change_course_visibility
 *
 * @package    tool_coursebulkactions
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class change_course_visibility extends external_api {
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courses' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Course ID'),
                    'visible' => new external_value(PARAM_INT, '1: available to student, 0:not available'),
                ], 'Courses to update'),
            ),
        ]);
    }

    /**
     * Change course visibility.
     *
     * @param array $courses
     * @return array
     */
    public static function execute(array $courses): array {
        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courses' => $courses,
        ]);

        // Change course visibility.
        $result = [];
        foreach ($params['courses'] as $course) {
            // Check capability.
            $context = \core\context\course::instance($course['id'], MUST_EXIST);
            self::validate_context($context);
            require_capability('moodle/course:update', $context);
            require_capability('moodle/course:visibility', $context);
            try {
                update_course((object)$course);
            } catch (\Exception $e) {
                $warning = [];
                $warning['item'] = 'course';
                $warning['itemid'] = $course['id'];
                if ($e instanceof moodle_exception) {
                    $warning['warningcode'] = $e->errorcode;
                } else {
                    $warning['warningcode'] = $e->getCode();
                }
                $warning['message'] = $e->getMessage();
                $result['warnings'][] = $warning;
            }
        }

        return $result;
    }

    /**
     * Returns result method value
     *
     * @return external_description
     */
    public static function execute_returns(): external_description {
        return new external_single_structure(
            [
                'warnings' => new external_warnings(),
            ]
        );
    }
}
