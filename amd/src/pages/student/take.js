/* eslint-disable no-unused-vars */
/* eslint-disable no-async-promise-executor */
/* eslint-disable promise/no-nesting */
import Ajax from 'core/ajax';
import * as Str from 'core/str';


let student = null;
const removeSession = function(sessionId, removeOnEdusign = true) {
    return Ajax.call([{
        methodname: 'mod_edusign_remove_session',
        args: {
            sessionId,
            withEdusignDelete: removeOnEdusign,
        }
    }])[0];
};

export const init = async(_student) => {
    student = _student;
    // eslint-disable-next-line no-console
    console.log('init student take', student);
};
