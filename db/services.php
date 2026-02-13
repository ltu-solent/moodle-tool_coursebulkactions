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

/**
 * External functions and service declaration for Course bulk actions
 *
 * Documentation: {@link https://moodledev.io/docs/apis/subsystems/external/description}
 *
 * @package    tool_coursebulkactions
 * @category   webservice
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'tool_coursebulkactions_queue_courses_for_deletion' => [
        'classname' => 'tool_coursebulkactions\external\queue_courses_for_deletion',
        'description' => 'Queue courses for deletion',
        'type' => 'write',
        'capabilities' => 'moodle/course:delete',
        'ajax' => true,
    ],
    'tool_coursebulkactions_change_course_visibility' => [
        'classname' => 'tool_coursebulkactions\external\change_course_visibility',
        'description' => 'Change course visibility',
        'type' => 'write',
        'capabilities' => 'moodle/course:update',
        'ajax' => true,
    ],
];

$services = [
];
