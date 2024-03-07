<?php
/**
 * The index.php should be used to list all instances of an activity that the current user has access to in the specified course.
 * @doc       https://moodledev.io/docs/apis/plugintypes/mod#indexphp---instance-list
 * @package   mod_edusign
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$id = required_param('id', PARAM_INT);

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
require_login($course);

$PAGE->set_url('/mod/edusign/index.php', array('id' => $id));
$PAGE->set_pagelayout('incourse');

// Print the header.
$strplural = get_string("modulename", "edusign");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($strplural));

echo "<br />";

echo $OUTPUT->footer();
