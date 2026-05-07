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

class AddSessionForm extends moodleform
{
    //Add elements to form
    public function definition()
    {
        global $CFG;
        global $ADMIN;
        $mform = $this->_form; // Don't forget the underscore!
        $mform->addElement('header', 'general', get_string('addsession', 'edusign'));
        $mform->addElement('text', 'title', get_string('title', 'edusign'), [ 'value' => get_string('defaultSessionTitle', 'mod_edusign')]);
        $mform->setType('title', PARAM_TEXT);
        edusign_form_sessiondate_selector($mform);

        if (empty($this->_customdata['editing'])) {
            $mform->addElement('header', 'multiplesessions', get_string('multiplesessions', 'edusign'));
            $mform->setExpanded('multiplesessions', false);

            $mform->addElement(
                'advcheckbox',
                'repeatsessions',
                '',
                get_string('repeatsessions', 'edusign')
            );
            $mform->setDefault('repeatsessions', 0);

            $days = [
                $mform->createElement('advcheckbox', 'monday', '', get_string('monday', 'edusign')),
                $mform->createElement('advcheckbox', 'tuesday', '', get_string('tuesday', 'edusign')),
                $mform->createElement('advcheckbox', 'wednesday', '', get_string('wednesday', 'edusign')),
                $mform->createElement('advcheckbox', 'thursday', '', get_string('thursday', 'edusign')),
                $mform->createElement('advcheckbox', 'friday', '', get_string('friday', 'edusign')),
                $mform->createElement('advcheckbox', 'saturday', '', get_string('saturday', 'edusign')),
                $mform->createElement('advcheckbox', 'sunday', '', get_string('sunday', 'edusign')),
            ];
            $mform->addGroup($days, 'repeatdays', get_string('repeaton', 'edusign'), ['&nbsp;&nbsp;&nbsp;&nbsp;'], false);
            $mform->disabledIf('repeatdays', 'repeatsessions', 'notchecked');

            $repeatoptions = [];
            for ($i = 1; $i <= 12; $i++) {
                $repeatoptions[$i] = $i;
            }
            $repeatgroup = [
                $mform->createElement('select', 'repeatinterval', '', $repeatoptions),
                $mform->createElement('static', 'repeatintervalunit', '', get_string('weeks', 'edusign')),
            ];
            $mform->addGroup($repeatgroup, 'repeatevery', get_string('repeatevery', 'edusign'), [' '], false);
            $mform->setDefault('repeatevery[repeatinterval]', 1);
            $mform->disabledIf('repeatevery', 'repeatsessions', 'notchecked');

            $mform->addElement('date_selector', 'repeatuntil', get_string('repeatuntil', 'edusign'));
            $mform->setDefault('repeatuntil', strtotime('+1 month'));
            $mform->disabledIf('repeatuntil', 'repeatsessions', 'notchecked');
        }

        $mform->addElement('checkbox', 'forcesync', get_string('forcesync', 'edusign'), false);
        $mform->addElement('checkbox', 'processcompletion', get_string('processcompletion', 'edusign'), false);
        $mform->addElement('submit', 'submitbutton', get_string('savechanges'));
    }

    
    // Custom validation should be added here.
    function validation($data, $files) {
        $startDate = date('Y-m-d', $data['sessiondate']) . ' ' . str_pad($data['sestime']['starthour'], 2, "0", STR_PAD_LEFT) . ':' . str_pad($data['sestime']['startminute'], 2, "0", STR_PAD_LEFT) . ':00';
        $endDate = date('Y-m-d', $data['sessiondate']) . ' ' . str_pad($data['sestime']['endhour'], 2, "0", STR_PAD_LEFT) . ':' . str_pad($data['sestime']['endminute'], 2, "0", STR_PAD_LEFT) . ':00';
    
        $errors = [];
        if (strtotime($startDate) < strtotime('now at midnight')) {
            $errors['sessiondate'] = get_string('errordateinpast', 'edusign');
        }
        if (strtotime($startDate) >= strtotime($endDate)) {
            $errors['sestime'] = get_string('errorstartdatebeforeenddate', 'edusign');
        }
        if (!empty($data['repeatsessions'])) {
            $repeatdays = array_filter($data['repeatdays'] ?? []);
            if (empty($repeatdays)) {
                $errors['repeatdays'] = get_string('errorrepeatdaysrequired', 'edusign');
            }
            if (empty($data['repeatuntil']) || $data['repeatuntil'] < $data['sessiondate']) {
                $errors['repeatuntil'] = get_string('errorrepeatuntilbeforestart', 'edusign');
            }
        }
        return $errors;
    }
}
