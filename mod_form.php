<?php

/**
 * This file is used when adding/editing a module to a course. 
 * It contains the elements that will be displayed on the form responsible for creating/installing an instance of your module. 
 * @doc: https://moodledev.io/docs/apis/plugintypes/mod#mod_formphp---instance-createedit-form
 */

/**
 * Forms for updating/adding edusign
 *
 * @package    mod_edusign
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * class for displaying add/update form.
 */
class mod_edusign_mod_form extends moodleform_mod {

    /**
     * Called to define this moodle form
     *
     * @return void
     */
    public function definition() {
        $edusignconfig = get_config('edusign');
        if (!isset($edusignconfig->subnet)) {
            $edusignconfig->subnet = '';
        }
        $mform    =& $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->setDefault('name', get_string('modulename', 'edusign'));

        $this->standard_intro_elements();

        // Grade settings.
        $this->standard_grading_coursemodule_elements();

        $this->standard_coursemodule_elements(true);

        // IP address.
        if (get_config('edusign', 'subnetactivitylevel')) {
            $mform->addElement('header', 'security', get_string('extrarestrictions', 'edusign'));
            $mform->addElement('text', 'subnet', get_string('defaultsubnet', 'edusign'), array('size' => '164'));
            $mform->setType('subnet', PARAM_TEXT);
            $mform->addHelpButton('subnet', 'defaultsubnet', 'edusign');
            $mform->setDefault('subnet', $edusignconfig->subnet);
        } else {
            $mform->addElement('hidden', 'subnet', '');
            $mform->setType('subnet', PARAM_TEXT);
        }

        $this->add_action_buttons();
    }
}
