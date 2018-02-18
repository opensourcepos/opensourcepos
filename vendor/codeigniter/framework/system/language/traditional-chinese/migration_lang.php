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

$lang['migration_none_found'] = '沒有發現任何遷移';
$lang['migration_not_found'] = '無法根據版本號碼 %s 找到遷移方法';
$lang['migration_sequence_gap'] = '版本遷移存在間隙：%s';
$lang['migration_multiple_version'] = '有多個遷移對應到同一版本號：%s';
$lang['migration_class_doesnt_exist'] = '無法找到遷移類別 "%s"';
$lang['migration_missing_up_method'] = '無法找到遷移類別 "%s" 中的 "up" 方法';
$lang['migration_missing_down_method'] = '無法找到遷移類別 "%s" 中的 " 方法';
$lang['migration_invalid_filename'] = '無效的遷移檔名："%s"';
