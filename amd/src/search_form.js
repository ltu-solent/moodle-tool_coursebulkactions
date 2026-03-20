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
 * TODO describe module search_form
 *
 * @module     tool_coursebulkactions/search_form
 * @copyright  2026 Southampton Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import ModalForm from 'core_form/modalform';
import {get_string as getString} from 'core/str';
import {add as addToast} from 'core/toast';

export const init = () => {
    const searchbuttons = document.querySelectorAll('[data-action="tool-coursebulkactions-search"]');
    searchbuttons.forEach(searchbutton => {
        searchbutton.addEventListener('click', search);
    });
};

const search = (e) => {
    e.preventDefault();
    const element = e.currentTarget;
    const modal = new ModalForm({
        formClass: 'tool_coursebulkactions\\forms\\dynamic_search_form',
        args: {
            id: element.getAttribute('data-id'),
        },
        modalConfig: {
            title: getString('searchcourses', 'tool_coursebulkactions'),
        },
        saveButtonText: getString('savesearch', 'tool_coursebulkactions'),
        returnFocus: element,
    });
    modal.addEventListener(modal.events.FORM_SUBMITTED, event => {
        const data = event.detail;
        if (data.error) {
            addToast(getString(data.error, 'tool_coursebulkactions'));
        } else {
            addToast(getString('searchsaved', 'tool_coursebulkactions'));
            window.location.reload();
        }
    });
    modal.show();
};