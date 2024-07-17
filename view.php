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

$id           = required_param('id', PARAM_INT);
$sessions = array_values(array_filter($DB->get_records('edusign_sessions', ['activity_module_id' => $id]), function ($session) {
  return strtotime($session->date_start) <= time() && strtotime($session->date_end) >= time();
}));

$incomingSessions = array_values(array_filter($DB->get_records('edusign_sessions', ['activity_module_id' => $id]), function ($session) {
  return strtotime($session->date_start) > time();
}));

function formatSessions($sessions) {
  foreach($sessions as $session) {
      $session->date_start = strtotime($session->date_start);
      $session->date_end = strtotime($session->date_end);
  }
  return array_values($sessions);
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
  }
  else if (has_any_capability($canTakeAttendance, $context)) {
    redirect(new moodle_url('/mod/edusign/take.php', ['sessionId' => $sessionId]));
  } else {
    redirect(new moodle_url('/mod/edusign/session.php', ['id' => $id, 'sessionId' => $sessionId]));
  }
}
else if (has_any_capability($canManageAttendance, $context)) {
  redirect(new moodle_url('/mod/edusign/manage.php', ['id' => $cm->id]));
}

$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
$PAGE->set_cm($cm);
$PAGE->set_context($context);
$PAGE->set_title($course->shortname . ": " . $att->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);

$sessions = formatSessions($sessions);


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
  'incomingSessions' => $incomingSessions
]);

echo $OUTPUT->header();
echo $output;
echo $OUTPUT->footer();
