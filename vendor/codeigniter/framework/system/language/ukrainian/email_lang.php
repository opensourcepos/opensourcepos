<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['email_must_be_array'] = 'Методу перевірки адреси електронної пошти повинен бути переданий массив.';
$lang['email_invalid_address'] = 'Некорректна адреса електронної пошти: %s';
$lang['email_attachment_missing'] = 'Не вдалося знайти вкладення: %s';
$lang['email_attachment_unreadable'] = 'Неможливо відкрити вкладення: %s';
$lang['email_no_from'] = 'Не вдалося відправити лист без вказаного заголовку "Від"/"From".';
$lang['email_no_recipients'] = 'Ви не вказали отримувачів: Кому/To, Копії/Cc, або Скриті копії/Bcc';
$lang['email_send_failure_phpmail'] = 'Неможливо відправити електронну пошту за допомогою PHP mail(). Ваш сервер, можливо, не налаштований для відправки пошти за допомогою цієї функції.';
$lang['email_send_failure_sendmail'] = 'Неможливо відправити електронну пошту за допомогою PHP Sendmail. Ваш сервер, можливо, не налаштований для відправки пошти за допомогою цієї функції.';
$lang['email_send_failure_smtp'] = 'Неможливо відправити електронну пошту за допомогою PHP SMTP. Ваш сервер, можливо, не налаштований для відправки пошти за допомогою цієї функції.';
$lang['email_sent'] = 'Ваше повідомлення було успішно відправлено за допомогою протоколу: %s';
$lang['email_no_socket'] = 'Неможливо відкрити сокет для Sendmail. Будь-ласка, перевірте налаштування.';
$lang['email_no_hostname'] = 'Ви не вказали ім’я хосту SMTP.';
$lang['email_smtp_error'] = 'Виявлено наступну помилку SMTP: %s';
$lang['email_no_smtp_unpw'] = 'Помилка: Ви повинні вказати ім’я користувача та пароль SMTP.';
$lang['email_failed_smtp_login'] = 'Неможливо відправити команду AUTH LOGIN. Помилка: %s';
$lang['email_smtp_auth_un'] = 'Збій перевірки імені користувача. Помилка: %s';
$lang['email_smtp_auth_pw'] = 'Збій перевірки пароля. Помилка: %s';
$lang['email_smtp_data_failure'] = 'Неможливо відправити дані: %s';
$lang['email_exit_status'] = 'Код завершення: %s';
