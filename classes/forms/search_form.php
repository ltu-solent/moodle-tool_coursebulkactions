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

namespace tool_coursebulkactions\forms;

use core\form\persistent as persistent_form;
use core\lang_string;
use local_solalerts\filters\course_filter_customfield;
use stdClass;
use tool_coursebulkactions\persistents\search;
use user_filter_date;
use user_filter_text;
use user_filter_yesno;

/**
 * Class search_form
 *
 * @package    tool_coursebulkactions
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class search_form extends persistent_form {
    /**
     * Persistent class for this form
     *
     * @var string
     */
    protected static $persistentclass = search::class;

    /**
     * Define the form.
     */
    public function definition() {
        $mform = $this->_form;
        $mform->addElement('html', '<p>' . get_string('searchinstructions', 'tool_coursebulkactions') . '</p>');
        $mform->addElement('text', 'title', new lang_string('searchtitle', 'tool_coursebulkactions'));
        $mform->addElement('textarea', 'description', new lang_string('description', 'tool_coursebulkactions'));

        $mform->addElement('header', 'filterheader', new lang_string('searchcriteria', 'tool_coursebulkactions'));
        // See solalert_form for reference.
        $filters = [
            'fullname' => 1,
            'shortname' => 1,
            'startdate' => 1,
            'enddate' => 1,
            'categoryidnumber' => 1,
            'visible' => 1,
            'customfield' => 1,
        ];
        foreach ($filters as $filter => $value) {
            if ($field = $this->get_field($filter, false)) {
                $field->setupForm($mform);
            }
        }

        $mform->addElement('hidden', 'usermodified');
        $mform->addElement('hidden', 'timemodified');
        $mform->addElement('hidden', 'id');
        $mform->setType('usermodified', PARAM_INT);
        $mform->setType('timemodified', PARAM_INT);
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(false, get_string('search', 'tool_coursebulkactions'));
    }

    /**
     * Get form for field
     *
     * @param string $fieldname
     * @param bool $advanced
     * @return object|null
     */
    protected function get_field($fieldname, $advanced) {
        return match ($fieldname) {
            'fullname' => new user_filter_text(
                'fullname',
                new lang_string('fullname'),
                $advanced,
                'c.fullname'
            ),
            'shortname' => new user_filter_text(
                'shortname',
                new lang_string('shortname'),
                $advanced,
                'c.shortname'
            ),
            'startdate' => new user_filter_date(
                'startdate',
                new lang_string('startdate'),
                $advanced,
                'c.startdate'
            ),
            'enddate' => new user_filter_date(
                'enddate',
                new lang_string('enddate'),
                $advanced,
                'c.enddate'
            ),
            'categoryidnumber' => new user_filter_text(
                'categoryidnumber',
                new lang_string('categoryidnumber', 'tool_coursebulkactions'),
                $advanced,
                'cat.idnumber'
            ),
            'visible' => new user_filter_yesno(
                'visible',
                new lang_string('visible'),
                $advanced,
                'c.visible'
            ),
            'customfield' => new course_filter_customfield(
                'customfield',
                new lang_string('customfield', 'tool_coursebulkactions'),
                $advanced
            ),
            default => null,
        };
    }

    /**
     * Convert form data to be stored in the persistent
     *
     * @param stdClass $data
     * @return stdClass
     */
    protected static function convert_fields(stdClass $data) {
        $data = parent::convert_fields($data);
        // print_r($data);
        $criteria = (object)[
            'fullname' => (object)[
                'op' => $data->fullname_op,
                'value' => $data->fullname,
            ],
            'shortname' => (object)[
                'op' => $data->shortname_op,
                'value' => $data->shortname,
            ],
            'startdate' => (object)[
                'sdt' => $data->startdate_sdt,
                'edt' => $data->startdate_edt,
            ],
            'enddate' => (object)[
                'sdt' => $data->enddate_sdt,
                'edt' => $data->enddate_edt,
            ],
            'categoryidnumber' => (object)[
                'op' => $data->categoryidnumber_op,
                'value' => $data->categoryidnumber,
            ],
            'visible' => (object)[
                'value' => $data->visible,
            ],
            'customfield' => (object)[
                'op' => $data->customfield_op,
                'value' => $data->customfield,
                'fld' => $data->customfield_fld,
            ],
        ];
        $data->criteria = json_encode($criteria);
        return $data;
    }

    /**
     * Get default data for form, including converting criteria from persistent to form data.
     *
     * @return stdClass
     */
    protected function get_default_data() {
        $data = parent::get_default_data();
        $getcriteria = $this->get_persistent()->get('criteria');
        $criteria = null;
        if ($getcriteria) {
            $criteria = json_decode($getcriteria);
        } else {
            return $data;
        }
        $fullname = $criteria->fullname->value ?? '';
        if ($fullname) {
            $data->fullname = $fullname;
            $data->fullname_op = $criteria->fullname->op;
        }
        $shortname = $criteria->shortname->value ?? '';
        if ($shortname) {
            $data->shortname = $shortname;
            $data->shortname_op = $criteria->shortname->op;
        }
        $startdate = $criteria->startdate ?? null;
        if ($startdate) {
            $data->startdate_sdt = $startdate->sdt ?? null;
            $data->startdate_edt = $startdate->edt ?? null;
        }
        $enddate = $criteria->enddate ?? null;
        if ($enddate) {
            $data->enddate_sdt = $enddate->sdt ?? null;
            $data->enddate_edt = $enddate->edt ?? null;
        }
        $categoryidnumber = $criteria->categoryidnumber->value ?? '';
        if ($categoryidnumber) {
            $data->categoryidnumber = $categoryidnumber;
            $data->categoryidnumber_op = $criteria->categoryidnumber->op;
        }
        $visible = $criteria->visible->value ?? null;
        if (!is_null($visible)) {
            $data->visible = $visible;
        }
        $customfield = $criteria->customfield ?? null;
        if ($customfield) {
            $data->customfield = $customfield->value;
            $data->customfield_op = $customfield->op;
            $data->customfield_fld = $customfield->fld;
        }
        return $data;
    }

    public function definition_after_data() {
        $mform = $this->_form;
        $criteria = $this->_customdata['data'];
        $data = new stdClass();

        $data->fullname = $criteria->fullname->value ?? '';
        $data->fullname_op = $criteria->fullname->op ?? 0;

        $data->shortname = $criteria->shortname->value ?? '';
        $data->shortname_op = $criteria->shortname->op ?? 0;

        $data->startdate_sdt = $startdate->sdt ?? null;
        $data->startdate_edt = $startdate->edt ?? null;

        $data->enddate_sdt = $enddate->sdt ?? null;
        $data->enddate_edt = $enddate->edt ?? null;

        $data->categoryidnumber = $criteria->categoryidnumber->value ?? '';
        $data->categoryidnumber_op = $criteria->categoryidnumber->op;

        $data->visible = $criteria->visible->value ?? null;

        $data->customfield = $criteria->customfield->value ?? '';
        $data->customfield_op = $criteria->customfield->op ?? 0;
        $data->customfield_fld = $criteria->customfield->fld ?? null;

        self::convert_fields($data);
    }
}
