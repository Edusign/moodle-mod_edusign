<?php

/**
 * Prints edusign info for particular user
 *
 * Moodle will automatically generate links to view the activity using the /view.php page and passing in an id value.
 * The id passed is the course module ID, which can be used to fetch all remaining data for the activity instance.
 *
 * @package    mod_edusign
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_edusign\classes\commons\EdusignApi;

require_once(dirname(__FILE__) . '/../../config.php');

$sessionId    = required_param('sessionId', PARAM_INT);
if (!$sessionId) {
    throw new moodle_exception('mod_edusign_invalid_session_id');
}
$session = $DB->get_record('edusign_sessions', ['id' => $sessionId]);

if (!$session) {
    throw new moodle_exception('mod_edusign_session_not_found');
}

$cm           = get_coursemodule_from_id('edusign', $session->activity_module_id, 0, false, MUST_EXIST);
$course       = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$edusign      = $DB->get_record('edusign', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

if (
    !is_student_has_session($session, $USER->id)
    || strtotime($session->date_start) > time()
    || strtotime($session->date_end) < time()
) {
    throw new \Exception('You are not allowed to access this session');
}

$context = context_module::instance($cm->id);

$canTakeOrManageAttendance = array(
    'mod/edusign:changeattendances',
    'mod/edusign:manageattendances',
    'mod/edusign:takeattendances',
);

if (has_any_capability($canTakeOrManageAttendance, $context)) {
    redirect(new moodle_url('/mod/edusign/take.php', ['sessionId' => $sessionId]));
}

$userEdusignApi = $DB->get_record('users_edusign_api', ['user_id' => $USER->id], '*', MUST_EXIST);
$edusignUserInCourse = reset(EdusignApi::getStudentSignatureLinks($session->edusign_api_id, [$userEdusignApi->edusign_api_id]));
$signatureLink = $edusignUserInCourse->SIGNATURE_LINK;

$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
$PAGE->set_cm($cm);
$PAGE->set_context($context);
$PAGE->set_title($course->shortname . ": " . $att->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);

$PAGE->requires->js_call_amd('mod_edusign/pages/student/take', 'init', [
    'cmId' => $cm->id,
    'student' => $USER,
    'course' => $course,
    'session' => $session,
]);

$output = $OUTPUT->render_from_template('mod_edusign/student/take', [
    'title' => $title,
    'instance' => $cm,
    'id' => $id,
    'url' => $url,
    'context' => $context,
    'course' => $course,
    'session' => $session,
    'PAGE' => $PAGE,
    'OUTPUT' => $OUTPUT,
    'CFG' => $CFG,
    'DB' => $DB,
    'USER' => $USER,
    'PAGE' => $PAGE,
    'signatureLink' => $signatureLink,
]);


echo $OUTPUT->header();
echo $output;
echo $OUTPUT->footer();
