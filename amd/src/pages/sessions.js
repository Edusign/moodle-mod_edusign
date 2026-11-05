import * as Str from 'core/str';

const setSubmitLoading = function(submitButton, loadingLabel) {
    submitButton.dataset.originalValue = submitButton.value;
    submitButton.value = loadingLabel;
    submitButton.classList.add('is-loading');
    submitButton.setAttribute('aria-disabled', 'true');

    const indicator = document.createElement('span');
    indicator.className = 'edusign-submit-loading ml-2';
    indicator.setAttribute('role', 'status');
    indicator.setAttribute('aria-live', 'polite');
    indicator.innerHTML = `
        <span class="spinner-border spinner-border-sm mr-1" aria-hidden="true"></span>
        <span>${loadingLabel}</span>
    `;
    submitButton.insertAdjacentElement('afterend', indicator);
};

export const init = async(editing) => {
    if (typeof editing === 'object') {
        editing = Boolean(editing.editing);
    }

    const form = document.querySelector('form.mform');
    const submitButton = document.querySelector('#id_submitbutton');

    if (!form || !submitButton) {
        return;
    }

    const loadingLabel = await Str.get_string(
        editing ? 'savingSessionOnEdusign' : 'creatingSessionsOnEdusign',
        'mod_edusign'
    );

    form.addEventListener('submit', (event) => {
        if (form.dataset.edusignSubmitting === '1') {
            event.preventDefault();
            return;
        }

        form.dataset.edusignSubmitting = '1';
        setSubmitLoading(submitButton, loadingLabel);
    });
};
