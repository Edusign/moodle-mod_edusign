<?php

/**
 * @package     mod_edusign
 * @author      Sébastien Lampazona
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @docs        https://ext.edusign.fr/doc/
 */

namespace mod_edusign\classes\commons;

use stdClass;

require_once(__DIR__.'/ApiCaller.php');

class EdusignApi extends ApiCaller{
    
    private static function prepareBaseEvent(array $baseEvent = [], string $method, string $url, \Exception | stdClass $result = null): array{
        $additionnalConfig = [
            'other' => [
                'url' => $url,
                'method' => $method,
            ],
        ];
        
        if ($result instanceof \Exception) {
            $additionnalConfig['other']['error'] = $result->getMessage();
        }
        else {
            $additionnalConfig['other']['result'] = $result;
        }
        return array_merge($additionnalConfig, $baseEvent);
    }
    
    private static function tryRequestWithEvent($method, $url, $queryParams = [], $bodyParams = [], array $baseEvent = []): stdClass {
        $result = null;
        try {
            $result = self::call($method, $url, $queryParams, $bodyParams);
        }
        catch(\Exception $e){
            $result = $e;
        }
        finally {
            if (isset($baseEvent['objectid']) && isset($baseEvent['context'])){
                if (!function_exists('api_call_made::create')) { // Workaround for buggy PHP versions.
                    require_once(__DIR__.'/../event/api_call_made.php');
                }
                $event = \mod_edusign\event\api_call_made::create(self::prepareBaseEvent($baseEvent, $method, $url, $result));
                $event->trigger();
            }
        }
        if ($result instanceof \Exception) {
            throw $result;
        }
        return $result;
    }
    
    public static function getTraining(string $trainingId, array $baseEvent = []) : stdClass | null {
        $cr = self::tryRequestWithEvent('GET', '/v1/trainings/' . $trainingId, [], [], $baseEvent);
        return !empty($cr->result) ? $cr->result : null;
    }
    
    public static function createTraining(array $trainingData, array $baseEvent = []) : string | null {
        $cr = self::tryRequestWithEvent('POST', '/v1/trainings', [], $trainingData, $baseEvent);
        return !empty($cr->result) ? $cr->result : null;
    }
    
    public static function updateTraining(array $trainingData, array $baseEvent = []) : string | null {
        $cr = self::tryRequestWithEvent('PUT', '/v1/trainings', [], $trainingData, $baseEvent);
        return !empty($cr->result) ? $cr->result : null;
    }    
    
    public static function createCourse(array $courseData, $fromStudents = true, array $baseEvent = []) : string | null {
        $cr = self::tryRequestWithEvent('POST', '/v1/course', ['fromStudents' => $fromStudents], ['course' => $courseData], $baseEvent);
        return !empty($cr->result->ID) ? $cr->result->ID : null;
    }
    
    
    public static function updateCourse(string $courseId, array $courseData, array $baseEvent = []) : string | null {
        $courseData['ID'] = $courseId;
        $cr = self::tryRequestWithEvent('PATCH', '/v1/course', ['fromStudents' => true], ['course' => $courseData], $baseEvent);
        return !empty($cr->result->ID) ? $cr->result->ID : null;
    }

    public static function createStudent($studentInfos, array $baseEvent = []) : string | null {
        $cr = self::tryRequestWithEvent('POST', '/v1/student', [], ["student" => $studentInfos], $baseEvent);
        return !empty($cr->result->id) ? $cr->result->ID : null;
    }
    
    public static function getStudentByEmail($email, array $baseEvent = []) : stdClass | null {
        $cr = self::tryRequestWithEvent('GET', '/v1/student/by-email/' . urlencode($email), [], [], $baseEvent);
        return !empty($cr->result) ? $cr->result : null;
    }
    
    public static function getStudentById(string $studentId, array $baseEvent = []) : stdClass | null {
        $cr = self::tryRequestWithEvent('GET', '/v1/student/' . $studentId, [], [], $baseEvent);
        return !empty($cr->result) ? $cr->result : null;
    }
    
    public static function createProfessor($professorInfos, array $baseEvent = []) : string | null {
        $cr = self::tryRequestWithEvent('POST', '/v1/professor', [], ["professor" => $professorInfos], $baseEvent);
        return !empty($cr->result->id) ? $cr->result->ID : null;
    }
    
    public static function getProfessorByEmail($email, array $baseEvent = []) : stdClass | null {
        $cr = self::tryRequestWithEvent('GET', '/v1/professor/by-email/' . urlencode($email), [], [], $baseEvent);
        return !empty($cr->result) ? $cr->result : null;
    }
    
    public static function getProfessorById(string $professorId, array $baseEvent = []) : stdClass | null {
        $cr = self::tryRequestWithEvent('GET', '/v1/professor/' . $professorId, [], [], $baseEvent);
        return !empty($cr->result) ? $cr->result : null;
    }
    
    public static function getCourseById(string $courseId, array $baseEvent = []) : stdClass | null {
        $cr = self::tryRequestWithEvent('GET', '/v1/course/' . $courseId, [], [], $baseEvent);
        return !empty($cr->result) ? $cr->result : null;
    }
    
    public static function sendSignEmails(string $courseId, array $students, array $baseEvent = []) : bool {
        $cr = self::tryRequestWithEvent('POST', '/v1/course/send-sign-emails', [], [
            'course' => $courseId,
            'students' => $students,
        ], $baseEvent);
        
        return $cr->status === "success";
    }

    public static function setStudentAbsent(string $courseId, string $studentId, $comment = '', array $baseEvent = []) : bool {
        $cr = self::tryRequestWithEvent('PATCH', '/v1/presential-states/set-absent/'.$courseId.'/'.$studentId, [], [
            'comment' => $comment,
        ], $baseEvent);
        return $cr->status === "success";
    }
    
    public static function setStudentDelay(string $courseId, string $studentId, int $delay, array $baseEvent = []) : bool {
        $cr = self::tryRequestWithEvent('PATCH', '/v1/presential-states/set-delay/'.$courseId.'/'.$studentId, [], [
            'delay' => $delay,
        ], $baseEvent);
        return $cr->status === "success";
    }

    public static function setStudentEarlyDeparture(string $courseId, string $studentId, string $earlyDate, array $baseEvent = []) : bool {
        $cr = self::tryRequestWithEvent('PATCH', '/v1/presential-states/set-early-departure/'.$courseId.'/'.$studentId, [], [
            'earlyDate' => $earlyDate,
        ], $baseEvent);
        return $cr->status === "success";
    }

    public static function getStudentSignatureLinks(string $courseId, array $studentsId, array $baseEvent = []) : array {
        $cr = self::tryRequestWithEvent('GET', '/v1/course/get-signature-links/'.$courseId.'?studentids=' . implode(',', $studentsId), [], [], $baseEvent);
        if(!empty($cr->result)){
            
            return array_filter($cr->result, function($entry) use ($studentsId) {
                return in_array($entry->ID, $studentsId);
            });
        }
        return [];
    }
    
    public static function getProfessorSignatureLinks(string $courseId, array $professorsId, array $baseEvent = []) : array {
        $cr = self::tryRequestWithEvent('GET', '/v1/course/get-professors-signature-links/'.$courseId, [], [], $baseEvent);
        if(!empty($cr->result)){
            return array_filter($cr->result, function($entry) use ($professorsId) {
                return in_array($entry->ID, $professorsId);
            });
        }
        return [];
    }
    
    public static function deleteCourse(string $courseId, array $baseEvent = []) : bool {
        $cr = self::tryRequestWithEvent('DELETE', '/v1/course/'.$courseId, [], [], $baseEvent);
        return $cr->status === "success";
    }
    
    public static function addTrainingResources(string $trainingId, array $resources, array $baseEvent = []) : bool {
        $cr = self::tryRequestWithEvent('POST', '/v1/trainings/resources/'.$trainingId, [], $resources, $baseEvent);
        return $cr->status === "success";
    }
    
    public static function lockCourse(string $courseId, array $baseEvent = []) : bool {
        $cr = self::tryRequestWithEvent('GET', '/v1/course/lock/'.$courseId, [], [], $baseEvent);
        return $cr->status === "success";
    }
    
    public static function unlockCourse(string $courseId, array $baseEvent = []) : bool {
        $cr = self::tryRequestWithEvent('PATCH', '/v1/course/unlock/'.$courseId, [], [], $baseEvent);
        return $cr->status === "success";
    }
}