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

use core\context;
use core\lang_string;
use core\url;
use core_form\dynamic_form;
use local_solalerts\filters\course_filter_customfield;
use stdClass;
use tool_coursebulkactions\persistents\search;
use user_filter_date;
use user_filter_text;
use user_filter_yesno;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/user/filters/lib.php');

/**
 * Class dynamic_search_form
 *
 * @package    tool_coursebulkactions
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dynamic_search_form extends dynamic_form {
    /**
     * Search record
     *
     * @var search
     */
    private $search;

    /**
     * Gets the search persistent for the given id
     * @return search
     */
    protected function get_search(): search {
        if ($this->search === null) {
            $this->search = new search($this->_ajaxformdata['id'], null);
        }
        return $this->search;
    }

    /**
     * Define the form.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;
        $mform->addElement('html', '<p>' . get_string('searchinstructions', 'tool_coursebulkactions') . '</p>');
        $mform->addElement('text', 'title', get_string('searchtitle', 'tool_coursebulkactions'));
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', get_string('required'), 'required', null, 'client');
        $mform->addElement('textarea', 'description', get_string('description', 'tool_coursebulkactions'));
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement('header', 'filterheader', get_string('searchcriteria', 'tool_coursebulkactions'));
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
        foreach ($filters as $filter => $enabled) {
            if ($enabled) {
                if ($field = $this->get_field($filter, false)) {
                    $field->setupForm($mform);
                }
            }
        }

        $mform->addElement('hidden', 'usermodified');
        $mform->addElement('hidden', 'timemodified');
        $mform->addElement('hidden', 'id');
        $mform->setType('usermodified', PARAM_INT);
        $mform->setType('timemodified', PARAM_INT);
        $mform->setType('id', PARAM_INT);
    }

    /**
     * Return context
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        return context\system::instance();
    }

    /**
     * Checks if current user has access to this form
     *
     * @return void
     * @throws \core\exception\required_capability_exception
     */
    protected function check_access_for_dynamic_submission(): void {
        require_capability('moodle/course:delete', $this->get_context_for_dynamic_submission());
    }

    /**
     * Load in existing data as form defaults.
     */
    public function set_data_for_dynamic_submission(): void {
        $search = $this->get_search();
        $data = $search->to_record();
        $getcriteria = json_decode($data->criteria);
        $criteria = $getcriteria ?? null;
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
        $this->set_data($data);
    }

    /**
     * Process the form submission
     *
     * @return array
     */
    public function process_dynamic_submission(): array {
        global $USER;
        $data = $this->get_data();
        $search = $this->get_search();
        $search->set('title', $data->title);
        $search->set('description', $data->description);
        $criteria = $this->convert_fields($data);
        $search->set('criteria', json_encode($criteria));
        $search->save();
        return ['status' => 'success'];
    }

    /**
     * Returns url
     *
     * @return url
     */
    public function get_page_url_for_dynamic_submission(): url {
        return new url('/admin/tool/coursebulkactions/index.php', ['tab' => 'saved', 'id' => $this->get_search()->get('id')]);
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
        return $criteria;
    }
}
