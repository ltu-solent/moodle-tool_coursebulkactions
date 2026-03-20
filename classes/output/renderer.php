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

use core\output\html_writer;
use core\output\plugin_renderer_base;
use core\url;
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
        $id = required_param('id', PARAM_INT);
        $search = new search($id, null);
        echo html_writer::link(
            new url('#'),
            get_string('editcoursesearch', 'tool_coursebulkactions', ['title' => $search->get('title')]),
            [
                'class' => 'btn btn-primary',
                'data-id' => $id,
                'data-action' => 'tool-coursebulkactions-search',
            ]
        );
        echo html_writer::tag('p', clean_text($search->get('description')));
        echo $search->print_criteria();

        $searchresults = new searchresults_table('searchresults', $search->to_record());
        $output = '';
        if ($searchresults) {
            ob_start();
            $searchresults->out(5, false);
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
        echo html_writer::link(
            new url('#'),
            get_string('newcoursesearch', 'tool_coursebulkactions'),
            [
                'class' => 'btn btn-primary',
                'data-id' => 0,
                'data-action' => 'tool-coursebulkactions-search',
            ]
        );
        $table = new searches_table('coursesearches');
        $table->out(5, false);
    }

    /**
     * Render queued actions
     *
     * @return void
     */
    public function render_queue() {
        $table = new queued_table('queuedcourses', [manager::STATUS_QUEUED, manager::STATUS_DEFERRED]);
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
