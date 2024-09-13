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
 * @package    mod_edusign
 * @subpackage backup-moodle2
 * @copyright  2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_edusign_activity_task
 */

/**
 * Define the complete edusign structure for backup, with file and id annotations
 */
class backup_edusign_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {
        // Define each element separated
        $edusign = new backup_nested_element('edusign', array('id'), array(
            'type', 'name', 'intro', 'introformat', 'duedate', 'cutoffdate',
            'assessed', 'assesstimestart', 'assesstimefinish', 'scale',
            'maxbytes', 'maxattachments', 'forcesubscribe', 'trackingtype',
            'rsstype', 'rssarticles', 'timemodified', 'warnafter',
            'blockafter', 'blockperiod', 'displaywordcount', 'lockdiscussionafter'));

        // Return the root element (edusign), wrapped into standard activity structure
        return $this->prepare_activity_structure($edusign);
    }

}
