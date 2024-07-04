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
 * Adding edusign sessions
 *
 * @package    mod_edusign
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_edusign\classes\commons\EdusignApi;
use mod_edusign\classes\form\AddSessionForm;

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/classes/form/add_session.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once($CFG->dirroot . '/lib/formslib.php');

$cmId         = required_param('cmId', PARAM_INT);
$sessionId    = optional_param('sessionId', false, PARAM_INT);
$cm           = get_coursemodule_from_id('edusign', $cmId, 0, false, MUST_EXIST);
$course       = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$att          = $DB->get_record('edusign', array('id' => $cm->instance), '*', MUST_EXIST);
$courseEdusign = $DB->get_record('course_edusign_api', ['course_id' => $course->id], '*', MUST_EXIST);
// get session if exists
$session = $sessionId ? $DB->get_record('edusign_sessions', ['id' => $sessionId], '*', MUST_EXIST) : null;

$context = context_module::instance($cm->id);
require_capability('mod/edusign:manageattendances', $context);


$url = new moodle_url('/mod/edusign/sessions.php?cmId=' . $cm->id);

if ($session) {
    // Add session id to url
    $url->param('sessionId', $session->id);
}

$PAGE->set_cm($cm);
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title($course->shortname . ": " . $att->name);
$PAGE->set_heading($course->fullname);
$PAGE->force_settings_menu(true);
$PAGE->set_cacheable(true);

$mform = new AddSessionForm($url, ['id' => $cmId]);

if ($session) {
    $mform->set_data([
        'title' => $session->title,
        'sessiondate' => strtotime($session->date_start),
        'sestime' => [
            'starthour' => date('H', strtotime($session->date_start)),
            'startminute' => date('i', strtotime($session->date_start)),
            'endhour' => date('H', strtotime($session->date_end)),
            'endminute' => date('i', strtotime($session->date_end))
        ],
    ]);
}
if ($fromForm = $mform->get_data()) {
    $startDate = date('Y-m-d', $fromForm->sessiondate) . ' ' . str_pad($fromForm->sestime['starthour'], 2, "0", STR_PAD_LEFT) . ':' . str_pad($fromForm->sestime['startminute'], 2, "0", STR_PAD_LEFT) . ':00';
    $endDate = date('Y-m-d', $fromForm->sessiondate) . ' ' . str_pad($fromForm->sestime['endhour'], 2, "0", STR_PAD_LEFT) . ':' . str_pad($fromForm->sestime['endminute'], 2, "0", STR_PAD_LEFT) . ':00';
    if ($session) {
        try {
            $cr = update_session($session, [
                'title' => $fromForm->title,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ], !!$fromForm->forcesync);
            if ($cr) {
                \core\notification::success('Session successfully updated');
            }
        } catch (Exception $e) {
            \core\notification::error('An error occured while updating session : ' . $e->getMessage());
        }
    } else {
        try {
            $cr = create_session($context, $cm, [
                'title' => $fromForm->title,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ], !!$fromForm->forcesync);
            if ($cr) {
                \core\notification::success('Session successfully created');
                redirect(new moodle_url('/mod/edusign/manage.php', ['id' => $cm->id]));
            }
        } catch (Exception $e) {
            \core\notification::error('An error occured while creating session : ' . $e->getMessage());
        }
    }
}
$output = $PAGE->get_renderer('mod_edusign');
echo $output->header();
$mform->display();
echo $output->footer();
