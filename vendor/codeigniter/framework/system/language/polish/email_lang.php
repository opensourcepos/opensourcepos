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

$lang['email_must_be_array']            = 'Metoda weryfikacji e-maila musi być przekazana w tablicy.';
$lang['email_invalid_address']          = 'Niepoprawny adres e-mail: %s';
$lang['email_attachment_missing']       = 'Nie można zlokalizować następujących załączników: %s';
$lang['email_attachment_unreadable']    = 'Nie można otworzyć następujących załączników: %s';
$lang['email_no_from']                  = 'Nie można wysłać wiadomości bez nagłówka "From".';
$lang['email_no_recipients']            = 'Należy dodać odbiorców: To, Cc lub Bcc';
$lang['email_send_failure_phpmail']     = 'Nie można wysłać e-maila za pomocą PHP mail(). Twój serwer może nie być skonfigurowany by wysyłać e-maile za pomocą tej metody.';
$lang['email_send_failure_sendmail']    = 'Nie można wysłać e-maila za pomocą PHP Sendmail. Twój serwer może nie być skonfigurowany by wysyłać e-maile za pomocą tej metody.';
$lang['email_send_failure_smtp']        = 'Nie można wysłać e-maila za pomoc PHP SMTP. Twój serwer może nie być skonfigurowany by wysyłać e-maile za pomocą tej metody.';
$lang['email_sent']                     = 'Twój e-mail został pomyślnie wysłany za pomocą metody: %s';
$lang['email_no_socket']                = 'Nie można otworzyć socketu do Sendmail. Proszę sprawdzić ustawienia.';
$lang['email_no_hostname']              = 'Nie podano nazwy hosta SMTP.';
$lang['email_smtp_error']               = 'Wystąpił następujący błąd SMTP: %s';
$lang['email_no_smtp_unpw']             = 'Błąd: Należy podać nazwę użytkownika i hasło SMTP.';
$lang['email_failed_smtp_login']        = 'Błąd przy wysyłaniu komendy AUTH LOGIN. Błąd: %s';
$lang['email_smtp_auth_un']             = 'Błąd autentykacji nazwy użytkownika. Błąd: %s';
$lang['email_smtp_auth_pw']             = 'Błąd autentykacji hasła. Błąd: %s';
$lang['email_smtp_data_failure']        = 'Nie można wysłać danych: %s';
$lang['email_exit_status']              = 'Status kodu wyjścia: %s';
