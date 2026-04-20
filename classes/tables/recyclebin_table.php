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

use core\lang_string;
use core\output\html_writer;
use core\url;
use core_table\sql_table;

/**
 * Class recyclebin_tanle
 *
 * @package    tool_coursebulkactions
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class recyclebin_table extends sql_table {
    /**
     * Recycle bin table constructor.
     *
     * @param string $uniqueid Unique ID for the table.
     * @param array $params Parameters for the table.
     */
    public function __construct($uniqueid, $params = []) {
        parent::__construct($uniqueid, $params);
        $columns = [
            'categoryid' => 'id',
            'category' => new lang_string('categoryrecyclebin', 'tool_coursebulkactions'),
            'idnumber' => new lang_string('categoryidnumber', 'tool_coursebulkactions'),
            'itemcount' => new lang_string('items', 'tool_coursebulkactions'),
        ];
        $this->define_columns(array_keys($columns));
        $this->define_headers(array_values($columns));
        $fields = "rbc.categoryid, cat.name category, cx.id contextid, cat.idnumber, COUNT(rbc.categoryid) itemcount";
        $from = "{tool_recyclebin_category} rbc
            JOIN {context} cx ON cx.instanceid = rbc.categoryid AND cx.contextlevel = 40
            JOIN {course_categories} cat ON cat.id = rbc.categoryid
            ";
        $where = '1=1 GROUP BY rbc.categoryid';
        $this->set_sql($fields, $from, $where, []);
        $countsql = "SELECT COUNT(DISTINCT(categoryid)) FROM {tool_recyclebin_category}";
        $this->set_count_sql($countsql);
        $this->collapsible(false);
        $this->define_baseurl(new url('/admin/tool/coursebulkactions/index.php', ['tab' => 'recyclebin']));
    }

    /**
     * Link to category recycle bin
     *
     * @param stdClass $row
     * @return string
     */
    public function col_category($row): string {
        $url = new url(
            '/admin/tool/recyclebin/index.php',
            ['contextid' => $row->contextid]
        );
        return html_writer::link($url, $row->category);
    }
}
