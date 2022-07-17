<?php
//The email validation method must be passed an array.
$lang['email_must_be_array'] = 'La méthode de validation par courrier électronique doit être transmise à un tableau.';
//Invalid email address: %s
$lang['email_invalid_address'] = 'Adresse e-mail invalide:% s';
//Unable to locate the following email attachment: %s
$lang['email_attachment_missing'] = 'Impossible de localiser la pièce jointe suivante:% s';
//Unable to open this attachment: %s
$lang['email_attachment_unreadable'] = 'Impossible d\'ouvrir cette pièce jointe:% s';
//Cannot send mail with no "From" header.
$lang['email_no_from'] = 'Impossible d\'envoyer un courrier sans en-tête "De".';
//You must include recipients: To, Cc, or Bcc
$lang['email_no_recipients'] = 'Vous devez inclure les destinataires: à, Cc ou Bcc';
//Unable to send email using PHP mail(). Your server might not be configured to send mail using this method.
$lang['email_send_failure_phpmail'] = 'Impossible d\'envoyer un courrier électronique à l\'aide du courrier PHP (). Votre serveur peut ne pas être configuré pour envoyer un courrier à l\'aide de cette méthode.';
//Unable to send email using PHP Sendmail. Your server might not be configured to send mail using this method.
$lang['email_send_failure_sendmail'] = 'Impossible d\'envoyer un courrier électronique à l\'aide de PHP Sendmail. Votre serveur peut ne pas être configuré pour envoyer un courrier à l\'aide de cette méthode.';
//Unable to send email using PHP SMTP. Your server might not be configured to send mail using this method.
$lang['email_send_failure_smtp'] = 'Impossible d\'envoyer un courrier électronique à l\'aide de SMTP PHP. Votre serveur peut ne pas être configuré pour envoyer un courrier à l\'aide de cette méthode.';
//Your message has been successfully sent using the following protocol: %s
$lang['email_sent'] = 'Votre message a été envoyé avec succès à l\'aide du protocole suivant:% s';
//Unable to open a socket to Sendmail. Please check settings.
$lang['email_no_socket'] = 'Impossible d\'ouvrir une socket à Sendmail. Veuillez vérifier les paramètres.';
//You did not specify a SMTP hostname.
$lang['email_no_hostname'] = 'Vous n\'avez pas spécifié un nom d\'hôte SMTP.';
//The following SMTP error was encountered: %s
$lang['email_smtp_error'] = 'L\'erreur SMTP suivante a été rencontrée:% s';
//Error: You must assign a SMTP username and password.
$lang['email_no_smtp_unpw'] = 'Erreur: Vous devez attribuer un nom d\'utilisateur et un mot de passe SMTP.';
//Failed to send AUTH LOGIN command. Error: %s
$lang['email_failed_smtp_login'] = 'Impossible d\'envoyer la commande AUTH LOGIN. Les erreurs';
//Failed to authenticate username. Error: %s
$lang['email_smtp_auth_un'] = 'Échec de l\'authentification du nom d\'utilisateur. Les erreurs';
//Failed to authenticate password. Error: %s
$lang['email_smtp_auth_pw'] = 'Échec de l\'authentification du mot de passe. Les erreurs';
//Unable to send data: %s
$lang['email_smtp_data_failure'] = 'Impossible d\'envoyer des données:% s';
//Exit status code: %s
$lang['email_exit_status'] = 'Code d\'état de sortie:% s';
?>