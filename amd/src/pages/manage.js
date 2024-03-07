/* eslint-disable no-async-promise-executor */
/* eslint-disable promise/no-nesting */
import Ajax from 'core/ajax';
import * as Str from 'core/str';
import notification from 'core/notification';
import ModalDeleteCancel from 'core/modal_delete_cancel';
import ModalSaveCancel from 'core/modal_save_cancel';
import ModalEvents from 'core/modal_events';

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

export const init = async() => {
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

};
