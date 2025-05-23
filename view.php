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

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');

$id           = required_param('id', PARAM_INT);

$allSessions = $DB->get_records('edusign_sessions', ['activity_module_id' => $id]);
$sessions = array_filter($allSessions, function ($session) {
  return strtotime($session->date_start) <= time() && strtotime($session->date_end) >= time();
});

$incomingSessions = array_filter($allSessions, function ($session) {
  return strtotime($session->date_start) > time() && strtotime($session->date_end) > time();
});

$userEdusignApi = getUserWithEdusignApiId('student', $USER->id);

function formatSessions($sessionsToFormat)
{
  return array_map(function ($sessionToFormat) {
    $sessionToFormat->date_start = strtotime($sessionToFormat->date_start);
    $sessionToFormat->date_end = strtotime($sessionToFormat->date_end);
    return $sessionToFormat;
  }, $sessionsToFormat);
}


function formatAndFilterFromEdusign($sessionsToFormat)
{
  global $userEdusignApi;
  $formatedSessions = formatSessions($sessionsToFormat);
  $formatedSessions = add_edusign_sessions_infos($formatedSessions);
  $formatedSessions = array_map(function ($session) use ($userEdusignApi) {
    $session->edusign_course->STUDENTS = array_filter($session->edusign_course->STUDENTS, function ($student) use ($userEdusignApi) {
      return $student->studentId === $userEdusignApi->edusign_api_id;
    });
    $session->edusign_course->STUDENT = reset($session->edusign_course->STUDENTS);
    unset($session->edusign_course->STUDENTS);
    return $session;
  }, $formatedSessions);
  return array_values(array_filter($formatedSessions, function ($session) {
    return $session->edusign_course->STUDENT;
  }));
}

$sessionId    = optional_param('sessionId', null, PARAM_INT);

$cm           = get_coursemodule_from_id('edusign', $id, 0, false, MUST_EXIST);
$course       = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$edusign      = $DB->get_record('edusign', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

// Si on a pas de session explicitement passée en paramètre
// On prend celle qui est entre le date_start et le date_end
// Si on en trouve une et une seule on redirige vers la page de la session
// Sinon on affiche la page pour lister les sessions disponibles
if (!$sessionId) {
  if ($sessions && count($sessions) === 1) {
    $sessionId = array_values($sessions)[0]->id;
  }
}

$canManageAttendance = array(
  'mod/edusign:changeattendances',
  'mod/edusign:manageattendances'
);

$canTakeAttendance = array(
  'mod/edusign:takeattendances',
);

// Si on a une session on redirige vers la page de la session
if ($sessionId) {
  if (has_any_capability($canManageAttendance, $context)) {
    redirect(new moodle_url('/mod/edusign/manage.php', ['id' => $cm->id, 'sessionId' => $sessionId]));
  } else if (has_any_capability($canTakeAttendance, $context)) {
    redirect(new moodle_url('/mod/edusign/take.php', ['sessionId' => $sessionId]));
  } else {
    redirect(new moodle_url('/mod/edusign/session.php', ['id' => $id, 'sessionId' => $sessionId]));
  }
} else if (has_any_capability($canManageAttendance, $context)) {
  redirect(new moodle_url('/mod/edusign/manage.php', ['id' => $cm->id]));
}

$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
$PAGE->set_cm($cm);
$PAGE->set_context($context);
$PAGE->set_title($course->shortname);
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);


$sessions = formatAndFilterFromEdusign($sessions);
$incomingSessions = formatAndFilterFromEdusign($incomingSessions);

$PAGE->requires->js_call_amd('mod_edusign/pages/student/view-list', 'init', [
  'student' => $USER,
  'course' => $course,
  'sessions' => $sessions,
]);

$output = $OUTPUT->render_from_template('mod_edusign/student/view-list', [
  'title' => $title,
  'instance' => $cm,
  'id' => $id,
  'url' => $url,
  'context' => $context,
  'course' => $course,
  'PAGE' => $PAGE,
  'OUTPUT' => $OUTPUT,
  'CFG' => $CFG,
  'DB' => $DB,
  'USER' => $USER,
  'PAGE' => $PAGE,
  'sessions' => $sessions,
  'incomingSessions' => $incomingSessions,
]);

echo $OUTPUT->header();
echo $output;
echo $OUTPUT->footer();
