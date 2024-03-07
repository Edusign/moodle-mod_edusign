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

require_once(dirname(__FILE__).'/../../config.php');

$id           = required_param('id', PARAM_INT);

$cm           = get_coursemodule_from_id('edusign', $id, 0, false, MUST_EXIST);
$course       = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$edusign      = $DB->get_record('edusign', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);


$context = context_module::instance($cm->id);

$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);

$capabilities = array(
  'mod/edusign:takeattendances',
  'mod/edusign:changeattendances',
  'mod/edusign:manageattendances'
);
if (has_any_capability($capabilities, $context)) {
  redirect(new moodle_url('/mod/edusign/manage.php', ['id' => $cm->id]));
}

