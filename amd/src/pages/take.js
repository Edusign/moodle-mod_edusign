/* eslint-disable no-async-promise-executor */
/* eslint-disable no-console */
/* eslint-disable max-len */
/* eslint-disable promise/always-return */

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
import ModalSaveCancel from 'core/modal_save_cancel';
import ModalDeleteCancel from 'core/modal_delete_cancel';
import Modal from 'core/modal';
import ModalEvents from 'core/modal_events';
import dayjs from '../lib/dayjs';
import { add as addToast } from 'core/toast';

const getStudentIframeLink = function (studentId) {
    return Ajax.call([{
        methodname: 'mod_edusign_get_signature_link_from_course',
        args: {
            sessionId,
            userId: studentId,
            userType: 'student',
        }
    }])[0]
        .then(({ result }) => {
            return result?.[0]?.SIGNATURE_LINK;
        });
};

const getTeacherIframeLink = function (teacherId) {
    return Ajax.call([{
        methodname: 'mod_edusign_get_signature_link_from_course',
        args: {
            sessionId,
            userId: teacherId,
            userType: 'teacher',
        }
    }])[0]
        .then(({ result }) => {
            return result?.[0]?.SIGNATURE_LINK;
        });
};

const archiveSession = function (archiveState = true) {
    return Ajax.call([{
        methodname: 'mod_edusign_archive_session',
        args: {
            sessionId,
            archiveState,
        }
    }])[0]
        .then((result) => {
            window.location.href = '/mod/edusign/view.php?id=' + cmId;
            return result;
        });
};

// Fonction pour créer le select
const createHourAndMinutesSelect = (id = 'input-time', defaultValue = dayjs()) => {
    const selectHour = document.createElement('select');
    selectHour.id = `${id}-hour`;
    selectHour.classList.add('mr-2');
    selectHour.classList.add('form-control');

    // Boucle pour générer les options pour les heures de 00 à 23
    for (let hour = 0; hour <= 23; hour++) {
        const option = document.createElement('option');
        option.value = hour.toString().padStart(2, '0'); // Ajouter un 0 en tête si l'heure est inférieure à 10
        option.textContent = hour.toString().padStart(2, '0') + ' h'; // Format HH
        selectHour.appendChild(option);
    }

    // Sélecteur HTML pour les minutes
    const selectMinute = document.createElement('select');
    selectMinute.id = `${id}-minutes`;
    // Boucle pour générer les options pour les minutes avec un intervalle de 5 minutes
    for (let minute = 0; minute <= 55; minute += 5) {
        const option = document.createElement('option');
        option.value = minute.toString().padStart(2, '0'); // Ajouter un 0 en tête si les minutes sont inférieures à 10
        option.textContent = minute.toString().padStart(2, '0') + ' min'; // Format MM
        selectMinute.appendChild(option);
    }

    selectMinute.classList.add('form-control');

    const container = document.createElement('div');
    container.classList.add('input-group');

    if (defaultValue.isValid()) {
        selectHour.value = defaultValue.hour().toString().padStart(2, '0');
        // Round the minutes to the nearest multiple of 5
        const roundedMinutes = Math.round(defaultValue.minute() / 5) * 5;
        if (roundedMinutes === 60) {
            selectHour.value = (Number(selectHour.value) + 1).toString().padStart(2, '0');
            selectMinute.value = '00';
        } else {
            selectMinute.value = roundedMinutes.toString().padStart(2, '0');
        }
    }
    container.appendChild(selectHour);
    container.appendChild(selectMinute);
    return container;
};

const cmId = Number(document.querySelector('.students-table').dataset.instanceId);
const sessionId = Number(document.querySelector('.students-table').dataset.sessionId);

const sendStudentSignEmail = (studentsId) => {
    return sendMethod('send_sign_email', studentsId).then((result) => {
        setTimeout(() => {
            // A timeout to wait for the server to send the email, because mails are synced asynchronously
            refreshView();
        }, 4000);
        return result;
    });
};

const setStudentAbsent = (studentId, comment) => {
    return sendMethod('set_student_absent', [studentId], { comment });
};

/**
 * Opens a modal to manually sign
 * @param {String} userType
 * @param {Object} user
 * @returns Promise
 */
const askUserSignature = async function (userType, user) {
    let iframeURL = null;
    if (userType === 'student') {
        iframeURL = await getStudentIframeLink(user.edusign_api_id);
    } else {
        iframeURL = await getTeacherIframeLink(user.edusign_api_id);
    }
    return Modal.create({
        title: 'Signature',
        body: (`
            <iframe id="signature-iframe" src="${iframeURL}"></iframe>
        `),
        footer: '',
        show: true,
        removeOnClose: true,
    }).then((modalInstance) => {
        modalInstance.getRoot().addClass('signature-modal');

        // A la fermeture de la modale, on rafraichit la vue
        modalInstance.getRoot().on(ModalEvents.hidden, () => {
            refreshView();
        });

        window.addEventListener("message", ({data}) => {
            modalInstance.hide();
            if (data === 'accept-signature') {
                refreshView();
            }
        });

    });
};

/**
 * Opens a modal to add a comment to the student absence
 * @returns Promise
 */
const openModalAddCommentToStudentAbsence = function () {
    return new Promise((resolve) => {
        return ModalSaveCancel.create({
            title: 'Student absence',
            body: (`
                <textarea placeholder="Add a comment for this absence (optionnaly)" class="form-control" id="input-comment" aria-label="Comment for absence"></textarea>
            `),
            show: true,
            removeOnClose: true,
        }).then((modalInstance) => {
            modalInstance.getRoot().on(ModalEvents.save, () => {
                resolve(document.querySelector('#input-comment').value);
            });
        });
    });
};

/**
 * Opens a modal to set the student as delayed
 * @returns Promise
 */
const openModalSetStudentDelayed = function () {
    return new Promise((resolve) => {
        return ModalSaveCancel.create({
            title: 'Student delay',
            body: (`
                <label for="input-delay">Delay</label>
                <div class="input-group mb-3">
                    <input type="number" class="form-control" id="input-delay" aria-label="Minutes of delay">
                    <div class="input-group-append">
                        <span class="input-group-text">minutes</span>
                    </div>
                </div>
            `),
            show: true,
            removeOnClose: true,
        }).then((modalInstance) => {
            modalInstance.getRoot().on(ModalEvents.save, () => {
                resolve(Number(document.querySelector('#input-delay').value));
            });
        });
    });
};

/**
 * Opens a modal to set the student as early departure
 * @returns Promise
 */
const openModalSetStudentEarlyDeparture = function () {
    return new Promise((resolve) => {
        return ModalSaveCancel.create({
            title: 'Student early departure',
            body: (`
                <label for="input-early-departure">Early departure</label>
                <div class="input-group mb-3" id="input-early-departure-container">
                </div>
            `),
            show: true,
            removeOnClose: true,
        }).then((modalInstance) => {
            const selects = createHourAndMinutesSelect('input-early-departure');
            document.querySelector('#input-early-departure-container').appendChild(selects);

            modalInstance.getRoot().on(ModalEvents.save, () => {
                const hour = document.querySelector('#input-early-departure-hour').value;
                const minutes = document.querySelector('#input-early-departure-minutes').value;

                const departureDate = dayjs().hour(hour).minute(minutes);
                resolve(departureDate);
            });
        });
    });
};

/**
 * Set the student as delayed
 * @param {int} delay minutes to delay
 * @param {string} studentId
 * @returns Promise
 */
const setStudentDelayed = function (delay = 15, studentId) {
    return sendMethod('set_student_delay', [studentId], { delay });
};

const sendMethod = function (methodName, studentsId, args = {}) {
    return Ajax.call([{
        methodname: 'mod_edusign_take_attendance',
        args: {
            cmId,
            sessionId,
            method: methodName,
            studentsId: studentsId.join(','),
            args: JSON.stringify(args || {})
        }
    }])[0]
        .then((result) => {
            refreshView();
            return result;
        });
};

const refreshView = () => {
    return Ajax.call([{
        methodname: 'mod_edusign_get_students_and_teachers',
        args: {
            cmId,
            sessionId,
        }
    }])[0].then(({ result }) => {
        initTable(result.students);
        initTeachers(result.teachers);
        return result;
    }).catch(async (error) => {
        console.error(error);
        addToast(await Str.get_string('refresh_error', 'mod_edusign', error?.message || 'An unknowed error has occured'), {
            type: 'error'
        });
    });
};

const initTeachers = function (teachers) {
    const teachersList = document.querySelector('#teachersList');
    teachersList.innerHTML = '';
    const template = document.querySelector("#teachers");

    teachers.forEach((teacher) => {
        const clone = template.content.cloneNode(true);
        const teacherCard = clone.querySelector(".teacher");
        const fullNameTeacher = clone.querySelector(".teacher--name>strong");
        const actionsTeacher = clone.querySelector(".teacher--actions");
        const teacherSignature = clone.querySelector(".teacher--signature-img");
        initActionButtonForTeacher(teacher, actionsTeacher);

        if (teacher.hasSigned) {
            actionsTeacher.remove();
            teacherSignature.src = teacher.signature;
        } else {
            teacherSignature.remove();
        }

        teacherCard.dataset.teacherId = teacher.edusign_api_id;
        fullNameTeacher.textContent = teacher.firstname + ' ' + teacher.lastname;

        teachersList.appendChild(clone);
    });
};

const initTable = async function (students) {
    const tbody = document.querySelector("#studentListTbody");
    tbody.innerHTML = '';
    const template = document.querySelector("#studentRow");

    // Sort students by firstname
    const sortedStudents = students.sort((a, b) => {
        return a.firstname.localeCompare(b.firstname);
    });

    const sortedStudentsPromise = sortedStudents.map(async (student) => {
        const clone = template.content.cloneNode(true);
        const studentTR = clone.querySelector("tr");
        const checkboxTD = clone.querySelector(".user-checkbox");
        const fullNameTD = clone.querySelector(".fullname");
        const presentialStateTD = clone.querySelector(".presential-state");
        const actionsTD = clone.querySelector(".actions");

        if (student.edusign_data?.signature) {
            checkboxTD.setAttribute('disabled', 'disabled');
            checkboxTD.setAttribute('data-toggle', 'tooltip');
            checkboxTD.setAttribute('title', `This student has already signed`);
        }
        studentTR.dataset.studentId = student.edusign_api_id;
        fullNameTD.textContent = student.firstname + ' ' + student.lastname;
        presentialStateTD.innerHTML = await getStudentPresentialStateHTML(student);
        initActionButtonForStudent(student, actionsTD);
        return clone;
    });
    const allStudentsNodes = await Promise.all(sortedStudentsPromise);
    tbody.append(...allStudentsNodes);
    // Removes the disabled property of the sign button if at least one checkbox is checked
    tbody.querySelectorAll('.user-checkbox').forEach((checkbox) => {
        checkbox.addEventListener('change', function () {
            const signSelectedBtn = document.querySelector('#sign-selected-btn');
            if (tbody.querySelectorAll('.user-checkbox:checked').length > 0) {
                signSelectedBtn.removeAttribute('disabled');
                signSelectedBtn.removeAttribute('data-toggle');
            } else {
                signSelectedBtn.setAttribute('disabled', 'disabled');
                signSelectedBtn.setAttribute('data-toggle', 'tooltip');
            }
        });
    });
};

const getStudentPresentialStateHTML = async function (student) {
    let html = '';
    let signatureHTML = '';

    if (!student.edusign_data) {
        html = `<span class="badge badge-default">${await Str.get_string('noData', 'mod_edusign')}</span>`;
    } else if (student.edusign_data.signature) {
        html = `<span class="badge badge-success">${await Str.get_string('present', 'mod_edusign')}</span>`;
        signatureHTML = `<img src="${student.edusign_data.signature}" style="height: 50px" class="signature" />`;
    } else if (!student.edusign_data.signature && student.edusign_data.signatureEmail) {
        html = `<span class="badge badge-info">${await Str.get_string('waitingSignature', 'mod_edusign')}</span>`;
    } else {
        html = '<span class="badge badge-danger">Absent</span>';
    }
    if (student.edusign_data?.delay > 0) {
        html += `<span class="text-small text-muted">${student.edusign_data.delay} ${await Str.get_string('minLate', 'mod_edusign')}</span>`;
    }
    if (student.edusign_data?.earlyDeparture) {
        html += `<span class="text-small text-muted">${await Str.get_string('departureAt', 'mod_edusign')} ${dayjs(student.edusign_data.earlyDeparture).format('HH:mm')} (${dayjs(session.date_end).diff(dayjs(student.edusign_data.earlyDeparture), 'minutes')} min)</span>`;
    }
    return `<div class="state-container">
                <div class="left-container">${html}</div>
                <div class="right-container">${signatureHTML}</div>
            </div>`;
};

const initActionButtonForTeacher = function (teacher, context) {
    // Allows a user to manually sign
    context.querySelector('.manual-sign-btn--teacher').addEventListener('click', function () {
        askUserSignature('teacher', teacher);
    });
};

const initActionButtonForStudent = function (student, context) {
    // Allows a user to manually sign
    context.querySelector('.manual-sign-btn').addEventListener('click', function () {
        askUserSignature('student', student);
    });

    // Send an email to the student to sign the document
    context.querySelector('.send-sign-email-btn').addEventListener('click', function () {
        sendStudentSignEmail([student.edusign_api_id])
            .then(async (data) => {
                addToast(await Str.get_string('send_sign_email_success', 'mod_edusign'), {
                    type: 'success'
                });
                return data;
            })
            .catch(async (error) => {
                console.error(error);
                addToast(await Str.get_string('send_sign_email_error', 'mod_edusign', error?.message || 'An unknowed error has occured'), {
                    type: 'error'
                });
            });
    });

    // Set the student as absent
    context.querySelector('.justified-abscence-btn').addEventListener('click', function () {
        openModalAddCommentToStudentAbsence()
            .then((comment) => {
                return setStudentAbsent(student.edusign_api_id, comment);
            })
            .then(async (data) => {
                addToast(await Str.get_string('set_student_absent_success', 'mod_edusign'), {
                    type: 'success'
                });
                return data;
            })
            .catch(async (error) => {
                console.error(error);
                addToast(await Str.get_string('set_student_absent_error', 'mod_edusign', error?.message || 'An unknowed error has occured'), {
                    type: 'error'
                });
            });
    });

    // Set the student as delayed
    context.querySelector('.late-btn').addEventListener('click', function () {
        // Opens a modal to set in minutes the delay of the student
        openModalSetStudentDelayed()
            .then((delay) => {
                // Use the delay to set the student as delayed
                return setStudentDelayed(delay, student.edusign_api_id);
            })
            .then(async (data) => {
                addToast(await Str.get_string('set_student_delay_success', 'mod_edusign'), {
                    type: 'success'
                });
                return data;
            })
            .catch(async (error) => {
                console.error(error);
                addToast(await Str.get_string('set_student_delay_error', 'mod_edusign', error?.message || 'An unknowed error has occured'), {
                    type: 'error'
                });
            });
    });

    // Set the early departure of the student
    context.querySelector('.early-departure-btn').addEventListener('click', function () {
        // Opens a modal to set in minutes the early departure of the student
        openModalSetStudentEarlyDeparture()
            .then((earlyDeparture) => {
                // Use the early departure to set the student as early departure
                return sendMethod('set_student_early_departure', [student.edusign_api_id], { earlyDeparture: earlyDeparture.toISOString() });
            })
            .then(async (data) => {
                addToast(await Str.get_string('set_student_early_departure_success', 'mod_edusign'), {
                    type: 'success'
                });
                return data;
            })
            .catch(async (error) => {
                console.error(error);
                addToast(await Str.get_string('set_student_early_departure_error', 'mod_edusign', error?.message || 'An unknowed error has occured'), {
                    type: 'error'
                });
            });
    });
};
const initCheckbox = function () {
    // Allows to check or uncheck all the students
    document.querySelector('#main-checkbox').addEventListener('change', function () {
        document.querySelectorAll('.user-checkbox:not([disabled])').forEach((checkbox) => {
            checkbox.checked = this.checked;
            // Trigger change to update the sign button bellow
            checkbox.dispatchEvent(new Event('change'));
        });
    });
};

const initRefreshButton = function () {
    // Allows to refresh the table
    document.querySelector('#refresh-button').addEventListener('click', function () {
        refreshView();
    });
};

const initSignButton = function () {
    // Allows to send signature request for all checked users
    document.querySelector('#sign-selected-btn').addEventListener('click', function () {
        // Select all checked students
        const studentsId = Array.from(document.querySelectorAll('.user-checkbox:checked')).map((checkbox) => {
            return checkbox.closest('tr').dataset.studentId;
        });
        // Send them a signature request
        sendStudentSignEmail(studentsId).then(async () => {
            const mainCheckbox = document.querySelector('#main-checkbox');
            mainCheckbox.checked = false;
            document.querySelector('#main-checkbox').dispatchEvent(new Event('change'));
            return addToast(await Str.get_string('send_sign_email_success', 'mod_edusign'), {
                type: 'success'
            });
        }).catch(async (data) => {
            addToast(await Str.get_string('send_sign_email_error', 'mod_edusign', data?.error || 'An unknowed error has occured'), {
                type: 'error'
            });
        });
    });
};

const initArchiveButton = function () {
    document.querySelector('#archive-session-btn').addEventListener('click', function () {
        return new Promise(async (resolve, reject) => {
            return ModalDeleteCancel.create({
                title: await Str.get_string('archiveSession', 'mod_edusign'),
                body: (`
                    <p>${await Str.get_string('archiveSessionQuestion', 'mod_edusign')}</p>
                `),
                show: true,
                removeOnClose: true,
                buttons: {
                    'delete': await Str.get_string('archive', 'mod_edusign'),
                },
            }).then((modalInstance) => {
                modalInstance.getRoot().on(ModalEvents.delete, () => {
                    archiveSession();
                });
                return modalInstance;
            })
                .then(resolve)
                .catch(reject);
        });
    });
};


const onDocumentSigned = () => {
    return Ajax.call([{
        methodname: 'mod_edusign_on_attendance_sheet_signed',
        args: {
            cmId,
            sessionId: session.id,
        }
    }])[0]
    .catch(async(error) => {
        console.error(error);
    }).then(() => {
        document.location.href = '/mod/edusign/view.php?id=' + cmId;
        return;
    });
};


// eslint-disable-next-line no-unused-vars
let course = null;
let session = null;

export const init = async (students, teachers, _course, _session) => {
    course = _course;
    session = _session;
    initTable(students);
    initTeachers(teachers);
    initCheckbox();
    initRefreshButton();
    initSignButton();
    initArchiveButton();
};
