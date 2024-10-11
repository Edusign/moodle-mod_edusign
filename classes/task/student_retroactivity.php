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

        $edusignCourseApiIds = [];
        foreach ($edusignModules as $edusignModule) {
            // Getting all future sessions of this course
            $edusignCourseApiIds = array_merge(
                $edusignCourseApiIds,
                array_keys($DB->get_records_sql('SELECT s.edusign_api_id FROM {edusign_sessions} s WHERE activity_module_id = ? AND date_start > NOW()', [$edusignModule->coursemodule])),
            );
        }

        // Sync students and prof to courses
        foreach ($edusignCourseApiIds as $edusignCourseApiId) {
            if ($data->operation_type === 'ADD_PROFESSOR' || $data->operation_type === 'REMOVE_PROFESSOR') {
                //TODO: Add or remove professor
                // Get all professors of this course
                // And update the course on edusign
                // EdusignApi::updateCourse($edusignCourseApiId, [

                // ], [
                //     'objectid' => $course->id,
                //     'context' => context_course::instance($course->id),
                // ]);
            } else if ($data->operation_type === 'ADD_STUDENT' || $data->operation_type === 'REMOVE_STUDENT') {
                $userApiId = $DB->get_record('users_edusign_api', ['user_id' => $data->user_id, 'role' => 'student'], 'edusign_api_id', IGNORE_MISSING);

                if ($userApiId) {
                    if ($data->operation_type === 'ADD_STUDENT') {
                        EdusignApi::addStudentToCourse($edusignCourseApiId, $data->user_id);
                    } else if ($data->operation_type === 'REMOVE_STUDENT') {
                        EdusignApi::deleteStudentFromCourse($edusignCourseApiId, $data->user_id);
                    }
                }
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
