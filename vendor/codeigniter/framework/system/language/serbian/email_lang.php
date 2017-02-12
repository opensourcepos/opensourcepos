<?php
 /**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @copyright	Novak Urošević
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 */
defined('BASEPATH') OR exit('Nije dozvoljen direktan pristup');

$lang['email_must_be_array'] = 'Metod za validaciju emaila mora biti poslat kao niz.';
$lang['email_invalid_address'] = 'Nepostojeća email adresa: %s';
$lang['email_attachment_missing'] = 'Nemoguće je locirati dodatni fajl za email: %s';
$lang['email_attachment_unreadable'] = 'Nemoguće je otvoriti dodatni fajl: %s';
$lang['email_no_from'] = 'Ne može se poslati mejl bez "From" zaglavlja.';
$lang['email_no_recipients'] = 'Morate navesti primaoce: To, Cc, or Bcc';
$lang['email_send_failure_phpmail'] = 'Nemoguće je poslati mail korišćenjem PHP mail(). Vaš server možda nije podešen da šalje email ovim metodom.';
$lang['email_send_failure_sendmail'] = 'Nemoguće je poslati email korišćenjem PHP Sendmail. Vaš server možda nije podešen da šalje email ovim metodom.';
$lang['email_send_failure_smtp'] = 'Nemoguće je poslati email korišćenjem PHP SMTP. Vaš server možda nije podešen da šalje email ovim metodom.';
$lang['email_sent'] = 'Vaša poruka je uspešno poslana korišćenjem sledećeg protokola: %s';
$lang['email_no_socket'] = 'Nemoguće je otvoriti soket za Sendmail. Proverite podešavanja.';
$lang['email_no_hostname'] = 'Niste podesili ime SMTP hosta.';
$lang['email_smtp_error'] = 'The following SMTP error was encountered: %s';
$lang['email_no_smtp_unpw'] = 'Greška: Morate podesiti SMTP korisničko ime i šifru.';
$lang['email_failed_smtp_login'] = 'Neuspešan pokušaj slanja AUTH LOGIN komande. Greška: %s';
$lang['email_smtp_auth_un'] = 'Neuspešna autentikacija korisničkog imena. Greška: %s';
$lang['email_smtp_auth_pw'] = 'Neuspešna autentikacija šifre. Greška: %s';
$lang['email_smtp_data_failure'] = 'Nemoguće je poslati podatke: %s';
$lang['email_exit_status'] = 'Šifra izlaznog statusa: %s';
