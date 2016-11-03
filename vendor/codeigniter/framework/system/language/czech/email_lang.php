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

$lang['email_must_be_array']         = 'Parametrem validace emailu musí bát pole.';
$lang['email_invalid_address']       = 'Nesprávna emailová adresa: %s';
$lang['email_attachment_missing']    = 'Nepodařilo se nalézt přílohu: %s';
$lang['email_attachment_unreadable'] = 'Nepodařilo se otevřít přílohu: %s';
$lang['email_no_from']               = 'Není možné odeslat  email bez "From" v hlavičce.';
$lang['email_no_recipients']         = 'Je nutné specifikovat alespoň jednoho adresáta: To, Cc, or Bcc';
$lang['email_send_failure_phpmail']  = 'Nepodařilo se odeslat email pomocí mail(). Je možné že není serverem podporována.';
$lang['email_send_failure_sendmail'] = 'Nepodařilo se odeslat email pomocí Sendmail. Je možné že není serverem podporována.';
$lang['email_send_failure_smtp']     = 'Nepodařilo se odeslat email pomocí SMTP. Je možné že není serverem podporována.';
$lang['email_sent']                  = 'Vaše zpráva byla úspěšně odeslána pmocí protokolu: %s';
$lang['email_no_socket']             = 'Nepodařilo se otevřít socket pro Sendmail. Zkontrolujte prosím nastavení';
$lang['email_no_hostname']           = 'Není specifikováno SMTP hostname.';
$lang['email_smtp_error']            = 'Nastal SMTP error: %s';
$lang['email_no_smtp_unpw']          = 'Error: Je nutné poskytnout SMTP přihlašovací jméno a heslo.';
$lang['email_failed_smtp_login']     = 'Nepodařilo se odeslat příkaz AUTH LOGIN. Error: %s';
$lang['email_smtp_auth_un']          = 'Nepodařilo se autentizovat pomocjí přihlašovacího jména. Error: %s';
$lang['email_smtp_auth_pw']          = 'Nepodařilo se autentizovat pomocjí hesla. Error: %s';
$lang['email_smtp_data_failure']     = 'Nemodařilo se odeslat data: %s';
$lang['email_exit_status']           = 'Exit s kódem: %s';
