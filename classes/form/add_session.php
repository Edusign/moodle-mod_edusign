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
        $mform->addElement('text', 'title', get_string('title', 'edusign'), [ 'value' => 'Ma feuille de présence']);
        edusign_form_sessiondate_selector($mform);
        $mform->addElement('checkbox', 'forcesync', get_string('forcesync', 'edusign'), false);
        $mform->addElement('submit', 'submitbutton', get_string('savechanges'));
    }

    
    // Custom validation should be added here.
    function validation($data, $files) {
        $startDate = date('Y-m-d', $data['sessiondate']) . ' ' . str_pad($data['sestime']['starthour'], 2, "0", STR_PAD_LEFT) . ':' . str_pad($data['sestime']['startminute'], 2, "0", STR_PAD_LEFT) . ':00';
        $endDate = date('Y-m-d', $data['sessiondate']) . ' ' . str_pad($data['sestime']['endhour'], 2, "0", STR_PAD_LEFT) . ':' . str_pad($data['sestime']['endminute'], 2, "0", STR_PAD_LEFT) . ':00';
    
        $errors = [];
        if (strtotime($startDate) < strtotime('now at midnight')) {
            $errors['sessiondate'] = get_string('errorsessiondateinpast', 'edusign');
        }
        if (strtotime($startDate) >= strtotime($endDate)) {
            $errors['sestime'] = get_string('errorstartdatebeforeenddate', 'edusign');
        }
        return $errors;
    }
}
