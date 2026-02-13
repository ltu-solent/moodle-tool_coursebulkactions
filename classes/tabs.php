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

namespace tool_coursebulkactions;

use core\lang_string;
use core\output\tabobject;
use core\url;

/**
 * Class tabs
 *
 * @package    tool_coursebulkactions
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @author Mark Sharp <mark.sharp@solent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tabs {
    /**
     * Get the tab row for the course bulk actions tool.
     *
     * @return array
     */
    public static function get_tabrow() {
        $tabrow = [];
        $tabrow[] = new tabobject(
            'search',
            new url('/admin/tool/coursebulkactions/index.php', ['tab' => 'search']),
            new lang_string('search', 'tool_coursebulkactions')
        );
        $tabrow[] = new tabobject(
            'saved',
            new url('/admin/tool/coursebulkactions/index.php', ['tab' => 'saved']),
            new lang_string('savedsearches', 'tool_coursebulkactions')
        );
        $tabrow[] = new tabobject(
            'queue',
            new url('/admin/tool/coursebulkactions/index.php', ['tab' => 'queue']),
            new lang_string('queue', 'tool_coursebulkactions')
        );
        $tabrow[] = new tabobject(
            'logs',
            new url('/admin/tool/coursebulkactions/index.php', ['tab' => 'logs']),
            new lang_string('logs', 'tool_coursebulkactions')
        );
        return $tabrow;
    }
}
