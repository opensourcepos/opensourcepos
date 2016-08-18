<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author CodeIgniter community
 * @author Peter Denk
 * @copyright Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license http://opensource.org/licenses/MIT MIT License
 * @link http://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['email_must_be_array']		= 'Metoden för e-postvalidering kräver en matris.';
$lang['email_invalid_address']		= 'Ogiltig e-postadress: %s';
$lang['email_attachment_missing']	= 'Saknar följande bilaga: %s';
$lang['email_attachment_unreadable']	= 'Kunde inte öppna följande bilaga: %s';
$lang['email_no_from']			= 'Saknar avsändare.';
$lang['email_no_recipients']		= 'Saknar mottagare: Till, Kopia eller Dold kopia';
$lang['email_send_failure_phpmail']	= 'Kunde inte skicka med PHP mail(). Kontrollera att servern är konfigurerad för att skicka e-post med denna metod.';
$lang['email_send_failure_sendmail']	= 'Kunde inte skicka med PHP Sendmail. Kontrollera att servern är konfigurerad för att skicka e-post med denna metod.';
$lang['email_send_failure_smtp']	= 'Kunde inte skicka med PHP SMTP. Kontrollera att servern är konfigurerad för att skicka e-post med denna metod.';
$lang['email_sent']			= 'Meddelandet har skickats genom följande protokoll: %s';
$lang['email_no_socket']		= 'Kunde inte få kontakt med Sendmail. Kontrollera konfigurationen.';
$lang['email_no_hostname']		= 'Saknar värdnamn för SMTP.';
$lang['email_smtp_error']		= 'Följande SMTP-fel uppstod: %s';
$lang['email_no_smtp_unpw']		= 'Saknar användarnamn och lösenord för SMTP.';
$lang['email_failed_smtp_login']	= 'Kunde inte skicka kommandot AUTH LOGIN. Felmeddelande: %s';
$lang['email_smtp_auth_un']		= 'Användarnamnet godkändes inte. Felmeddelande: %s';
$lang['email_smtp_auth_pw']		= 'Lösenordet godkändes inte. Felmeddelande: %s';
$lang['email_smtp_data_failure']	= 'Kunde inte skicka data: %s';
$lang['email_exit_status']		= 'Statuskod vid avslut: %s';
