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
 * Settings for course bulk actions tool.
 *
 * @package    tool_coursebulkactions
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @author Mark Sharp <mark.sharp@solent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\lang_string;
use core\output\notification;
use core\url;
use core_admin\local\settings\autocomplete;

defined('MOODLE_INTERNAL') || die();


if ($hassiteconfig) {
    $parent = new admin_category('tool_coursebulkactionscat', new lang_string('pluginname', 'tool_coursebulkactions'));
    $ADMIN->add('tools', $parent);
    $ADMIN->add('tool_coursebulkactionscat', new admin_externalpage(
        'tool_coursebulkactions/index',
        new lang_string('managecoursebulkactions', 'tool_coursebulkactions'),
        new url('/admin/tool/coursebulkactions/index.php', ['tab' => 'saved']),
        'moodle/course:delete'
    ));

    $settings = new admin_settingpage(
        'tool_coursebulkactions_general',
        new lang_string('generalsettings', 'tool_coursebulkactions')
    );

    $settings->add(
        new admin_setting_configselect(
            'tool_coursebulkactions/limitqueueditemsrun',
            new lang_string('limitqueueditemsrun', 'tool_coursebulkactions'),
            new lang_string('limitqueueditemsrun_desc', 'tool_coursebulkactions'),
            5,
            array_combine(range(1, 30), range(1, 30))
        )
    );
    // Grace period.
    $settings->add(
        new admin_setting_configduration(
            'tool_coursebulkactions/graceperiod',
            new lang_string('graceperiod', 'tool_coursebulkactions'),
            new lang_string('graceperiod_desc', 'tool_coursebulkactions'),
            604800 // Default to 7 days.
        )
    );

    $settings->add(
        new admin_setting_configduration(
            'tool_coursebulkactions/logretention',
            new lang_string('logretention', 'tool_coursebulkactions'),
            new lang_string('logretention_desc', 'tool_coursebulkactions'),
            15552000 // Default to 6 months.
        )
    );

    $settings->add(
        new admin_setting_configselect(
            'tool_coursebulkactions/spacewarningthreshold',
            new lang_string('spacewarningthreshold', 'tool_coursebulkactions'),
            new lang_string('spacewarningthreshold_desc', 'tool_coursebulkactions'),
            10737418240, // Default to 10GB.
            array_combine([1073741824, 2147483648, 5368709120, 10737418240, 21474836480], ['1GB', '2GB', '5GB', '10GB', '20GB'])
        )
    );

    if (!function_exists('disk_free_space')) {
        $settings->add(
            new admin_setting_description(
                'tool_coursebulkactions/undeterminedspace',
                '',
                $OUTPUT->notification(
                    get_string('undeterminedspace', 'tool_coursebulkactions'),
                    notification::NOTIFY_ERROR
                )
            )
        );
    }

    $settings->add(
        new admin_setting_heading(
            'tool_coursebulkactions/hardsettings',
            new lang_string('hardsettings', 'tool_coursebulkactions'),
            new lang_string('hardsettings_desc', 'tool_coursebulkactions')
        )
    );
    $choices = core_course_category::make_categories_list();
    $settings->add(
        new autocomplete(
            'tool_coursebulkactions/excludedcategories',
            new lang_string('excludedcategories', 'tool_coursebulkactions'),
            new lang_string('excludedcategories_desc', 'tool_coursebulkactions'),
            [],
            $choices,
            [
                'multiple' => true,
                'placeholder' => new lang_string('selectcategoriestoexclude', 'tool_coursebulkactions'),
                'manageurl' => false,
            ]
        )
    );

    $ADMIN->add('tool_coursebulkactionscat', $settings);
}
