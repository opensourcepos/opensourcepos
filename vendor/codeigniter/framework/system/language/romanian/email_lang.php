<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @copyright	Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['email_must_be_array'] = 'Metoda de validare email trebuie să fie de tip array.';
$lang['email_invalid_address'] = 'Adresa de email nu este validă: %s';
$lang['email_attachment_missing'] = 'Nu s-a putut localiza următorul atașament de email: %s';
$lang['email_attachment_unreadable'] = 'Atașamentul următor nu a putut fi deschis: %s';
$lang['email_no_from'] = 'Nu se poate trimite email fără header-ul "De la".';
$lang['email_no_recipients'] = 'Trebuie să includeți destinatarii: To, Cc, or Bcc';
$lang['email_send_failure_phpmail'] = 'Nu s-a putut trimite email folosind PHP mail(). Este posibil ca server-ul dvs. să nu fie configurat pentru a trimite email-uri folosind această metodă.';
$lang['email_send_failure_sendmail'] = 'Nu s-a putut trimite email folosind PHP Sendmail. Este posibil ca server-ul dvs. să nu fie configurat pentru a trimite email-uri folosind această metodă.';
$lang['email_send_failure_smtp'] = 'Nu s-a putut trimite email folosind PHP SMTP. Este posibil ca server-ul dvs. să nu fie configurat pentru a trimite email-uri folosind această metodă.';
$lang['email_sent'] = 'Mesajul dvs. a fost trimis cu succes folosind următorul protocol: %s';
$lang['email_no_socket'] = 'Nu s-a putut deschide un socket pentru Sendmail. Verificați setările.';
$lang['email_no_hostname'] = 'Nu ați specificat un hostname SMTP.';
$lang['email_smtp_error'] = 'Următoarea eroare SMTP a fost întâlnită: %s';
$lang['email_no_smtp_unpw'] = 'Eroare: Trebuie să atribuiți un nume de utilizator și parolă SMTP.';
$lang['email_failed_smtp_login'] = 'Eroare la trimiterea comenzii AUTH LOGIN. Eroare: %s';
$lang['email_smtp_auth_un'] = 'Autentificarea numelui de utilizator nu a putut fi efectuată. Eroare: %s';
$lang['email_smtp_auth_pw'] = 'Autentificarea parolei nu a putut fi efectuată. Error: %s';
$lang['email_smtp_data_failure'] = 'Datele nu pot fi trimise: %s';
$lang['email_exit_status'] = 'Codul stării de ieșire: %s';
