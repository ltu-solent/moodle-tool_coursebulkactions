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
                'default' => '',
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
}
