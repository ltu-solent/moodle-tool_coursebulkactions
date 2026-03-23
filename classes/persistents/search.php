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

namespace tool_coursebulkactions\persistents;

use core\lang_string;
use core\persistent;
use html_writer;
use tool_coursebulkactions\filters\course_filter_customfield;
use user_filter_date;
use user_filter_text;
use user_filter_yesno;

/**
 * Class search
 *
 * @package    tool_coursebulkactions
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search extends persistent {
    /**
     * Table used by the class
     */
    const TABLE = 'tool_coursebulkactions';

    /**
     * Return the definition of the properties of this persistent class.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'title' => [
                'type' => PARAM_TEXT,
                'description' => 'The name of the search.',
            ],
            'description' => [
                'type' => PARAM_TEXT,
                'description' => 'A description of the search.',
                'default' => '',
            ],
            'criteria' => [
                'type' => PARAM_RAW,
                'description' => 'The search criteria in JSON format.',
                'default' => '{}',
            ],
        ];
    }

    /**
     * Print criteria as a list
     *
     * @return string
     */
    public function print_criteria(): string {
        $criterias = $this->get('criteria');
        if (!$criterias) {
            return '';
        }
        $criteria = json_decode($criterias);
        $items = [];
        if (isset($criteria->fullname->value) && $criteria->fullname->value != '') {
            $fullname = new user_filter_text('fullname', new lang_string('fullname', 'tool_coursebulkactions'), false, 'fullname');
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
            $shortname = new user_filter_text(
                'shortname',
                new lang_string('shortname', 'tool_coursebulkactions'),
                false,
                'shortname'
            );
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
                new lang_string('categoryidnumber', 'tool_coursebulkactions'),
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
}
