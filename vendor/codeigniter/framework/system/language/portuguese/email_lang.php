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

$lang['email_must_be_array'] = 'É necessário passar um array ao método de validação de email.';
$lang['email_invalid_address'] = 'Endereço de email inválido: %s';
$lang['email_attachment_missing'] = 'Incapaz de encontrar o seguinte anexo de email: %s';
$lang['email_attachment_unreadable'] = 'Incapaz de abrir este anexo: %s';
$lang['email_no_from'] = 'Não é possível enviar o email sem definir o cabeçalho "From".';
$lang['email_no_recipients'] = 'Tem que definir os destinatários: To, Cc, ou Bcc';
$lang['email_send_failure_phpmail'] = 'Incapaz de enviar o email através do PHP mail(). O seu servidor pode não estar configurado para enviar mails usando este método.';
$lang['email_send_failure_sendmail'] = 'Incapaz de enviar o email através do PHP Sendmail. O seu servidor pode não estar configurado para enviar mails usando este método.';
$lang['email_send_failure_smtp'] = 'Incapaz de enviar o email através do PHP SMTP. O seu servidor pode não estar configurado para enviar mails usando este método.';
$lang['email_sent'] = 'A sua mensagem foi enviado com sucesso através do seguinte protocolo: %s';
$lang['email_no_socket'] = 'Incapaz de abrir um socket para o Sendmail. Por favor, confira as definições.';
$lang['email_no_hostname'] = 'Não especificou o hostname do SMTP.';
$lang['email_smtp_error'] = 'Foi encontrado o seguinte erro SMTP: %s';
$lang['email_no_smtp_unpw'] = 'Erro: Tem que definir um username e passowrd para o SMTP.';
$lang['email_failed_smtp_login'] = 'Falha no envio do comando AUTH LOGIN. Erro: %s';
$lang['email_smtp_auth_un'] = 'Falha na autenticação de username. Erro: %s';
$lang['email_smtp_auth_pw'] = 'Falha na autenticação da password. Erro: %s';
$lang['email_smtp_data_failure'] = 'Incapaz de enviar dados: %s';
$lang['email_exit_status'] = 'Código do estado de saída: %s';
