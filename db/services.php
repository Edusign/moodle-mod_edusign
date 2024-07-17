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
 * Web service mod plugin attendance external functions and service definitions.
 *
 * @package    mod_edusign
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'mod_edusign_test_api' => array(
        'classname'    => 'mod_edusign_external',
        'methodname'   => 'test_api',
        'classpath'    => 'mod/edusign/externallib.php',
        'description'  => 'Test an api call',
        'type'         => 'read',
        'ajax'         => true,
        'loginrequired'=> true,
    ),
    'mod_edusign_take_attendance' => array(
        'classname'    => 'mod_edusign_external',
        'methodname'   => 'take_attendance',
        'classpath'    => 'mod/edusign/externallib.php',
        'description'  => 'Actions for taking attendance',
        'type'         => 'update',
        'ajax'         => true,
        'loginrequired'=> true,
    ),
    'mod_edusign_get_students_and_teachers' => array(
        'classname'    => 'mod_edusign_external',
        'methodname'   => 'get_students_and_teachers',
        'classpath'    => 'mod/edusign/externallib.php',
        'description'  => 'Get students and teachers of activity module',
        'type'         => 'read',
        'ajax'         => true,
        'loginrequired'=> true,
    ),
    'mod_edusign_get_signature_link_from_course' => array(
        'classname'    => 'mod_edusign_external',
        'methodname'   => 'get_signature_link_from_course',
        'classpath'    => 'mod/edusign/externallib.php',
        'description'  => 'Get the iframe link to sign a course',
        'type'         => 'read',
        'ajax'         => true,
        'loginrequired'=> true,
    ),
    'mod_edusign_remove_session' => array(
        'classname'    => 'mod_edusign_external',
        'methodname'   => 'remove_session',
        'classpath'    => 'mod/edusign/externallib.php',
        'description'  => 'Delete a session',
        'type'         => 'delete',
        'ajax'         => true,
        'loginrequired'=> true,
    ),
    'mod_edusign_archive_session' => array(
        'classname'    => 'mod_edusign_external',
        'methodname'   => 'archive_session',
        'classpath'    => 'mod/edusign/externallib.php',
        'description'  => 'Archive a session',
        'type'         => 'update',
        'ajax'         => true,
        'loginrequired'=> true,
    ),
    'mod_edusign_parse_csv' => array(
        'classname'    => 'mod_edusign_external',
        'methodname'   => 'parse_csv',
        'classpath'    => 'mod/edusign/externallib.php',
        'description'  => 'Parse a CSV',
        'type'         => 'read',
        'ajax'         => true,
        'loginrequired'=> true,
    ),
    'mod_edusign_import_sessions' => array(
        'classname'    => 'mod_edusign_external',
        'methodname'   => 'import_sessions',
        'classpath'    => 'mod/edusign/externallib.php',
        'description'  => 'Import multiple sessions',
        'type'         => 'write',
        'ajax'         => true,
        'loginrequired'=> true,
    ),
);


// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
    'Edusign' => array(
        'functions' => array(
            'mod_edusign_test_api',
            'mod_edusign_take_attendance',
            'mod_edusign_get_students_and_teachers',
            'mod_edusign_get_signature_link_from_course',
            'mod_edusign_remove_session',
            'mod_edusign_archive_session',
        ),
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'mod_edusign'
    )
);
