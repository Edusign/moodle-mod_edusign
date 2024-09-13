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
require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * class for displaying add/update form.
 */
class mod_edusign_mod_form extends moodleform_mod
{

    /**
     * Called to define this moodle form
     *
     * @return void
     */
    public function definition()
    {
        $edusignconfig = get_config('edusign');
        $mform    = &$this->_form;
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->setDefault('name', get_string('modulename', 'edusign'));

        $this->standard_intro_elements();

        // Grade settings.
        $this->standard_grading_coursemodule_elements();

        $this->standard_coursemodule_elements(true);

        $this->add_action_buttons();
    }

    protected function get_suffixed_name(string $fieldname): string
    {
        return $fieldname . $this->get_suffix();
    }

   
    /**
     * Add elements for setting the custom completion rules.
     *
     * @return array List of added element names, or names of wrapping group elements.
     * @category completion
     */
    public function add_completion_rules(): array
    {
        $mform = $this->_form;
        
        // Création de la checkbox pour "Toutes le feuilles signées"
        $mform->addElement(
            'radio',
            $this->get_suffixed_name('complete_mode'),
            '',
            get_string('completion_all_attendance', 'edusign'),
            'allsheets',
        );

        $mform->addHelpButton(
            $this->get_suffixed_name('complete_mode'),
            'completion_all_attendance',
            'edusign'
        );
        
        // Création de l'input pour choisir le nombre de feuilles signées au minimum
        $group = [
            $mform->createElement(
                'radio',
                $this->get_suffixed_name('complete_mode'),
                '',
                get_string('completeonxattendancesigned', 'edusign'),
                'xsheets',
            ),
            $mform->createElement(
                'text',
                $this->get_suffixed_name('completeonxattendancesigned'),
                ' ',
                ['size' => 3]
            ),
        ];
        $mform->setType('completeonxattendancesigned', PARAM_INT);
        $mform->addGroup(
            $group,
            $this->get_suffixed_name('completeonxattendancesignedgroup'),
            get_string('completion_of_X_attendance','edusign'),
            [' '],
            false
        );
        $mform->addHelpButton(
            $this->get_suffixed_name('completeonxattendancesignedgroup'),
            'completion_X_attendance',
            'edusign'
        );
        
        $mform->disabledIf(
            $this->get_suffixed_name('completeonxattendancesigned'),
            $this->get_suffixed_name('complete_mode'),
            'neq',
            'xsheets'
        );
        
        return [
            $this->get_suffixed_name('complete_mode'),
            $this->get_suffixed_name('completeonxattendancesignedgroup'),
        ];
    }
    
    function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return $data;
        }
        if (empty($data)) {
            $data = new stdClass;
        }
        if (empty($data->{$this->get_suffixed_name('complete_mode')})){
            $data->{$this->get_suffixed_name('complete_mode')} = 'allsheets';
        }
        return $data;
    }

    /**
     * Called during validation to see whether some activity-specific completion rules are selected.
     *
     * @param array $data Input data not yet validated.
     * @return bool True if one or more rules is enabled, false if none are.
     */
    public function completion_rule_enabled($data)
    {
        
        if (
            empty($data[$this->get_suffixed_name('complete_mode')])) {
            return false;
        }
        if ($data[$this->get_suffixed_name('complete_mode')] === 'xsheets' &&
            (
                empty($data[$this->get_suffixed_name('completeonxattendancesigned')])
                || 
                $data[$this->get_suffixed_name('completeonxattendancesigned')] == 0
            )
        ) {
            return false;
        }
        return true;
    }
    
}
