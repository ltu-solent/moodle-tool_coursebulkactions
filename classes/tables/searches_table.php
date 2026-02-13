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
use local_solalerts\filters\course_filter_customfield;
use user_filter_date;
use user_filter_text;
use user_filter_yesno;

/**
 * Class searches_table
 *
 * @package    tool_coursebulkactions
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class searches_table extends sql_table {
    /**
     * Constructor
     *
     * @param string $uniqueid
     */
    public function __construct($uniqueid) {
        parent::__construct($uniqueid);
        $this->useridfield = 'modifiedby';
        $columns = [
            'id' => 'id',
            'title' => new lang_string('searchtitle', 'tool_coursebulkactions'),
            'description' => new lang_string('description', 'tool_coursebulkactions'),
            'criteria' => new lang_string('criteria', 'tool_coursebulkactions'),
            'usermodified' => new lang_string('usermodified', 'tool_coursebulkactions'),
            'timemodified' => new lang_string('timemodified', 'tool_coursebulkactions'),
            'actions' => new lang_string('actions', 'tool_coursebulkactions'),
        ];
        $this->define_columns(array_keys($columns));
        $this->define_headers(array_values($columns));
        $this->no_sorting('actions');
        $this->no_sorting('criteria');
        $this->no_sorting('description');
        $this->sortable(true, 'lastmodified', SORT_DESC);
        $this->collapsible(false);
        $this->define_baseurl(new url('/admin/tool/coursebulkactions/index.php', ['tab' => 'saved']));

        $userfieldsapi = \core_user\fields::for_name(context\system::instance(), false);
        $userfieldssql = $userfieldsapi->get_sql('u', false, '', '', false)->selects;
        $select = "cba.id, cba.title, cba.description, cba.criteria, cba.timemodified, cba.usermodified, {$userfieldssql}";
        $from = "{tool_coursebulkactions} cba
            JOIN {user} u ON u.id = cba.usermodified";
        $where = '1=1';
        $this->set_sql($select, $from, $where);
    }

    /**
     * Actions column
     *
     * @param stdClass $record
     * @return string html
     */
    protected function col_actions($record) {
        $editurl = new url('/admin/tool/coursebulkactions/index.php', ['tab' => 'search', 'id' => $record->id]);
        $deleteurl = new url(
            '/admin/tool/coursebulkactions/index.php',
            ['tab' => 'saved', 'id' => $record->id, 'action' => 'delete', 'sesskey' => sesskey()]
        );
        return html_writer::link($editurl, get_string('view')) . ' | ' .
            html_writer::link($deleteurl, get_string('delete'));
    }

    /**
     * Criteria column
     *
     * @param stdClass $record
     * @return string list of selected criteria
     */
    protected function col_criteria($record) {
        $criteria = json_decode($record->criteria);
        if (!$criteria) {
            return '';
        }
        $items = [];
        if (isset($criteria->fullname->value) && $criteria->fullname->value != '') {
            $fullname = new user_filter_text('fullname', get_string('fullname', 'tool_coursebulkactions'), false, 'fullname');
            $fielddata = [
                'value' => $criteria->fullname->value,
                'operator' => $criteria->fullname->op,
            ];
            $item = $fullname->get_label($fielddata);
            if (!empty($item)) {
                $items[] = $item;
            }
        }
        if (isset($criteria->shortname->value) && $criteria->shortname->value != '') {
            $shortname = new user_filter_text('fullname', get_string('shortname', 'tool_coursebulkactions'), false, 'fullname');
            $fielddata = [
                'value' => $criteria->shortname->value,
                'operator' => $criteria->shortname->op,
            ];
            $item = $shortname->get_label($fielddata);
            if (!empty($item)) {
                $items[] = $item;
            }
        }
        if (isset($criteria->startdate->sdt) && $criteria->startdate->edt != '') {
            $startdate = new user_filter_date('startdate', new lang_string('startdate'), false, 'startdate');
            $fielddata = [
                'after' => $criteria->startdate->sdt,
                'before' => $criteria->startdate->edt,
            ];
            $item = $startdate->get_label($fielddata);
            if (!empty($item)) {
                $items[] = $item;
            }
        }
        if (isset($criteria->enddate->sdt) && $criteria->enddate->edt != '') {
            $enddate = new user_filter_date('enddate', new lang_string('enddate'), false, 'enddate');
            $fielddata = [
                'after' => $criteria->enddate->sdt,
                'before' => $criteria->enddate->edt,
            ];
            $item = $enddate->get_label($fielddata);
            if (!empty($item)) {
                $items[] = $item;
            }
        }
        if (isset($criteria->categoryidnumber->value) && $criteria->categoryidnumber->value != '') {
            $categoryidnumber = new user_filter_text(
                'categoryidnumber',
                get_string('categoryidnumber', 'tool_coursebulkactions'),
                false,
                'categoryidnumber'
            );
            $fielddata = [
                'value' => $criteria->categoryidnumber->value,
                'operator' => $criteria->categoryidnumber->op,
            ];
            $item = $categoryidnumber->get_label($fielddata);
            if (!empty($item)) {
                $items[] = $item;
            }
        }
        if (isset($criteria->visible->value) && $criteria->visible->value != '') {
            $visible = new user_filter_yesno('visible', new lang_string('visible'), false, 'visible');
            $fielddata = [
                'value' => $criteria->visible->value,
            ];
            $item = $visible->get_label($fielddata);
            if (!empty($item)) {
                $items[] = $item;
            }
        }
        if (isset($criteria->customfield->value) && $criteria->customfield->value != '') {
            $customfield = new course_filter_customfield(
                'customfield',
                new lang_string('customfield', 'tool_coursebulkactions'),
                false
            );
            $fielddata = [
                'fieldid' => $criteria->customfield->fld,
                'value' => $criteria->customfield->value,
                'operator' => $criteria->customfield->op,
            ];
            $item = $customfield->get_label($fielddata);
            if ($item) {
                $items[] = $item;
            }
        }
        return html_writer::alist($items);
    }

    /**
     * Time modified column
     *
     * @param stdClass $record
     * @return string formatted date
     */
    protected function col_timemodified($record): string {
        return userdate($record->timemodified, get_string('strftimedatetime', 'core_langconfig'));
    }

    /**
     * Search title column
     *
     * @param stdClass $row
     * @return string
     */
    protected function col_title($row): string {
        return html_writer::link(
            new url('/admin/tool/coursebulkactions/index.php', ['tab' => 'search', 'id' => $row->id]),
            $row->title
        );
    }

    /**
     * Search saved/modified by
     *
     * @param stdClass $row
     * @return string
     */
    protected function col_usermodified($row): string {
        return fullname($row);
    }
}
