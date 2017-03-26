<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @copyright	Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['email_must_be_array'] = 'يجب تمرير مصفوفة إلى دالة التحقق من البريد الإلكتروني.';
$lang['email_invalid_address'] = 'عنوان بريد إلكتروني خاطيء: %s';
$lang['email_attachment_missing'] = 'غير قادر على إيجاد الملف المرفق: %s';
$lang['email_attachment_unreadable'] = 'غير قاد على فتح الملف المرفق: %s';
$lang['email_no_from'] = 'لا يمكن إرسال البريد الإلكتروني بدون تحديد المرسل.';
$lang['email_no_recipients'] = 'يجب إضافة مستقبلين: To, Cc, or Bcc';
$lang['email_send_failure_phpmail'] = 'غير قادر على الإرسال بإستخدام PHP mail(). قد يكون الخادم غير معد للإرسال بإستخدام هذه الطريقة.';
$lang['email_send_failure_sendmail'] = 'غير قادر على الإرسال بإستخدام PHP Sendmail. قد يكون الخادم غير معد للإرسال بإستخدام هذه الطريقة.';
$lang['email_send_failure_smtp'] = 'غير قادر على الإرسال بإستخدام PHP SMTP. قد يكون الخادم غير معد للإرسال بإستخدام هذه الطريقة.';
$lang['email_sent'] = 'تم إرسال الرسالة بنجاح بإستخدام البروتوكول التالي: %s';
$lang['email_no_socket'] = 'غير قادر على فتح إتصال مع Sendmail. الرجاء التأكد من الإعدادات.';
$lang['email_no_hostname'] = 'لم تحدد إسم المضيف لـ SMTP.';
$lang['email_smtp_error'] = 'خطأ SMTP حدث: %s';
$lang['email_no_smtp_unpw'] = 'خطأ: يجب تحديد إسم مستخدم وكلمة مرور لـ SMTP.';
$lang['email_failed_smtp_login'] = 'فشل في إرسال أمر AUTH LOGIN command. الخطأ: %s';
$lang['email_smtp_auth_un'] = 'فشل في التحقق من إسم المستخدم. الخطأ: %s';
$lang['email_smtp_auth_pw'] = 'فشل في التحقق من كلمة مرور. الخطأ: %s';
$lang['email_smtp_data_failure'] = 'غير قادر على إرسال البيانات: %s';
$lang['email_exit_status'] = 'رمز الخطأ: %s';
