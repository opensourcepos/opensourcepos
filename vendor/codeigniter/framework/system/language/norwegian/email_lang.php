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

$lang['email_must_be_array'] = 'E-postvalideringsmetoden krever en matrise.';
$lang['email_invalid_address'] = 'Ugyldig e-postadresse: %s';
$lang['email_attachment_missing'] = 'Fant ikke følgende e-postvedlegg: %s';
$lang['email_attachment_unreadable'] = 'Klarte ikke å åpne dette vedlegget: %s';
$lang['email_no_from'] = 'Kan ikke sende e-post uten «From»-header.';
$lang['email_no_recipients'] = 'Du må oppgi mottakere: til, kopi eller blindkopi.';
$lang['email_send_failure_phpmail'] = 'Klarte ikke å sende e-post ved hjelp av PHP mail(). Tjeneren din er kanskje ikke konfigurert til å sende e-post ved hjelp av denne metoden.';
$lang['email_send_failure_sendmail'] = 'Klarte ikke å sende e-post ved hjelp av PHP Sendmail. Tjeneren din er kanskje ikke konfigurert til å sende e-post ved hjelp av denne metoden.';
$lang['email_send_failure_smtp'] = 'Klarte ikke å sende e-post ved hjelp av PHP SMTP. Tjeneren din er kanskje ikke konfigurert til å sende e-post ved hjelp av denne metoden.';
$lang['email_sent'] = 'Meldingen din er sendt ved hjelp av følgende protokoll: %s';
$lang['email_no_socket'] = 'Klarte ikke å oppnå kontakt med Sendmail. Vennligst sjekk innstillingene dine.';
$lang['email_no_hostname'] = 'Du oppgav ikke noe vertsnavn for SMTP.';
$lang['email_smtp_error'] = 'Følgende SMTP-feil oppstod: %s';
$lang['email_no_smtp_unpw'] = 'Feil: Du må oppgi SMTP-brukernavn og -passord.';
$lang['email_failed_smtp_login'] = 'AUTH LOGIN-kommandoen ble ikke sendt. Feil: %s';
$lang['email_smtp_auth_un'] = 'Feil ved autentisering av brukernavn: %s';
$lang['email_smtp_auth_pw'] = 'Feil ved autentisering av passord: %s';
$lang['email_smtp_data_failure'] = 'Klarte ikke å sende data: %s';
$lang['email_exit_status'] = 'Statuskode ved avslutning: %s';
