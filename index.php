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
 * TODO describe file index
 *
 * @package    tool_coursebulkactions
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\context\system;
use core\url;
use tool_coursebulkactions\manager;
use tool_coursebulkactions\tabs;

require('../../../config.php');

require_login();
$tab = optional_param('tab', 'search', PARAM_ALPHA);
$action = optional_param('action', '', PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);

$url = new url('/admin/tool/coursebulkactions/index.php', []);
$PAGE->set_url($url);
$PAGE->set_context(system::instance());

$PAGE->set_heading($SITE->fullname);
if ($tab == 'search') {
    $PAGE->requires->js_call_amd('tool_coursebulkactions/course_bulk_actions', 'init');
}
if ($tab == 'queue') {
    if ($action === 'dequeue' && $id && confirm_sesskey()) {
        manager::dequeue($id);
        redirect(new url('/admin/tool/coursebulkactions/index.php', ['tab' => 'queue']));
    }
}
if ($tab == 'saved') {
    if ($action === 'delete' && $id && confirm_sesskey()) {
        manager::delete_search($id);
        redirect(new url('/admin/tool/coursebulkactions/index.php', ['tab' => 'saved']));
    }
}
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('managecoursebulkactions', 'tool_coursebulkactions'));

$tabrow = tabs::get_tabrow();
$tabs = [$tabrow];

print_tabs($tabs, $tab, null, null, false);
$renderer = $PAGE->get_renderer('tool_coursebulkactions');

echo match ($tab) {
    'search' => $renderer->render_search(),
    'saved' => $renderer->render_searches(),
    'queue' => $renderer->render_queue(),
    'logs' => $renderer->render_logs(),
};

echo $OUTPUT->footer();
