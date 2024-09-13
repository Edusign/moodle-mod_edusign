<?php
/**
 * @package     mod_edusign
 * @author      Sébastien Lampazona
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_edusign\classes\form\WebhookConfigurationForm;

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require($CFG->dirroot . '/mod/edusign/classes/form/webhook_configuration.php');
require_once(dirname(__FILE__).'/lib.php');

$webhook_token    = optional_param('webhook_token', null, PARAM_TEXT);

$url = new moodle_url('/mod/edusign/advanced.php', ['enrolid' => $instance->id]);
$title = get_string('modulename', 'mod_edusign');

admin_externalpage_setup('managemodules');

$webhook_form = new WebhookConfigurationForm($url);

$PAGE->set_url($url);
$PAGE->set_title($title);

$url_webhook_student_has_signed = $CFG->httpswwwroot . '/mod/edusign/webhook.php';

// Pensez à éxecuter la commande "grunt watch" dans mod/edusign pour compiler le fichier JS
$PAGE->requires->js_call_amd('mod_edusign/pages/settings/advanced', 'init', [
    'webhookBaseUrl' => $url_webhook_student_has_signed,
]);

if ($fromForm = $webhook_form->get_data()) {
    set_config('webhook_token', $fromForm->webhook_token, 'mod_edusign');
}

$output = $OUTPUT->render_from_template('mod_edusign/advanced', [
    'title' => $title,
    'instance' => $instance,
    'course' => $course,
    'webhook_form' => $webhook_form->render(),
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
$tabmenu = edusign_print_settings_tabs('advanced');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('plugin_advanced', 'mod_edusign'));

echo $tabmenu;
echo $output;

echo $OUTPUT->footer();