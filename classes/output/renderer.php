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

namespace tool_coursebulkactions\output;

use core\output\plugin_renderer_base;
use tool_coursebulkactions\forms\search_form;
use tool_coursebulkactions\manager;
use tool_coursebulkactions\persistents\search;
use tool_coursebulkactions\tables\queued_table;
use tool_coursebulkactions\tables\searches_table;
use tool_coursebulkactions\tables\searchresults_table;

/**
 * Renderer for Course bulk actions
 *
 * @package    tool_coursebulkactions
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {
    /**
     * Search renderer
     *
     * @return string
     */
    public function render_search() {
        global $USER;
        $id = optional_param('id', 0, PARAM_INT);
        $search = new search($id, null);
        $customdata = [
            'persistent' => $search,
            'userid' => $USER->id,
        ];
        $form = new search_form($this->page->url->out(false), $customdata);
        $searchresults = null;
        if ($formdata = $form->get_data()) {
            if ($formdata->id == 0 && !empty($formdata->title)) {
                $search = new search(0, $formdata);
                $search->create();
                $formdata->id = $search->get('id');
            } else if ($formdata->id != 0) {
                $search = new search($formdata->id, $formdata);
                $search->update();
            }
            $searchresults = new searchresults_table('searchresults', $formdata);
        }
        $output = $form->render();
        if ($searchresults) {
            ob_start();
            $searchresults->out(100, false);
            $content = ob_get_contents();
            ob_end_clean();
            $output .= $content;
        }

        return $output;
    }

    /**
     * Render saved searches table
     *
     * @return void
     */
    public function render_searches() {
        $table = new searches_table('coursesearches');
        $table->out(100, false);
    }

    /**
     * Render queued actions
     *
     * @return void
     */
    public function render_queue() {
        $table = new queued_table('queuedcourses', [manager::STATUS_QUEUED]);
        $table->out(100, false);
    }

    /**
     * Render logs for non-queued items
     *
     * @return void
     */
    public function render_logs() {
        $table = new queued_table(
            'coursebulkactions_logs_table',
            [
                manager::STATUS_COMPLETED,
                manager::STATUS_FAILED,
                manager::STATUS_PENDING,
                manager::STATUS_PROCESSING,
            ],
            'logs'
        );
        $table->out(100, false);
    }
}
