<?php

use mod_edusign\classes\commons\EdusignApi;

require_once(__DIR__ . '/classes/commons/EdusignApi.php');
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
}

function isTrainingExistsOnEdusign($trainingId, $baseEvent = [])
{
    try {
        $training = EdusignApi::getTraining($trainingId, $baseEvent);
        if ($training) {
            return true;
        }
    }
    catch(\Exception $e){
        return false;
    }
    return false;
}

function createTrainingFromCourse($courseId, $context, array $baseEvent = [])
{
    global $DB;

    $course = get_course($courseId);
    // $courseEdusignApi = $DB->get_record('course_edusign_api', array('course_id' => $courseId));
    // Try to create course on edusign api
    // if ($courseEdusignApi->edusign_api_id) {
    //     $course->edusign_api_id = $courseEdusignApi->edusign_api_id;
    //     return $course;
    // }
    $students = getStudentsFromContext($context);
    // $teachers = getTeachersFromContext($context);

    try {
        $trainingData = [
            'NAME' => $course->fullname,
            'START' =>  date('Y-m-d', $course->startdate),
            'END' =>  date('Y-m-d', $course->enddate),
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
        \core\notification::success('Course successfully linked on edusign API');
        $course->edusign_api_id = $edusignAPIID;
        return $course;
    } catch (Exception $e) {
        // Error is journalized
        \core\notification::error('Error while creating course on edusign API : ' . $e->getMessage());
    }
}

function updateTrainingFromCourse($courseId, array $baseEvent = [])
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
            'START' =>  date('Y-m-d', $course->startdate),
            'END' =>  date('Y-m-d', $course->enddate),
        ];

        EdusignApi::updateTraining($trainingData, $baseEvent);
        \core\notification::success('Course successfully updated on edusign API');
        return $course;
    } catch (Exception $e) {
        // Error is journalized
        \core\notification::error('Error while updating course on edusign API : ' . $e->getMessage());
    }
}

function getStudentsFromContext($context)
{
    global $DB;
    $studentRole = $DB->get_record_sql('SELECT id FROM {role} WHERE shortname = "student"');
    $students = get_role_users($studentRole->id, $context, '*');

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


function getUserWithEdusignApiId(string $role, int $userId)
{
    global $DB;
    $user = $DB->get_record('user', ['id' => $userId]);
    if ($role === "editingteacher") {
        $role = "teacher";
    }
    $userEdusignApi = $DB->get_record_sql(
        'SELECT user_id, edusign_api_id FROM {users_edusign_api} WHERE user_id = ' . $userId . ' AND role = "' . $role . '"'
    );
    $user->edusign_api_id = null;
    $user->role = $role;
    if (empty($userEdusignApi)) {
        return $user;
    }
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
                "SEND_EMAIL_CREDENTIALS" => true,
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
        
        if(!$studentToInsert){
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
                "dontSendCredentials" => false,
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
        
        if(!$teacherToInsert){
            $DB->insert_record('users_edusign_api', [
                'user_id' => $teacher->id,
                'role' => 'teacher',
                'edusign_api_id' => $teacherAPIID,
            ]);
        }
    }
    return $teachers;
}

function syncStudentsToApiFromContext($context, $withVerification = false)
{
    // Récupération des étudiants à synchroniser sur edusign
    $students = getStudentsFromContext($context);
    syncStudentsToApi($students, $context, $withVerification);
    return getStudentsFromContext($context);
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

function create_session($context, stdClass $cm, array $data, $forceSync = false)
{
    global $DB;
    // Synchronisation et récupération des étudiants liés au module d'activité
    $students = syncStudentsToApiFromContext($context, $forceSync);
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
    
    if (!isTrainingExistsOnEdusign($courseData['TRAINING_ID'])) {
        $updatedCourse = createTrainingFromCourse($cm->course, $context);
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
    ]);
    return $DB->get_record('edusign_sessions', ['id' => $cr]);
}

function update_session($session, array $data, )
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
    return $session;
}
