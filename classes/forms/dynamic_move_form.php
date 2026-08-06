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
use core\output\html_writer;
use core\url;
use core_form\dynamic_form;
use core_course_category;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/externallib.php');

/**
 * Class dynamic_move_form
 *
 * @package    tool_coursebulkactions
 * @author Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dynamic_move_form extends dynamic_form {
    /**
     * Add elements to the form
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'courseids');
        $mform->setType('courseids', PARAM_SEQUENCE);
        $mform->addElement('hidden', 'searchid');
        $mform->setType('searchid', PARAM_INT);
        $mform->addElement('static', 'coursenames', get_string('movingcourses', 'tool_coursebulkactions'), '');
        $categories = core_course_category::make_categories_list();
        $categories = ['' => get_string('select')] + $categories;
        $mform->addElement('select', 'category', get_string('tocategory', 'tool_coursebulkactions'), $categories);
        $mform->setType('category', PARAM_INT);
        $mform->addRule('category', get_string('required'), 'required', null, 'client');
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
        require_capability('moodle/course:update', $this->get_context_for_dynamic_submission());
        require_capability('moodle/course:changecategory', $this->get_context_for_dynamic_submission());
    }

    /**
     * Load in data from the ajax form data into the form for display
     *
     * @return void
     */
    public function set_data_for_dynamic_submission(): void {
        $data = new \stdClass();
        $data->courseids = $this->_ajaxformdata['courseids'];
        $courseids = explode(',', $data->courseids);
        $coursenames = [];
        foreach ($courseids as $courseid) {
            $course = get_course($courseid);
            if (!empty($course)) {
                $coursenames[] = get_course_display_name_for_list($course);
            }
        }

        $data->coursenames = html_writer::alist($coursenames, ['class' => 'tool_coursebulkactions-move-coursenames']);
        $data->category = '';
        $data->searchid = $this->_ajaxformdata['searchid'];
        $this->set_data($data);
    }

    /**
     * Process the form submission. In this case, it's going to send update_courses ws request.
     *
     * @return array
     */
    public function process_dynamic_submission(): array {
        global $DB;
        $data = $this->get_data();
        if (empty($data->category) || $data->category == 0) {
            throw new \moodle_exception('nocategoryselected', 'tool_coursebulkactions');
        }
        $courseids = explode(',', $data->courseids);
        if (empty($courseids)) {
            throw new \moodle_exception('nocoursesselected', 'tool_coursebulkactions');
        }
        $courses = [];
        foreach ($courseids as $courseid) {
            $courses[] = ['id' => $courseid, 'categoryid' => $data->category];
        }
        $result = \core_course_external::update_courses($courses);
        // Can't create a log for this because courseid and action combine to make a unique index,
        // and there might be multiple course moves.
        return $result;
    }

    /**
     * Get the page URL for the dynamic submission
     *
     * @return url
     */
    public function get_page_url_for_dynamic_submission(): url {
        return new url('/admin/tool/coursebulkactions/index.php', ['tab' => 'search', 'id' => $this->_ajaxformdata['searchid']]);
    }
}
