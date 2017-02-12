<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @author	Kim Chanthoeun
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['email_must_be_array']            = 'អ៊ីម៉ែល validation method ត្រូវតែជា​ array';
$lang['email_invalid_address']          = 'អាសយដ្ឋានអ៊ីមែលមិនត្រឹមត្រូវ: %s';
$lang['email_attachment_missing']       = 'មិនអាចកំណត់ទីតាំងឯកសារភ្ជាប់អ៊ីម៉ែលដូចខាងក្រោមតាម: %s';
$lang['email_attachment_unreadable']    = 'មិនអាចបើកឯកសារភ្ជាប់នេះ: %s';
$lang['email_no_from']			= 'មិនអាចផ្ញើអ៊ីមែលដោយមិនមាន​អ្នកផ្ញើរ "From"។';
$lang['email_no_recipients']            = 'អ្នកត្រូវតែរួមបញ្ចូលទាំងអ្នកទទួល: To, Cc, or Bcc';
$lang['email_send_failure_phpmail']     = 'មិនអាចផ្ញើអ៊ីមែលដោយប្រើ mail() របស់ PHP។​ ម៉ាស៊ីនរបស់អ្នកមិនបានកំណត់លក្ខ័ន​ត្រឹម​ត្រូវ​ដើម្បីផ្ញើសំបុត្រដោយប្រើវិធីសាស្រ្តនេះ។';
$lang['email_send_failure_sendmail']    = 'មិនអាចផ្ញើអ៊ីមែលដោយប្រើ Sendmail របស់ PHP។​ ម៉ាស៊ីនរបស់អ្នកមិនបានកំណត់លក្ខ័ន​ត្រឹម​ត្រូវ​ដើម្បីផ្ញើសំបុត្រដោយប្រើវិធីសាស្រ្តនេះ។';
$lang['email_send_failure_smtp']        = 'មិនអាចផ្ញើអ៊ីមែលដោយប្រើ SMTP របស់ PHP ។ ម៉ាស៊ីនរបស់អ្នកមិនបានកំណត់លក្ខ័ន​ត្រឹម​ត្រូវ​ដើម្បីផ្ញើសំបុត្រដោយប្រើវិធីសាស្រ្តនេះ។';
$lang['email_sent']                     = 'សាររបស់អ្នកត្រូវបានផ្ញើដោយជោគជ័យដោយប្រើ protocol: %s';
$lang['email_no_socket']                = 'មិនអាចបើកផ្លូវដើម្បីផ្ញើអ៊ីមែល។ សូមពិនិត្យមើលលក្ខ័ណ​ដែលបាន​កំណត់។';
$lang['email_no_hostname']              = 'អ្នកមិនបានបញ្ជាក់ឈ្មោះម៉ាស៊ីន SMTP។';
$lang['email_smtp_error']               = 'ជួបប្រទះកំហុស SMTP ដូចជា: %s';
$lang['email_no_smtp_unpw']             = 'កំហុស: អ្នកត្រូវតែផ្ដល់ឈ្មោះអ្នកប្រើនិងពាក្យសម្ងាត់របស់ SMTP។';
$lang['email_failed_smtp_login']        = 'បរាជ័យក្នុងការបញ្ជូន AUTH LOGIN command។ កំហុស: %s';
$lang['email_smtp_auth_un']             = 'បរាជ័យក្នុងការផ្ទៀងផ្ទាត់ឈ្មោះអ្នកប្រើ។ កំហុស: %s';
$lang['email_smtp_auth_pw']             = 'បរាជ័យក្នុងការផ្ទៀងផ្ទាត់ពាក្យសម្ងាត់។ កំហុស​: %s';
$lang['email_smtp_data_failure']        = 'មិន​អាច​ផ្ញើ​ទិន្នន័យ : %s នេះ។';
$lang['email_exit_status']              = 'កូដស្ថានភាពចេញ: %s';
