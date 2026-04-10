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
 * Main entry point for course bulk actions tool.
 *
 * @package    tool_coursebulkactions
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @author Mark Sharp <mark.sharp@solent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\context\system;
use core\output\notification;
use core\url;
use tool_coursebulkactions\manager;
use tool_coursebulkactions\scale;
use tool_coursebulkactions\tabs;

require('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$tab = optional_param('tab', 'saved', PARAM_ALPHA);
$action = optional_param('action', '', PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);

$pageparams = [
    'tab' => $tab,
    'action' => $action,
    'id' => $id,
    'page' => $page,
];

admin_externalpage_setup('tool_coursebulkactions/index');

$context = system::instance();
require_capability('moodle/course:delete', $context);

switch ($tab) {
    case 'search':
        $PAGE->navbar->add(
            get_string('managecoursebulkactions', 'tool_coursebulkactions'),
            new url('/admin/tool/coursebulkactions/index.php', ['tab' => 'saved'])
        );
        $PAGE->navbar->add(get_string('searchcourses', 'tool_coursebulkactions'));
        $PAGE->requires->js_call_amd('tool_coursebulkactions/course_bulk_actions', 'init');
        break;
    case 'queue':
        if ($action === 'dequeue' && $id && confirm_sesskey()) {
            manager::dequeue($id);
            redirect(new url('/admin/tool/coursebulkactions/index.php', ['tab' => 'queue', 'page' => $page]));
        }
        if ($action === 'requeue' && $id && confirm_sesskey()) {
            manager::requeue($id);
            redirect(new url('/admin/tool/coursebulkactions/index.php', ['tab' => 'queue', 'page' => $page]));
        }
        $PAGE->navbar->add(
            get_string('managecoursebulkactions', 'tool_coursebulkactions'),
            new url('/admin/tool/coursebulkactions/index.php', ['tab' => 'saved'])
        );
        $PAGE->navbar->add(get_string('queue', 'tool_coursebulkactions'));
        break;
    case 'logs':
        $PAGE->navbar->add(
            get_string('managecoursebulkactions', 'tool_coursebulkactions'),
            new url('/admin/tool/coursebulkactions/index.php', ['tab' => 'saved'])
        );
        $PAGE->navbar->add(get_string('logs', 'tool_coursebulkactions'));
        break;
    case 'saved':
        if ($action === 'delete' && $id && confirm_sesskey()) {
            manager::delete_search($id);
            redirect(new url('/admin/tool/coursebulkactions/index.php', ['tab' => 'saved', 'page' => $page]));
        }
        break;
}

$PAGE->set_context($context);

$PAGE->set_heading($SITE->fullname);
$PAGE->requires->js_call_amd('tool_coursebulkactions/search_form', 'init');

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('managecoursebulkactions', 'tool_coursebulkactions'));

$categorybinenabled = get_config('tool_recyclebin', 'categorybinenable');
if ($categorybinenabled) {
    echo $OUTPUT->notification(
        get_string('categorybinenabled', 'tool_coursebulkactions'),
        notification::NOTIFY_WARNING
    );
}

if (manager::has_space_warning()) {
    if (function_exists('disk_free_space')) {
        $space = manager::available_space();
        echo $OUTPUT->notification(
            get_string('categorybinwarning', 'tool_coursebulkactions', [
                'threshold' => scale::humanize($space['threshold']),
                'available' => scale::humanize($space['available']),
            ]),
            notification::NOTIFY_ERROR
        );
    } else {
        echo $OUTPUT->notification(
            get_string('undeterminedspace', 'tool_coursebulkactions'),
            notification::NOTIFY_ERROR
        );
    }
}

$tabrow = tabs::get_tabrow($tab);
$tabs = [$tabrow];

print_tabs($tabs, $tab, null, null, false);
$renderer = $PAGE->get_renderer('tool_coursebulkactions');

echo match ($tab) {
    'search' => $renderer->render_search(),
    'saved' => $renderer->render_searches(),
    'queue' => $renderer->render_queue(),
    'logs' => $renderer->render_logs(),
    'recyclebin' => $renderer->render_recyclebin(),
};

echo $OUTPUT->footer();
