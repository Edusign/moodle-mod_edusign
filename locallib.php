<?php

use mod_edusign\classes\commons\EdusignApi;

require_once(__DIR__ . '/classes/commons/EdusignApi.php');
require_once($CFG->dirroot . '/group/lib.php');
require_once("{$CFG->libdir}/completionlib.php"); //require missing?

defined('EDUSIGN_SESSION_COMMON') || define('EDUSIGN_SESSION_COMMON', 0);
defined('EDUSIGN_SESSION_GROUP') || define('EDUSIGN_SESSION_GROUP', 1);

/**
 * Helper function to add sessiondate_selector to add/update forms.
 *
 * @param MoodleQuickForm $mform
 */
function edusign_form_sessiondate_selector(MoodleQuickForm $mform)
{

    $mform->addElement('date_selector', 'sessiondate', get_string('sessiondate', 'edusign'));

    for ($i = 0; $i <= 23; $i++) {
        $hours[$i] = sprintf("%02d", $i);
    }
    for ($i = 0; $i < 60; $i++) {
        $minutes[$i] = sprintf("%02d", $i);
    }

    $sesendtime = array();
    if (!right_to_left()) {
        $sesendtime[] = &$mform->createElement('static', 'from', '', get_string('from', 'edusign'));
        $sesendtime[] = &$mform->createElement('select', 'starthour', get_string('hour', 'form'), $hours, false, true);
        $sesendtime[] = &$mform->createElement('select', 'startminute', get_string('minute', 'form'), $minutes, false, true);
        $sesendtime[] = &$mform->createElement('static', 'to', '', get_string('to', 'edusign'));
        $sesendtime[] = &$mform->createElement('select', 'endhour', get_string('hour', 'form'), $hours, false, true);
        $sesendtime[] = &$mform->createElement('select', 'endminute', get_string('minute', 'form'), $minutes, false, true);
    } else {
        $sesendtime[] = &$mform->createElement('static', 'from', '', get_string('from', 'edusign'));
        $sesendtime[] = &$mform->createElement('select', 'startminute', get_string('minute', 'form'), $minutes, false, true);
        $sesendtime[] = &$mform->createElement('select', 'starthour', get_string('hour', 'form'), $hours, false, true);
        $sesendtime[] = &$mform->createElement('static', 'to', '', get_string('to', 'edusign'));
        $sesendtime[] = &$mform->createElement('select', 'endminute', get_string('minute', 'form'), $minutes, false, true);
        $sesendtime[] = &$mform->createElement('select', 'endhour', get_string('hour', 'form'), $hours, false, true);
    }
    
    $mform->addGroup($sesendtime, 'sestime', get_string('time', 'edusign'), array(' '), true);
    
    $mform->setDefault('sestime[starthour]', 8);
    $mform->setDefault('sestime[startminute]', 0);
    $mform->setDefault('sestime[endhour]', 17);
    $mform->setDefault('sestime[endminute]', 0);
}

function edusign_build_recurring_sessions(stdClass $formdata, string $startDate, string $endDate): array
{
    if (empty($formdata->repeatsessions)) {
        return [[
            'title' => $formdata->title,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]];
    }

    $selecteddays = edusign_get_selected_repeat_days($formdata);
    if (empty($selecteddays)) {
        throw new invalid_parameter_exception(get_string('errorrepeatdaysrequired', 'mod_edusign'));
    }

    $daynumbers = [
        'monday' => 1,
        'tuesday' => 2,
        'wednesday' => 3,
        'thursday' => 4,
        'friday' => 5,
        'saturday' => 6,
        'sunday' => 7,
    ];

    $selecteddaynumbers = array_map(function ($day) use ($daynumbers) {
        return $daynumbers[$day];
    }, $selecteddays);

    $timezone = core_date::get_user_timezone_object();
    $interval = max(1, (int)($formdata->repeatevery['repeatinterval'] ?? 1));
    $repeatuntil = (new DateTimeImmutable('@' . (int)$formdata->repeatuntil))
        ->setTimezone($timezone)
        ->setTime(23, 59, 59);
    $base = new DateTimeImmutable($startDate, $timezone);
    $baseend = new DateTimeImmutable($endDate, $timezone);
    $duration = $baseend->getTimestamp() - $base->getTimestamp();
    $baseweek = $base->setTime(0, 0, 0)->modify('monday this week');
    $sessions = [[
        'title' => $formdata->title,
        'startDate' => $startDate,
        'endDate' => $endDate,
    ]];

    for ($current = $base; $current <= $repeatuntil; $current = $current->modify('+1 day')) {
        $daynumber = (int)$current->format('N');
        if (!in_array($daynumber, $selecteddaynumbers, true)) {
            continue;
        }

        $currentweek = $current->setTime(0, 0, 0)->modify('monday this week');
        $weekdiff = (int)floor($baseweek->diff($currentweek)->days / 7);
        if ($weekdiff < 0 || $weekdiff % $interval !== 0) {
            continue;
        }

        $sessionstart = $current->format('Y-m-d H:i:s');
        if (strtotime($sessionstart) <= strtotime($startDate)) {
            continue;
        }

        $sessions[] = [
            'title' => $formdata->title,
            'startDate' => $sessionstart,
            'endDate' => $current->setTimestamp($current->getTimestamp() + $duration)->format('Y-m-d H:i:s'),
        ];
    }

    return $sessions;
}

function edusign_get_selected_repeat_days($formdata): array
{
    $data = (array)$formdata;
    $repeatdays = (array)($data['repeatdays'] ?? []);
    $weekdays = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    return array_values(array_filter($weekdays, function ($day) use ($data, $repeatdays) {
        return !empty($repeatdays[$day]) || !empty($data[$day]);
    }));
}

function edusign_get_session_groupids(stdClass $formdata): array
{
    if (($formdata->sessiontype ?? EDUSIGN_SESSION_COMMON) != EDUSIGN_SESSION_GROUP) {
        return [0];
    }

    $groups = array_filter(array_map('intval', (array)($formdata->groups ?? [])));
    if (empty($groups)) {
        throw new invalid_parameter_exception(get_string('errorgroupsnotselected', 'mod_edusign'));
    }

    return array_values($groups);
}

function edusign_normalize_groupids($groupids): array
{
    $groupids = array_filter(array_map('intval', (array)$groupids));
    $groupids = array_values(array_unique($groupids));
    sort($groupids);

    return $groupids;
}

function edusign_get_session_groupids_from_session(stdClass $session): array
{
    global $DB;

    if (!empty($session->id) && $DB->get_manager()->table_exists('edusign_session_groups')) {
        $records = $DB->get_records('edusign_session_groups', ['sessionid' => $session->id], 'groupid ASC');
        if (!empty($records)) {
            return array_values(array_map(function ($record) {
                return (int)$record->groupid;
            }, $records));
        }
    }

    return empty($session->groupid) ? [] : [(int)$session->groupid];
}

function edusign_set_session_groupids(int $sessionid, array $groupids): void
{
    global $DB;

    if (!$DB->get_manager()->table_exists('edusign_session_groups')) {
        return;
    }

    $DB->delete_records('edusign_session_groups', ['sessionid' => $sessionid]);
    foreach (edusign_normalize_groupids($groupids) as $groupid) {
        $DB->insert_record('edusign_session_groups', [
            'sessionid' => $sessionid,
            'groupid' => $groupid,
        ]);
    }
}

function edusign_get_session_group_label(stdClass $session): string
{
    $groupids = edusign_get_session_groupids_from_session($session);
    if (empty($groupids)) {
        return get_string('commonsession', 'mod_edusign');
    }

    $groupnames = array_filter(array_map(function ($groupid) {
        return groups_get_group_name($groupid);
    }, $groupids));

    return empty($groupnames) ? get_string('groupsession', 'mod_edusign') : implode(', ', $groupnames);
}

function isTrainingExistsOnEdusign($trainingId, $baseEvent = [])
{
    try {
        $training = EdusignApi::getTraining($trainingId, $baseEvent);
        if ($training) {
            return true;
        }
    } catch (\Exception $e) {
        return false;
    }
    return false;
}

function createTrainingFromCourse($courseId, $startDate, $endDate, $context, array $baseEvent = [])
{
    global $DB;

    $course = get_course($courseId);
    $courseEdusignApi = $DB->get_record('course_edusign_api', array('course_id' => $courseId));
    // Try to create course on edusign api
    if ($courseEdusignApi->edusign_api_id) {
        $course->edusign_api_id = $courseEdusignApi->edusign_api_id;
        return $course;
    }
    $students = getStudentsFromContext($context);
    // $teachers = getTeachersFromContext($context);

    try {
        $trainingData = [
            'NAME' => $course->fullname,
            'START' =>  $startDate,
            'END' =>  $endDate,
        ];
        if (!empty($students)) {
            $trainingData['STUDENTS'] = array_filter(array_map(function ($student) {
                return $student->edusign_api_id;
            }, array_values($students)), function ($student) {
                return $student !== null;
            });
        }
        if (!empty($teachers)) {
            $trainingData['PROFESSORS'] = array_map(function ($teacher) {
                return $teacher->edusign_api_id;
            }, array_values($teachers));
        }

        $edusignAPIID = EdusignApi::createTraining($trainingData, $baseEvent);

        if ($edusignAPIID) {
            $DB->delete_records('course_edusign_api', [
                'course_id' => $courseId,
            ]);
            $DB->insert_record('course_edusign_api', [
                'course_id' => $courseId,
                'edusign_api_id' => $edusignAPIID,
            ]);
        }
        \core\notification::success(get_string('course_linked_success', 'mod_edusign'));
        $course->edusign_api_id = $edusignAPIID;
        return $course;
    } catch (\Exception $e) {
        // Error is journalized
        \core\notification::error(get_string('course_linked_error', 'mod_edusign', $e->getMessage()));
    }
}

function updateTrainingFromCourse($courseId, $startDate, $endDate, array $baseEvent = [])
{
    global $DB;

    $course = get_course($courseId);
    $courseEdusignApi = $DB->get_record('course_edusign_api', array('course_id' => $courseId));
    // Try to create course on edusign api
    if (!$courseEdusignApi->edusign_api_id) {
        throw new Error('Missing api edusign id on course for updating');
    }

    try {
        $trainingData = [
            'NAME' => $course->fullname,
            'START' =>  $startDate,
            'END' =>  $endDate,
        ];

        EdusignApi::updateTraining($courseEdusignApi->edusign_api_id, $trainingData, $baseEvent);
        \core\notification::success(get_string('course_updated_success', 'mod_edusign'));
        return $course;
    } catch (\Exception $e) {
        // Error is journalized
        \core\notification::error(get_string('course_updated_error', 'mod_edusign', $e->getMessage()));
    }
}

function getStudentsFromContext($context, $groupids = 0)
{
    global $DB;
    $studentRole = $DB->get_record_sql('SELECT id FROM {role} WHERE shortname = "student"');
    $students = get_role_users($studentRole->id, $context, '*');
    $groupids = edusign_normalize_groupids($groupids);

    if (!empty($groupids)) {
        $students = array_filter($students, function ($student) use ($groupids) {
            foreach ($groupids as $groupid) {
                if (groups_is_member($groupid, $student->id)) {
                    return true;
                }
            }
            return false;
        });
    }

    if (empty($students)) {
        return [];
    }

    $studentsIds = array_map(function ($student) {
        return $student->id;
    }, $students);


    $usersEdusignApi = $DB->get_records_sql(
        'SELECT user_id, edusign_api_id FROM {users_edusign_api} WHERE user_id IN (' . implode(', ', $studentsIds) . ') AND role = "student"'
    );
    $students = array_map(function ($student) {
        $student->edusign_api_id = null;
        return $student;
    }, $students);

    foreach ($usersEdusignApi as $userEdusignApi) {
        if ($userEdusignApi->edusign_api_id) {
            $students = array_map(function ($student) use ($userEdusignApi) {
                if ($student->id === $userEdusignApi->user_id) {
                    $student->edusign_api_id = $userEdusignApi->edusign_api_id;
                }
                return $student;
            }, $students);
        }
    }
    return $students;
}


function getUserWithEdusignApiId(string|null $role, int $userId)
{
    global $DB;
    $user = $DB->get_record('user', ['id' => $userId]);

    if ($role === "editingteacher") {
        $role = "teacher";
    }
    
    $userEdusignApi = null;
    if(!$role) {
        $userEdusignApi = $DB->get_record_sql(
            'SELECT user_id, edusign_api_id, role FROM {users_edusign_api} WHERE user_id = ?',
            [$userId]
        );
    } else {
        $userEdusignApi = $DB->get_record_sql(
            'SELECT user_id, edusign_api_id, role FROM {users_edusign_api} WHERE user_id = ? AND role = ?',
            [$userId, $role]
        );
    }
    
    $user->edusign_api_id = null;
    $user->role = $role;
    
    if (empty($userEdusignApi)) {
        return $user;
    }
    
    $user->edusign_api_id = $userEdusignApi->edusign_api_id;
    return $user;
}

function getUserFromEdusignApiId(string $role, string $userId)
{
    global $DB;
    if ($role === "editingteacher") {
        $role = "teacher";
    }
    $userEdusignApi = $DB->get_record_sql(
        'SELECT user_id, edusign_api_id FROM {users_edusign_api} WHERE edusign_api_id = ? AND role = ?',
        [$userId, $role]
    );
    $user = $DB->get_record('user', ['id' => $userEdusignApi->user_id]);
    $user->edusign_api_id = $userEdusignApi->edusign_api_id;
    return $user;
}



function getTeachersFromContext($context)
{
    global $DB;
    $teacherRoles = $DB->get_records_sql('SELECT id FROM {role} WHERE shortname IN("teacher","editingteacher")');
    $teachers = [];
    foreach ($teacherRoles as $role) {
        $teachers = get_role_users($role->id, $context, '*') + $teachers;
    }

    if (empty($teachers)) {
        return [];
    }

    $teachersIds = array_map(function ($teacher) {
        return $teacher->id;
    }, $teachers);

    $usersEdusignApi = $DB->get_records_sql(
        'SELECT user_id, edusign_api_id FROM {users_edusign_api} WHERE user_id IN (' . implode(', ', $teachersIds) . ') AND role = "teacher"'
    );
    $teachers = array_map(function ($teacher) {
        $teacher->edusign_api_id = null;
        return $teacher;
    }, $teachers);

    foreach ($usersEdusignApi as $userEdusignApi) {
        if ($userEdusignApi->edusign_api_id) {
            $teachers = array_map(function ($teacher) use ($userEdusignApi) {
                if ($teacher->id === $userEdusignApi->user_id) {
                    $teacher->edusign_api_id = $userEdusignApi->edusign_api_id;
                }
                return $teacher;
            }, $teachers);
        }
    }
    return $teachers;
}


function syncStudentsToApi($students, $context, $withVerification = false)
{
    global $DB;
    if (!$withVerification) {
        // Filtrer tous les étudiants avec des ids edusign pour la synchronisation
        $students = array_filter($students, function ($student) {
            return $student->edusign_api_id === null;
        });
    }

    // Vérification si les étudiants sont inscrits sur edusign
    foreach ($students as $student) {
        $studentAPIID = null;
        if ($student->edusign_api_id && $withVerification) {
            try {
                $studentAPI = EdusignApi::getStudentById($student->edusign_api_id, [
                    'objectid' => $student->id,
                    'context' => $context,
                ]);
                $studentAPIID = $studentAPI->ID;
            } catch (\Exception $e) {
                // Student does not exists anymore on edusign so remove sync
                $DB->delete_records('users_edusign_api', [
                    'role' => 'student',
                    'edusign_api_id' => $student->edusign_api_id,
                ]);
            }
        }
        // If student not found with verification, trying to find with email
        if (!$studentAPIID) {
            try {
                $studentAPI = EdusignApi::getStudentByEmail($student->email, [
                    'objectid' => $student->id,
                    'context' => $context,
                ]);
                $studentAPIID = $studentAPI->ID;
            } catch (\Exception $e) {
                // Student not exists on edusign
            }
        }

        // If student not found with email, create it
        if (!$studentAPIID) {
            $studentAPIID = EdusignApi::createStudent([
                "FIRSTNAME" => $student->firstname,
                "LASTNAME" => $student->lastname,
                "EMAIL" => $student->email,
                "API_ID" => $student->id,
                "SEND_EMAIL_CREDENTIALS" => false,
            ], [
                'objectid' => $student->id,
                'context' => $context,
            ]);
        }

        // If student cannot be founded or created, throw exception
        if (!$studentAPIID) {
            throw new Exception('Error while creating student ' . $student->firstname . ' ' . $student->lastname . ' on edusign');
        }

        $studentToInsert = $DB->get_record('users_edusign_api', [
            'user_id' => $student->id,
            'role' => 'student',
            'edusign_api_id' => $studentAPIID,
        ]);

        if (!$studentToInsert) {
            $DB->insert_record('users_edusign_api', [
                'user_id' => $student->id,
                'role' => 'student',
                'edusign_api_id' => $studentAPIID,
            ]);
        }
    }
    return $students;
}

function syncTeachersToApi(array $teachers, $context, $withVerification = false)
{
    global $DB;
    if (!$withVerification) {
        // Filtrer tous les étudiants avec des ids edusign pour la synchronisation
        $teachers = array_filter($teachers, function ($teacher) {
            return $teacher->edusign_api_id === null;
        });
    }

    // Vérification si les étudiants sont inscrits sur edusign
    foreach ($teachers as $teacher) {
        $teacherAPIID = null;
        if ($teacher->edusign_api_id && $withVerification) {
            try {
                $teacherAPI = EdusignApi::getProfessorById($teacher->edusign_api_id, [
                    'objectid' => $teacher->id,
                    'context' => $context,
                ]);

                $teacherAPIID = $teacherAPI->ID;
            } catch (\Exception $e) {
                // Teacher does not exists anymore on edusign so remove sync
                $DB->delete_records('users_edusign_api', [
                    'role' => 'teacher',
                    'edusign_api_id' => $teacher->edusign_api_id,
                ]);
            }
        }
        try {
            $teacherAPI = EdusignApi::getProfessorByEmail($teacher->email, [
                'objectid' => $teacher->id,
                'context' => $context,
            ]);

            $teacherAPIID = $teacherAPI->ID;
        } catch (\Exception $e) {
            // Teacher not exists on edusign
        }

        // If teacher not found with email, create it
        if (!$teacherAPIID) {
            $teacherAPIID = EdusignApi::createProfessor([
                "FIRSTNAME" => $teacher->firstname,
                "LASTNAME" => $teacher->lastname,
                "EMAIL" => $teacher->email,
                "API_ID" => $teacher->id,
                "dontSendCredentials" => true,
            ], [
                'objectid' => $teacher->id,
                'context' => $context,
            ]);
        }

        // If teacher cannot be founded or created, throw exception
        if (!$teacherAPIID) {
            throw new Exception('Error while creating teacher ' . $teacher->firstname . ' ' . $teacher->lastname . ' on edusign');
        }

        $teacherToInsert = $DB->get_record('users_edusign_api', [
            'user_id' => $teacher->id,
            'role' => 'teacher',
            'edusign_api_id' => $teacherAPIID,
        ]);

        if (!$teacherToInsert) {
            $DB->insert_record('users_edusign_api', [
                'user_id' => $teacher->id,
                'role' => 'teacher',
                'edusign_api_id' => $teacherAPIID,
            ]);
        }
    }
    return $teachers;
}

function syncStudentsToApiFromContext($context, $withVerification = false, $groupids = 0)
{
    // Récupération des étudiants à synchroniser sur edusign
    $students = getStudentsFromContext($context, $groupids);
    syncStudentsToApi($students, $context, $withVerification);
    return getStudentsFromContext($context, $groupids);
}

function syncTeachersToApiFromContext($context, $withVerification = false)
{
    // Récupération des étudiants à synchroniser sur edusign
    $teachers = getTeachersFromContext($context);
    syncTeachersToApi($teachers, $context, $withVerification);
    return getTeachersFromContext($context);
}

function findUserByApiIdInArray($userId, $users)
{
    foreach ($users as $user) {
        if ($user->edusignApiId === $userId) {
            return $user;
        }
    }
    return null;
}

function getStudentsWithPresentialStates($context, $edusignApiCourse = null)
{
    $moodleStudents = getStudentsFromContext($context);
    $edusignStudentsById = [];
    $students = [];

    if ($edusignApiCourse) {
        // Synchro des étudiants
        foreach ($edusignApiCourse->STUDENTS as $student) {
            $edusignStudentsById[$student->studentId] = $student;
        }

        foreach ($edusignStudentsById as $studentApiId => $edusignStudent) {
            $student = [];
            foreach ($moodleStudents as $moodleStudent) {
                if ($moodleStudent->edusign_api_id === $studentApiId) {
                    $student = $moodleStudent;
                    break;
                }
            }
            if (empty($student)) {
                $edusignStudentFromAPI = EdusignApi::getStudentById($studentApiId);
                $student = new stdClass();
                $student->edusign_api_id = $studentApiId;
                $student->edusign_data = $edusignStudent;
                $student->id = null;
                $student->firstname = $edusignStudentFromAPI->FIRSTNAME;
                $student->lastname = $edusignStudentFromAPI->LASTNAME;
                $student->email = $edusignStudentFromAPI->EMAIL;
            }
            $student->edusign_data = $edusignStudent;
            $students[] = $student;
        }
    } else {
        foreach ($moodleStudents as $student) {
            $student->edusign_data = null;
            $students[] = $student;
        }
    }
    return $students;
}

function getTeachersWithPresentialStates($context, $edusignApiCourse = null)
{
    $moodleTeachers = getTeachersFromContext($context);
    $teachers = [];

    if ($edusignApiCourse) {
        // Filter all keys on $edusignApiCourse that match with pattern /PROFESSOR(_\d+)?/
        $professorsKeys = array_filter(array_keys((array)$edusignApiCourse), function ($key) use ($edusignApiCourse) {
            return preg_match('/^PROFESSOR(_\d+)?$/', $key) && !empty($edusignApiCourse->$key);
        });

        foreach ($professorsKeys as $professorKey) {
            $profIndex = 1;
            if ($professorKey !== 'PROFESSOR') {
                $profIndex = explode('_', $professorKey)[1];
            }

            $professor = new stdClass();

            foreach ($moodleTeachers as $teacher) {
                if ($teacher->edusign_api_id === $edusignApiCourse->$professorKey) {
                    $professor = $teacher;
                    break;
                }
            }

            if (empty($professor->id)) {
                $edusignProfessorFromAPI = EdusignApi::getProfessorById($edusignApiCourse->$professorKey);
                $professor->edusign_api_id = $edusignApiCourse->$professorKey;
                $professor->id = null;
                $professor->firstname = $edusignProfessorFromAPI->FIRSTNAME;
                $professor->lastname = $edusignProfessorFromAPI->LASTNAME;
                $professor->email = $edusignProfessorFromAPI->EMAIL;
            }

            $professor->hasSigned = !!$edusignApiCourse->{'PROFESSOR_SIGNATURE' . ($profIndex > 1 ? '_' . $profIndex : '')};
            $professor->signature = $edusignApiCourse->{'PROFESSOR_SIGNATURE' . ($profIndex > 1 ? '_' . $profIndex : '')};

            $teachers[] = $professor;
        }
    } else {
        foreach ($moodleTeachers as $teacher) {
            $teacher->hasSigned = null;
            $teacher->signature = null;
            $teachers[] = $teacher;
        }
    }
    
    return $teachers;
}

function create_session($context, stdClass $cm, array $data, $forceSync = false, $processCompletion = false)
{
    global $DB;
    $edusign = reset($DB->get_records('edusign', ['id' => $cm->instance]));
    $course       = $DB->get_record('course', array('id' => $cm->course), '*');
    $groupids = edusign_normalize_groupids($data['groupids'] ?? ($data['groupid'] ?? 0));
    $groupid = count($groupids) === 1 ? reset($groupids) : 0;
    
    // Synchronisation et récupération des étudiants liés au module d'activité
    $students = syncStudentsToApiFromContext($context, $forceSync, $groupids);
    $teachers = syncTeachersToApiFromContext($context, $forceSync);
    
    // Create course to edusign api with students edusign api ids
    $courseData = [
        'NAME' => $data['title'],
        'START' => $data['startDate'],
        'END' => $data['endDate'],
        'STUDENTS' => array_map(function ($student) {
            return ['studentId' => $student->edusign_api_id];
        }, array_values($students))
    ];

    // Récupération de l'id Training edusign
    $courseEdusignApi = $DB->get_record_sql(
        'SELECT edusign_api_id FROM {course_edusign_api} WHERE course_id = ' . $cm->course
    );
    if ($courseEdusignApi->edusign_api_id) {
        $courseData['TRAINING_ID'] = $courseEdusignApi->edusign_api_id;
    }

    if (!$courseData['TRAINING_ID'] || !isTrainingExistsOnEdusign($courseData['TRAINING_ID'])) {
        if ($edusign){
            createTrainingFromCourse($cm->course, $edusign->date_start, $edusign->date_end, $context);
        }
    }

    foreach (array_values($teachers) as $index => $teacher) {
        $key = $index > 0 ? ('PROFESSOR_' . $index + 1) : 'PROFESSOR';
        $courseData[$key] = $teacher->edusign_api_id;
    }

    $edusignCourseID = EdusignApi::createCourse($courseData);

    // Create session in moodle BDD
    $cr = $DB->insert_record('edusign_sessions', [
        'edusign_api_id' => $edusignCourseID,
        'activity_module_id' => $cm->id,
        'date_start' => $data['startDate'],
        'date_end' => $data['endDate'],
        'title' => $data['title'],
        'groupid' => $groupid,
    ]);
    edusign_set_session_groupids($cr, $groupids);


    // Update completion state
    if ($processCompletion) {
        // Disabled for now because of perfissue
        $completion = new completion_info($course);
        if ($completion->is_enabled($cm) && !empty($edusign->complete_mode)) {
            foreach ($students as $student) {
                $completion->update_state($cm, COMPLETION_UNKNOWN, $student->id);
            }
        }
    }

    if (function_exists('edusign_maybe_update_attendance_grades')) {
        edusign_maybe_update_attendance_grades($edusign);
    }

    return $DB->get_record('edusign_sessions', ['id' => $cr]);
}

function update_session($context, stdClass $cm, $session, array $data, $processCompletion = false)
{
    global $DB;
    // Create course to edusign api with students edusign api ids
    $edusignCourseData = [
        'NAME' => $data['title'],
        'START' => $data['startDate'],
        'END' => $data['endDate'],
    ];

    if ($session->edusign_api_id) {
        $course = EdusignApi::getCourseById($session->edusign_api_id);
        EdusignApi::updateCourse($session->edusign_api_id, array_merge($edusignCourseData, (array)$course));
    }

    $session->title = $data['title'];
    $session->date_start = $data['startDate'];
    $session->date_end = $data['endDate'];

    // Create session in moodle BDD
    $DB->update_record('edusign_sessions', $session);
    
    $students = syncStudentsToApiFromContext($context);
    // Update completion state
    if ($processCompletion) {
        // Disabled for now because of perfissue
        $completion = new completion_info($course);
        if ($completion->is_enabled($cm) && !empty($edusign->complete_mode)) {
            foreach ($students as $student) {
                $completion->update_state($cm, COMPLETION_UNKNOWN, $student->id);
            }
        }
    }
    $edusign = $DB->get_record('edusign', ['id' => $cm->instance]);
    if (function_exists('edusign_maybe_update_attendance_grades')) {
        edusign_maybe_update_attendance_grades($edusign);
    }
    return $session;
}

function is_student_has_session(&$session, $userId)
{
    
    try {
        $userEdusignApi = getUserWithEdusignApiId('student', $userId);
        $studentEdusignApiId = $userEdusignApi->edusign_api_id;
        $edusignCourse = EdusignApi::getCourseById($session->edusign_api_id);
        foreach ($edusignCourse->STUDENTS as $student) {
            if ($student->studentId === $studentEdusignApiId) {
                return true;
            }
        }
    } catch (\Exception $e) {
        mtrace('Error in is_student_has_session: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    }
    return false;
}


function add_edusign_session_infos($session)
{
    $edusignCourse = EdusignApi::getCourseById($session->edusign_api_id);
    $session->edusign_course = $edusignCourse;
    return $session;
}

function add_edusign_sessions_infos($sessions)
{
    return array_map(function ($session) {
        return add_edusign_session_infos($session);
    }, $sessions);
}

function edusign_attendance_status(stdClass $student): string
{
    if (!empty($student->signature)) {
        return 'present';
    }
    if (!empty($student->comment) && trim($student->comment) !== '') {
        return 'justified';
    }
    if (!empty($student->signatureEmail)) {
        return 'pending';
    }
    return 'absent';
}

function edusign_attendance_status_counts_towards_grade(string $status): bool
{
    return in_array($status, ['present', 'justified'], true);
}

function edusign_get_attendance_grades(stdClass $cm, stdClass $edusign, int $userid = 0): array
{
    global $DB;

    if (empty($edusign->attendancegradeenabled) || (float)$edusign->grade <= 0) {
        return [];
    }

    $context = context_module::instance($cm->id);
    $moodlestudents = getStudentsFromContext($context);
    $statsbyapiid = [];

    foreach ($moodlestudents as $student) {
        if ($userid > 0 && (int)$student->id !== $userid) {
            continue;
        }
        if (empty($student->edusign_api_id)) {
            continue;
        }
        $statsbyapiid[$student->edusign_api_id] = [
            'userid' => (int)$student->id,
            'expected' => 0,
            'attended' => 0,
        ];
    }

    if (empty($statsbyapiid)) {
        return [];
    }

    $sessions = $DB->get_records('edusign_sessions', ['activity_module_id' => $cm->id], 'date_start ASC');
    foreach ($sessions as $session) {
        if (empty($session->edusign_api_id)) {
            continue;
        }

        $edusigncourse = EdusignApi::getCourseById($session->edusign_api_id);
        foreach ($edusigncourse->STUDENTS ?? [] as $edusignstudent) {
            $apiid = $edusignstudent->studentId ?? null;
            if (empty($apiid) || !isset($statsbyapiid[$apiid])) {
                continue;
            }

            $statsbyapiid[$apiid]['expected']++;
            if (edusign_attendance_status_counts_towards_grade(edusign_attendance_status($edusignstudent))) {
                $statsbyapiid[$apiid]['attended']++;
            }
        }
    }

    $grades = [];
    foreach ($statsbyapiid as $stats) {
        if ($stats['expected'] <= 0) {
            continue;
        }

        $grade = new stdClass();
        $grade->userid = $stats['userid'];
        $grade->rawgrade = round(($stats['attended'] / $stats['expected']) * (float)$edusign->grade, 5);
        $grades[$grade->userid] = $grade;
    }

    return $grades;
}

function filter_sessions_by_student($sessions, $userId)
{
    return array_filter($sessions, function ($session) use ($userId) {
        return is_student_has_session($session, $userId);
    });
}

function has_student_signed_all_sessions(cm_info $cm, int $userId)
{
    global $DB;
    $sessions = $DB->get_records('edusign_sessions', ['activity_module_id' => $cm->id]);
    $sessions = add_edusign_sessions_infos($sessions);
    $sessions = filter_sessions_by_student($sessions, $userId);

    if (empty($sessions)) {
        return false;
    }

    $hasSigned = true;
    try {
        $userEdusignApi = getUserWithEdusignApiId('student', $userId);
        $studentEdusignApiId = $userEdusignApi->edusign_api_id;

        foreach ($sessions as $session) {
            if ($session->archived === '1') {
                continue;
            }
            $edusignCourse = EdusignApi::getCourseById($session->edusign_api_id);
            foreach ($edusignCourse->STUDENTS as $student) {
                if ($student->studentId === $studentEdusignApiId && !$student->signature) {
                    $hasSigned &= false;
                }
            }
        }
    } catch (\Exception $e) {
        mtrace('Error in has_student_signed_all_sessions: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    }

    return $hasSigned;
}

function has_student_signed_x_sessions(cm_info $cm, int $userId, int $nbSessions)
{
    global $DB;
    $sessions = $DB->get_records('edusign_sessions', ['activity_module_id' => $cm->id]);
    $sessions = add_edusign_sessions_infos($sessions);
    $sessions = filter_sessions_by_student($sessions, $userId);

    if (empty($sessions)) {
        return false;
    }

    $nbSessionsSigned = 0;
    try {
        $userEdusignApi = getUserWithEdusignApiId('student', $userId);
        $studentEdusignApiId = $userEdusignApi->edusign_api_id;

        foreach ($sessions as $session) {
            if ($session->archived === '1') {
                continue;
            }
            $edusignCourse = EdusignApi::getCourseById($session->edusign_api_id);
            foreach ($edusignCourse->STUDENTS as $student) {
                if ($student->studentId === $studentEdusignApiId && $student->signature) {
                    $nbSessionsSigned++;
                }
            }
        }
    } catch (\Exception $e) {
        mtrace('Error in has_student_signed_x_sessions: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    }

    return $nbSessionsSigned >= $nbSessions;
}

function get_cm_by_edusign_course_id($edusignCourseId)
{
    global $DB;
    $courseEdusign = $DB->get_record('edusign_sessions', ['edusign_api_id' => $edusignCourseId]);
    return get_coursemodule_from_id('edusign', $courseEdusign->activity_module_id);
}
