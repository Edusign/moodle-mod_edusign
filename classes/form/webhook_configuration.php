<?php

/**
 * @package     mod_edusign
 * @author      Sébastien Lampazona
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_edusign\classes\form;

use moodleform;

require_once("$CFG->libdir/formslib.php");
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__ . '/../../locallib.php');

class WebhookConfigurationForm extends moodleform
{
    //Add elements to form
    public function definition()
    {
        global $CFG;
        global $ADMIN;

        $url_webhook_student_has_signed = $CFG->httpswwwroot . '/mod/edusign/webhook.php?token=' . get_config('mod_edusign', 'webhook_token');

        $mform = $this->_form; // Don't forget the underscore!
        $mform->addElement('header', 'general', get_string('webhooks_settings', 'edusign'));

        $group = [
            $mform->createElement('text', 'webhook_token', '', [
                'value' => get_config('mod_edusign', 'webhook_token'),
                'size' => 50,
                'placeholder' => get_string('webhook_token_placeholder', 'edusign')
            ]),
            $mform->createElement('button', 'refreshtoken', get_string('refresh_token', 'edusign'), ['type' => 'button', 'class' => 'ml-2']),
        ];
        
        $mform->addGroup($group, 'webhook_token_group', get_string('webhook_token', 'edusign'), [''], false);
        $mform->addElement('text', 'webhook_url', get_string('webhook_url', 'edusign'), [
            'value' => $url_webhook_student_has_signed,
            'readOnly' => true,
            'size' => 80,
        ]);
        $mform->addElement('static', 'description', '', get_string('webhook_student_has_signed_help', 'edusign'));

        $mform->addElement('submit', 'submitbutton', get_string('savechanges'));
    }


    // Custom validation should be added here.
    function validation($data, $files)
    {
        $errors = [];
        if (empty($data['webhook_token'])) {
            $errors['webhook_token_group'] = get_string('required');
        }
        return $errors;
    }
}
