<?php
 /**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @copyright	Copyright (c) 2014-2018, British Columbia Institute of Technology (http://bcit.ca/)
 * @copyright	Mario Ljubičić
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 */
defined('BASEPATH') OR exit('Nije dozvoljen izravan pristup');

$lang['email_must_be_array'] = 'Metoda za validaciju emaila mora biti poslana kao niz.';
$lang['email_invalid_address'] = 'Nepostojeća email adresa: %s';
$lang['email_attachment_missing'] = 'Nemoguće je locirati dodatak za email: %s';
$lang['email_attachment_unreadable'] = 'Nemoguće je otvoriti dodatak: %s';
$lang['email_no_from'] = 'Ne može se poslati email bez "From" zaglavlja.';
$lang['email_no_recipients'] = 'Morate navesti primatelje: To, Cc, ili Bcc';
$lang['email_send_failure_phpmail'] = 'Nemoguće je poslati mail korištenjem PHP mail() metode. Server možda nije podešen da šalje email ovom metodom.';
$lang['email_send_failure_sendmail'] = 'Nemoguće je poslati email korištenjem PHP Sendmail metode. Server možda nije podešen da šalje email ovom metodom.';
$lang['email_send_failure_smtp'] = 'Nemoguće je poslati email korištenjem PHP SMTP. Server možda nije podešen da šalje email ovom metodom.';
$lang['email_sent'] = 'Vaša poruka je uspješno poslana korištenjem slijedećeg protokola: %s';
$lang['email_no_socket'] = 'Nemoguće je otvoriti socket za Sendmail. Provjerite postavke.';
$lang['email_no_hostname'] = 'Niste podesili naziv SMTP poslužitelja.';
$lang['email_smtp_error'] = 'Dogodila se slijedeća SMTP greška: %s';
$lang['email_no_smtp_unpw'] = 'Greška: Morate podesiti SMTP korisničko ime i lozinku.';
$lang['email_failed_smtp_login'] = 'Neuspješan pokušaj slanja AUTH LOGIN naredbe. Greška: %s';
$lang['email_smtp_auth_un'] = 'Neuspješna autentikacija korisničkog imena. Greška: %s';
$lang['email_smtp_auth_pw'] = 'Neuspješna autentikacija lozinke. Greška: %s';
$lang['email_smtp_data_failure'] = 'Nemoguće je poslati podatke: %s';
$lang['email_exit_status'] = 'Kod izlaznog statusa: %s';
