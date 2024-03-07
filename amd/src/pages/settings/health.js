/* eslint-disable promise/always-return */
/* eslint-disable no-alert */

/**
 * Allows status form elements to be modified.
 *
 * @module    mod_edusign
 * @author    Sébastien Lampazona
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * Ex: https://moodledev.io/docs/guides/javascript/ajax
 */


import Ajax from 'core/ajax';
import * as Str from 'core/str';

const testApi = () => Ajax.call([{
    methodname: 'mod_edusign_test_api',
    args: {}
}])[0];


export const init = async () => {
    document.querySelector('#test-api-connection').addEventListener('click', () => {
        testApi()
            .then(async (data) => {
                if (data?.error || !data?.result) {
                    throw new Error(await Str.get_string('test_api_error', 'mod_edusign', data.error || 'API connection failed'));
                }
                alert(await Str.get_string('test_api_success', 'mod_edusign'));
                return data;
            })
            .catch((response) => {
                alert(response?.message || response.error);
            });
    });
};
