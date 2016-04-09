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

$lang['email_must_be_array'] = 'Die Methode zur Überprüfung der E-Mail muss in einem Array übergeben werden.';
$lang['email_invalid_address'] = 'Ungültige E-Mail-Adresse: %s';
$lang['email_attachment_missing'] = 'Der folgende E-Mail-Anhang konnte nicht gefunden werden: %s';
$lang['email_attachment_unreadable'] = 'Der folgende Anhang konnte nicht geöffnet werden: %s';
$lang['email_no_from'] = 'Cannot send mail with no "From" header.';// to translate
$lang['email_no_recipients'] = 'Sie müssen mindestens einen der folgenden Empfänger angeben: To, Cc, oder Bcc';
$lang['email_send_failure_phpmail'] = 'Die E-Mail konnte mit PHP mail() nicht gesendet werden. Ihr Server ist offenbar nicht konfiguriert, um mit dieser Methode E-Mails zu versenden.';
$lang['email_send_failure_sendmail'] = 'Die E-Mail konnte mit PHP Sendmail nicht gesendet werden. Der Server ist offenbar nicht konfiguriert, um mit dieser Methode E-Mails zu versenden.';
$lang['email_send_failure_smtp'] = 'E-Mail konnte mit PHP SMTP nicht gesendet werden. Der Server ist offenbar nicht konfiguriert, um mit dieser Methode E-Mails zu versenden.';
$lang['email_sent'] = 'Ihre Nachricht wurde erfolgreich über das folgenden Protokoll verschickt: %s';
$lang['email_no_socket'] = 'Es konnte keine Socket-Verbindung zu Sendmail hergestellt werden. Bitte überprüfen Sie Ihre Einstellungen.';
$lang['email_no_hostname'] = 'Sie haben keine Angaben zum SMTP-Server vorgenommen.';
$lang['email_smtp_error'] = 'Der folgenden SMTP-Fehler ist aufgetreten: %s';
$lang['email_no_smtp_unpw'] = 'Fehler: Sie müssen einen SMTP Usernamen und ein Passwort angeben.';
$lang['email_failed_smtp_login'] = 'Das Senden des Kommandos AUTH LOGIN ist fehlgeschlagen. Fehler: %s';
$lang['email_smtp_auth_un'] = 'Der angegebene  Username konnte nicht authentifiziert werden. Fehler: %s';
$lang['email_smtp_auth_pw'] = 'Das angegebene Passwort konnte nicht authentifiziert werden. Fehler: %s';
$lang['email_smtp_data_failure'] = 'Die Daten konnten nicht gesendet werden: %s';
$lang['email_exit_status'] = 'Abbruch Status-Code: %s';
