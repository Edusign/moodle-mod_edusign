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
function mod_edusign_before_footer()
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
    $edusign->id = $DB->update_record('edusign', $edusign);

    return true;
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
    $features = [
        FEATURE_COMPLETION_TRACKS_VIEWS => false,
        FEATURE_COMPLETION_HAS_RULES => true,
    ];
    if (isset($features[(string) $feature])) {
        return $features[$feature];
    }
    return null;
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
        'health',
        $CFG->wwwroot . '/mod/edusign/health.php',
        get_string('plugin_health', 'mod_edusign'),
        get_string('plugin_health', 'mod_edusign'),
        false
    );

    ob_start();
    print_tabs(array($tabs), $selected);
    $tabmenu = ob_get_contents();
    ob_end_clean();

    return $tabmenu;
}

/**
 * Obtains the automatic completion state for this forum based on any conditions
 * in forum settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function edusign_get_completion_state($course, $cm, $userid, $type)
{
    global $CFG, $DB;
    
    // Get forum details
    $forum = $DB->get_record('edusign', ['id' => $cm->instance], '*', MUST_EXIST);

    // If completion option is enabled, evaluate it and return true/false
    if ($forum->completionposts) {
        return $forum->completionposts <= $DB->get_field_sql("
            SELECT
                COUNT(1)
            FROM
                {forum_posts} fp
                INNER JOIN {forum_discussions} fd ON fp.discussion=fd.id
            WHERE
                fp.userid = :userid AND fd.forum = :forumid
       ", [
            'userid' => $userid,
            'forumid' => $forum->id,
        ]);
    } else {
        // Completion option is not enabled so just return $type
        return $type;
    }
}
