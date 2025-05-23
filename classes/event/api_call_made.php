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
 * This file contains an event for when a edusign report is viewed.
 *
 * @package    mod_edusign
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_edusign\event;

/**
 * Event for when a edusign report is viewed.
 *
 * @property-read array $other {
 *      Extra information about event properties.
 *
 *      string mode Mode of the report viewed.
 * }
 * @package    mod_edusign
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api_call_made extends \core\event\base {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'edusign';
    }

    /**
     * Returns non-localised description of what happened.
     *
     * @return string
     */
    public function get_description() {
        if ($this->data['other']['result']) {
            return 'User with id ' . $this->userid . ' made an api call to ' . $this->data['other']['url'] . ' with instance id : ' . $this->objectid . ' and the result is ' . $this->data['other']['result'];
        }
        else if ($this->data['other']['error']) {
            return 'User with id ' . $this->userid . ' made an api call to ' . $this->data['other']['url'] . ' with instance id : ' . $this->objectid . ' and the error is ' . $this->data['other']['error'];
        }
        return 'User with id ' . $this->userid . ' made an api call to ' . $this->data['other']['url'] . ' with instance id : ' . $this->objectid;
    }

    public static function get_objectid_mapping() {
        return [
            'db' => 'edusign',
            'restore' => 'edusign'
        ];
    }
    
    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return 'Edusign Api Call';
        return get_string('apicallmade', 'mod_edusign');
    }


}
