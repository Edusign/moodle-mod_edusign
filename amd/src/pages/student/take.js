/* eslint-disable no-console */
/* eslint-disable no-unused-vars */
import Ajax from 'core/ajax';
import {add as addToast} from 'core/toast';
import * as Str from 'core/str';

let cmId;
let session;
export const init = async(_cmId, _student, _course, _session) => {
    cmId = _cmId;
    session = _session;
    window.addEventListener("message", onDocumentSigned);
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
