<?php

/**
 * @package     mod_edusign
 * @author      Sébastien Lampazona
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_edusign\form;

use admin_setting_configcheckbox;
use admin_setting_configtext;
use admin_settingpage;
use moodleform;

require_once("$CFG->libdir/formslib.php");
require_once($CFG->libdir . '/adminlib.php');

class EditForm extends moodleform
{
    //Add elements to form
    public function definition()
    {
        global $CFG;
        global $ADMIN;
        $mform = $this->_form; // Don't forget the underscore!

        // Ajoutez ici les éléments du formulaire
        $page = new admin_settingpage('mod_edusign_settings', 'Configuration de votre plugin');
        
        $page->settings->add(new admin_setting_configtext(
            'mod_edusign/textsetting',
            'Text Setting',
            'Description du paramètre de texte',
            'Valeur par défaut',
            PARAM_TEXT
        ));

        $page->settings->add(new admin_setting_configcheckbox(
            'mod_edusign/checkboxsetting',
            'Checkbox Setting',
            'Description du paramètre de case à cocher',
            1
        ));

        // Ajoutez d'autres éléments de formulaire selon vos besoins

        $ADMIN->add('mod_edusign', $page);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('text', 'messagetext', get_string('message_text', 'mod_edusign')); // Add elements to your form
        $mform->setType('messagetext', PARAM_NOTAGS);                   //Set type of element
        $mform->setDefault('messagetext', get_string('enter_message', 'mod_edusign'));        //Default value

        $choices = array();
        $choices['0'] = \core\output\notification::NOTIFY_WARNING;
        $choices['1'] = \core\output\notification::NOTIFY_SUCCESS;
        $choices['2'] = \core\output\notification::NOTIFY_ERROR;
        $choices['3'] = \core\output\notification::NOTIFY_INFO;
        $mform->addElement('select', 'messagetype', get_string('message_type', 'mod_edusign'), $choices);
        $mform->setDefault('messagetype', '3');

        $this->add_action_buttons();
    }
    //Custom validation should be added here
    function validation($data, $files)
    {
        return array();
    }
}
