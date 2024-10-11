<?php

namespace mod_edusign\task;

/**
 * An example of an adhoc task.
 */
class student_retroactivity extends \core\task\adhoc_task
{
    /**
     * Execute the task.
     */
    public function execute()
    {
        $data = $this->get_custom_data();
        print_r($data);
        
        return true;
    }

    public static function instance(int $userId, int $courseId): self
    {
        $task = new self();
        $task->set_custom_data((object) [
            'user_id' => $userId,
            'course_id' => $courseId,
        ]);
        
        $task->set_component('mod_edusign');

        return $task;
    }
}
