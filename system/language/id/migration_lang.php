<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author CodeIgniter community
 * @author Mutasim Ridlo, S.Kom
 * @copyright Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license http://opensource.org/licenses/MIT MIT License
 * @link http://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['migration_none_found'] = 'Tidak ada migrasi ditemukan.';
$lang['migration_not_found'] = 'Tidak ada migrasi yang dapat ditemukan dengan nomor versi: %s.';
$lang['migration_sequence_gap'] = 'Ada kesenjangan dalam urutan migrasi dekat nomor versi: %s.';
$lang['migration_multiple_version'] = 'Ada beberapa migrasi dengan nomor versi yang sama: %s.';
$lang['migration_class_doesnt_exist'] = 'Kelas migrasi "%s" tidak dapat ditemukan.';
$lang['migration_missing_up_method'] = 'Kelas migrasi "%s" kehilangan metode "up".';
$lang['migration_missing_down_method'] = 'Kelas migrasi "%s" kehilangan metode "down".';
$lang['migration_invalid_filename'] = 'Migrasi "%s" memiliki nama berkas yang tidak sah.';