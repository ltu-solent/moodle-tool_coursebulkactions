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

use core\lang_string;
use core\output\checkbox_toggleall;
use core\output\html_writer;
use core\url;
use core_collator;
use core_table\sql_table;
use local_solalerts\filters\course_filter_customfield;
use stdClass;
use tool_coursebulkactions\manager;
use user_filter_date;
use user_filter_text;
use user_filter_yesno;

/**
 * Class searchresults_table
 *
 * @package    tool_coursebulkactions
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class searchresults_table extends sql_table {
    /**
     * Constructor
     *
     * @param string $uniqueid
     * @param stdClass $search
     * @param string $downloadformat
     * @return void
     */
    public function __construct($uniqueid, $search, $downloadformat = '') {
        global $OUTPUT;
        parent::__construct($uniqueid);
        $this->set_attribute('id', 'coursebulkactions_searchresults_table');
        $columns = [];
        if ($downloadformat == '') {
            $mastercheckbox = new checkbox_toggleall(
                $uniqueid,
                true,
                [
                    'id' => 'select-all-courses',
                    'name' => 'select-all-courses',
                    'label' => get_string('selectall'),
                    'labelclasses' => 'sr-only',
                    'classes' => 'm-1',
                    'checked' => false,
                ]
            );
            $columns['select'] = $OUTPUT->render($mastercheckbox);
        }
        $columns += [
            'id' => 'id',
            'coursename' => new lang_string('coursefullname', 'tool_coursebulkactions'),
            'shortname' => new lang_string('shortname'),
            'startdate' => new lang_string('startdate'),
            'enddate' => new lang_string('enddate'),
            'visible' => new lang_string('visible'),
            'category' => new lang_string('category'),
            'categoryidnumber' => new lang_string('categoryidnumber', 'tool_coursebulkactions'),
            'customfields' => new lang_string('customfields', 'tool_coursebulkactions'),
            'enrolments' => new lang_string('enrolments', 'tool_coursebulkactions'),
            'sections' => new lang_string('sections'),
            'activities' => new lang_string('activities'),
        ];
        $this->define_columns(array_keys($columns));
        $this->define_headers(array_values($columns));
        $this->collapsible(false);
        $criteria = json_decode($search->criteria);
        // We need to add the params to the url for paging to work, but only if not saved.
        $id = $search->id ?? 0;
        $urlparams = [
            'id' => $id,
            'tab' => 'search',
        ];

        $this->define_baseurl(new url('/admin/tool/coursebulkactions/index.php', $urlparams));

        $select = 'c.id, c.fullname coursename, c.shortname, c.startdate, c.enddate, c.visible,
            c.category, cat.name catname, cat.idnumber catidnumber, q.action status';
        $from = "{course} c
        JOIN {course_categories} cat ON cat.id = c.category
        LEFT JOIN {tool_coursebulkactions_queue} q ON q.courseid = c.id";

        $wheres = [];
        $params = [];

        // Don't show queued items.
        $wheres[] = '(q.id IS NULL OR q.action != :queuedaction)';
        $params['queuedaction'] = manager::BULKACTION_DELETE;

        if (isset($criteria->fullname)) {
            $field = new user_filter_text('fullname', '', false, 'c.fullname');
            $data = [
                'operator' => $criteria->fullname->op,
                'value' => $criteria->fullname->value,
            ];
            [$sql, $filter] = $field->get_sql_filter($data);
            if (!empty($filter)) {
                $wheres[] = $sql;
                $params = array_merge($params, $filter);
            }
        }

        if (isset($criteria->shortname)) {
            $field = new user_filter_text('shortname', '', false, 'c.shortname');
            $data = [
                'operator' => $criteria->shortname->op,
                'value' => $criteria->shortname->value,
            ];
            [$sql, $filter] = $field->get_sql_filter($data);
            if (!empty($filter)) {
                $wheres[] = $sql;
                $params = array_merge($params, $filter);
            }
        }

        if (isset($criteria->startdate)) {
            $field = new user_filter_date('startdate', 'startdate', false, 'c.startdate');
            $data = [
                'after' => $criteria->startdate->sdt,
                'before' => $criteria->startdate->edt,
            ];
            [$sql, $filter] = $field->get_sql_filter($data);
            if (!empty($sql)) {
                $wheres[] = $sql;
            }
        }

        if (isset($criteria->enddate)) {
            $field = new user_filter_date('enddate', 'enddate', false, 'c.enddate');
            $data = [
                'after' => $criteria->enddate->sdt,
                'before' => $criteria->enddate->edt,
            ];
            [$sql, $filter] = $field->get_sql_filter($data);
            if (!empty($sql)) {
                $wheres[] = $sql;
            }
        }

        if (isset($criteria->categoryidnumber)) {
            $field = new user_filter_text('categoryidnumber', '', false, 'cat.idnumber');
            $data = [
                'operator' => $criteria->categoryidnumber->op,
                'value' => $criteria->categoryidnumber->value,
            ];
            [$sql, $filter] = $field->get_sql_filter($data);
            if (!empty($filter)) {
                $wheres[] = $sql;
                $params = array_merge($params, $filter);
            }
        }

        if (isset($criteria->visible) && $criteria->visible->value != '') {
            $field = new user_filter_yesno('visible', '', false, 'c.visible');
            $data = [
                'value' => $criteria->visible->value,
            ];
            [$sql, $filter] = $field->get_sql_filter($data);
            if (!empty($filter)) {
                $wheres[] = $sql;
                $params = array_merge($params, $filter);
            }
        }

        if (isset($criteria->customfield)) {
            $field = new course_filter_customfield('customfield', '', false, 'c.id');
            $data = [
                'fieldid' => $criteria->customfield->fld,
                'value' => $criteria->customfield->value,
                'operator' => $criteria->customfield->op,
            ];
            [$sql, $filter] = $field->get_sql_filter($data);
            if (!empty($filter)) {
                $wheres[] = $sql;
                $params = array_merge($params, $filter);
            }
        }
        $where = '1=1';
        if (count($wheres) > 0) {
            $where = join(' AND ', $wheres);
        }

        $this->set_sql($select, $from, $where, $params);
        $this->no_sorting('actions');
        $this->no_sorting('activities');
        $this->no_sorting('customfields');
        $this->no_sorting('enrolments');
        $this->no_sorting('queued');
        $this->no_sorting('sections');
        $this->sortable(true, 'startdate', SORT_ASC);
    }

    /**
     * Activities column
     *
     * @param stdClass $row
     * @return string
     */
    public function col_activities($row): string {
        $modinfo = get_fast_modinfo($row->id);
        $items = [];
        $activities = [];
        if (count($modinfo->cms) == 0) {
            return "0";
        }
        foreach ($modinfo->cms as $cm) {
            if (!isset($activities[$cm->modname])) {
                $activities[$cm->modname] = 1;
            } else {
                $activities[$cm->modname] = $activities[$cm->modname] + 1;
            }
        }
        core_collator::ksort($activities);
        foreach ($activities as $key => $count) {
            $items[] = "{$count} {$key}";
        }
        return html_writer::alist($items);
    }

    /**
     * Category idnumber columns
     *
     * @param stdClass $row
     * @return string
     */
    public function col_categoryidnumber($row): string {
        return $row->catidnumber ?? '';
    }

    /**
     * Category name column
     *
     * @param stdClass $row
     * @return string
     */
    public function col_category($row): string {
        return $row->catname ?? '';
    }

    /**
     * Course name column
     *
     * @param stdClass $row
     * @return string
     */
    public function col_coursename($row): string {
        return html_writer::link(
            new url('/course/view.php', ['id' => $row->id]),
            $row->coursename
        );
    }

    /**
     * Customfields column
     *
     * @param stdClass $row
     * @return string
     */
    public function col_customfields($row): string {
        global $DB;
        $metadata = \local_placeholders\get_course_metadata($row->id);
        $items = [];
        foreach ($metadata as $key => $value) {
            if (strpos($value, 'http') === 0) {
                $value = html_writer::link($value, ucwords($key));
            } else if ($key == 'expiration') {
                $value = "Expiration date: " . userdate($value);
            }
            $items[] = "{$key}: $value";
        }
        return html_writer::alist($items);
    }

    /**
     * Course enddate column
     *
     * @param stdClass $row
     * @return string
     */
    public function col_enddate($row): string {
        return ($row->enddate == 0) ? '' : userdate($row->enddate, get_string('strftimedate', 'langconfig'));
    }

    /**
     * Enrolments column
     *
     * @param stdClass $row
     * @return string
     */
    public function col_enrolments($row): string {
        global $DB;
        $sql = "SELECT UUID() eid, e.enrol, COUNT(ue.id) enrolments,  IF(ue.status = 0, 'Active', 'Suspended') status
            FROM {enrol} e
            LEFT JOIN {user_enrolments} ue ON ue.enrolid = e.id
            WHERE e.courseid = :courseid AND e.status = :status
            GROUP BY e.id, ue.status";

        $enrolments = $DB->get_records_sql($sql, [
            'courseid' => $row->id,
            'status' => ENROL_INSTANCE_ENABLED,
        ]);
        if (count($enrolments) == 0) {
            return "0";
        }
        $list = [];
        foreach ($enrolments as $enrolment) {
            $list[] = "{$enrolment->enrol}: {$enrolment->enrolments} {$enrolment->status}";
        }
        return html_writer::alist($list);
    }

    /**
     * Section count column
     *
     * @param stdClass $row
     * @return string
     */
    public function col_sections($row): string {
        $modinfo = get_fast_modinfo($row->id);
        return count($modinfo->sections);
    }

    /**
     * Select checkbox column
     *
     * @param stdClass $row
     * @return string
     */
    public function col_select($row): string {
        global $OUTPUT;
        $name = 'course' . $row->id;
        $checkbox = new checkbox_toggleall(
            $this->uniqueid,
            false,
            [
                'classes' => 'coursecheckbox m-1',
                'id' => $name,
                'name' => $name,
                'checked' => false,
                'label' => get_string('selectitem', 'tool_coursebulkactions', $row),
                'labelclasses' => 'accesshide',
                'value' => $row->id,
            ]
        );
        return $OUTPUT->render($checkbox);
    }

    /**
     * Course startdate column
     *
     * @param stdClass $row
     * @return string
     */
    public function col_startdate($row): string {
        return ($row->startdate == 0) ? '' : userdate($row->startdate, get_string('strftimedate', 'langconfig'));
    }

    /**
     * Modified by column
     *
     * @param stdClass $row
     * @return string
     */
    public function col_usermodified($row): string {
        return fullname($row);
    }

    /**
     * Course visible column
     *
     * @param stdClass $row
     * @return string
     */
    public function col_visible($row): string {
        return ($row->visible == 1) ? get_string('visible') : get_string('notvisible', 'tool_coursebulkactions');
    }

    /**
     * Adds bulk actions selector
     *
     * @return void
     */
    public function wrap_html_finish(): void {
        global $OUTPUT;
        $data = new stdClass();
        $data->showbulkactions = true;
        if ($data->showbulkactions) {
            $data->id = 'coursebulkactions_coursebulkactions';
            $data->attributes = [
                [
                    'name' => 'data-action',
                    'value' => 'toggle',
                ],
                [
                    'name' => 'data-togglegroup',
                    'value' => $this->uniqueid,
                ],
                [
                    'name' => 'data-toggle',
                    'value' => 'action',
                ],
                [
                    'name' => 'disabled',
                    'value' => true,
                ],
            ];
            $data->actions = [
                [
                    'value' => '#hideselected',
                    'name' => get_string('hideselected', 'tool_coursebulkactions'),
                ],
                [
                    'value' => '#showselected',
                    'name' => get_string('showselected', 'tool_coursebulkactions'),
                ],
                [
                    'value' => '#queuefordeletion',
                    'name' => get_string('queuefordeletion', 'tool_coursebulkactions'),
                ],
            ];

            echo $OUTPUT->render_from_template('tool_coursebulkactions/bulk_action_menu', $data);
        }
    }
}
