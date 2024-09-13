<?php
/**
 * Chaînes pour le composant 'mod_edusign', langue 'fr'
 *
 * @package   mod_edusign
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 ou ultérieure
 */

$string['modulename'] = 'Edusign';
$string['pluginadministration'] = 'Administration d\'Edusign';
$string['modulename_help'] = 'Le module d\'activité Edusign permet à un enseignant de prendre les présences pendant les cours et aux étudiants de consulter leur propre relevé de présence.';
$string['loading'] = 'Chargement en cours...';
$string['settings'] = 'Paramètres';
$string['save'] = 'Enregistrer';
$string['settings_section_config'] = 'Configuration du plugin';
$string['refresh_token'] = 'Générer un nouveau jeton';
$string['modhealth'] = 'État du plugin';
$string['webhooks_settings'] = 'Paramètres des webhooks';
$string['webhook_token'] = 'Jeton de sécurité des webhooks';
$string['webhook_url'] = 'URL du webhook à ajouter à votre plateforme scolaire Edusign';
$string['webhook_token_placeholder'] = '32747e3ee3f0b75b3638ec53305cdc77';

$string['webhook_student_has_signed_help'] = 'Si vos étudiants signent par e-mail, Moodle peut ne pas mettre à jour l\'état d\'achèvement de l\'activité. Pour cela, vous pouvez utiliser cette URL sur le webhook <strong>[on_student_sign]</strong> depuis votre interface Edusign.<br /><a target="_blank" href="https://developers.edusign.com/docs/webhooks-2">Consultez la documentation pour plus d\'informations.</a>';
$string['apiurl_text'] = 'URL de l\'API';
$string['apiurl_text_help'] = 'URL de l\'API pour contacter les services Edusign';
$string['apikey_text'] = 'Clé de l\'API';
$string['apikey_text_help'] = 'Clé de l\'API pour synchroniser les utilisateurs et les cours avec Edusign';
$string['completion_all_attendance'] = 'Signer toutes les feuilles de présence pour l\'activité';
$string['completeonallattendancesigned:submit'] = 'Signer toutes les feuilles';
$string['completeonallattendancesigned'] = 'L\'étudiant doit signer toutes les feuilles de présence pour l\'activité';
$string['completion_all_attendance_help'] = 'Lorsque cette option est activée, l\'activité est automatiquement marquée comme terminée pour tous les étudiants qui ont été marqués présents dans toutes les sessions de l\'activité';

$string['completion_of_X_attendance'] = 'Signer un nombre donné de feuilles de présence pour l\'activité';
$string['completeonxattendancesigned:submit'] = 'Signer {$a} feuille(s)';
$string['completeonxattendancesigned'] = 'Nombre de feuilles de présence que l\'étudiant doit signer pour l\'activité';
$string['completion_X_attendance_help'] = 'Lorsque cette option est activée, l\'activité est automatiquement marquée comme terminée pour tous les étudiants qui ont été marqués présents dans le nombre de session fourni de l\'activité';

$string['plugin_advanced'] = 'Paramètres avancés';
$string['test_api_error'] = 'Une erreur s\'est produite lors de la connexion à l\'API : {$a}';
$string['test_api_success'] = 'Test de connexion à l\'API réussi';
$string['testapiconnection'] = 'Tester la connexion à l\'API';

$string['attendance'] = 'Présence';
$string['add_session'] = 'Ajouter une session';
$string['date'] = 'Date';
$string['hourStart'] = 'Heure de début';
$string['hourEnd'] = 'Heure de fin';
$string['title'] = 'Titre';
$string['action'] = 'Action';
$string['takeAttendance'] = 'Prendre les présences';
$string['editAttendance'] = 'Modifier les présences';
$string['deleteAttendance'] = 'Supprimer les présences';
$string['removeSession'] = 'Supprimer la session';
$string['removeSessionQuestions'] = 'Vous êtes sur le point de supprimer une session. Êtes-vous sûr de vouloir continuer ?';
$string['removeSessionAndSheet'] = 'Supprimer également la feuille de présence sur Edusign (Attention, action irréversible)';
$string['archiveSessionQuestion'] = 'Vous êtes sur le point d\'archiver une session. Êtes-vous sûr de vouloir continuer ?';
$string['unarchiveSessionQuestion'] = 'Vous êtes sur le point de désarchiver une session. Êtes-vous sûr de vouloir continuer ?';

$string['addsession'] = 'Ajouter une session';
$string['editSession'] = 'Modifier une session';
$string['sessiondate'] = 'Date de la session';
$string['from'] = 'De';
$string['to'] = 'à';
$string['time'] = 'Heure';
$string['savechanges'] = 'Enregistrer les modifications';
$string['errorsessiondateinpast'] = 'La date de la session ne peut pas être dans le passé';
$string['errorstartdatebeforeenddate'] = 'La date de début ne peut pas être après la date de fin';
$string['forcesync'] = 'Forcer la synchronisation avec Edusign (peut prendre quelques secondes)';
$string['processcompletion'] = 'Re-traiter l\'état d\'achèvement des étudiants (peut prendre du temps en fonction du nombre d\'étudiants)';

$string['session'] = 'Session';
$string['status'] = 'Statut';
$string['studentName'] = 'Nom';
$string['refresh'] = 'Actualiser';
$string['refresh_help'] = 'Actualiser la feuille de présence';
$string['changePresenceStatus'] = 'Présence';
$string['manualSignature'] = 'Signature manuelle';
$string['absence'] = 'Absence';
$string['delay'] = 'Retard';
$string['earlyDeparture'] = 'Départ anticipé';
$string['sendSignatureMail'] = 'Envoyer un e-mail de signature';
$string['sendSignatureMailSelected'] = 'Envoi groupé des e-mails de signature';
$string['signSelected_help'] = 'Pour envoyer des e-mails à plusieurs personnes, veuillez sélectionner les utilisateurs dans le tableau';
$string['teacherSignature'] = 'Signature de l\'enseignant';
$string['present'] = 'Présent';
$string['noData'] = 'Aucune donnée';
$string['waitingSignature'] = 'En attente de signature';
$string['minLate'] = 'min de retard';
$string['departureAt'] = 'Départ anticipé à';
$string['archive'] = 'Archiver';
$string['archiveSession_help'] = 'Archiver la session';
$string['archiveSession'] = 'Archiver la session';
$string['unarchiveSession'] = 'Désarchiver la session';
$string['sessionArchivedCannotTake'] = 'Cette session est archivée, vous ne pouvez pas prendre les présences.';
$string['showOnEdusignWebsite'] = 'Afficher sur l\'application Edusign';
$string['noArchivedSession'] = 'Aucune session archivée';
$string['noUnarchivedSession'] = 'Aucune session en cours';

$string['send_sign_email_error'] = 'Une erreur s\'est produite lors de l\'envoi de l\'e-mail de signature à l\'étudiant : {$a}';
$string['send_sign_email_success'] = 'E-mail de signature envoyé avec succès à l\'étudiant';
$string['set_student_absent_error'] = 'Une erreur s\'est produite lors du marquage de l\'étudiant comme absent : {$a}';
$string['set_student_absent_success'] = 'Étudiant marqué comme absent avec succès';
$string['set_student_delay_error'] = 'Une erreur s\'est produite lors du marquage de l\'étudiant comme en retard : {$a}';
$string['set_student_delay_success'] = 'Étudiant marqué comme en retard avec succès';
$string['set_student_early_departure_success'] = 'Étudiant marqué comme partant plus tôt avec succès';
$string['set_student_early_departure_error'] = 'Une erreur s\'est produite lors du marquage de l\'étudiant comme partant plus tôt : {$a}';
$string['refresh_error'] = 'Une erreur s\'est produite lors de l\'actualisation de la feuille de présence : {$a}';
$string['archive_session_success'] = 'La session a été archivée avec succès';
$string['archive_session_error'] = 'Une erreur s\'est produite lors de l\'archivage';
$string['archive_session_sync_error'] = 'Une erreur s\'est produite lors de la synchronisation avec Edusign : {$a}';

$string['should_import_sessions_with_csv'] = 'Vous pouvez importer une liste de sessions à créer en important un fichier CSV.';
$string['download_csv_model'] = 'Télécharger le modèle de fichier CSV';
$string['session_name'] = 'Nom de la session';
$string['session_start_date'] = 'Date de début';
$string['session_end_date'] = 'Date de fin';
$string['create_sessions'] = 'Créer ces sessions';
$string['choose_file'] = 'Choisir un fichier';
$string['csv_import_error'] = 'Une erreur s\'est produite lors du traitement du fichier CSV, a-t-il le format correct ?';
$string['csv_no_data_found_error'] = 'Aucune donnée trouvée dans le fichier CSV';
$string['sessions_have_errors'] = 'Certaines sessions comportent des erreurs, veuillez vérifier le formulaire';
$string['import_sessions_success'] = 'Sessions importées avec succès';
$string['import_sessions'] = 'Importer les sessions via CSV';