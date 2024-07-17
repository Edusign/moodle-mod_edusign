/* eslint-disable no-console */
/* eslint-disable no-unused-vars */


export const init = async(cmId) => {
    const signatureIframe = document.querySelector("#signature-iframe");
    // TODO - Not functionnal yet need to be coded in the iframe itself
    signatureIframe.contentWindow.addEventListener("accept-signature", () => {
        document.location.href = '/mod/edusign/view.php?id=' + cmId;
    });
};
