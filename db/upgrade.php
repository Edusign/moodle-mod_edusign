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
 * upgrade processes for this module.
 *
 * @package   mod_edusign
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * upgrade this edusign instance - this function could be skipped but it will be needed later
 * @param int $oldversion The old version of the edusign module
 * @return bool
 */
function xmldb_edusign_upgrade($oldversion=0) {

    global $DB;
    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < 2024022913) {
        $table = new xmldb_table('edusign_sessions');

        $field = new xmldb_field('archived');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2024022913, 'edusign');
    }
    
    if ($oldversion < 2024111215) {
        $table = new xmldb_table('edusign');

        //Define if activity is complete when a student has signed all sessions
        $field = new xmldb_field('complete_mode', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        //Define if activity is complete when a student has signed this number of sessions, 0 is disabled
        $field = new xmldb_field('completeonxattendancesigned', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        upgrade_mod_savepoint(true, 2024111215, 'edusign');
    }

    if ($oldversion < 2024111218) {
        $table = new xmldb_table('edusign');

        //Define the start date activity ( required for creating training on edusign )
        $field = new xmldb_field('date_start', XMLDB_TYPE_DATETIME);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        //Define the end date activity ( required for creating training on edusign )
        $field = new xmldb_field('date_end', XMLDB_TYPE_DATETIME);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        $activities = $DB->get_records('edusign');
        foreach($activities as $activity){
            try {
                $course = get_course($activity->course);
            
                $data = new stdClass();
                $data->id = $activity->id;
                $data->date_start = date('Y-m-d H:i:s', $course->startdate);
                $data->date_end = date('Y-m-d H:i:s', $course->enddate ?: ($course->startdate + 86400));
                $DB->update_record('edusign', $data);
    
            } catch(Exception $exception) {
                debugging('Error updating edusign activities: ' . $exception->getMessage() . "\n" . $exception->getTraceAsString());
            }
        }
        
        upgrade_mod_savepoint(true, 2024111218, 'edusign');
    }

    return true;
}
