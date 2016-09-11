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

$lang['email_must_be_array'] = 'Xác nhận EMAIL phải được truyền qua một mãng.';
$lang['email_invalid_address'] = 'Email hợp lệ (ex: name@example.com): %s';
$lang['email_attachment_missing'] = 'Không thể xác định (Vị trí) các tập tin đính kém: %s';
$lang['email_attachment_unreadable'] = 'Không thể mở tập tin đính kèm: %s';
$lang['email_no_from'] = 'Không thể gởi thư không có header "From".';
$lang['email_no_recipients'] = 'Phải ghi người nhận Email: To, Cc, or Bcc';
$lang['email_send_failure_phpmail'] = 'Không thể gởi EMAIL sử dụng PHP mail(). Server không hỗ trợ phương thức này.';
$lang['email_send_failure_sendmail'] = 'Không thể gởi EMAIL sử dụng PHP Sendmail. Server không hỗ trợ phương thức này.';
$lang['email_send_failure_smtp'] = 'Không thể gởi EMAIL sử dụng PHP SMTP. Server không hỗ trợ function này.';
$lang['email_sent'] = 'Gởi Email thành công, Phương thức gởi: %s';
$lang['email_no_socket'] = 'Không thể mở socket để Sendmail. Kiểm tra lại cài đặt.';
$lang['email_no_hostname'] = 'Không thể xác định SMTP hostname.';
$lang['email_smtp_error'] = 'Phát hiện lỗi SMTP: %s';
$lang['email_no_smtp_unpw'] = 'Lỗi: Bạn phải ghi chính xác SMTP username và password.';
$lang['email_failed_smtp_login'] = 'Không thể gởi lệnh AUTH LOGIN. Lỗi: %s';
$lang['email_smtp_auth_un'] = 'Không thể xác thực username. Lỗi: %s';
$lang['email_smtp_auth_pw'] = 'Không thể xác thực password. Lỗi: %s';
$lang['email_smtp_data_failure'] = 'Không thể gởi dữ liệu: %s';
$lang['email_exit_status'] = 'Thoát, Mã trạng thái: %s';
