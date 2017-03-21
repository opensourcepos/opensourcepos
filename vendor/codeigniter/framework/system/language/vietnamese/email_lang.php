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

$lang['email_must_be_array'] = 'Phương thức xác thực email phải được truyền qua một mảng.';
$lang['email_invalid_address'] = 'Địa chỉ email hợp lệ (ví dụ: name@example.com): %s';
$lang['email_attachment_missing'] = 'Không thể xác định (Vị trí) các tập tin đính kèm: %s';
$lang['email_attachment_unreadable'] = 'Không thể mở tập tin đính kèm: %s';
$lang['email_no_from'] = 'Không thể gửi thư không có header "From".';
$lang['email_no_recipients'] = 'Phải ghi người nhận Email: To, Cc, or Bcc';
$lang['email_send_failure_phpmail'] = 'Không thể gửi EMAIL sử dụng PHP mail(). Server không hỗ trợ phương thức này.';
$lang['email_send_failure_sendmail'] = 'Không thể gửi EMAIL sử dụng PHP Sendmail. Server không hỗ trợ phương thức này.';
$lang['email_send_failure_smtp'] = 'Không thể gửi EMAIL sử dụng PHP SMTP. Server không hỗ trợ phương thức này.';
$lang['email_sent'] = 'Tin nhắn của bạn đã được gửi thành công sử dụng phương thức sau: %s';
$lang['email_no_socket'] = 'Không thể mở socket để Sendmail. Kiểm tra lại cài đặt.';
$lang['email_no_hostname'] = 'Bạn chưa chỉ định một tên máy chủ SMTP.';
$lang['email_smtp_error'] = 'Phát hiện lỗi SMTP: %s';
$lang['email_no_smtp_unpw'] = 'Lỗi: Bạn phải ghi chính xác tên người dùng và mật khẩu SMTP.';
$lang['email_failed_smtp_login'] = 'Không thể gửi lệnh AUTH LOGIN. Lỗi: %s';
$lang['email_smtp_auth_un'] = 'Xác thực tên người dùng thất bại. Lỗi: %s';
$lang['email_smtp_auth_pw'] = 'Xác thực mật khẩu thất bại. Lỗi: %s';
$lang['email_smtp_data_failure'] = 'Không thể gửi dữ liệu: %s';
$lang['email_exit_status'] = 'Mã trạng thái thoát: %s';
