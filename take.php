<?php
/**
 * @package     mod_edusign
 * @author      Sébastien Lampazona
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_edusign\classes\commons\EdusignApi;

require(__DIR__ . '/../../config.php');
require(__DIR__ . '/classes/commons/EdusignApi.php');
require(__DIR__ . '/locallib.php');

$sessionId = required_param('sessionId', PARAM_INT);
$session = $DB->get_record('edusign_sessions', ['id' => $sessionId]);
$cm = get_coursemodule_from_id('edusign', $session->activity_module_id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$courseEdusign = $DB->get_record('course_edusign_api', ['course_id' => $course->id], '*', MUST_EXIST);
$edusign      = $DB->get_record('edusign', array('id' => $cm->instance), '*', MUST_EXIST);

$context = context_module::instance($cm->id);

$capabilities = array(
    'mod/edusign:takeattendances',
);
if (!has_any_capability($capabilities, $context)) {
    redirect(new moodle_url('/mod/edusign/view.php', ['id' => $cm->id]));
}
if (!!$session->archived) {
    \core\notification::warning(get_string('sessionArchivedCannotTake', 'mod_edusign'));
    redirect(new moodle_url('/mod/edusign/view.php', ['id' => $cm->id]));
}
if (!empty($courseEdusign)){
    $course->edusign_api_id = $courseEdusign->edusign_api_id;
}

if (!empty($session->edusign_api_id)){
    $edusignApiCourse = EdusignApi::getCourseById($session->edusign_api_id);
}


$students = getStudentsWithPresentialStates($context, $edusignApiCourse);
$teachers = getTeachersWithPresentialStates($context, $edusignApiCourse);

$title = "take";
$url = new moodle_url('/mod/edusign/take.php?sessionId='.$sessionId);

$PAGE->set_cm($cm);
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading(get_string('attendance', 'mod_edusign'));
$PAGE->set_cacheable(true);

$PAGE->requires->js_call_amd('mod_edusign/pages/take', 'init', [
    'students' => array_values($students),
    'teachers' => array_values($teachers),
    'course' => $course,
    'session' => $session,
]);

$output = $OUTPUT->render_from_template('mod_edusign/take', [
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
]);

echo $OUTPUT->header();
echo $output;

echo $OUTPUT->footer();
