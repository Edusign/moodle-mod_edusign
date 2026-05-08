<?php
/**
 * Course attendance report.
 *
 * @package    mod_edusign
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_edusign\classes\commons\EdusignApi;

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/classes/commons/EdusignApi.php');

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('edusign', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$edusign = $DB->get_record('edusign', ['id' => $cm->instance], '*', MUST_EXIST);
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
if (!has_any_capability([
    'mod/edusign:takeattendances',
    'mod/edusign:changeattendances',
    'mod/edusign:manageattendances',
], $context)) {
    require_capability('mod/edusign:manageattendances', $context);
}

$url = new moodle_url('/mod/edusign/report.php', ['id' => $cm->id]);
$PAGE->set_cm($cm);
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('report');
$PAGE->set_title($course->shortname . ': ' . $edusign->name . ' - ' . get_string('report', 'mod_edusign'));
$PAGE->set_heading($course->fullname);
$PAGE->force_settings_menu(true);
$PAGE->set_cacheable(false);
$PAGE->navbar->add(get_string('report', 'mod_edusign'));

$gradesyncenabled = !empty($edusign->attendancegradeenabled) && (float)$edusign->grade > 0;

function edusign_report_percent(int $part, int $total): int
{
    if ($total <= 0) {
        return 0;
    }
    return (int)round(($part / $total) * 100);
}

function edusign_report_status(stdClass $student): string
{
    return edusign_attendance_status($student);
}

function edusign_report_empty_student(string $apiid): stdClass
{
    $student = new stdClass();
    $student->apiid = $apiid;
    $student->name = get_string('unknownStudent', 'mod_edusign');
    $student->email = '';
    $student->expected = 0;
    $student->present = 0;
    $student->justified = 0;
    $student->pending = 0;
    $student->absent = 0;
    $student->late = 0;
    $student->earlydeparture = 0;
    $student->attendancepercent = 0;
    $student->attendanceLabel = '0%';
    $student->attendancegradepercent = 0;
    $student->attendancegradeLabel = '-';
    $student->attendancegraderaw = '-';
    $student->lastpresent = get_string('never', 'mod_edusign');
    $student->needsattention = false;
    $student->rowclass = '';
    return $student;
}

$sessions = array_values($DB->get_records('edusign_sessions', ['activity_module_id' => $cm->id], 'date_start ASC'));
$moodlestudents = getStudentsFromContext($context);
$studentsbyapiid = [];
$studentrows = [];
$sessionrows = [];
$errors = [];

foreach ($moodlestudents as $student) {
    if (empty($student->edusign_api_id)) {
        continue;
    }
    $studentreport = edusign_report_empty_student($student->edusign_api_id);
    $studentreport->name = fullname($student);
    $studentreport->email = $student->email;
    $studentsbyapiid[$student->edusign_api_id] = $studentreport;
}

$totals = [
    'expected' => 0,
    'present' => 0,
    'justified' => 0,
    'pending' => 0,
    'absent' => 0,
    'late' => 0,
    'earlydeparture' => 0,
];

foreach ($sessions as $session) {
    $sessionrow = [
        'title' => $session->title,
        'date' => userdate(strtotime($session->date_start), get_string('strftimedatetimeshort', 'core_langconfig')),
        'groupname' => empty($session->groupid)
            ? get_string('commonsession', 'mod_edusign')
            : (groups_get_group_name($session->groupid) ?: get_string('groupsession', 'mod_edusign')),
        'takeurl' => (new moodle_url('/mod/edusign/take.php', ['sessionId' => $session->id]))->out(false),
        'expected' => 0,
        'present' => 0,
        'justified' => 0,
        'pending' => 0,
        'absent' => 0,
        'attendancepercent' => 0,
        'attendanceLabel' => '0%',
        'rowclass' => '',
        'haserror' => false,
        'error' => '',
    ];

    try {
        if (empty($session->edusign_api_id)) {
            throw new moodle_exception('course_linked_error', 'mod_edusign', '', get_string('noData', 'mod_edusign'));
        }
        $edusigncourse = EdusignApi::getCourseById($session->edusign_api_id);
        foreach ($edusigncourse->STUDENTS ?? [] as $edusignstudent) {
            $apiid = $edusignstudent->studentId;
            if (empty($studentsbyapiid[$apiid])) {
                $studentsbyapiid[$apiid] = edusign_report_empty_student($apiid);
                try {
                    $studentfromapi = EdusignApi::getStudentById($apiid);
                    $studentsbyapiid[$apiid]->name = trim(($studentfromapi->FIRSTNAME ?? '') . ' ' . ($studentfromapi->LASTNAME ?? ''));
                    $studentsbyapiid[$apiid]->email = $studentfromapi->EMAIL ?? '';
                } catch (Exception $e) {
                    // Keep the unknown fallback; the row still carries useful attendance counts.
                }
            }

            $status = edusign_report_status($edusignstudent);
            $sessionrow['expected']++;
            $sessionrow[$status]++;
            $totals['expected']++;
            $totals[$status]++;

            $studentreport = $studentsbyapiid[$apiid];
            $studentreport->expected++;
            $studentreport->$status++;
            if (!empty($edusignstudent->delay)) {
                $studentreport->late++;
                $totals['late']++;
            }
            if (!empty($edusignstudent->earlyDeparture)) {
                $studentreport->earlydeparture++;
                $totals['earlydeparture']++;
            }
            if ($status === 'present') {
                $studentreport->lastpresent = $sessionrow['date'];
            }
        }
    } catch (Exception $e) {
        $sessionrow['haserror'] = true;
        $sessionrow['error'] = $e->getMessage();
        $errors[] = [
            'session' => $session->title,
            'message' => $e->getMessage(),
        ];
    }

    $sessionrow['attendancepercent'] = edusign_report_percent($sessionrow['present'], $sessionrow['expected']);
    $sessionrow['attendanceLabel'] = $sessionrow['attendancepercent'] . '%';
    if ($sessionrow['attendancepercent'] < 75 && $sessionrow['expected'] > 0) {
        $sessionrow['rowclass'] = 'table-warning';
    }
    $sessionrows[] = $sessionrow;
}

foreach ($studentsbyapiid as $studentreport) {
    $studentreport->attendancepercent = edusign_report_percent($studentreport->present, $studentreport->expected);
    $studentreport->attendanceLabel = $studentreport->attendancepercent . '%';
    $studentreport->attendancegradepercent = edusign_report_percent(
        $studentreport->present + $studentreport->justified,
        $studentreport->expected
    );
    if ($gradesyncenabled && $studentreport->expected > 0) {
        $studentreport->attendancegradeLabel = $studentreport->attendancegradepercent . '%';
        $studentreport->attendancegraderaw = format_float(
            ($studentreport->attendancegradepercent / 100) * (float)$edusign->grade,
            2
        ) . ' / ' . format_float((float)$edusign->grade, 2);
    }
    $studentreport->needsattention = $studentreport->expected > 0 && $studentreport->attendancepercent < 75;
    $studentreport->rowclass = $studentreport->needsattention ? 'table-warning' : '';
    $studentrows[] = $studentreport;
}

usort($studentrows, function ($a, $b) {
    if ($a->attendancepercent === $b->attendancepercent) {
        return strcmp($a->name, $b->name);
    }
    return $a->attendancepercent <=> $b->attendancepercent;
});

$attentionrows = array_values(array_filter($studentrows, function ($student) {
    return $student->needsattention;
}));

$reportdata = [
    'title' => get_string('attendanceReport', 'mod_edusign'),
    'summary' => [
        'attendanceLabel' => edusign_report_percent($totals['present'], $totals['expected']) . '%',
        'attendanceGradeLabel' => edusign_report_percent(
            $totals['present'] + $totals['justified'],
            $totals['expected']
        ) . '%',
        'expected' => $totals['expected'],
        'present' => $totals['present'],
        'justified' => $totals['justified'],
        'pending' => $totals['pending'],
        'absent' => $totals['absent'],
        'late' => $totals['late'],
        'earlydeparture' => $totals['earlydeparture'],
        'sessions' => count($sessions),
        'students' => count($studentrows),
    ],
    'attentionrows' => $attentionrows,
    'hasattentionrows' => !empty($attentionrows),
    'studentrows' => $studentrows,
    'hasstudentrows' => !empty($studentrows),
    'sessionrows' => $sessionrows,
    'hassessionrows' => !empty($sessionrows),
    'errors' => $errors,
    'haserrors' => !empty($errors),
    'manageurl' => (new moodle_url('/mod/edusign/manage.php', ['id' => $cm->id]))->out(false),
    'gradesyncenabled' => $gradesyncenabled,
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_edusign/report', $reportdata);
echo $OUTPUT->footer();
