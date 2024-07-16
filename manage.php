<?php
/**
 * @package     mod_edusign
 * @author      Sébastien Lampazona
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

function formatSessions($sessions) {
    foreach($sessions as $session) {
        $session->date = strtotime($session->date_start);
        $session->time_start = date('H:i', strtotime($session->date_start));
        $session->time_end = date('H:i', strtotime($session->date_end));
        
    }
    return array_values($sessions);
}

$cmId           = required_param('id', PARAM_INT);
$cm           = get_coursemodule_from_id('edusign', $cmId, 0, false, MUST_EXIST);
$course       = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$att          = $DB->get_record('edusign', array('id' => $cm->instance), '*', MUST_EXIST);
$sessions     = formatSessions($DB->get_records('edusign_sessions', ['activity_module_id' => $cm->id]));

$context = context_module::instance($cm->id);
require_capability('mod/edusign:manageattendances', $context);

$title = $course->shortname. ": ".$att->name;
$url = new moodle_url('/mod/edusign/manage.php?id='.$id);

$PAGE->set_cm($cm);
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);
$PAGE->force_settings_menu(true);
$PAGE->set_cacheable(true);

$PAGE->requires->js_call_amd('mod_edusign/pages/manage', 'init');

$unarchivedSessions = array_filter($sessions, function($session) {
    return !$session->archived;
});
$archivedSessions = array_filter($sessions, function($session) {
    return !!$session->archived;
});


$output = $OUTPUT->render_from_template('mod_edusign/manage', [
    'title' => $title,
    'instance' => $cm,
    'cmId' => $cmId,
    'course' => $course,
    'url' => $url,
    'context' => $context,
    'modals' => [
        'import-csv' => $OUTPUT->render_from_template('mod_edusign/modals/import-csv', new stdClass()),
    ],
    'unarchivedSessions' => array_values($unarchivedSessions),
    'archivedSessions' => array_values($archivedSessions),
    'PAGE' => $PAGE,
    'OUTPUT' => $OUTPUT,
    'CFG' => $CFG,
    'DB' => $DB,
    'USER' => $USER,
    'SESSION' => $SESSION,
    'PAGE' => $PAGE,
]);

echo $OUTPUT->header();
echo $output;

echo $OUTPUT->footer();
