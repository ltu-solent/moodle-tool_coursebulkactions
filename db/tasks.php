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
 * Scheduled task definitions for Course bulk actions
 *
 * Documentation: {@link https://moodledev.io/docs/apis/subsystems/task/scheduled}
 *
 * @package    tool_coursebulkactions
 * @category   task
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => tool_coursebulkactions\task\bulkactions_cron_task::class,
        'blocking' => 0,
        'minute' => '0/15',
        'hour' => '20-23,0-4',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ], [
        'classname' => tool_coursebulkactions\task\bulkactions_cleanup_task::class,
        'blocking' => 0,
        'minute' => '14',
        'hour' => '2',
        'day' => '*',
        'month' => '*',
        'dayofweek' => 6,
    ],
];
