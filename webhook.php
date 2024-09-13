<?php

/**
 * @package     mod_edusign
 * @author      Sébastien Lampazona
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once($CFG->dirroot.'/lib/completionlib.php');

header('Content-Type: application/json; charset=utf-8');
$url = new moodle_url('/mod/edusign/webhook.php');

$webhook_token = $_GET['token'] ?? null;
if ($webhook_token !== get_config('mod_edusign', 'webhook_token')) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid token'
    ]);
    exit;
}

$webhook_data = [];
try {
    $webhook_data = json_decode(file_get_contents('php://input') ?: '{}', true);
} catch (Exception $e) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON'
    ]);
    exit;
}

function webhook_on_student_sign($data) {
    global $DB;
    if (!isset($data['course']) || !isset($data['course']['ID'])){
        throw new Exception('Missing course ID');
    }
    if (!isset($data['student']) || !isset($data['student']['ID'])){
        throw new Exception('Missing student ID');
    }
    $cm = get_cm_by_edusign_course_id($data['course']['ID']);
    if (!$cm) {
        throw new Exception('Course module not found');
    }
    
    $course = $DB->get_record('course', array('id' => $cm->course), '*');
    
    $student = getUserFromEdusignApiId('student', $data['student']['ID']);
    // Update completion state
    $completion = new completion_info($course);
    if ($completion->is_enabled($cm)) {
        $completion->update_state($cm, COMPLETION_UNKNOWN, $student->id);
    }
    return;    
}

$funcToCall = 'webhook_' . $webhook_data['type'];
if (!is_callable($funcToCall)) {
    header('HTTP/1.1 422 Unprocessable Entity');
    echo json_encode([
        'status' => 'error',
        'message' => 'Hook type not supported'
    ]);
}

// Calling the function
try {
   echo json_encode([
        'status' => 'success',
        'message' => call_user_func($funcToCall, $webhook_data)
    ]);
} catch (\Throwable $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
exit;