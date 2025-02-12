<?php

namespace mod_edusign\task;

use mod_edusign\classes\commons\EdusignApi;

enum OperationType
{
    case ADD_STUDENT;
    case ADD_PROFESSOR;
    case REMOVE_STUDENT;
    case REMOVE_PROFESSOR;
}

class student_retroactivity extends \core\task\adhoc_task
{
    /**
     * Execute the task.
     */
    public function execute()
    {
        global $DB;
        $data = $this->get_custom_data();

        $course = get_course($data->course_id);

        $edusignModules = get_all_instances_in_course('edusign', $course);

        $edusignTrainingId = $DB->get_field('course_edusign_api', 'edusign_api_id', ['course_id' => $data->course_id]);
        $userRole = ($data->operation_type === 'ADD_PROFESSOR' || $data->operation_type === 'REMOVE_PROFESSOR') ? 'teacher' : 'student';

        foreach ($edusignModules as $edusignModule) {
            $context = \context_module::instance($edusignModule->coursemodule);
            $baseEvent = [
                'objectid' => $course->id,
                'context' => $context,
            ];
            // Getting all future sessions of this course
            $edusignCourseApiIds = array_keys($DB->get_records_sql('SELECT s.edusign_api_id FROM {edusign_sessions} s WHERE activity_module_id = ? AND date_start > NOW()', [$edusignModule->coursemodule]));

            $userBDD = $DB->get_record('users_edusign_api', ['user_id' => $data->user_id, 'role' => $userRole], 'edusign_api_id', IGNORE_MISSING);
            $userApiId = $userBDD->edusign_api_id;
            
            // Sync students and prof to courses
            foreach ($edusignCourseApiIds as $edusignCourseApiId) {
                if ($userRole === 'teacher') {
                    $teachers = getTeachersFromContext($context);
                    $students = getStudentsFromContext($context);
                    
                    $teacherEdusignApiIDs = array_values(array_map(function ($teacher) {
                        return $teacher->edusign_api_id;
                    }, $teachers));
                    $studentEdusignApiIDs = array_values(array_map(function ($student) {
                        return $student->edusign_api_id;
                    }, $students));
                    $requestBody = [
                        'STUDENTS' => array_map(function ($studentEdusignApiID) {
                            return ['studentId' => $studentEdusignApiID];
                        }, $studentEdusignApiIDs)
                    ];
                    $i = 0;
                    foreach ($teacherEdusignApiIDs as $teacherEdusignApiId) {
                        $i++;
                        $teacherKey = 'PROFESSOR';
                        if ($i > 1) {
                            $teacherKey .= "_$i";
                        }
                        $requestBody[$teacherKey] = $teacherEdusignApiId;
                    }
                    // With update course, we must send all students and all teachers at each call
                    EdusignApi::updateCourse($edusignCourseApiId, $requestBody, $baseEvent);
                } else if ($userRole === "student") {
                    if (!$userBDD) {
                        continue;
                    }
                    if ($userApiId) {
                        if ($data->operation_type === 'ADD_STUDENT') {
                            EdusignApi::addStudentToCourse($edusignCourseApiId, $userApiId, $baseEvent);
                        } else if ($data->operation_type === 'REMOVE_STUDENT') {
                            EdusignApi::deleteStudentFromCourse($edusignCourseApiId, $userApiId, $baseEvent);
                        }
                    }
                }
            }
            
            // Sync students and prof to training
            $userApiId = $userBDD->edusign_api_id;
            $trainingResourceKey = ($userRole === 'teacher') ? 'professorsIds' : 'studentsIds';
            $resourceData = [$trainingResourceKey => [$userApiId]];
            try {
                if ($userApiId && $edusignTrainingId) {
                    if ($data->operation_type === 'ADD_STUDENT' || $data->operation_type === 'ADD_PROFESSOR') {
                        EdusignApi::addTrainingResources($edusignTrainingId, [$trainingResourceKey => [$userApiId]], $baseEvent);
                    } else if ($data->operation_type === 'REMOVE_STUDENT' || $data->operation_type === 'REMOVE_PROFESSOR') {
                        EdusignApi::removeTrainingResources($edusignTrainingId, [$trainingResourceKey => [$userApiId]], $baseEvent);
                    }
                }
            }
            catch(\Exception $e){
                print_r($resourceData);
                print_r($e);
                throw $e;
            }
        
        }
        return true;
    }

    public static function instance(
        int $userId,
        int $courseId,
        string $operationType
    ): self {
        $task = new self();
        $task->set_custom_data((object) [
            'user_id' => $userId,
            'course_id' => $courseId,
            'operation_type' => $operationType,
        ]);

        $task->set_component('mod_edusign');

        return $task;
    }
}
