<?php
/**
* System messages translation for CodeIgniter(tm)
*
* @author	CodeIgniter community
* @copyright	Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
* @copyright	Pieter Krul
* @license	http://opensource.org/licenses/MIT MIT License
* @link	http://codeigniter.com
*/
defined('BASEPATH') OR exit('Directe toegang tot scripts is niet toegestaan');
$lang['email_must_be_array'] = 'ईमेल सत्यापन विधि को  सरणी (array) भेजी जानी चाहिए।';
$lang['email_invalid_address'] = 'अवैध ईमेल पता है:% s';
$lang['email_attachment_missing'] = '% S: निम्नलिखित ईमेल संलग्नक (अटैचमेंट) पता लगाने में असमर्थ';
$lang['email_attachment_unreadable'] = 'अटैचमेंट खोलने के  असमर्थ :% s';
$lang['email_no_from'] = 'From हेडर के बिना मेल नहीं भेज सकते हैं।';
$lang['email_no_recipients'] = 'To, CC, BCC  प्राप्तकर्ताओं को शामिल करना जरुरी है ';
$lang['email_send_failure_phpmail'] = 'PHP mail() का उपयोग करके ईमेल  भेजने में असमर्थ। आपका सर्वर इस पद्धति का उपयोग करके मेल भेजने के लिए शायद  कॉन्फ़िगर नहीं किया गया है';
$lang['email_send_failure_sendmail'] = 'PHP sendmail का उपयोग करके ईमेल  भेजने में असमर्थ। आपका सर्वर इस पद्धति का उपयोग करके मेल भेजने के लिए शायद  कॉन्फ़िगर नहीं किया गया है';
$lang['email_send_failure_smtp'] = 'PHP SMTP  का उपयोग करके ईमेल  भेजने में असमर्थ। आपका सर्वर इस पद्धति का उपयोग करके मेल भेजने के लिए शायद  कॉन्फ़िगर नहीं किया गया है';
$lang['email_sent'] = 'आपका संदेश सफलतापूर्वक दिए गए प्रोटोकॉल का उपयोग करके भेज दिया गया है:% s';
$lang['email_no_socket'] = 'सेंडमेल करने के लिए सॉकेट खोलने में असमर्थ। सेटिंग्स की जाँच करें।';
$lang['email_no_hostname'] = 'आपने SMTP होस्टनेम  निर्दिष्ट नहीं किया है';
$lang['email_smtp_error'] = 'निम्नलिखित एसएमटीपी त्रुटि आई है:% s';
$lang['email_no_smtp_unpw'] = 'त्रुटि: एसएमटीपी यूज़रनेम और पासवर्ड प्रदान करना जरुरी है';
$lang['email_failed_smtp_login'] = 'Auth लॉग इन कमांड भेजने में विफल। त्रुटि:% s';
$lang['email_smtp_auth_un'] = 'उपयोगकर्ता नाम को प्रमाणित करने में विफल। त्रुटि:% s';
$lang['email_smtp_auth_pw'] = 'पासवर्ड प्रमाणित करने में विफल। त्रुटि:% s';
$lang['email_smtp_data_failure'] = 'डेटा भेजने के लिए असमर्थ : % s';
$lang['email_exit_status'] = 'एग्जिट स्थिति कोड है:% s';
