<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @author	Ignasi Molsosa
 * @copyright	Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['email_must_be_array'] = 'El mètode de validació de l\'email s\'ha de passar com a array.';
$lang['email_invalid_address'] = 'Adreça email invàlida: %s';
$lang['email_attachment_missing'] = 'Impossible trobar els següents arxius adjunts: %s';
$lang['email_attachment_unreadable'] = 'Impossible obrir aquest arxiu adjunt: %s';
$lang['email_no_from'] = 'No es pot enviar un correu electrònic sense remitent.';
$lang['email_no_recipients'] = 'Has d\'incloure els destinataris: Per a, Cc, o Cco';
$lang['email_send_failure_phpmail'] = 'Impossible enviar correu electrònic utilitzant PHP mail(). Potser el teu servidor no està configurat per enviar correus mitjançant aquest mètode.';
$lang['email_send_failure_sendmail'] = 'Impossible enviar correu electrònic utilitzant PHP Sendmail. Potser el teu servidor no està configurat per enviar correus mitjançant aquest mètode.';
$lang['email_send_failure_smtp'] = 'Impossible enviar correu electrònic utilitzant PHP SMTP. Potser el teu servidor no està configurat per enviar correus mitjançant aquest mètode.';
$lang['email_sent'] = 'El correu electrònic s\'ha enviat correctament fent servir el protocol: %s';
$lang['email_no_socket'] = 'Impossible obrir un socket a Sendmail. Per favor comprova la configuració.';
$lang['email_no_hostname'] = 'No has especificat un nom d\'equip SMTP';
$lang['email_smtp_error'] = 'S\'ha trobat el següent error SMTP: %s';
$lang['email_no_smtp_unpw'] = 'Error: Has d\'assignar un nom d\'usuari i una contrassenya SMTP.';
$lang['email_failed_smtp_login'] = 'Error a l\'enviar l\'ordre AUTH LOGIN. Error: %s';
$lang['email_smtp_auth_un'] = 'Error al autenticar el nom d\'usuari. Error: %s';
$lang['email_smtp_auth_pw'] = 'Error al autenticar la contrassenya. Error: %s';
$lang['email_smtp_data_failure'] = 'Impossible enviar les dades: %s';
$lang['email_exit_status'] = 'Codi d\'estat de sortida: %s';
