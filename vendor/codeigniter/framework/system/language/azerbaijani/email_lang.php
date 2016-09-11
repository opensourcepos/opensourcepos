<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @copyright	Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['email_must_be_array']            = 'Email doğrulama metoduna massiv verilməlidir.';
$lang['email_invalid_address']          = 'Səhv email ünvanı: %s';
$lang['email_attachment_missing']       = 'Email fayl əlavəsi tapılmır: %s';
$lang['email_attachment_unreadable']    = 'Email fayl əlavəsi açılmır: %s';
$lang['email_no_from']                  = '"From" başlığı olmadan email göndərmək olmaz.';
$lang['email_no_recipients']            = 'Alıcıları yazmalısınız: To, Cc, or Bcc';
$lang['email_send_failure_phpmail']     = 'PHP mail() funksiyasi ilə email göndərilmir. Serveriniz bu metod ilə email göndərmək üçün tənzimlənməmiş ola bilər.';
$lang['email_send_failure_sendmail']    = 'PHP Sendmail ilə email göndərilmir. Serveriniz bu metod ilə email göndərmək üçün tənzimlənməmiş ola bilər.';
$lang['email_send_failure_smtp']        = 'PHP SMTP ilə email göndərilmir. Serveriniz bu metod ilə email göndərmək üçün tənzimlənməmiş ola bilər.';
$lang['email_sent']                     = 'Mesajınız %s protokolu ilə müvəffəqiyyətlə göndərildi.';
$lang['email_no_socket']                = 'Sendmail soketi açılmır. Lütfən tənzimləmələri yoxlayın.';
$lang['email_no_hostname']              = 'SMTP server adi yazmalısız.';
$lang['email_smtp_error']               = 'SMTP səhvi: %s';
$lang['email_no_smtp_unpw']             = 'Səhv: SMTP istifadəçi adı və şifrəsi yazılmalıdır.';
$lang['email_failed_smtp_login']        = 'AUTH LOGIN əmri göndərilmədi. Səhv: %s';
$lang['email_smtp_auth_un']             = 'İstifadəçi adı düzgün deyil. Səhv: %s';
$lang['email_smtp_auth_pw']             = 'Şifrə düzgün deyil. Səhv: %s';
$lang['email_smtp_data_failure']        = 'Məlümatlar göndərilmədi: %s';
$lang['email_exit_status']              = 'Çıxış vəziyyət kodu: %s';
