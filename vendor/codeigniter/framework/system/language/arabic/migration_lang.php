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

$lang['migration_none_found'] = "لا يوجد أي عمليات ترحيل.";
$lang['migration_not_found'] = "تعذر العثور على عملية الترحيل هذه.";
$lang['migration_sequence_gap'] = 'هناك فجوة بين تسلسل عمليات الترحيل عند الرقم : %s.';
$lang['migration_multiple_version'] = "هناك عدة عمليات ترحيل لها نفس التعداد: %d.";
$lang['migration_class_doesnt_exist'] = "تعذر العثور على صنف الترحيل \"%s\".";
$lang['migration_missing_up_method'] = "صنيف الترحيل \"%s\" يفتقد إلى دالة 'up'.";
$lang['migration_missing_down_method'] = "صنف الترحيل \"%s\" يفتقد إلى دالة 'down'.";
$lang['migration_invalid_filename'] = "اسم الملف الخاص بالترحيل \"%s\" غير صحيح.";