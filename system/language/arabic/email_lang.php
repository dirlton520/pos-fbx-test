<?php
//The email validation method must be passed an array.
$lang['email_must_be_array'] = 'يجب أن يتم تمرير طريقة التحقق من صحة البريد الإلكتروني مصفوفة.';
//Invalid email address: %s
$lang['email_invalid_address'] = 'عنوان البريد الإلكتروني غير صالح:٪ s';
//Unable to locate the following email attachment: %s
$lang['email_attachment_missing'] = 'تعذر تحديد موقع مرفق البريد الإلكتروني التالي:٪ s';
//Unable to open this attachment: %s
$lang['email_attachment_unreadable'] = 'تعذر فتح هذا المرفق:٪ s';
//Cannot send mail with no "From" header.
$lang['email_no_from'] = 'لا يمكن إرسال البريد بدون رأس "من".';
//You must include recipients: To, Cc, or Bcc
$lang['email_no_recipients'] = 'يجب تضمين المستلمين: إلى أو نسخة إلى أو نسخة مخفية الوجهة';
//Unable to send email using PHP mail(). Your server might not be configured to send mail using this method.
$lang['email_send_failure_phpmail'] = 'تعذر إرسال البريد الإلكتروني باستخدام بريد فب (). قد لا تتم تهيئة الخادم لإرسال البريد باستخدام هذه الطريقة.';
//Unable to send email using PHP Sendmail. Your server might not be configured to send mail using this method.
$lang['email_send_failure_sendmail'] = 'تعذر إرسال البريد الإلكتروني باستخدام فب سيندمايل. قد لا تتم تهيئة الخادم لإرسال البريد باستخدام هذه الطريقة.';
//Unable to send email using PHP SMTP. Your server might not be configured to send mail using this method.
$lang['email_send_failure_smtp'] = 'تعذر إرسال البريد الإلكتروني باستخدام فب سمتب. قد لا تتم تهيئة الخادم لإرسال البريد باستخدام هذه الطريقة.';
//Your message has been successfully sent using the following protocol: %s
$lang['email_sent'] = 'تم إرسال رسالتك بنجاح باستخدام البروتوكول التالي:٪ s';
//Unable to open a socket to Sendmail. Please check settings.
$lang['email_no_socket'] = 'تعذر فتح مأخذ توصيل إلى سيندمايل. يرجى التحقق من الإعدادات.';
//You did not specify a SMTP hostname.
$lang['email_no_hostname'] = 'لم تحدد اسم مضيف سمتب.';
//The following SMTP error was encountered: %s
$lang['email_smtp_error'] = 'حدث خطأ سمتب التالي:٪ s';
//Error: You must assign a SMTP username and password.
$lang['email_no_smtp_unpw'] = 'خطأ: يجب تعيين اسم مستخدم وكلمة مرور سمتب.';
//Failed to send AUTH LOGIN command. Error: %s
$lang['email_failed_smtp_login'] = 'أخفق إرسال أمر أوث لوجين. خطأ:٪ s';
//Failed to authenticate username. Error: %s
$lang['email_smtp_auth_un'] = 'فشل مصادقة اسم المستخدم. خطأ:٪ s';
//Failed to authenticate password. Error: %s
$lang['email_smtp_auth_pw'] = 'أخفق مصادقة كلمة المرور. خطأ:٪ s';
//Unable to send data: %s
$lang['email_smtp_data_failure'] = 'تعذر إرسال البيانات:٪ s';
//Exit status code: %s
$lang['email_exit_status'] = 'رمز حالة الخروج:٪ s';
?>