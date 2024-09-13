<?php
/**
 * Strings for component 'mod_edusign', language 'en'
 *
 * @package   mod_edusign
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['modulename'] = 'Edusign';
$string['pluginadministration'] = 'Edusign administration';
$string['modulename_help'] = 'The edusign activity module enables a teacher to take attendance during class and students to view their own attendance record.';
$string['loading'] = 'Loading ...';

$string['settings'] = 'Settings';
$string['save'] = 'Enregistrer';
$string['settings_section_config'] = 'Plugin configuration';
$string['refresh_token'] = 'Generate a new token';
$string['modhealth'] = 'Plugin Health';
$string['webhooks_settings'] = 'Webhooks settings';
$string['webhook_token'] = 'Security Webhooks Token';
$string['webhook_url'] = 'Webhook URL to add to your edusign school platform';
$string['webhook_token_placeholder'] = '32747e3ee3f0b75b3638ec53305cdc77';

$string['webhook_student_has_signed_help'] = 'If your students sign by email, Moodle may not update the completion status of the activity. To do this, you can use this URL on the webhook <strong>[on_student_sign]</strong> from your Edusign interface.<br /><a target="_blank" href="https://developers.edusign.com/docs/webhooks-2">See the documentation for more information.</a>';
$string['apiurl_text'] = 'API URL';
$string['apiurl_text_help'] = 'API URL to contact edusign services';
$string['apikey_text'] = 'API Key';
$string['apikey_text_help'] = 'API Key for synchronizing users and courses with Edusign';
$string['completion_all_attendance'] = 'Sign all attendance sheets for the activity';
$string['completeonallattendancesigned:submit'] = 'Sign all sheets';
$string['completeonallattendancesigned'] = 'Student must sign all attendance sheets for the activity';
$string['completion_all_attendance_help'] = 'When this option is enabled, the activity is automatically marked as completed for all students who have been marked as present in all sessions of the activity';

$string['completion_of_X_attendance'] = 'Sign a provided number of attendance sheets for the activity';
$string['completeonxattendancesigned:submit'] = 'Sign {$a} sheet(s)';
$string['completeonxattendancesigned'] = 'Number of attendance sheets student must sign for the activity';
$string['completion_X_attendance_help'] = 'When this option is enabled, the activity is automatically marked as completed for all students who have been marked as present in the provided session\'s number of the activity';

$string['plugin_advanced'] = 'Advanced settings';
$string['test_api_error'] = 'An error occurred while connecting to the API : {$a}';
$string['test_api_success'] = 'API connection test successful';
$string['testapiconnection'] = 'Test API connection';

$string['attendance'] = 'Attendance';
$string['add_session'] = 'Add a session';
$string['date'] = 'Date';
$string['hourStart'] = 'Start time';
$string['hourEnd'] = 'End time';
$string['title'] = 'Title';
$string['action'] = 'Action';
$string['takeAttendance'] = 'Take attendance';
$string['editAttendance'] = 'Edit attendance';
$string['deleteAttendance'] = 'Delete attendance';
$string['removeSession'] = 'Remove session';
$string['removeSessionQuestions'] = 'You are about to delete a session. Are you sure you want to continue ?';
$string['removeSessionAndSheet'] = 'Also delete the attendance sheet on Edusign ( Warning, irreversible action )';
$string['archiveSessionQuestion'] = 'You are about to archive a session. Are you sure you want to continue?';
$string['unarchiveSessionQuestion'] = 'You are about to unarchive a session. Are you sure you want to continue?';

$string['addsession'] = 'Add a session';
$string['editSession'] = 'Edit a session';
$string['sessiondate'] = 'Session date';
$string['from'] = 'From';
$string['to'] = 'to';
$string['time'] = 'Time';
$string['savechanges'] = 'Save changes';
$string['errorsessiondateinpast'] = 'Session date cannot be in the past';
$string['errorstartdatebeforeenddate'] = 'Start date cannot be after end date';
$string['forcesync'] = 'Force synchronization with Edusign (may take some seconds)';
$string['processcompletion'] = 'Re process students completion status (may take long times following the number of students)';

$string['session'] = 'Session';
$string['status'] = 'Status';
$string['studentName'] = 'Name';
$string['refresh'] = 'refresh';
$string['refresh_help'] = 'Refresh the attendance sheet';
$string['changePresenceStatus'] = 'Presence';
$string['manualSignature'] = 'Manual signature';
$string['absence'] = 'Absence';
$string['delay'] = 'Delay';
$string['earlyDeparture'] = 'Early departure';
$string['sendSignatureMail'] = 'Send signature mail';
$string['sendSignatureMailSelected'] = 'Grouped signatures mailings';
$string['signSelected_help'] = 'To send emails to several people, please select users from the table';
$string['teacherSignature'] = 'Teacher’s signature';
$string['present'] = 'Present';
$string['noData'] = 'No data';
$string['waitingSignature'] = 'Waiting for signature';
$string['minLate'] = 'min late';
$string['departureAt'] = 'Early departure at';
$string['archive'] = 'Archive';
$string['archiveSession_help'] = 'Archive the session';
$string['archiveSession'] = 'Archive the session';
$string['unarchiveSession'] = 'Disarchive the session';
$string['sessionArchivedCannotTake'] = 'This session is archived, you cannot take attendance.';
$string['showOnEdusignWebsite'] = 'Show on edusign app';
$string['noArchivedSession'] = 'No archived session';
$string['noUnarchivedSession'] = 'No current session ';

$string['send_sign_email_error'] = 'An error occurred while send sign email to student : {$a}';
$string['send_sign_email_success'] = 'Sign email successfully sent to student';
$string['set_student_absent_error'] = 'An error occurred while setting student absent : {$a}';
$string['set_student_absent_success'] = 'Student successfully set as absent';
$string['set_student_delay_error'] = 'An error occurred while setting student delay : {$a}';
$string['set_student_delay_success'] = 'Student successfully set as late';
$string['set_student_early_departure_success'] = 'Student successfully set as leaving early';
$string['set_student_early_departure_error'] = 'An error occurred while setting student early departure : {$a}';
$string['refresh_error'] = 'An error occurred while refreshing the attendance sheet : {$a}';
$string['archive_session_success'] = 'The session has been successfully archived';
$string['archive_session_error'] = 'An error occurred during archiving';
$string['archive_session_sync_error'] = 'An error occurred during synchronization with Edusign : {$a}';

$string['should_import_sessions_with_csv'] = 'You can import a list of sessions to create by importing a CSV file.';
$string['download_csv_model'] = 'Download the CSV file template';
$string['session_name'] = 'Session name';
$string['session_start_date'] = 'Start date';
$string['session_end_date'] = 'End date';
$string['create_sessions'] = 'Create these sessions';
$string['choose_file'] = 'Choose a file';
$string['csv_import_error'] = 'An error occurred during the processing of the CSV file, does it have the correct format?';
$string['csv_no_data_found_error'] = 'No data found in the CSV file';
$string['sessions_have_errors'] = 'Some sessions have errors, please check the form';
$string['import_sessions_success'] = 'Sessions imported successfully';
$string['import_sessions'] = 'Import sessions via CSV';