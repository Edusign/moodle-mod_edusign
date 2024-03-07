<?php
/**
 * @package     mod_edusign
 * @author      Sébastien Lampazona
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require($CFG->dirroot . '/mod/edusign/classes/form/edit.php');
require_once(dirname(__FILE__).'/lib.php');


$url = new moodle_url('/mod/edusign/health.php', ['enrolid' => $instance->id]);
$title = get_string('modulename', 'mod_edusign');

admin_externalpage_setup('managemodules');

$PAGE->set_url($url);
$PAGE->set_title($title);

// Pensez à éxecuter la commande "grunt watch" dans mod/edusign pour compiler le fichier JS
$PAGE->requires->js_call_amd('mod_edusign/pages/settings/health', 'init');

$output = $OUTPUT->render_from_template('mod_edusign/health', [
    'title' => $title,
    'instance' => $instance,
    'course' => $course,
    'url' => $url,
    'context' => $context,
    'PAGE' => $PAGE,
    'OUTPUT' => $OUTPUT,
    'CFG' => $CFG,
    'DB' => $DB,
    'USER' => $USER,
    'SESSION' => $SESSION,
    'PAGE' => $PAGE,
]);
$tabmenu = edusign_print_settings_tabs('health');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('plugin_health', 'mod_edusign'));

echo $tabmenu;
echo $output;

echo $OUTPUT->footer();