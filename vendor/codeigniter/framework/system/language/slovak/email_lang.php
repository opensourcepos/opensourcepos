<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author    CodeIgniter community
 * @author    Gabriel Potkány <gadelat+codeigniter@gmail.com>
 * @copyright Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license   http://opensource.org/licenses/MIT MIT License
 * @link      https://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['email_must_be_array']         = 'Metóde na kontrolu e-mailu musí byť poslané pole.';
$lang['email_invalid_address']       = 'Neplatná e-mailová adresa: %s';
$lang['email_attachment_missing']    = 'Nie je možné nájsť prílohu e-mailu: %s';
$lang['email_attachment_unreadable'] = 'Nepodarilo sa otvoriť prílohu: %s';
$lang['email_no_from']               = 'Nie je možné odoslať e-mail bez odosielateľa.';
$lang['email_no_recipients']         = 'Musíte uviesť príjemcu: Komu, Cc, alebo Bcc';
$lang['email_send_failure_phpmail']  = 'Nie je možné poslať e-mail pomocou PHP funkcie mail(). Server nemusí byť nastavený pre posielanie e-mailov touto metódou.';
$lang['email_send_failure_sendmail'] = 'Nie je možné poslať e-mail pomocou programu Sendmail. Server nemusí byť nastavený pre posielanie e-mailov touto metódou.';
$lang['email_send_failure_smtp']     = 'Nie je možné poslať e-mail pomocou PHP funkcie pre SMTP. Server nemusí byť nastavený pre posielanie e-mailov touto metódou.';
$lang['email_sent']                  = 'Správa bola úspešne odoslaná pomocou protokolu: %s';
$lang['email_no_socket']             = 'Nie je možné otvoriť prístup k programu Sendmail. Skontrolujte nastavenia.';
$lang['email_no_hostname']           = 'Nie je nastavené meno SMTP servera';
$lang['email_smtp_error']            = 'Bola zaznamenaná chyba SMTP: %s';
$lang['email_no_smtp_unpw']          = 'Chyba: Musíte nastaviť užívateľské meno a heslo pre SMTP.';
$lang['email_failed_smtp_login']     = 'Zlyhalo odoslanie príkazu AUTH LOGIN. Chyba: %s';
$lang['email_smtp_auth_un']          = 'Zlyhalo overenie užívateľského mena. Chyba: %s';
$lang['email_smtp_auth_pw']          = 'Zlyhalo overenie hesla. Chyba: %s';
$lang['email_smtp_data_failure']     = 'Nie je možné odoslať dáta: %s';
$lang['email_exit_status']           = 'Stav pri ukončení: %s';
