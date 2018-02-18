<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @copyright	Copyright (c) 2014-2018, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['email_must_be_array'] = 'மின்னஞ்சல் சரிபார்த்தல் முறை ஒரு வரிசையை செலுத்தியிருக்க வேண்டும்.';
$lang['email_invalid_address'] = '%s இது சரியான மின்னஞல் முகவரி அல்ல.';
$lang['email_attachment_missing'] = 'பின்வரும் மின்னஞ்சல் இணைப்பை கண்டறிய முடியவில்லை : %s';
$lang['email_attachment_unreadable'] = 'மின்னஞ்சல் இணைப்பை திறக்க முடியவில்லை: %s';
$lang['email_no_from'] = '"From" மேற்குறிப்பு இல்லாமல் மின்னஞ்சல் அனுப்ப இயலாது. ';
$lang['email_no_recipients'] = 'பெறுநரை நீங்கள் குறிப்பிட வேண்டும் : To, Cc, or Bcc';
$lang['email_send_failure_phpmail'] = 'PHP mail() பயன்படுத்தி மின்னஞ்சல் அனுப்ப இயலவில்லை. இந்த முறையை பயன்டுத்தி மின்னஞ்சல் அனுப்ப தங்களது சேவை கணினி கட்டமைக்கப்படவில்லை.';
$lang['email_send_failure_sendmail'] = 'PHP Sendmail பயன்படுத்தி மின்னஞ்சல் அனுப்ப இயலவில்லை. இந்த முறையை பயன்டுத்தி மின்னஞ்சல் அனுப்ப தங்களது சேவை கணினி கட்டமைக்கப்படாமல் இருக்கலாம்.';
$lang['email_send_failure_smtp'] = 'PHP SMTP பயன்படுத்தி மின்னஞ்சல் அனுப்ப இயலவில்லை. இந்த முறையை பயன்டுத்தி மின்னஞ்சல் அனுப்ப தங்களது சேவை கணினி கட்டமைக்கப்படாமல் இருக்கலாம்.';
$lang['email_sent'] = 'பின்வரும் நெறிமுறையைப் பயன்படுத்தி தங்களது செய்தி வெற்றிகரமாக அனுப்பப்பட்டுள்ளது : %s';
$lang['email_no_socket'] = 'மின்னஞ்சலை அனுப்ப மின்குதைகுழியை திறக்க முடியவில்லை. தயவுசெய்து கட்டமைப்புகளை சரிபார்க்கவும். ';
$lang['email_no_hostname'] = 'நீங்கள் SMTP hostname ஐ குறிப்பிடவில்லை.';
$lang['email_smtp_error'] = 'பின்வரும்  SMTP வழு ஏற்பட்டது  : %s';
$lang['email_no_smtp_unpw'] = 'வழு: நீங்கள் ஒரு  SMTP பயனர் பெயர் மற்றும் கடவுச்சொல்லை கட்டாயம்  கொடுக்க வேண்டும். ';
$lang['email_failed_smtp_login'] = 'AUTH LOGIN கட்டளையை அனுப்புவதில் தோல்வி. பிழை : %s';
$lang['email_smtp_auth_un'] = 'பயனர்பெயரை அங்கீகரிப்பதில் தோல்வி. பிழை: %s';
$lang['email_smtp_auth_pw'] = 'கடவுச்சொல்லை அங்கீகரிப்பதில் தோல்வி. பிழை: %s';
$lang['email_smtp_data_failure'] = 'தரவை அனுப்ப இயலவில்லை : %s';
$lang['email_exit_status'] = 'வெளியேறு நிலை குறியீடு : %s';
