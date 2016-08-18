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

$lang['db_invalid_connection_str'] = 'Không thể xác định các giá trị bạn chỉnh trên cơ sở dữ liệu.';
$lang['db_unable_to_connect'] = 'Không thể kết nối tới cơ sở dữ liệu (Kiểm tra cấu hình).';
$lang['db_unable_to_select'] = 'Không thể chọn cơ sở dữ liệu: %s';
$lang['db_unable_to_create'] = 'Không thể tạo cơ sở dữ liệu: %s';
$lang['db_invalid_query'] = 'Truy vấn không hợp lệ.'; 
$lang['db_must_set_table'] = 'Bạn phải thiết lập cơ sở dữ liệu để truy vấn.';
$lang['db_must_use_set'] = 'Bạn phải sử dụng phương thức "set" để cập nhật một mục.';
$lang['db_must_use_index'] = 'Bạn phải xác định chỉ số phù hợp để cập nhật hàng loạt.';
$lang['db_batch_missing_index'] = 'Một hoặc nhiều hàng đang cập nhật thiếu chỉ số theo qui định.';
$lang['db_must_use_where'] = 'Câp nhật không cho phép trừ khi chúng có "Where" trong câu lệnh.';
$lang['db_del_must_use_where'] = 'Không được phép XÓA trừ khi trong câu lệnh có chứa "where" hoặc "like".';
$lang['db_field_param_missing'] = 'Để lấy các một trường(fields) yêu cầu tên của bảng (table) là một tham số.';
$lang['db_unsupported_function'] = 'Tính năng này không tồn tại trong cở sở dữ liệu bạn đang làm việc.';
$lang['db_transaction_failure'] = 'Thất bại. Đang quay lại các bước...';
$lang['db_unable_to_drop'] = 'Không thể DROP cơ sở dữ liệu được chọn.';
$lang['db_unsupported_feature'] = 'Cơ sở dữ liệu (database) không hỗ trợ các tính năng này.';
$lang['db_unsupported_compression'] = 'Server không hỗ trợ các định dạng file nén này.';
$lang['db_filepath_error'] = 'Không thể ghi dữ liệu vào đường dẫn này.';
$lang['db_invalid_cache_path'] = 'Sai đường dẫn CACHE hoặc thư mục không cho phép ghi (vui lòng CHMOD 755 hoặc 777).';
$lang['db_table_name_required'] = 'Thiếu tên BẢNG (Bắt buộc).';
$lang['db_column_name_required'] = 'Bạn phải ĐỊNH NGHĨA tên cột.';
$lang['db_column_definition_required'] = 'Thiếu tên CỘT (Bắt buộc).';
$lang['db_unable_to_set_charset'] = 'Không thể cài đặt kết nối với ký tự: %s';
$lang['db_error_heading'] = 'Lỗi cơ sở dữ liệu.';
