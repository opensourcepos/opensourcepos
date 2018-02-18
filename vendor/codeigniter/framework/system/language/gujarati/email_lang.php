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

$lang['email_must_be_array'] = 'આ ઇમેઇલ માન્યતા પદ્ધતિ માટે એક એરે (array)પસાર કરવુ જરૂરી.';
$lang['email_invalid_address'] = 'અમાન્ય ઇમેઇલ સરનામું: %s';
$lang['email_attachment_missing'] = 'નીચેના ઇમેઇલ જોડાણ (attachment) સ્થિત કરવા મા નિષ્ફળતા: %s';
$lang['email_attachment_unreadable'] = 'આ જોડાણ (attachment) ખોલવા માટે અસમર્થ: %s';
$lang['email_no_from'] = 'હેડર "From" વગર કોય મેલ મોકલી નય શકો..';
$lang['email_no_recipients'] = 'આ પ્રાપ્તિકર્તાઓ નો સમાવેશ કરવો જ જોઇએ: To, Cc, અને Bcc';
$lang['email_send_failure_phpmail'] = 'તમારો સર્વર  PHP mail() આ પદ્ધતિનો ઉપયોગ કરીને મેઇલ મોકલવામા અસક્ષમ,  અથવા રૂપરેખાંકિત કરેલ નથી.';
$lang['email_send_failure_sendmail'] = 'તમારો સર્વર  PHP Sendmail આ પદ્ધતિનો ઉપયોગ કરીને મેઇલ મોકલવામા અસક્ષમ,  અથવા રૂપરેખાંકિત કરેલ નથી.';
$lang['email_send_failure_smtp'] = 'તમારો સર્વર  PHP SMTP આ પદ્ધતિનો ઉપયોગ કરીને મેઇલ મોકલવામા અસક્ષમ,  અથવા રૂપરેખાંકિત કરેલ નથી.';
$lang['email_sent'] = 'તમારો સંદેશ સફળતાપૂર્વક નીચેના પ્રમાણે પ્રોટોકોલ ઉપયોગ કરીને મોકલવામાં આવ્યો છે: %s';
$lang['email_no_socket'] = 'Sendmail માટે સોકેટ ખોલવા મા અસમર્થ. સેટિંગ્સ તપાસો.';
$lang['email_no_hostname'] = 'તમે SMTP hostname સ્પષ્ટ કરેલ નથી.';
$lang['email_smtp_error'] = 'નીચેના મુજબ SMTP ભૂલ આવી હતી: %s';
$lang['email_no_smtp_unpw'] = 'ભૂલ: તમારે એક SMTP username નામ અને પાસવર્ડ લખવાનું રહેશે.';
$lang['email_failed_smtp_login'] = 'Auth LOGIN આદેશ મોકલવામાં નિષ્ફળ. ભુલ: %s';
$lang['email_smtp_auth_un'] = 'વપરાશકર્તા નામ (username) અધિકૃત કરવામાં નિષ્ફળ. ભુલ: %s';
$lang['email_smtp_auth_pw'] = 'પાસવર્ડ અધિકૃત કરવામાં નિષ્ફળ. ભુલ: %s';
$lang['email_smtp_data_failure'] = 'માહિતી મોકલવા મા નિષ્ફળ: %s';
$lang['email_exit_status'] = 'એક્ઝીટ સટેટસ કોડ: %s';
