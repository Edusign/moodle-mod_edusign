<?php

/**
 * Strings for component 'mod_edusign', language 'fr'
 *
 * @package   mod_edusign
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['modulename'] = 'Edusign';
$string['modulename_help'] = 'Le module d\'activité edusign permet à un enseignant de prendre des présences pendant les cours et aux étudiants de consulter leur propre registre de présences.';
$string['loading'] = 'Chargement ...';
/*************
 * Settings Page
 *************/

/** Settings Tab */
$string['settings'] = 'Paramètres';
$string['settings_section_config'] = 'Configuration du plugin';
$string['apiurl_text'] = 'URL de l\'API';
$string['apiurl_text_help'] = 'URL de l\'API pour contacter les services Edusign';
$string['apikey_text'] = 'Clef d\'API';
$string['apikey_text_help'] = 'Clef d\'API pour synchroniser les utilisateurs et les cours avec Edusign';

/** Settings Plugin Health Tab */
$string['plugin_health'] = 'Santé du plugin';
$string['test_api_error'] = 'Une erreur est survenue lors de la connexion à l\'API: {$a}';
$string['test_api_success'] = 'La connexion à l\'API a réussi';
$string['testapiconnection'] = 'Tester la connexion avec l\'api';

/** Attendance */
$string['attendance'] = 'Présence';
$string['add_session'] = 'Ajouter une session';
$string['date'] = 'Date';
$string['hourStart'] = 'Horaire de début';
$string['hourEnd'] = 'Horaire de fin';
$string['title'] = 'Titre';
$string['action'] = 'Action';
$string['takeAttendance'] = 'Démarrer l’assiduité';
$string['editAttendance'] = 'Modifier la feuille d’assiduité';
$string['deleteAttendance'] = 'Supprimer la feuille d’assiduité';
$string['removeSession'] = 'Supprimer la session';
$string['removeSessionQuestions'] = 'Vous êtes sur le point de supprimer une session. Êtes-vous sûr de vouloir continuer ?';
$string['removeSessionAndSheet'] = 'Supprimer aussi la feuille de présence sur Edusign ( Attention, action irréversible )';
$string['archiveSessionQuestion'] = 'Vous êtes sur le point d’archiver une session. Êtes-vous sûr de vouloir continuer ?';
$string['unarchiveSessionQuestion'] = 'Vous êtes sur le point de désarchiver une session. Êtes-vous sûr de vouloir continuer ?';

/** Add Session */
$string['addsession'] = 'Ajouter une session';
$string['editSession'] = 'Éditer une session';
$string['sessiondate'] = 'Date de la session';
$string['from'] = 'De';
$string['to'] = 'à';
$string['time'] = 'Heure';
$string['savechanges'] = 'Enregistrer les modifications';
$string['errorsessiondateinpast'] = 'La date de la session ne peut pas être dans le passé';
$string['errorstartdatebeforeenddate'] = 'La date de début ne peut pas être après la date de fin';
$string['forcesync'] = 'Forcer la synchronisation avec Edusign (peut durer quelques secondes)';

/** Session */
$string['session'] = 'Session';
$string['status'] = 'Status';
$string['studentName'] = 'Nom';
$string['refresh'] = 'rafraîchir';
$string['refresh_help'] = 'Rafraîchir la feuille de présence';
$string['changePresenceStatus'] = 'Présence';
$string['manualSignature'] = 'Signer manuellement';
$string['absence'] = 'Absence';
$string['delay'] = 'Retard';
$string['earlyDeparture'] = 'Départ anticipé';
$string['sendSignatureMail'] = 'Envoyer mail de signature';
$string['sendSignatureMailSelected'] = 'Envois groupés de signatures par mail';
$string['signSelected_help'] = 'Pour envoyer des mails à plusieurs personnes, veuillez sélectionner des utilisateurs dans le tableau';
$string['teacherSignature'] = 'Signature de l’intervenant';
$string['present'] = 'Présent';
$string['noData'] = 'Pas de données';
$string['waitingSignature'] = 'En attente de signature';
$string['minLate'] = 'min de retard';
$string['departureAt'] = 'Départ anticipé à';
$string['archive'] = 'Archiver';
$string['archiveSession_help'] = 'Archiver la session';
$string['archiveSession'] = 'Archiver la session';
$string['unarchiveSession'] = 'Désarchiver la session';
$string['sessionArchivedCannotTake'] = 'Cette session est archivée, vous ne pouvez pas récupérer les présences';
$string['showOnEdusignWebsite'] = 'Afficher sur l\'app Edusign';
$string['noArchivedSession'] = 'Aucune session archivée';
$string['noUnarchivedSession'] = 'Aucune session en cours';

/** ---- Ajax messages */

$string['send_sign_email_error'] = 'Une erreur s\'est produite lors de l\'envoi de l\'email de signature à l\'étudiant : {$a}';
$string['send_sign_email_success'] = 'Email de signature envoyé avec succès à l\'étudiant';
$string['set_student_absent_error'] = 'Une erreur s\'est produite lors de la mise en absence de l\'étudiant : {$a}';
$string['set_student_absent_success'] = 'Étudiant mis avec succès en absence';
$string['set_student_delay_error'] = 'Une erreur s\'est produite lors de la mise en retard de l\'étudiant : {$a}';
$string['set_student_delay_success'] = 'Étudiant mis avec succès en retard';
$string['set_student_early_departure_success'] = 'Étudiant mis avec succès en situation de départ anticipé';
$string['set_student_early_departure_error'] = 'Une erreur s’est produite lors de la définition du départ anticipé de l’étudiant : {$a}';
$string['refresh_error'] = 'Une erreur s\'est produite lors du rafraîchissement : {$a}';
$string['archive_session_success'] = 'La session a bien été archivée';
$string['archive_session_error'] = 'Une erreur est survenue durant l’archivage';
$string['archive_session_sync_error'] = 'Une erreur est survenue durant la synchronisation avec Edusign : {$a}';


/** ---- Import CSV */
$string['should_import_sessions_with_csv'] = 'Vous pouvez importer une liste de sessions à créer en important un fichier CSV.';
$string['download_csv_model'] = 'Télécharger le modèle de fichier CSV';
$string['session_name'] = 'Nom de la session';
$string['session_start_date'] = 'Date de début';
$string['session_end_date'] = 'Date de fin';
$string['create_sessions'] = 'Créer ces sessions';
$string['choose_file'] = 'Choisir un fichier';
$string['csv_import_error'] = 'Une erreur est survenue durant le traitement du fichier CSV, a-t-il le bon format ?';
$string['csv_no_data_found_error'] = 'Aucune donnée trouvée dans le fichier CSV';
$string['sessions_have_errors'] = 'Certaines sessions comportent des erreurs, veuillez vérifier le formulaire';
$string['import_sessions_success'] = 'Sessions importées avec succès';
$string['import_sessions'] = 'Import de sessions via CSV';
