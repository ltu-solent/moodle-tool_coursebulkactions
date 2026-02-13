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
 * TODO describe module course_bulk_actions
 *
 * @module     tool_coursebulkactions/course_bulk_actions
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @author Mark sharp <mark.sharp@solent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getList} from 'core/normalise';
import Notification from 'core/notification';
import {prefetchStrings} from 'core/prefetch';
import {get_string as getString} from 'core/str';
import Ajax from 'core/ajax';
import Pending from 'core/pending';
import {add as addToast} from 'core/toast';

const selectors = {
    activeRows: 'input.coursecheckbox:checked',
    bulkActions: '#coursebulkactions_coursebulkactions',
    table: '#coursebulkactions_searchresults_table',
};

export const init = () => {
    prefetchStrings('tool_coursebulkactions', [
        'confirmdelete',
        'deletewarning',
        'coursesqueuedfordeletion',
    ]);
    prefetchStrings('core', [
        'delete',
    ]);
    registerEventListeners();
};

/**
 * Register event listeners for bulk deleting
 */
const registerEventListeners = () => {
    document.querySelector(selectors.bulkActions)?.addEventListener('change', e => {
        const action = e.target;
        if (action.value.indexOf('#') !== -1) {
            e.preventDefault();
            if (action.value == '#queuefordeletion') {
                deleteCoursesConfirm();
            }
            if (action.value == '#hideselected') {
                changeVisibility('hide');
            }
            if (action.value == '#showselected') {
                changeVisibility('show');
            }
        }
    });
};

const deleteCoursesConfirm = () => {
    const table = document.querySelector(selectors.table);
    const activeRows = getList(table.querySelectorAll(selectors.activeRows));
    const courseids = activeRows.map(item => item.value);
    if (courseids.length === 0) {
        return;
    }

    Notification.saveCancelPromise(
        getString('confirmdelete', 'tool_coursebulkactions'),
        getString('deletewarning', 'tool_coursebulkactions', {count: courseids.length}),
        getString('delete', 'core'),
    ).then(() => {
        return deleteCourses(courseids);
    }).catch(() => {
        // User cancelled, do nothing.
        return;
    });
};

/**
 * Queue courses for deletion
 *
 * @param {Number[]} ids Course IDs
 */
async function deleteCourses(ids) {
    const request = {
        methodname: 'tool_coursebulkactions_queue_courses_for_deletion',
        args: {
            courseids: ids,
        },
    };
    const pendingPromise = new Pending('tool_coursebulkactions/deletingcourses');
    try {
        const result = await Ajax.call([request])[0];
        const errorcount = result.errors?.length || 0;
        const successcount = ids.length - errorcount;
        if (successcount > 0) {
            addToast(getString('coursesqueuedfordeletion', 'tool_coursebulkactions', {count: successcount}), 'success');
        }
        if (errorcount > 0) {
            result.errors.forEach(message => {
                    addToast(message, 'error', {delay: 10000, autohide: false, closeButton: true});
            });
        }
        // Reload the page to update the list of courses.
        window.location.reload();
    } catch (error) {
        Notification.exception(error);
    }
    pendingPromise.resolve();
}

/**
 * Change course visibility
 * @param {string} action The action to perform, either 'hide' or 'show'
 * @returns {Promise<void>}
 */
async function changeVisibility(action) {
    const table = document.querySelector(selectors.table);
    const activeRows = getList(table.querySelectorAll(selectors.activeRows));
    const courseids = activeRows.map(item => item.value);
    if (courseids.length === 0) {
        return;
    }
    let courses = [];
    const visible = action === 'show';
    courseids.forEach(id => {
        courses.push({id: id, visible: +visible});
    });
    const request = {
        methodname: 'tool_coursebulkactions_change_course_visibility',
        args: {
            courses: courses,
        },
    };
    try {
        await Ajax.call([request])[0];
        // Reload the page to update the list of courses.
        window.location.reload();
    } catch (error) {
        Notification.exception(error);
    }
}
