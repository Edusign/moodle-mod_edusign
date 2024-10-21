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

declare(strict_types=1);

namespace mod_edusign\completion;

use cm_info;
use core_completion\activity_custom_completion;
use Exception;

/**
 * Activity custom completion subclass for the edusign activity.
 *
 * Class for defining mod_edusign's custom completion rules and fetching the completion statuses
 * of the custom completion rules for a given edusign instance and a user.
 *
 * @package mod_edusign
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_completion extends activity_custom_completion
{

    /**
     * Fetches the completion state for a given completion rule.
     *
     * @param string $rule The completion rule.
     * @return int The completion state.
     */
    public function get_state(string $rule): int
    {
        try {
            global $CFG;

            $this->validate_rule($rule);

            $userid = $this->userid;
            $cm = $this->cm;

            require_once($CFG->dirroot . '/mod/edusign/locallib.php');

            if ($rule === 'allsheets') {
                return has_student_signed_all_sessions($cm, $userid) ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
            } elseif ($rule === 'xsheets') {
                $completeonxattendancesigned = intval($cm->customdata['customcompletionrules']['xsheets'] ?: 0);
                return has_student_signed_x_sessions($cm, $userid, $completeonxattendancesigned) ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
            }
        } catch (Exception $e) {
            \core\notification::error('An error occured while getting session state : ' . $e->getMessage());
        }
        return COMPLETION_INCOMPLETE;
    }

    /**
     * Fetch the list of custom completion rules that this module defines.
     *
     * @return array
     */
    public static function get_defined_custom_rules(): array
    {
        return ['allsheets', 'xsheets'];
    }

    /**
     * Returns an associative array of the descriptions of custom completion rules.
     *
     * @return array
     */
    public function get_custom_rule_descriptions(): array
    {
        $completeonxattendancesigned = 0;
        if (isset($this->cm->customdata['customcompletionrules']) && isset($this->cm->customdata['customcompletionrules']['xsheets'])) {
            $completeonxattendancesigned = $this->cm->customdata['customcompletionrules']['xsheets'] ?: 0;
        }

        return [
            'allsheets' => get_string('completeonallattendancesigned:submit', 'edusign'),
            'xsheets' => get_string('completeonxattendancesigned:submit', 'edusign', $completeonxattendancesigned),
        ];
    }


    /**
     * Returns an array of all completion rules, in the order they should be displayed to users.
     *
     * @return array
     */
    public function get_sort_order(): array
    {
        return [
            'allsheets',
            'xsheets',
        ];
    }
}
