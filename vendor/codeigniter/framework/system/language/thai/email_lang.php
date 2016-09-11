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

$lang['email_must_be_array'] = 'การตรวจสอบความถูกต้องของอีเมลจะรับค่าเป็นอาร์เรย์';
$lang['email_invalid_address'] = 'รูปแบบอีเมลไม่ถูกต้อง: %s';
$lang['email_attachment_missing'] = 'ไม่พบเอกสารแนบอีเมลต่อไปนี้:% s';
$lang['email_attachment_unreadable'] = 'ไม่สามารถเปิดเอกสารแนบนี้ได้: %s';
$lang['email_no_from'] = 'ไม่สามารถส่งอีเมลที่ไม่ระบุอีเมลของผู้ส่ง';
$lang['email_no_recipients'] = 'คุณต้องระบุผู้รับต่างๆเหล่านี้ด้วย: To, Cc หรือ Bcc';
$lang['email_send_failure_phpmail'] = 'ไม่สามารถส่งอีเมลด้วยฟังก์ชั่น PHP mail(). เซอร์เวอร์ของคุณอาจไม่ได้รับการตั้งค่าให้ทำการส่งอีเมลด้วยวิธีนี้.';
$lang['email_send_failure_sendmail'] = 'ไม่สามารถส่งอีเมลด้วยฟังก์ชั่น PHP Sendmail. เซอร์เวอร์ของคุณอาจไม่ได้รับการตั้งค่าให้ทำการส่งอีเมลด้วยวิธีนี้.';
$lang['email_send_failure_smtp'] = 'ไม่สามารถส่งอีเมลด้วยโปรโตคอล PHP SMTP. เซอร์เวอร์ของคุณอาจไม่ได้รับการตั้งค่าให้ทำการส่งอีเมลด้วยวิธีนี้.';
$lang['email_sent'] = 'ข้อความของคุณถูกส่งออกเรียบร้อยแล้วด้วยโปรโตคอลนี้: %s';
$lang['email_no_socket'] = 'ไม่สามารถทำการเปิด Socket เพื่อส่งอีเมล. กรุณาตรวจสอบการตั้งค่า';
$lang['email_no_hostname'] = 'คุณไม่ได้ระบุ SMTP hostname.';
$lang['email_smtp_error'] = 'เกิดข้อผิดพลาดของ SMTP ดังต่อไหนี้: %s';
$lang['email_no_smtp_unpw'] = 'ผิดพลาด: คุณต้องกำหนดชื่อผู้ใช้งาน และ รหัสผ่าน ของ SMTP';
$lang['email_failed_smtp_login'] = 'กากรส่งคำสั่ง AUTH LOGIN ล้มเหลว. ข้อผิดพลาด: %s';
$lang['email_smtp_auth_un'] = 'การยืนยันผู้ใช้งานล้มเหลว. ข้อผิดพลาด: %s';
$lang['email_smtp_auth_pw'] = 'การยืนยันรหัสผ่านล้มเหลว. ข้อผิดพลาด: %s';
$lang['email_smtp_data_failure'] = 'ไม่สามารถส่งข้อมูล: %s';
$lang['email_exit_status'] = 'จบการทำงานด้วยรหัสสถานะ: %s';
