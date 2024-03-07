<?php

use core\event\course_created as CourseCreated;
use core\event\role_assigned as RoleAssigned;
use mod_edusign\classes\commons\EdusignApi;

require_once(__DIR__ . '/../locallib.php');
require_once(__DIR__ . '/commons/EdusignApi.php');
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

        $hasEdusignActivity = false;
        foreach($activities as $activity) {
            if ($activity->mod === 'edusign') {
                $hasEdusignActivity = true;
                break;
            }
        }

        if ($hasEdusignActivity) {
            $edusignCourseId = $DB->get_record('course_edusign_api', ['course_id' => $course->id]);
            if (!$edusignCourseId->edusign_api_id) {
                createTrainingFromCourse(
                    $course->id,
                    $event->get_context(),
                    [
                        'objectid' => $eventData['objectId'],
                        'context' => $event->get_context(),
                    ]
                );
            }
            else {
                updateTrainingFromCourse(
                    $course->id,
                    [
                        'objectid' => $eventData['objectId'],
                        'context' => $event->get_context(),
                    ]
                );
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
            $users = syncTeachersToApi([$user], $context);
            if (!empty($users)) {
                $user = $users[0];
            }
            if ($user->edusign_api_id !== null) {
                $ressourcesToAdd = ['professorsIds' => [$user->edusign_api_id]];
            }
        } else if ($user->role === 'student') {
            $users = syncStudentsToApi([$user], $context);
            if (!empty($users)) {
                $user = $users[0];
            }
            if ($user->edusign_api_id !== null) {
                $ressourcesToAdd = ['studentsIds' => [$user->edusign_api_id]];
            }
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
}
