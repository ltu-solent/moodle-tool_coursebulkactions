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
 * English language pack for tool_coursebulkactions
 *
 * @package    tool_coursebulkactions
 * @category   string
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @author Mark Sharp <mark.sharp@solent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['action'] = 'Action';
$string['actions'] = 'Actions';

$string['bulkactions'] = 'Bulk actions';
$string['bulkactionscrontask'] = 'Course bulk actions cron task';

$string['categorybinenabled'] = '<strong>The Category Recycle bin is enabled.</strong> This means that deleted courses ' .
    ' will be moved to the recycle bin, rather than being deleted immediately. This can be useful to prevent accidental ' .
    ' data loss, but it also means that deleted courses will not free space until the recycle bin is emptied.';
$string['categorybinwarning'] = 'Your server has {$a->available} of free disk space which is less than the threshold of {$a->threshold}. This may not be enough to safely use the category recycle bin feature.';
$string['categoryidnumber'] = 'Category ID number';
$string['cleanuptask'] = 'Clean up task (tool_coursebulkactions)';
$string['confirmdelete'] = 'Confirm delete';
$string['coursedeletionfailed'] = 'Course deletion failed';
$string['coursefilterfield'] = 'Custom course field name';
$string['coursefilterlimiter'] = 'Custom course field operator';
$string['coursefullname'] = 'Fullname';
$string['courselabel'] = '{$a->label}: "{$a->field}" {$a->operator} "{$a->value}"';
$string['courselabelnovalue'] = '{$a->label}: {$a->field} {$a->operator}';
$string['coursesqueuedfordeletion'] = '{$a->count} Courses queued for deletion';
$string['createdby'] = 'Created by';
$string['criteria'] = 'Criteria';
$string['customfield'] = 'Custom field';
$string['customfields'] = 'Custom fields';

$string['deletecoursetask'] = 'Delete course task (tool_coursebulkactions)';
$string['deletewarning'] = '<p>These courses will be queued for deletion, which cannot be undone once completed.</p>';
$string['deletion'] = 'Deletion';
$string['dequeue'] = 'Dequeue';
$string['description'] = 'Description';
$string['duplicatetitle'] = 'A search with this title already exists. Please choose a different title.';

$string['editcoursesearch'] = 'Edit course search';
$string['enddate'] = 'End date';
$string['enrolments'] = 'Enrolments';
$string['excludedcategories'] = 'Excluded categories';
$string['excludedcategories_desc'] = 'Categories that are excluded from bulk actions (includes child categories).';

$string['fullname'] = 'Full name';

$string['generalsettings'] = 'General settings';
$string['graceperiod'] = 'Grace period';
$string['graceperiod_desc'] = 'The amount of time to wait before processing queued items. Setting a grace period can allow time for any mistakes to be rectified before irreversible actions are performed.';

$string['hardsettings'] = 'Hard settings';
$string['hardsettings_desc'] = 'Settings that affect the search results to prevent accidental deletion. These settings cannot be overridden when performing a search.';
$string['hideselected'] = 'Hide selected';

$string['limitqueueditemsrun'] = 'Limit of queued items to run at once';
$string['limitqueueditemsrun_desc'] = 'The maximum number of queued items to process at once.
    Setting this to a low number can help reduce the load on the server, but will also increase the time it takes for
    all queued items to be processed.';
$string['logretention'] = 'Log retention';
$string['logretention_desc'] = 'Logs retained longer than this period are removed. This will just be Failed, or Completed tasks';
$string['logs'] = 'Logs';

$string['managecoursebulkactions'] = 'Manage course bulk actions';

$string['newcoursesearch'] = 'New course search';
$string['notvisible'] = 'Not visible';
$string['nsections'] = '{$a} sections';

$string['pluginname'] = 'Course bulk actions';
$string['processtime'] = 'Expected Process time';

$string['queue'] = 'Queue';
$string['queued'] = 'Queued';
$string['queuedfordeletion'] = 'Queued for deletion';
$string['queuefordeletion'] = 'Queue for deletion';

$string['requeue'] = 'Requeue';

$string['savedsearches'] = 'Saved searches';
$string['savesearch'] = 'Save search';
$string['searchcourses'] = 'Search courses';
$string['searchcriteria'] = 'Search criteria';
$string['searchinstructions'] = 'Use the form below to search for courses to perform bulk actions on. You can save your search criteria for later use by giving your search a name.';
$string['searchsaved'] = 'Search saved';
$string['searchtitle'] = 'Search title';
$string['selectcategoriestoexclude'] = 'Select categories to exclude';
$string['selectitem'] = 'Select \'{$a->coursename}\'';
$string['shortname'] = 'Short name';
$string['showselected'] = 'Show selected';
$string['spacewarningthreshold'] = 'Space warning threshold';
$string['spacewarningthreshold_desc'] = 'The amount of free disk space at which a warning message will be shown to users.
    This is used to help prevent users from queuing courses for deletion when there is not enough disk space to safely do so.
    The default is 10GB, but you may want to adjust this based on the size of your courses and the amount of free disk space
    you have available.';
$string['startdate'] = 'Start date';
$string['status'] = 'Status';
$string['status_0'] = 'Queued';
$string['status_1'] = 'Pending';
$string['status_2'] = 'Processing';
$string['status_3'] = 'Completed';
$string['status_4'] = 'Failed';
$string['status_5'] = 'Deferred';

$string['timecreated'] = 'Time created';
$string['timemodified'] = 'Time modified';

$string['undeterminedspace'] = 'The available disk space on your server cannot be determined. Consider switching off Category recycle bin and removing "disk_free_space" from "disabled_functions" in your php.ini file.';
$string['usermodified'] = 'Modified by';

$string['withselectedcourses'] = 'With selected courses...';
