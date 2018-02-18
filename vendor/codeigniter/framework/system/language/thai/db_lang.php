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

$lang['db_invalid_connection_str'] = 'ไม่สามารถกำหนดการตั้งค่าฐานข้อมูลโดยใช้ค่าการเชื่อต่อที่กำหนด';
$lang['db_unable_to_connect'] = 'ไม่สามารถเชื่อต่อเซอร์เวอร์ของฐานข้อมูลโดยใช้ค่าที่กำหนด';
$lang['db_unable_to_select'] = 'ไม่สามารถทำการเลือกฐานข้อมูลที่กำหนด: %s';
$lang['db_unable_to_create'] = 'ไม่สมารถสร้างฐานข้อมูลที่กำหนด: %s';
$lang['db_invalid_query'] = 'คำสั่งคิวรี่ที่คุณใช้ไม่ถูกต้อง';
$lang['db_must_set_table'] = 'คุณต้องเลือกตารางฐานข้อมูลที่จะใช้กับคำสั่งคิวรี่ของคุณ';
$lang['db_must_use_set'] = 'คุณต้องใช้คำสั่ง "set" ในการอัพเดทข้อมูล';
$lang['db_must_use_index'] = 'คุณต้องระบุค่าดัชนีเพื่อค้นหาในการอัพเดทแบบหลายรายการ';
$lang['db_batch_missing_index'] = 'ข้อมูลอย่างน้อยหนึ่งรายการไม่มีการระบุดัชนีสำหรับการอัพเดทแบบหลายรายการ';
$lang['db_must_use_where'] = 'ไม่อนุญาตให้อัพเพทข้อมูลหากไม่มีการใช้คำสั่ง where';
$lang['db_del_must_use_where'] = 'ไม่อนุญาตการให้ทำการลบข้อมูลหากไม่มีการใช้คำสั่ง where หรือ like';
$lang['db_field_param_missing'] = 'โปรดระบุชื่อฟิลด์ของตารางในรูปของพารามิเตอร์เพื่ดึงข้อมูล';
$lang['db_unsupported_function'] = 'ฟีเจอร์นี้ไม่สามารถใช้งานได้กับฐานข้อมูลที่คุณกำลังใช้อยู่';
$lang['db_transaction_failure'] = 'Transaction ล้มเหลว: ทำการย้อนกลับแล้ว';
$lang['db_unable_to_drop'] = 'ไม่สมารถลบฐานข้อมูลที่ระบุ';
$lang['db_unsupported_feature'] = 'รูปแบบฐานข้อมูลที่คุณใช้ไม่สนับสนุนการทำงานนี้';
$lang['db_unsupported_compression'] = 'เซอร์เวอร์ของคุณไม่สนับสนุนการบีบอัดไฟล์ตามรูปแบบที่คุณเลือก';
$lang['db_filepath_error'] = 'ไม่สามารถเขียนข้อมูลไปยังไฟล์ตามตำแหน่งที่กำหนด';
$lang['db_invalid_cache_path'] = 'ตำแหน่งของ cache ที่กำหนดไม่ถูกต้องหรือไม่สามารถเขียนได้';
$lang['db_table_name_required'] = 'โปรดระบุชื่อตารางสำหรับการทำงานนั้น';
$lang['db_column_name_required'] = 'โปรดระบุชื่อคอลัมน์สำหรับการทำงานนั้น';
$lang['db_column_definition_required'] = 'โปรดนิยามคอลัมน์สำหรับการทำงานนั้น';
$lang['db_unable_to_set_charset'] = 'ไม่สามารถกำหนดชุดอักขระของการเชื่อต่อลูกข่าย: %s';
$lang['db_error_heading'] = 'เกิดข้อผิดพลาดของฐานข้อมูล';
