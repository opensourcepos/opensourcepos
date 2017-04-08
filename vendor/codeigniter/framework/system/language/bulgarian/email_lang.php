<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @author	Ivan Tcholakov
 * @copyright	Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['email_must_be_array'] = 'Email-адресът трябва да се предаде за валидиране чрез масив (array).';
$lang['email_invalid_address'] = 'Грешен email-адрес: %s';
$lang['email_attachment_missing'] = 'Не може да се намери прикачения файл: %s';
$lang['email_attachment_unreadable'] = 'Не може да се отвори прикачения файл: %s';
$lang['email_no_from'] = 'Не е посочен подателят на съобщението (From).';
$lang['email_no_recipients'] = 'Трябва да включите получателите: To, Cc или Bcc';
$lang['email_send_failure_phpmail'] = 'Съобщението не може да бъде изпратено чрез PHP mail(). Вашият сървър може да не е конфигуриран да изпраща поща, използвайки този метод.';
$lang['email_send_failure_sendmail'] = 'Съобщението не може да бъде изпратено чрез PHP Sendmail. Вашият сървър може да не е конфигуриран да изпраща поща, използвайки този метод.';
$lang['email_send_failure_smtp'] = 'Съобщението не може да бъде изпратено чрез PHP SMTP. Вашият сървър може да не е конфигуриран да изпраща поща, използвайки този метод.';
$lang['email_sent'] = 'Вашето съобщение е изпратено успешно и използва следния протокол: %s';
$lang['email_no_socket'] = 'Не може да се отвори връзка към Sendmail. Моля, проверете настройките.';
$lang['email_no_hostname'] = 'Не сте посочили SMTP сървър.';
$lang['email_smtp_error'] = 'Получи се следната SMTP грешка: %s';
$lang['email_no_smtp_unpw'] = 'Грешка: Трябва да посочите име и парола за SMTP.';
$lang['email_failed_smtp_login'] = 'Не може да се изпрати AUTH LOGIN. Грешка: %s';
$lang['email_smtp_auth_un'] = 'Не може да се удостовери потребителското име. Грешка: %s';
$lang['email_smtp_auth_pw'] = 'Не може да се удостовери паролата. Грешка: %s';
$lang['email_smtp_data_failure'] = 'Не могат да се изпращат данни: %s';
$lang['email_exit_status'] = 'Код на завършване: %s';
