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

namespace tool_coursebulkactions\tables;

use core\context;
use core\lang_string;
use core\output\html_writer;
use core\url;
use core_table\sql_table;
use stdClass;
use tool_coursebulkactions\manager;

/**
 * Class queued_table
 *
 * @package    tool_coursebulkactions
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class queued_table extends sql_table {
    /**
     * Queue table constructor
     *
     * @param string $uniqueid
     * @param int $status
     * @param string $tab
     */
    public function __construct($uniqueid, $status = [manager::STATUS_QUEUED], $tab = 'queue') {
        global $DB;
        parent::__construct($uniqueid);
        $columns = [
            'id' => 'id',
            'coursename' => new lang_string('coursefullname', 'tool_coursebulkactions'),
            'shortname' => new lang_string('shortname'),
            'status' => new lang_string('status', 'tool_coursebulkactions'),
            'action' => new lang_string('action', 'tool_coursebulkactions'),
            'usermodified' => new lang_string('createdby', 'tool_coursebulkactions'),
            'timecreated' => new lang_string('timecreated', 'tool_coursebulkactions'),
            'timemodified' => new lang_string('timemodified', 'tool_coursebulkactions'),
        ];
        if ($tab == 'queue') {
            $columns['processtime'] = new lang_string('processtime', 'tool_coursebulkactions');
            $columns['actions'] = new lang_string('actions');
        }
        $this->define_columns(array_keys($columns));
        $this->define_headers(array_values($columns));
        $this->define_baseurl(new url('/admin/tool/coursebulkactions/index.php', ['tab' => $tab]));
        $userfieldsapi = \core_user\fields::for_name(context\system::instance(), false);
        $userfieldssql = $userfieldsapi->get_sql('u', false, '', '', false)->selects;

        $graceperiod = get_config('tool_coursebulkactions', 'graceperiod');

        $select = "q.id, c.id courseid, q.fullname coursename, q.shortname, q.action, q.status,
            q.usermodified, q.timecreated, q.timemodified, q.timecreated + :graceperiod processtime, {$userfieldssql}";
        $from = "{tool_coursebulkactions_queue} q
            LEFT JOIN {course} c ON c.id = q.courseid
            JOIN {user} u ON u.id = q.usermodified";
        [$insql, $inparams] = $DB->get_in_or_equal($status, SQL_PARAMS_NAMED);
        $where = " q.status $insql ";

        $this->set_sql($select, $from, $where, array_merge($inparams, ['graceperiod' => $graceperiod]));
        $this->collapsible(false);
        $this->no_sorting('actions');
        if ($tab == 'queue') {
            $this->sortable(true, 'processtime', SORT_ASC);
        } else {
            $this->sortable(true, 'timecreated', SORT_DESC);
        }
    }

    /**
     * Action column
     *
     * @param stdClass $row
     * @return string
     */
    public function col_action($row): string {
        $action = match ($row->action) {
            'delete' => html_writer::span(
                get_string('deletion', 'tool_coursebulkactions'),
                'badge badge-danger'
            ),
            default => '',
        };
        if (empty($action)) {
            return '';
        }
        return $action;
    }

    /**
     * Actions menu
     *
     * @param stdClass $row
     * @return string
     */
    public function col_actions($row): string {
        $html = '';
        $html .= html_writer::link(
            new url(
                '/admin/tool/coursebulkactions/index.php',
                ['action' => 'dequeue', 'id' => $row->id, 'sesskey' => sesskey(), 'tab' => 'queue']
            ),
            get_string('dequeue', 'tool_coursebulkactions'),
            ['class' => 'btn btn-warning']
        );
        if ($row->status == manager::STATUS_DEFERRED) {
            $html .= html_writer::link(
                new url(
                    '/admin/tool/coursebulkactions/index.php',
                    ['action' => 'requeue', 'id' => $row->id, 'sesskey' => sesskey(), 'tab' => 'queue']
                ),
                get_string('requeue', 'tool_coursebulkactions'),
                ['class' => 'btn btn-secondary']
            );
        }
        return $html;
    }

    /**
     * Course name column
     *
     * @param stdClass $row
     * @return string
     */
    public function col_coursename($row): string {
        if (!is_null($row->courseid)) {
            return html_writer::link(
                new url('/course/view.php', ['id' => $row->courseid]),
                $row->coursename
            );
        }
        return $row->coursename;
    }

    /**
     * Expected process time
     *
     * @param stdClass $row
     * @return string
     */
    public function col_processtime($row): string {
        return userdate($row->processtime, get_string('strftimedatetime', 'core_langconfig'));
    }

    /**
     * Action for this course
     *
     * @param stdClass $row
     * @return string
     */
    public function col_status($row): string {
        $status = match ((int)$row->status) {
            manager::STATUS_FAILED => html_writer::span(
                get_string('status_' . $row->status, 'tool_coursebulkactions'),
                'badge badge-danger'
            ),
            manager::STATUS_PENDING, manager::STATUS_PROCESSING, manager::STATUS_QUEUED => html_writer::span(
                get_string('status_' . $row->status, 'tool_coursebulkactions'),
                'badge badge-info'
            ),
            manager::STATUS_COMPLETED => html_writer::span(
                get_string('status_' . $row->status, 'tool_coursebulkactions'),
                'badge badge-success'
            ),
            manager::STATUS_DEFERRED => html_writer::span(
                get_string('status_' . $row->status, 'tool_coursebulkactions'),
                'badge badge-warning'
            ),
            default => ''
        };
        if (empty($status)) {
            return '';
        }
        return $status;
    }

    /**
     * Time created columns
     *
     * @param stdClass $row
     * @return string
     */
    public function col_timecreated($row): string {
        return userdate($row->timecreated, get_string('strftimedatetime', 'core_langconfig'));
    }

    /**
     * Time modified columns
     *
     * @param stdClass $row
     * @return string
     */
    public function col_timemodified($row): string {
        $timemodified = ($row->timemodified > 0) ? $row->timemodified : $row->timecreated;
        return userdate($timemodified, get_string('strftimedatetime', 'core_langconfig'));
    }

    /**
     * Who created this request
     *
     * @param stdClass $row
     * @return string
     */
    public function col_usermodified($row): string {
        return fullname($row);
    }
}
