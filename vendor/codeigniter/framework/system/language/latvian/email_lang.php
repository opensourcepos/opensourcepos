<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @copyright	Copyright (c) 2014-2018, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['email_must_be_array'] = 'E-pasta pārbaudes metode jānorāda, lietojot masīvu.';
$lang['email_invalid_address'] = 'Nepareiza e-pasta adrese: %s';
$lang['email_attachment_missing'] = 'Neizdevās atrast e-pasta ziņojuma pielikumu: %s';
$lang['email_attachment_unreadable'] = 'Neizdevās atvērt pielikumu: %s';
$lang['email_no_from'] = 'Nevar nosūtīt e-pastu bez "From" galvenes.';
$lang['email_no_recipients'] = 'Jānorāda saņēmēji lauciņos "To", "Cc" vai "Bcc"';
$lang['email_send_failure_phpmail'] = 'Neizdevās nosūtīt e-pasta ziņojumu, izmantojot PHP mail(). Lai nosūtītu ziņojumu, izmantojot šo metodi, jāpārkonfigurē serveris.';
$lang['email_send_failure_sendmail'] = 'Neizdevās nosūtīt e-pasta ziņojumu, izmantojot PHP Sendmail. Lai nosūtītu ziņojumu, izmantojot šo metodi, jāpārkonfigurē serveris.';
$lang['email_send_failure_smtp'] = 'Neizdevās nosūtīt e-pasta ziņojumu, izmantojot PHP protokolu SMTP. Lai nosūtītu ziņojumu, izmantojot šo metodi, jāpārkonfigurē serveris.';
$lang['email_sent'] = 'Jūsu ziņojums tika veiksmīgi nosūtīts, izmantojot protokolu: %s';
$lang['email_no_socket'] = 'Neizdevās atvērt ligzdu uz Sendmail. Lūdzu, pārbaudiet iestatījumus.';
$lang['email_no_hostname'] = 'Nav norādīts SMTP serveris.';
$lang['email_smtp_error'] = 'Nosūtot datus, tika konstatēta šāda SMTP kļūda: %s';
$lang['email_no_smtp_unpw'] = 'Kļūda: Norādiet SMTP lietotājvārdu un paroli.';
$lang['email_failed_smtp_login'] = 'Neizdevās izpildīt komandu AUTH LOGIN. Kļūda: %s';
$lang['email_smtp_auth_un'] = 'Lietotājvārda autentifikācija neizdevās. Kļūda: %s';
$lang['email_smtp_auth_pw'] = 'Paroles autentifikācija neizdevās. Kļūda: %s';
$lang['email_smtp_data_failure'] = 'Neizdevās nosūtīt datus: %s';
$lang['email_exit_status'] = 'Izejas statusa kods: %s';
