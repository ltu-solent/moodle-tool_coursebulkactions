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

/**
 * Class scale
 *
 * @package    tool_coursebulkactions
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @author Mark Sharp <mark.sharp@solent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class scale {
    /**
     * @var string[]
     */
    private static $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    /**
     * Scales a size in bytes to a human-readable format.
     *
     * @param int $bytes The size in bytes.
     * @return string The human-readable size.
     */
    public static function humanize($bytes) {
        $e = intval(floor(log($bytes) / log(1024)));

        if (!isset(self::$units[$e])) {
            return 'Can not calculate memory usage';
        }

        return sprintf('%.2f%s', ($bytes / pow(1024, floor($e))), self::$units[$e]);
    }
}
