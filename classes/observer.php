<?php

use core\event\course_created as CourseCreated;
use core\event\role_assigned as RoleAssigned;
use core\event\role_unassigned as RoleUnassigned;
use core\event\user_deleted as UserDeleted;
use mod_edusign\classes\commons\EdusignApi;

require_once(__DIR__ . '/../locallib.php');
require_once(__DIR__ . '/commons/EdusignApi.php');

enum OperationType
{
    case ADD_STUDENT;
    case ADD_PROFESSOR;
    case REMOVE_STUDENT;
    case REMOVE_PROFESSOR;
}

/**
 * Email signup notification event observers.
 *
 * @package    mod_edusign_observer
 * @author     Sébastien Lampazona
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_edusign_observer
{
    /**
     * Event processor - course created
     *
     * @param CourseCreated $event
     * @return bool
     */
    public static function course_created(CourseCreated $event)
    {
        global $DB, $CFG;
        // $eventData = $event->get_data();
        // $course = $DB->get_record('course', ['id' => $eventData['objectid']]);
        return true;
    }

    public static function course_updated($event)
    {
        global $DB, $CFG;
        $eventData = $event->get_data();
        $course = get_course($eventData['objectid']);
        $activities = course_modinfo::get_array_of_activities($course);

        foreach ($activities as $activity) {
            if ($activity->mod === 'edusign') {
                $edusignCourseId = $DB->get_record('course_edusign_api', ['course_id' => $course->id]);
                if (!$edusignCourseId->edusign_api_id) {
                    createTrainingFromCourse(
                        $course->id,
                        $activity->start_date,
                        $activity->end_date,
                        $event->get_context(),
                        [
                            'objectid' => $eventData['objectId'],
                            'context' => $event->get_context(),
                        ]
                    );
                }
            }
        }
        return true;
    }


    // S'abonner à l'événement d'assignation d'utilisateur
    public static function role_assigned(RoleAssigned $event)
    {
        global $DB;
        $context = $event->get_context();
        $role = $DB->get_record('role', ['id' => $event->get_data()['objectid']]);
        $user = getUserWithEdusignApiId($role->shortname, $event->get_data()['relateduserid']);
        $ressourcesToAdd = [];
        if ($user->role === 'teacher') {
            $users = syncTeachersToApi([$user], $context, true);
            if (!empty($users)) {
                $user = $users[0];
            }
            if ($user->edusign_api_id !== null) {
                $ressourcesToAdd = ['professorsIds' => [$user->edusign_api_id]];
            }
            self::triggerStudentRetroActivity($event, 'ADD_PROFESSOR');
        } else if ($user->role === 'student') {
            $users = syncStudentsToApi([$user], $context, true);
            if (!empty($users)) {
                $user = $users[0];
            }
            if ($user->edusign_api_id !== null) {
                $ressourcesToAdd = ['studentsIds' => [$user->edusign_api_id]];
            }
            self::triggerStudentRetroActivity($event, 'ADD_STUDENT');
        }
        // Add student to training
        if (!empty($ressourcesToAdd) && $context->contextlevel === CONTEXT_COURSE) {
            try {
                $edusignCourseId = $DB->get_record('course_edusign_api', ['course_id' => $context->instanceid]);
                if ($edusignCourseId->edusign_api_id !== null) {
                    EdusignApi::addTrainingResources($edusignCourseId->edusign_api_id, $ressourcesToAdd);
                }
            } catch (\Exception $e) {
                // Log error
                error_log($e->getMessage());
            }
        }

        return true;
    }

    // S'abonner à l'événement de désassignation d'utilisateur à un cours
    public static function role_unassigned(RoleUnassigned $event)
    {
        global $DB;

        mtrace('observer::role_unassigned - Event: ' . print_r($event, true));

        $role = $DB->get_record('role', ['id' => $event->get_data()['objectid']]);

        mtrace('observer::role_unassigned - Role: ' . print_r($role, true));

        $user = getUserWithEdusignApiId($role->shortname, $event->get_data()['relateduserid']);

        mtrace('observer::role_unassigned - User: ' . print_r($user, true));

        if ($user->role === 'teacher') {
            self::triggerStudentRetroActivity($event, 'REMOVE_PROFESSOR');
        } else if ($user->role === 'student') {
            self::triggerStudentRetroActivity($event, 'REMOVE_STUDENT');
        }
        return true;
    }
    
    // S'abonner à l'événement de désassignation d'utilisateur à un cours
    public static function user_deleted(UserDeleted $event)
    {
        self::triggerStudentRetroActivity($event, 'REMOVE_STUDENT');
        return true;
    }
    
    private static function triggerStudentRetroActivity($event, string $operationType)
    {
        global $DB;
        $context = $event->get_context();
        $role = $DB->get_record('role', ['id' => $event->get_data()['objectid']]);

        mtrace('triggerStudentRetroActivity - Operation type: ' . $operationType);
        mtrace('triggerStudentRetroActivity - Role: ' . print_r($role, true));
        mtrace('triggerStudentRetroActivity - Event object ID: ' . $event->get_data()['objectid']);
        mtrace('triggerStudentRetroActivity - Related user ID: ' . $event->get_data()['relateduserid']);

        $user = getUserWithEdusignApiId($role->shortname, $event->get_data()['relateduserid']);
        
        mtrace('triggerStudentRetroActivity - User: ' . print_r($user, true));

        $task = new \mod_edusign\task\student_retroactivity();
        $task = $task->instance($user->id, $context->instanceid, $operationType);
        return \core\task\manager::queue_adhoc_task($task);
    }
}
