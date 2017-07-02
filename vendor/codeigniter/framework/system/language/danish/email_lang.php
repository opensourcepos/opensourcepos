<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @copyright	Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['email_must_be_array'] = 'Email valideringsmetoden kræver at modtage et array.';
$lang['email_invalid_address'] = 'Ugyldig email adresse: %s';
$lang['email_attachment_missing'] = 'Fandt ikke følgende email vedhæftning: %s';
$lang['email_attachment_unreadable'] = 'Kan ikke åbne denne vedhæftning: %s';
$lang['email_no_from'] = 'Kan ikke sende mail uden "From"-header.';
$lang['email_no_recipients'] = 'Du skal angive for modtager: To, Cc, or Bcc';
$lang['email_send_failure_phpmail'] = 'Kan ikke sende email med PHP mail(). Din server er måske ikke konfigurerettil at sende mail med denne metode.';
$lang['email_send_failure_sendmail'] = 'Kan ikke sende email med PHP Sendmail. Din server er måske ikke konfigurerettil at sende mail med denne metode.';
$lang['email_send_failure_smtp'] = 'Kan ikke sende email med PHP SMTP. Din server er måske ikke konfigurerettil at sende mail med denne metode.';
$lang['email_sent'] = 'Din meddelelse blev sendt med denne protokol: %s';
$lang['email_no_socket'] = 'Kan ikke åbne kontakt til Sendmail. Prøv at checke dine indstillinger.';
$lang['email_no_hostname'] = 'Du mangler at angive et SMTP værtsnavn.';
$lang['email_smtp_error'] = 'Følgende SMTP-fejl opstod: %s';
$lang['email_no_smtp_unpw'] = 'Fejl: Du skal angive et SMTP brugernavn og password.';
$lang['email_failed_smtp_login'] = 'Fejl ved afsendelse af AUTH LOGIN kommando. Fejl: %s';
$lang['email_smtp_auth_un'] = 'Fejl ved autentificering af brugernavn. Fejl: %s';
$lang['email_smtp_auth_pw'] = 'Fejl ved autentificering af password. Fejl: %s';
$lang['email_smtp_data_failure'] = 'Kunne ikke sende dataene: %s';
$lang['email_exit_status'] = 'Afslutende statuskode: %s';
