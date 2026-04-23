<?php
/**
 * Cadenas para el componente 'mod_edusign', idioma 'es'
 *
 * @package   mod_edusign
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 o posterior
 */

$string['modulename'] = 'Edusign';
$string['pluginadministration'] = 'Administración de Edusign';
$string['modulename_help'] = 'El módulo de actividad Edusign permite al profesor pasar lista durante las clases y a los estudiantes consultar su propio registro de asistencia.';
$string['loading'] = 'Cargando...';

$string['settings'] = 'Configuración';
$string['save'] = 'Guardar';
$string['settings_section_config'] = 'Configuración del plugin';
$string['refresh_token'] = 'Generar un nuevo token';
$string['modhealth'] = 'Estado del plugin';
$string['webhooks_settings'] = 'Configuración de webhooks';
$string['webhook_token'] = 'Token de seguridad de los webhooks';
$string['webhook_url'] = 'URL del webhook a añadir en su plataforma escolar Edusign';
$string['webhook_token_placeholder'] = '32747e3ee3f0b75b3638ec53305cdc77';

$string['webhook_student_has_signed_help'] = 'Si sus estudiantes firman por correo electrónico, Moodle puede no actualizar el estado de finalización de la actividad. Para ello, puede utilizar esta URL en el webhook <strong>[on_student_sign]</strong> desde su interfaz Edusign.<br /><a target="_blank" href="https://developers.edusign.com/docs/webhooks-2">Consulte la documentación para más información.</a>';
$string['apiurl_text'] = 'URL de la API';
$string['apiurl_text_help'] = 'URL de la API para contactar con los servicios Edusign';
$string['apikey_text'] = 'Clave de la API';
$string['apikey_text_help'] = 'Clave de la API para sincronizar usuarios y cursos con Edusign';
$string['completion_all_attendance'] = 'Firmar todas las hojas de asistencia de la actividad';
$string['completeonallattendancesigned:submit'] = 'Firmar todas las hojas';
$string['completeonallattendancesigned'] = 'El estudiante debe firmar todas las hojas de asistencia de la actividad';
$string['completion_all_attendance_help'] = 'Cuando esta opción está activada, la actividad se marca automáticamente como completada para todos los estudiantes que han sido marcados como presentes en todas las sesiones de la actividad';

$string['completion_of_X_attendance'] = 'Firmar un número determinado de hojas de asistencia de la actividad';
$string['completeonxattendancesigned:submit'] = 'Firmar {$a} hoja(s)';
$string['completeonxattendancesigned'] = 'Número de hojas de asistencia que el estudiante debe firmar para la actividad';
$string['completion_X_attendance_help'] = 'Cuando esta opción está activada, la actividad se marca automáticamente como completada para todos los estudiantes que han sido marcados como presentes en el número de sesiones indicado de la actividad';

$string['plugin_advanced'] = 'Configuración avanzada';
$string['test_api_error'] = 'Se ha producido un error al conectar con la API: {$a}';
$string['test_api_success'] = 'Prueba de conexión a la API correcta';
$string['testapiconnection'] = 'Probar la conexión a la API';

$string['attendance'] = 'Asistencia';
$string['add_session'] = 'Añadir una sesión';
$string['date'] = 'Fecha';
$string['dateStart'] = 'Fecha de inicio';
$string['dateEnd'] = 'Fecha de fin';
$string['hourStart'] = 'Hora de inicio';
$string['hourEnd'] = 'Hora de fin';
$string['title'] = 'Título';
$string['action'] = 'Acción';
$string['takeAttendance'] = 'Pasar lista';
$string['editAttendance'] = 'Editar asistencia';
$string['deleteAttendance'] = 'Eliminar asistencia';
$string['removeSession'] = 'Eliminar la sesión';
$string['removeSessionQuestions'] = 'Está a punto de eliminar una sesión. ¿Está seguro de que desea continuar?';
$string['removeSessionAndSheet'] = 'Eliminar también la hoja de asistencia en Edusign (Atención, acción irreversible)';
$string['archiveSessionQuestion'] = 'Está a punto de archivar una sesión. ¿Está seguro de que desea continuar?';
$string['unarchiveSessionQuestion'] = 'Está a punto de desarchivar una sesión. ¿Está seguro de que desea continuar?';

$string['addsession'] = 'Añadir una sesión';
$string['editSession'] = 'Editar una sesión';
$string['sessiondate'] = 'Fecha de la sesión';
$string['from'] = 'De';
$string['to'] = 'a';
$string['time'] = 'Hora';
$string['savechanges'] = 'Guardar cambios';
$string['daterange'] = 'Rango de fechas';
$string['errordateinpast'] = 'La fecha no puede estar en el pasado';
$string['errorstartdatebeforeenddate'] = 'La fecha de inicio no puede ser posterior a la fecha de fin';
$string['forcesync'] = 'Forzar la sincronización con Edusign (puede tardar unos segundos)';
$string['processcompletion'] = 'Reprocesar el estado de finalización de los estudiantes (puede tardar mucho en función del número de estudiantes)';

$string['session'] = 'Sesión';
$string['status'] = 'Estado';
$string['studentName'] = 'Nombre';
$string['refresh'] = 'Actualizar';
$string['refresh_help'] = 'Actualizar la hoja de asistencia';
$string['changePresenceStatus'] = 'Presencia';
$string['manualSignature'] = 'Firma manual';
$string['absence'] = 'Ausencia';
$string['delay'] = 'Retraso';
$string['earlyDeparture'] = 'Salida anticipada';
$string['sendSignatureMail'] = 'Enviar un correo de firma';
$string['sendSignatureMailSelected'] = 'Envío agrupado de correos de firma';
$string['signSelected_help'] = 'Para enviar correos a varias personas, seleccione los usuarios de la tabla';
$string['teacherSignature'] = 'Firma del profesor';
$string['present'] = 'Presente';
$string['noData'] = 'Sin datos';
$string['waitingSignature'] = 'En espera de firma';
$string['minLate'] = 'min de retraso';
$string['departureAt'] = 'Salida anticipada a las';
$string['archive'] = 'Archivar';
$string['archiveSession_help'] = 'Archivar la sesión';
$string['archiveSession'] = 'Archivar la sesión';
$string['unarchiveSession'] = 'Desarchivar la sesión';
$string['sessionArchivedCannotTake'] = 'Esta sesión está archivada, no puede pasar lista.';
$string['showOnEdusignWebsite'] = 'Mostrar en la aplicación Edusign';
$string['noArchivedSession'] = 'Ninguna sesión archivada';
$string['noUnarchivedSession'] = 'Ninguna sesión en curso';

$string['send_sign_email_error'] = 'Se ha producido un error al enviar el correo de firma al estudiante: {$a}';
$string['send_sign_email_success'] = 'Correo de firma enviado con éxito al estudiante';
$string['set_student_absent_error'] = 'Se ha producido un error al marcar al estudiante como ausente: {$a}';
$string['set_student_absent_success'] = 'Estudiante marcado como ausente con éxito';
$string['set_student_delay_error'] = 'Se ha producido un error al marcar al estudiante como retrasado: {$a}';
$string['set_student_delay_success'] = 'Estudiante marcado como retrasado con éxito';
$string['set_student_early_departure_success'] = 'Estudiante marcado como salida anticipada con éxito';
$string['set_student_early_departure_error'] = 'Se ha producido un error al marcar la salida anticipada del estudiante: {$a}';
$string['refresh_error'] = 'Se ha producido un error al actualizar la hoja de asistencia: {$a}';
$string['archive_session_success'] = 'La sesión ha sido archivada con éxito';
$string['archive_session_error'] = 'Se ha producido un error durante el archivado';
$string['archive_session_sync_error'] = 'Se ha producido un error durante la sincronización con Edusign: {$a}';

$string['should_import_sessions_with_csv'] = 'Puede importar una lista de sesiones a crear importando un archivo CSV.';
$string['download_csv_model'] = 'Descargar la plantilla de archivo CSV';
$string['session_name'] = 'Nombre de la sesión';
$string['session_start_date'] = 'Fecha de inicio';
$string['session_end_date'] = 'Fecha de fin';
$string['create_sessions'] = 'Crear estas sesiones';
$string['choose_file'] = 'Elegir un archivo';
$string['csv_import_error'] = 'Se ha producido un error durante el procesamiento del archivo CSV, ¿tiene el formato correcto?';
$string['csv_no_data_found_error'] = 'No se han encontrado datos en el archivo CSV';
$string['sessions_have_errors'] = 'Algunas sesiones contienen errores, por favor verifique el formulario';
$string['import_sessions_success'] = 'Sesiones importadas con éxito';
$string['import_sessions'] = 'Importar sesiones mediante CSV';

$string['currentSessions'] = 'Sesiones en curso';
$string['archivedSessions'] = 'Sesiones archivadas';
$string['inProgress'] = 'En curso';
$string['upcoming'] = 'Próximas';
$string['alreadySigned'] = 'Ya firmado';
$string['toSign'] = 'Por firmar';
$string['startLabel'] = 'Inicio:';
$string['endLabel'] = 'Fin:';
$string['nothingToSignToday'] = 'No hay nada que firmar hoy.';
$string['noCourseScheduledToday'] = 'No hay ningún curso previsto para hoy.';
$string['nothingUpcoming'] = 'No hay nada próximamente.';
$string['noCourseScheduledLater'] = 'No hay ningún curso previsto más adelante.';
