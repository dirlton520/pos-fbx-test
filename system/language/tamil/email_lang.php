<?php
//The email validation method must be passed an array.
$lang['email_must_be_array'] = 'மின்னஞ்சல் சரிபார்ப்பு முறை ஒரு வரிசையை அனுப்ப வேண்டும்.';
//Invalid email address: %s
$lang['email_invalid_address'] = 'தவறான மின்னஞ்சல் முகவரி:% s';
//Unable to locate the following email attachment: %s
$lang['email_attachment_missing'] = 'பின்வரும் மின்னஞ்சல் இணைப்பை கண்டுபிடிக்க முடியவில்லை:% s';
//Unable to open this attachment: %s
$lang['email_attachment_unreadable'] = 'இந்த இணைப்பை திறக்க முடியவில்லை:% s';
//Cannot send mail with no "From" header.
$lang['email_no_from'] = '"இருந்து" தலைப்பு இல்லாமல் அஞ்சல் அனுப்ப முடியாது.';
//You must include recipients: To, Cc, or Bcc
$lang['email_no_recipients'] = 'நீங்கள் பெறுநர்களை சேர்க்க வேண்டும்: To, Cc, அல்லது Bcc';
//Unable to send email using PHP mail(). Your server might not be configured to send mail using this method.
$lang['email_send_failure_phpmail'] = 'PHP மெயில் () ஐப் பயன்படுத்தி மின்னஞ்சல் அனுப்ப முடியவில்லை. இந்த முறையைப் பயன்படுத்தி அஞ்சல் அனுப்ப உங்கள் சேவையகம் கட்டமைக்கப்படாமல் போகலாம்.';
//Unable to send email using PHP Sendmail. Your server might not be configured to send mail using this method.
$lang['email_send_failure_sendmail'] = 'PHP Sendmail ஐப் பயன்படுத்தி மின்னஞ்சல் அனுப்ப முடியவில்லை. இந்த முறையைப் பயன்படுத்தி அஞ்சல் அனுப்ப உங்கள் சேவையகம் கட்டமைக்கப்படாமல் போகலாம்.';
//Unable to send email using PHP SMTP. Your server might not be configured to send mail using this method.
$lang['email_send_failure_smtp'] = 'PHP SMTP ஐப் பயன்படுத்தி மின்னஞ்சல் அனுப்ப முடியவில்லை. இந்த முறையைப் பயன்படுத்தி அஞ்சல் அனுப்ப உங்கள் சேவையகம் கட்டமைக்கப்படாமல் போகலாம்.';
//Your message has been successfully sent using the following protocol: %s
$lang['email_sent'] = 'பின்வரும் நெறிமுறையைப் பயன்படுத்தி உங்கள் செய்தி வெற்றிகரமாக அனுப்பப்பட்டுள்ளது:% s';
//Unable to open a socket to Sendmail. Please check settings.
$lang['email_no_socket'] = 'Sendmail க்கு ஒரு சாக்கெட் திறக்க முடியவில்லை. அமைப்புகளைச் சரிபார்க்கவும்.';
//You did not specify a SMTP hostname.
$lang['email_no_hostname'] = 'நீங்கள் ஒரு SMTP ஹோஸ்ட்பெயரைக் குறிப்பிடவில்லை.';
//The following SMTP error was encountered: %s
$lang['email_smtp_error'] = 'பின்வரும் SMTP பிழை ஏற்பட்டது:% s';
//Error: You must assign a SMTP username and password.
$lang['email_no_smtp_unpw'] = 'பிழை: நீங்கள் ஒரு SMTP பயனர்பெயர் மற்றும் கடவுச்சொல்லை ஒதுக்க வேண்டும்.';
//Failed to send AUTH LOGIN command. Error: %s
$lang['email_failed_smtp_login'] = 'AUTH LOGIN கட்டளையை அனுப்புவதில் தோல்வி. பிழை:% s';
//Failed to authenticate username. Error: %s
$lang['email_smtp_auth_un'] = 'பயனர்பெயரை அங்கீகரிப்பதில் தோல்வி. பிழை:% s';
//Failed to authenticate password. Error: %s
$lang['email_smtp_auth_pw'] = 'கடவுச்சொல்லை அங்கீகரிப்பதில் தோல்வி. பிழை:% s';
//Unable to send data: %s
$lang['email_smtp_data_failure'] = 'தரவை அனுப்ப முடியவில்லை:% s';
//Exit status code: %s
$lang['email_exit_status'] = 'நிலைக் குறியீட்டிலிருந்து வெளியேறு:% s';
?>