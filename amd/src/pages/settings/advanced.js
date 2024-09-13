/* eslint-disable no-console */
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

/**
 * Generates a random string.
 *
 * @returns {string} A random string.
 */
function rand() {
    return Math.random().toString(36).substring(2); // Remove `0.`
}

/**
 * Generates a token by concatenating three random numbers.
 *
 * @returns {string} The generated token.
 */
function generateToken() {
    return rand() + rand() + rand() + rand();
}


export const init = async(webhookBaseUrl) => {
    document.querySelector('#test-api-connection').addEventListener('click', () => {
        testApi()
            .then(async(data) => {
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

    const webhookTokenInput = document.querySelector('#id_webhook_token');
    const webhookUrlInput = document.querySelector('#id_webhook_url');
    const tokenRefreshBtn = document.querySelector('#id_refreshtoken');

    webhookUrlInput.value = `${webhookBaseUrl}?token=${webhookTokenInput.value}`;

    console.log('INIT', webhookBaseUrl, webhookTokenInput.value, webhookUrlInput.value);

    webhookTokenInput.addEventListener('input', async(e) => {
        webhookUrlInput.value = `${webhookBaseUrl}?token=${e.target.value}`;
    });

    tokenRefreshBtn.addEventListener('click', async() => {
        webhookTokenInput.value = generateToken();
        webhookUrlInput.value = `${webhookBaseUrl}?token=${webhookTokenInput.value}`;
    });
};
