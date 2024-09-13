<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Externallib.php file for attendance plugin.
 *
 * @package    mod_edusign
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_recentlyaccesseditems\external;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

use mod_edusign\classes\commons\ApiCaller;
use mod_edusign\classes\commons\EdusignApi;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->libdir . '/filelib.php');

require($CFG->dirroot . '/mod/edusign/classes/commons/EdusignApi.php');

require(__DIR__ . '/locallib.php');

/**
 * Class mod_edusign_external
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_edusign_external extends external_api
{

    /**
     * Adds attendance instance to course.
     *
     * @param int $courseid
     * @param string $name
     * @param string $intro
     * @param int $groupmode
     * @return array
     */
    public static function test_api()
    {
        try {
            ApiCaller::test();
            return [
                'result' => true,
                'error' => '',
            ];
        } catch (Exception $e) {
            return [
                'result' => false,
                'error' => $e->getMessage(),
            ];
        }
    }


    /**
     * Describes test_api return values.
     *
     * @return external_multiple_structure
     */
    public static function test_api_returns()
    {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'Boolean that defines if the request was successful or not'),
            'error' => new external_value(PARAM_TEXT, 'Error message if the request failed'),
        ]);
    }

    /**
     * Describes the parameters for test_api.
     *
     * @return external_function_parameters
     */
    public static function test_api_parameters()
    {
        return new external_function_parameters([]);
    }


    /**
     * Adds attendance instance to course.
     *
     * @param int $courseid
     * @param string $name
     * @param string $intro
     * @param int $groupmode
     * @return array
     */
    public static function take_attendance(int $cmId, int $sessionId, string $method, string $studentsId, string $JSONArgs = '{}')
    {
        global $DB;
        $session = $DB->get_record('edusign_sessions', ['id' => $sessionId]);
        $args = [];

        try {
            $args = json_decode($JSONArgs, true);
        } catch (Exception $e) {
        }

        $cr = null;
        switch ($method) {
            case 'send_sign_email':
                $cr = EdusignApi::sendSignEmails($session->edusign_api_id, explode(',', $studentsId));
                break;
            case 'set_student_absent':
                $cr = EdusignApi::setStudentAbsent($session->edusign_api_id, explode(',', $studentsId)[0], $args['comment']);
                break;
            case 'set_student_delay':
                $cr = EdusignApi::setStudentDelay($session->edusign_api_id, explode(',', $studentsId)[0], $args['delay']);
                break;
            case 'set_student_early_departure':
                $cr = EdusignApi::setStudentEarlyDeparture($session->edusign_api_id, explode(',', $studentsId)[0], $args['earlyDeparture']);
                break;
            default:
                throw new Exception('Method not found');
        }
        return [
            'result' => $cr,
            'error' => '',
        ];
    }

    /**
     * Describes test_api return values.
     *
     * @return external_multiple_structure
     */
    public static function take_attendance_returns()
    {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'Boolean that defines if the request was successful or not'),
            'error' => new external_value(PARAM_TEXT, 'Error message if the request failed'),
        ]);
    }

    /**
     * Describes the parameters for test_api.
     *
     * @return external_function_parameters
     */
    public static function take_attendance_parameters()
    {
        return new external_function_parameters([
            'cmId' => new external_value(PARAM_INT, 'CM Id'),
            'sessionId' => new external_value(PARAM_INT, 'Session Id'),
            'method' => new external_value(PARAM_TEXT, 'Method to trigger'),
            'studentsId' => new external_value(PARAM_TAGLIST, 'Student id'),
            'args' => new external_value(PARAM_TEXT, 'JSON representing multiple args types depending on method'),
        ]);
    }

    /**
     * Get students and professors of a course
     *
     * @param int $cmId
     * @param int $sessionId
     * @return array
     */
    public static function get_students_and_teachers(int $cmId, int $sessionId)
    {
        global $DB;
        $context = context_module::instance($cmId);

        $session = $DB->get_record('edusign_sessions', ['id' => $sessionId]);
        if (!empty($session->edusign_api_id)) {
            $edusignApiCourse = EdusignApi::getCourseById($session->edusign_api_id);
        }

        $students = getStudentsWithPresentialStates($context, $edusignApiCourse);
        $teachers = getTeachersWithPresentialStates($context, $edusignApiCourse);

        try {
            return [
                'result' => [
                    'students' => array_values($students),
                    'teachers' => array_values($teachers),
                ],
                'error' => '',
            ];
        } catch (\Exception $e) {
            return [
                'result' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Describes test_api return values.
     *
     * @return external_multiple_structure
     */
    public static function get_students_and_teachers_returns()
    {
        return new external_single_structure([
            'result' => new external_single_structure([
                'students' => new external_multiple_structure(
                    new external_single_structure([
                        'id' => new external_value(PARAM_INT, 'ID of the user', VALUE_OPTIONAL, null, NULL_ALLOWED),
                        'firstname' => new external_value(PARAM_TEXT, 'First name'),
                        'lastname' => new external_value(PARAM_TEXT, 'Last name'),
                        'email' => new external_value(PARAM_EMAIL, 'User email'),
                        'picture' => new external_value(PARAM_INT, 'Picture user ID', VALUE_OPTIONAL, null, NULL_ALLOWED),
                        'edusign_api_id' => new external_value(PARAM_TEXT, 'Edusign API ID'),
                        'edusign_data' => new external_single_structure([
                            'studentId' => new external_value(PARAM_TEXT, 'Student ID'),
                            'courseId' => new external_value(PARAM_TEXT, 'Course ID'),
                            'schoolId' => new external_value(PARAM_TEXT, 'School ID'),
                            'start' => new external_value(PARAM_TEXT, 'Start time'),
                            'end' => new external_value(PARAM_TEXT, 'End time'),
                            'delay' => new external_value(PARAM_INT, 'Delay', VALUE_OPTIONAL),
                            'signature' => new external_value(PARAM_TEXT, 'Signature', VALUE_OPTIONAL),
                            'signatureEmail' => new external_single_structure([
                                'nbSent' => new external_value(PARAM_INT, 'Number of emails sent'),
                                'requestId' => new external_value(PARAM_TEXT, 'Request ID'),
                                'signUntil' => new external_value(PARAM_TEXT, 'Sign until date'),
                                'sendEmailDate' => new external_value(PARAM_TEXT, 'Email send date'),
                            ], 'Signature Email data', VALUE_OPTIONAL, null, NULL_ALLOWED),
                            'comment' => new external_value(PARAM_TEXT, 'Comment', VALUE_OPTIONAL),
                            'earlyDeparture' => new external_value(PARAM_TEXT, 'Early departure', VALUE_OPTIONAL),
                        ], 'Edusign Data'),
                    ], 'Student data'),
                ),
                'teachers' => new external_multiple_structure(
                    new external_single_structure([
                        'id' => new external_value(PARAM_INT, 'ID of the user', VALUE_OPTIONAL, null, NULL_ALLOWED),
                        'confirmed' => new external_value(PARAM_INT, 'Confirmation status', VALUE_OPTIONAL),
                        'username' => new external_value(PARAM_TEXT, 'Username', VALUE_OPTIONAL),
                        'firstname' => new external_value(PARAM_TEXT, 'First name'),
                        'lastname' => new external_value(PARAM_TEXT, 'Last name'),
                        'maildisplay' => new external_value(PARAM_INT, 'Mail display setting', VALUE_OPTIONAL),
                        'mailformat' => new external_value(PARAM_INT, 'Mail format setting', VALUE_OPTIONAL),
                        'maildigest' => new external_value(PARAM_INT, 'Mail digest setting', VALUE_OPTIONAL),
                        'email' => new external_value(PARAM_EMAIL, 'User email'),
                        'emailstop' => new external_value(PARAM_INT, 'Email stop setting', VALUE_OPTIONAL),
                        'city' => new external_value(PARAM_TEXT, 'City', VALUE_OPTIONAL, VALUE_OPTIONAL),
                        'country' => new external_value(PARAM_TEXT, 'Country', VALUE_OPTIONAL, VALUE_OPTIONAL),
                        'picture' => new external_value(PARAM_INT, 'Picture user ID', VALUE_OPTIONAL, VALUE_OPTIONAL),
                        'lang' => new external_value(PARAM_SAFEDIR, 'Language code', VALUE_OPTIONAL),
                        'timezone' => new external_value(PARAM_TEXT, 'Timezone', VALUE_OPTIONAL),
                        'lastaccess' => new external_value(PARAM_INT, 'Last access time', VALUE_OPTIONAL),
                        'mnethostid' => new external_value(PARAM_INT, 'MNET host ID', VALUE_OPTIONAL),
                        'roleshortname' => new external_value(PARAM_TEXT, 'Role shortname', VALUE_OPTIONAL),
                        'roleid' => new external_value(PARAM_INT, 'Role ID', VALUE_OPTIONAL),
                        'edusign_api_id' => new external_value(PARAM_TEXT, 'Edusign API ID'),
                        'hasSigned' => new external_value(PARAM_BOOL, 'Has signed'),
                        'signature' => new external_value(PARAM_TEXT, 'Signature', VALUE_REQUIRED, null, NULL_ALLOWED),
                    ], 'User data')
                ),
            ]),
            'error' => new external_value(PARAM_TEXT, 'Error message if the request failed'),
        ]);
    }

    /**
     * Describes the parameters for test_api.
     *
     * @return external_function_parameters
     */
    public static function get_students_and_teachers_parameters()
    {
        return new external_function_parameters([
            'cmId' => new external_value(PARAM_INT, 'CM Id'),
            'sessionId' => new external_value(PARAM_INT, 'Session Id'),
        ]);
    }
    /**
     * Get the iframe link to sign a course
     *
     * @param int $sessionId
     * @param string $studentId
     * @return array
     */
    public static function get_signature_link_from_course(int $sessionId, string $userId, string $userType)
    {
        global $DB;
        try {

            $signatureLinks = [];
            $session = $DB->get_record('edusign_sessions', ['id' => $sessionId]);

            if (!empty($session->edusign_api_id)) {
                if ($userType === 'teacher') {
                    $signatureLinks = EdusignApi::getProfessorSignatureLinks($session->edusign_api_id, [$userId]);
                } else if ($userType === 'student') {
                    $signatureLinks = EdusignApi::getStudentSignatureLinks($session->edusign_api_id, [$userId]);
                } else {
                    throw new \Exception('Bad userType must be teacher or student');
                }
            }

            return [
                'result' => $signatureLinks,
                'error' => '',
            ];
        } catch (\Exception $e) {
            return [
                'result' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Describes get_signature_link_from_course return values.
     *
     * @return external_multiple_structure
     */
    public static function get_signature_link_from_course_returns()
    {
        return new external_single_structure([
            'result' => new external_multiple_structure(
                new external_single_structure([
                    'ID' => new external_value(PARAM_TEXT, 'Edusign id of user'),
                    'API_ID' => new external_value(PARAM_TEXT, 'Moodle user id'),
                    'FIRSTNAME' => new external_value(PARAM_TEXT, 'User firstname'),
                    'LASTNAME' => new external_value(PARAM_TEXT, 'User lastname'),
                    'EMAIL' => new external_value(PARAM_TEXT, 'User email'),
                    'SIGNATURE_LINK' => new external_value(PARAM_URL, 'User iframe signature link'),
                    'state' => new external_value(PARAM_BOOL, 'User presence state', VALUE_OPTIONAL),
                ]),
            ),
            'error' => new external_value(PARAM_TEXT, 'Error message if the request failed'),
        ]);
    }

    /**
     * Describes the parameters for get_signature_link_from_course.
     *
     * @return external_function_parameters
     */
    public static function get_signature_link_from_course_parameters()
    {
        return new external_function_parameters([
            'sessionId' => new external_value(PARAM_INT, 'Session Id'),
            'userId' => new external_value(PARAM_TEXT, 'Edusign User ID'),
            'userType' => new external_value(PARAM_TEXT, 'Edusign User Type ( teacher, student )'),
        ]);
    }

    /**
     * Remove session from edusign and moodle
     *
     * @param int $sessionId session id
     * @param bool $withEdusignDelete if true, delete also on edusign
     * @return array
     */
    public static function remove_session(int $sessionId, bool $withEdusignDelete = true)
    {
        global $DB;
        $session = $DB->get_record('edusign_sessions', ['id' => $sessionId], 'edusign_api_id');
        if ($withEdusignDelete) {
            EdusignApi::deleteCourse($session->edusign_api_id);
        }

        $DB->delete_records('edusign_sessions', ['id' => $sessionId]);

        return [
            'result' => true,
            'error' => '',
        ];
    }

    /**
     * Describes get_signature_link_from_course return values.
     *
     * @return external_multiple_structure
     */
    public static function remove_session_returns()
    {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'Result'),
            'error' => new external_value(PARAM_TEXT, 'Error message if the request failed'),
        ]);
    }

    /**
     * Describes the parameters for get_signature_link_from_course.
     *
     * @return external_function_parameters
     */
    public static function remove_session_parameters()
    {
        return new external_function_parameters([
            'sessionId' => new external_value(PARAM_INT, 'Session Id'),
            'withEdusignDelete' => new external_value(PARAM_BOOL, 'Delete also on edusign ?', VALUE_OPTIONAL, true),
        ]);
    }
    
     /**
     * On attendance sheet has been signed
     *
     * @param int $cmId course module id
     * @param int $sessionId session id
     * @return array
     */
    public static function on_attendance_sheet_signed(int $cmId, int $sessionId)
    {
        global $DB;
        $cm = get_coursemodule_from_id('edusign', $cmId, 0, false, MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $cm->course), '*');
        
        // Update completion state
        $completion = new completion_info($course);
        if ($completion->is_enabled($cm)) {
            $completion->update_state($cm, COMPLETION_UNKNOWN);
        }

        return [
            'result' => true,
            'error' => '',
        ];
    }

    /**
     * Describes get_signature_link_from_course return values.
     *
     * @return external_multiple_structure
     */
    public static function on_attendance_sheet_signed_returns()
    {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'Result'),
            'error' => new external_value(PARAM_TEXT, 'Error message if the request failed'),
        ]);
    }

    /**
     * Describes the parameters for get_signature_link_from_course.
     *
     * @return external_function_parameters
     */
    public static function on_attendance_sheet_signed_parameters()
    {
        return new external_function_parameters([
            'cmId' => new external_value(PARAM_INT, 'Course module Id'),
            'sessionId' => new external_value(PARAM_INT, 'Session Id'),
        ]);
    }

    /**
     * Archive or unarchive session from edusign and moodle
     *
     * @param int $sessionId session id
     * @return array
     */
    public static function archive_session(int $cmId, int $sessionId, bool $archiveState = true)
    {
        global $DB;
        $session = $DB->get_record('edusign_sessions', ['id' => $sessionId]);

        try {
            if ($archiveState) {
                EdusignApi::lockCourse($session->edusign_api_id);
            } else {
                EdusignApi::unlockCourse($session->edusign_api_id);
            }
        } catch (\Exception $e) {
            \core\notification::error(get_string('archive_session_sync_error', 'mod_edusign', $e->getMessage()));
        }
        $session->archived = $archiveState ? 1 : 0;
        $DB->update_record('edusign_sessions', $session);
        
        $cm = get_coursemodule_from_id('edusign', $cmId, 0, false, MUST_EXIST);
        $course       = $DB->get_record('course', array('id' => $cm->course), '*');
        $edusign      = $DB->get_record('edusign', array('id' => $cm->instance), '*');
        $students     = getStudentsFromContext(context_module::instance($cm->id));
        
        // // Update completion state
        // $completion = new completion_info($course);
        // if ($completion->is_enabled($cm) && !empty($edusign->complete_mode)) {
        //     foreach ($students as $student) {
        //         $completion->update_state($cm, COMPLETION_UNKNOWN, $student->id);
        //     }
        // }
        
        \core\notification::success(get_string('archive_session_success', 'mod_edusign'));
        return [
            'result' => true,
            'error' => '',
        ];
    }

    /**
     * Describes get_signature_link_from_course return values.
     *
     * @return external_multiple_structure
     */
    public static function archive_session_returns()
    {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'Result'),
            'error' => new external_value(PARAM_TEXT, 'Error message if the request failed'),
        ]);
    }

    /**
     * Describes the parameters for get_signature_link_from_course.
     *
     * @return external_function_parameters
     */
    public static function archive_session_parameters()
    {
        return new external_function_parameters([
            'cmId' => new external_value(PARAM_INT, 'Course module Id'),
            'sessionId' => new external_value(PARAM_INT, 'Session Id'),
            'archiveState' => new external_value(PARAM_BOOL, 'Archive state', VALUE_OPTIONAL, true),
        ]);
    }

    private static function readCSVString($csvString)
    {
        $csv = array_map('str_getcsv', explode("\n", $csvString));
        array_walk($csv, function (&$a) use ($csv) {
            $a = array_combine($csv[0], $a);
        });
        array_shift($csv); # remove column header
        return $csv;
    }

    /**
     * Parse a CSV of sessions from edusign and moodle
     *
     * @param int $cmId course module id
     * @return array
     */
    public static function parse_csv(string $base64File)
    {
        $file = trim(file_get_contents($base64File));
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $ext = $finfo->buffer($file);

        if (str_starts_with($ext, 'text/') === false) {
            throw new \Exception('File must be a CSV');
        }

        $csvFile = self::readCSVString($file);

        return $csvFile;
    }

    /**
     * Describes parse_csv return values.
     *
     * @return external_multiple_structure
     */
    public static function parse_csv_returns()
    {
        return new external_multiple_structure(
            new external_single_structure([
                'session_name' => new external_value(PARAM_TEXT, 'Name of the session'),
                'start_date' => new external_value(PARAM_TEXT, 'Start date of the session (YYYY-MM-DD HH:MM:SS)'),
                'end_date' => new external_value(PARAM_TEXT, 'End date of the session (YYYY-MM-DD HH:MM:SS)'),
            ], 'CSV File'),
        );
    }

    /**
     * Describes the parameters for parse_csv.
     *
     * @return external_function_parameters
     */
    public static function parse_csv_parameters()
    {
        return new external_function_parameters([
            'base64File' => new external_value(PARAM_TEXT, 'BASE64 CSV file'),
        ]);
    }

    /**
     * Import multiple sessions from a config into moodle
     *
     * @param int $cmId course module id
     * @return array
     */
    public static function import_sessions(int $cmId, array $sessions)
    {
        if (empty($sessions)) {
            throw new \Exception('No sessions to import');
        }
        $context = context_module::instance($cmId);
        $cm = get_coursemodule_from_id('edusign', $cmId, 0, false, MUST_EXIST);

        $cr = true;
        foreach ($sessions as $session) {
            $cr = $cr && create_session($context, $cm, [
                'title' => $session['name'],
                'startDate' => $session['start_date'],
                'endDate' => $session['end_date'],
            ]);
        }

        if (!$cr) {
            throw new \Exception('An error occured while importing sessions');
        }

        \core\notification::success(get_string('import_sessions_success', 'mod_edusign'));

        return [
            'success' => $cr,
        ];
    }

    /**
     * Describes import_sessions return values.
     *
     * @return external_multiple_structure
     */
    public static function import_sessions_returns()
    {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Result'),
        ]);
    }

    /**
     * Describes the parameters for import_sessions.
     *
     * @return external_function_parameters
     */
    public static function import_sessions_parameters()
    {
        return new external_function_parameters([
            'cmId' => new external_value(PARAM_INT, 'Course module Id to create sessions into'),
            'sessions' => new external_multiple_structure(
                new external_single_structure([
                    'name' => new external_value(PARAM_TEXT, 'Name of the session'),
                    'start_date' => new external_value(PARAM_TEXT, 'Start date of the session (YYYY-MM-DD HH:MM:SS)'),
                    'end_date' => new external_value(PARAM_TEXT, 'End date of the session (YYYY-MM-DD HH:MM:SS)')
                ]),
                'Array of sessions to be created'
            ),
        ]);
    }
}
