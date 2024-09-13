<?php
require_once(__DIR__ . '/classes/commons/EdusignApi.php');
require_once(dirname(__FILE__) . '/locallib.php');

use \mod_edusign\classes\commons\EdusignApi;

/**
 * Create grade item for given edusign
 *
 * @param stdClass $edusign object with extra cmidnumber
 * @param mixed $grades optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok, error code otherwise
 */
function edusign_grade_item_update($edusign, $grades = null)
{
    global $CFG, $DB;

    if (!function_exists('grade_update')) { // Workaround for buggy PHP versions.
        require_once($CFG->libdir . '/gradelib.php');
    }

    if (!isset($edusign->courseid)) {
        $edusign->courseid = $edusign->course;
    }
    if (!$DB->get_record('course', array('id' => $edusign->course))) {
        error("Course is misconfigured");
    }

    if (!empty($edusign->cmidnumber)) {
        $params = array('itemname' => $edusign->name, 'idnumber' => $edusign->cmidnumber);
    } else {
        // MDL-14303.
        $params = array('itemname' => $edusign->name);
    }

    if ($edusign->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $edusign->grade;
        $params['grademin']  = 0;
    } else if ($edusign->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$edusign->grade;
    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('mod/edusign', $edusign->courseid, 'mod', 'edusign', $edusign->id, 0, $grades, $params);
}
/**
 * @package     mod_edusign
 * @author      Sébastien Lampazona
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function mod_edusign_before_footer_html_generation()
{
    global $USER;

    if (!get_config('mod_edusign', 'enabled')) {
        return;
    }
}

/**
 * Add new edusign instance.
 * 
 * Function is called when the activity creation form is submitted. 
 * This function is only called when adding an activity and should contain any logic required to add the activity.
 * 
 * @param stdClass $edusign
 * @return bool|int
 */
function edusign_add_instance($edusign)
{
    global $DB;
    $context = context_module::instance($edusign->coursemodule);

    $edusign->timemodified = time();

    // Default grade (similar to what db fields defaults if no grade attribute is passed),
    // but we need it in object for grading update.
    if (!isset($edusign->grade)) {
        $edusign->grade = 100;
    }

    if ($edusign->complete_mode) {
        $edusign->completeonxattendancesigned = $edusign->completeonxattendancesigned ?: 0;
        $edusign->completionexpected = true;
        $edusign->completion = COMPLETION_TRACKING_AUTOMATIC;
    }
    
    $edusign->id = $DB->insert_record('edusign', $edusign);

    createTrainingFromCourse(
        $edusign->course,
        $context,
        [
            'objectid' => $edusign->id,
            'context' => $context,
        ]
    );

    edusign_grade_item_update($edusign);

    return $edusign->id;
}


/**
 * Update existing edusign instance.
 * 
 * Function is called when the activity editing form is submitted.
 * 
 * @param stdClass $edusign
 * @return bool
 */
function edusign_update_instance($edusign)
{
    global $DB;

    $edusign->timemodified = time();
    $edusign->id = $edusign->instance;
    
    if ($edusign->complete_mode) {
        $edusign->completeonxattendancesigned = $edusign->completeonxattendancesigned ?: 0;
        $edusign->completionexpected = true;
        $edusign->completion = COMPLETION_TRACKING_AUTOMATIC;
    }

    if (! $DB->update_record('edusign', $edusign)) {
        return false;
    }

    return true;
}

/**
 * Add a get_coursemodule_info function in case any edusign type wants to add 'extra' information
 * for the course (see resource).
 *
 * Given a course_module object, this function returns any "extra" information that may be needed
 * when printing this activity in a course listing.  See get_array_of_activities() in course/lib.php.
 *
 * @param stdClass $coursemodule The coursemodule object (record).
 * @return cached_cm_info An object on information that the courses
 *                        will know about (most noticeably, an icon).
 */
function edusign_get_coursemodule_info($coursemodule)
{
    global $DB;

    $dbparams = ['id' => $coursemodule->instance];
    if (!$edusign = $DB->get_record('edusign', $dbparams)) {
        return false;
    }

    $result = new cached_cm_info();
    $result->name = $edusign->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $result->content = format_module_intro('edusign', $edusign, $coursemodule->id, false);
    }

    // Populate the custom completion rules as key => value pairs, but only if the completion mode is 'automatic'.
    if ($edusign->complete_mode === 'allsheets'){
        $result->customdata['customcompletionrules']['allsheets'] = $edusign->complete_mode === 'allsheets';
    }
    if ($edusign->complete_mode === 'xsheets'){
        $result->customdata['customcompletionrules']['xsheets'] = $edusign->completeonxattendancesigned;
    }

    // Populate some other values that can be used in calendar or on dashboard.
    if ($edusign->duedate) {
        $result->customdata['duedate'] = $edusign->duedate;
    }
    if ($edusign->cutoffdate) {
        $result->customdata['cutoffdate'] = $edusign->cutoffdate;
    }

    return $result;
}

/**
 * Delete existing edusign
 * 
 * Function is called when the activity deletion is confirmed. It is responsible for removing all data associated with the instance.
 *
 * @param int $id
 * @return bool
 */
function edusign_delete_instance($id)
{
    global $DB;
    if (!$edusign = $DB->get_record('edusign', array('id' => $id))) {
        return false;
    }

    $DB->delete_records('edusign_sessions', array('activity_module_id' => $edusign->id));

    return true;
}

/**
 * Returns the information if the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function edusign_supports($feature)
{
    switch ($feature) {
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return false;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        default:
            return null;
    }
}



/**
 * Print tabs on edusign settings page.
 *
 * @param string $selected - current selected tab.
 */
function edusign_print_settings_tabs($selected = 'settings')
{
    global $CFG;
    // Print tabs for different settings pages.
    $tabs = array();
    $tabs[] = new tabobject(
        'settings',
        "{$CFG->wwwroot}/{$CFG->admin}/settings.php?section=modsettingedusign",
        get_string('settings', 'mod_edusign'),
        get_string('settings', 'mod_edusign'),
        false
    );

    $tabs[] = new tabobject(
        'advanced',
        $CFG->wwwroot . '/mod/edusign/advanced.php',
        get_string('plugin_advanced', 'mod_edusign'),
        get_string('plugin_advanced', 'mod_edusign'),
        false
    );

    ob_start();
    print_tabs(array($tabs), $selected);
    $tabmenu = ob_get_contents();
    ob_end_clean();

    return $tabmenu;
}