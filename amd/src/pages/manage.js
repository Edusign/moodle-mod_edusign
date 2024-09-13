/* eslint-disable no-unused-vars */
/* eslint-disable no-console */
/* eslint-disable no-async-promise-executor */
/* eslint-disable promise/no-nesting */
import Ajax from 'core/ajax';
import * as Str from 'core/str';
import notification from 'core/notification';
import ModalDeleteCancel from 'core/modal_delete_cancel';
import ModalSaveCancel from 'core/modal_save_cancel';
import Modal from 'core/modal';
import dayjs from '../lib/dayjs';
import * as Toast from 'core/toast';
import ModalEvents from 'core/modal_events';

const toBase64 = file => new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = () => resolve(reader.result);
    reader.onerror = reject;
});

const removeSession = function(sessionId, removeOnEdusign = true) {
    return Ajax.call([{
        methodname: 'mod_edusign_remove_session',
        args: {
            sessionId,
            withEdusignDelete: removeOnEdusign,
        }
    }])[0];
};

const archiveSessionHandler = function(sessionId, archiveState) {
    return Ajax.call([{
        methodname: 'mod_edusign_archive_session',
        args: {
            cmId,
            sessionId,
            archiveState,
        }
    }])[0];
};

const askRemoveSession = function(sessionId) {
    return new Promise(async(resolve, reject) => {
        return ModalDeleteCancel.create({
            title: await Str.get_string('removeSession', 'mod_edusign'),
            body: (`
                <p>${await Str.get_string('removeSessionQuestions', 'mod_edusign')}</p>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="removeOnEdusign" checked>
                    <label class="form-check-label" for="removeOnEdusign">
                    ${await Str.get_string('removeSessionAndSheet', 'mod_edusign')}
                    </label>
                </div>
            `),
            show: true,
            removeOnClose: true,
        }).then((modalInstance) => {
            modalInstance.getRoot().on(ModalEvents.delete, () => {
                const removeOnEdusign = document.querySelector('#removeOnEdusign').checked;
                removeSession(sessionId, removeOnEdusign)
                    .then(resolve)
                    .catch(reject);
            });
            return modalInstance;
        });
    });
};

const askArchiveSession = function(sessionId) {
    return new Promise(async(resolve, reject) => {
        return ModalSaveCancel.create({
            title: await Str.get_string('archiveSession', 'mod_edusign'),
            body: (`
                <p>${await Str.get_string('archiveSessionQuestion', 'mod_edusign')}</p>
            `),
            show: true,
            removeOnClose: true,
        }).then((modalInstance) => {
            modalInstance.getRoot().on(ModalEvents.save, () => {
                archiveSessionHandler(sessionId, true)
                    .then(resolve)
                    .catch(reject);
            });
            return modalInstance;
        });
    });
};

const askUnarchiveSession = function(sessionId) {
    return new Promise(async(resolve, reject) => {
        return ModalSaveCancel.create({
            title: await Str.get_string('unarchiveSession', 'mod_edusign'),
            body: (`
                <p>${await Str.get_string('unarchiveSessionQuestion', 'mod_edusign')}</p>
            `),
            show: true,
            removeOnClose: true,
        }).then((modalInstance) => {
            modalInstance.getRoot().on(ModalEvents.save, () => {
                archiveSessionHandler(sessionId, false)
                    .then(resolve)
                    .catch(reject);
            });
            return modalInstance;
        });
    });
};

const importSessionFromCSV = function(sessions) {
    return Ajax.call([{
        methodname: 'mod_edusign_import_sessions',
        args: {
            sessions,
            cmId,
        }
    }])[0].promise();
};

const validateSessions = async(sessions, form = null) => {
    // Vérification des noms
    let hasError = false;
    sessions.forEach((session, index) => {
        form?.querySelector(`[name="sessions[${index}][name]"]`).classList.remove('is-invalid');
        if (!session.name) {
            hasError = true;
            form?.querySelector(`[name="sessions[${index}][name]"]`).classList.add('is-invalid');
        }
    });


    // Vérification des dates
    sessions.forEach((session, index) => {
        let isInvalid = false;
        form?.querySelector(`[name="sessions[${index}][start_date]"]`).classList.remove('is-invalid');
        form?.querySelector(`[name="sessions[${index}][end_date]"]`).classList.remove('is-invalid');
        if (!session.start_date || !session.end_date) {
            isInvalid = true;
        }
        if (!dayjs(session.start_date).isValid() || !dayjs(session.end_date).isValid()) {
            isInvalid = true;
        }
        if (dayjs(session.start_date).isAfter(dayjs(session.end_date))) {
            isInvalid = true;
        }
        hasError = hasError || isInvalid;
        if (isInvalid) {
            form?.querySelector(`[name="sessions[${index}][start_date]"]`).classList.add('is-invalid');
            form?.querySelector(`[name="sessions[${index}][end_date]"]`).classList.add('is-invalid');
        }
    });
    if (hasError) {
        throw new Error(await Str.get_string(
            'sessions_have_errors',
            'mod_edusign',
            'Some sessions have errors, please check the form'
        ));
    }

    return sessions;
};

const onImportCSVUpdatedSessionsSubmit = async(event) => {
    event.preventDefault();
    const form = event.target;
    const button = form.querySelector('#import-csv-save');
    button.classList.add('is-loading');
    try {
        const formData = new FormData(form);
        // Récupération des sessions
        const sessions = Array.from(formData.entries())
            .filter(([key]) => key.startsWith('sessions'))
            .map(([key, value]) => {
                const [, index, field] = key.match(/sessions\[(\d+)\]\[(\w+)\]/);
                return {index, field, value};
            })
            .reduce((acc, {index, field, value}) => {
                if (!acc[index]) {
                    acc[index] = {};
                }
                acc[index][field] = value;
                return acc;
            }, []);

        // Throw error if there is any
        await validateSessions(sessions, form);

        return await importSessionFromCSV(sessions)
            .then((result) => {
                button.classList.remove('is-loading');
                return result;
            });
    } catch (error) {
        button.classList.remove('is-loading');
        throw error;
    }
};

const addImportCSVTableFromData = async(data) => {
    if (!data || !data.length) {
        Toast.add(
            await Str.get_string(
                'csv_no_data_found_error',
                'mod_edusign',
                'No data found in the CSV file'
            ), {
            type: 'danger'
        });
        return;
    }
    const table = document.getElementById('import-csv-imported-form-table').content.cloneNode(true);
    const tbody = table.querySelector('tbody');
    const trTemplate = document.getElementById('import-csv-imported-line').content;
    data.forEach((row, index) => {
        const tr = trTemplate.cloneNode(true);
        const name = tr.querySelector('.name');
        const dateStart = tr.querySelector('.start_date');
        const dateEnd = tr.querySelector('.end_date');

        name.setAttribute('name', `sessions[${index}][name]`);
        name.value = row.session_name;
        dateStart.value = row.start_date;
        dateStart.setAttribute('name', `sessions[${index}][start_date]`);
        dateEnd.value = row.end_date;
        dateEnd.setAttribute('name', `sessions[${index}][end_date]`);
        tbody.appendChild(tr);
    });
    table.querySelector('#import-csv-form').addEventListener('submit', (event) => {
        onImportCSVUpdatedSessionsSubmit(event)
            .then(() => {
                return document.location.reload();
            })
            .catch((error) => {
                console.error(error);
                Toast.add(error?.message || error?.error, {
                    type: 'danger'
                });
            });
    });
    document.getElementById('import-csv-table').innerHTML = '';
    document.getElementById('import-csv-table').appendChild(table);
};

const parseSessionsCSVFile = async(file) => {
    return Ajax.call([{
        methodname: 'mod_edusign_parse_csv',
        args: {
            base64File: await toBase64(file),
        }
    }])[0].promise()
        .catch(async(error) => {
            console.error(error);
            Toast.add(
                await Str.get_string(
                    'csv_import_error',
                    'mod_edusign',
                    error?.error || 'An unknowed error has occured during the import'
                ), {
                type: 'danger'
            });
        });
};

const openModalImportSession = async() => {
    return Modal.create({
        title: await Str.get_string(
            'import_sessions',
            'mod_edusign',
            'Import sessions',
        ),
        body: document.querySelector('#import-csv-modal').innerHTML,
        large: true,
        show: true,
        removeOnClose: true,
    }).then((modalInstance) => {
        const modalRoot = modalInstance.getRoot()[0];
        modalRoot.querySelector('#import-session-form')
            .addEventListener('submit', async(event) => {
                event.preventDefault();
                const form = event.target;
                const formData = new FormData(form);
                const file = formData.get('csv-file');

                return parseSessionsCSVFile(file)
                    .then(addImportCSVTableFromData);
            });
        const inputCSVFile = modalRoot.querySelector('#input-csv-file');
        inputCSVFile.addEventListener('change', (event) => {
            modalRoot.querySelector('#import-session-form').requestSubmit();
        });
        return modalInstance;
    });
};


let cmId = null;
export const init = async(_cmId) => {
    cmId = _cmId;
    // Handles remove session
    const removeButtons = document.querySelectorAll('.remove-session');
    removeButtons.forEach((button) => {
        button.addEventListener('click', async(event) => {
            event.preventDefault();
            const sessionId = button.closest('tr').dataset.sessionId;
            await askRemoveSession(sessionId)
                .then(async() => {
                    return window.location.reload();
                })
                .catch(async(data) => {
                    notification.addNotification({
                        message: await Str.get_string(
                            'session_removed_error',
                            'mod_edusign',
                            data?.error || 'An unknowed error has occured'
                        ),
                        type: 'error'
                    });
                });
        });
    });

    // Handles archive session
    const archiveButtons = document.querySelectorAll('.archive-session');
    archiveButtons.forEach((button) => {
        button.addEventListener('click', async(event) => {
            event.preventDefault();
            const sessionId = button.closest('tr').dataset.sessionId;
            await askArchiveSession(sessionId)
                .then(async() => {
                    return window.location.reload();
                })
                .catch(async(data) => {
                    notification.addNotification({
                        message: await Str.get_string(
                            'session_archived_error',
                            'mod_edusign',
                            data?.error || 'An unknowed error has occured'
                        ),
                        type: 'error'
                    });
                });
        });
    });

    // Handles unarchive session
    const unarchiveButtons = document.querySelectorAll('.unarchive-session');
    unarchiveButtons.forEach((button) => {
        button.addEventListener('click', async(event) => {
            event.preventDefault();
            const sessionId = button.closest('tr').dataset.sessionId;
            await askUnarchiveSession(sessionId)
                .then(async() => {
                    return window.location.reload();
                })
                .catch(async(data) => {
                    notification.addNotification({
                        message: await Str.get_string(
                            'session_unarchived_error',
                            'mod_edusign',
                            data?.error || 'An unknowed error has occured'
                        ),
                        type: 'error'
                    });
                });
        });
    });


    // Handles import session button
    const importSessionButton = document.querySelector('#import-sessions');
    importSessionButton.addEventListener('click', openModalImportSession);
};
