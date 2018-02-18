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

$lang['db_invalid_connection_str']      = 'Verilen bağlantı dizisi bilgileriyle veri tabanı ayarları belirlenemiyor.';
$lang['db_unable_to_connect']           = 'Verilen ayarlarla veri tabanına bağlantı sağlanamıyor.';
$lang['db_unable_to_select']            = 'Belirtilen veri tabanı seçilemiyor: %s';
$lang['db_unable_to_create']            = 'Belirtilen veri tabanı oluşturulamıyor: %s';
$lang['db_invalid_query']               = 'Verilen sorgu geçersiz.';
$lang['db_must_set_table']              = 'Sorguda kullanılacak tabloyu belirtmelisiniz.';
$lang['db_must_use_set']                = 'Kaydı güncellemek için "set" metodu kullanmalısınız.';
$lang['db_must_use_index']              = 'Toplu güncellemede eşleştirme için bir indeks belirtmelisiniz.';
$lang['db_batch_missing_index']         = 'Toplu güncelleme için verilen bir veya birden fazla kaydın belirtilen indeksi eksik.';
$lang['db_must_use_where']              = '"where" sözcüğü olmayan güncellemelere izin verilmez.';
$lang['db_del_must_use_where']          = '"where" veya "like" sözcüğü olmayan silmelere izin verilmez.';
$lang['db_field_param_missing']         = 'Alanların okunabilmesi için tablo adı parametre olarak verilmeli.';
$lang['db_unsupported_function']        = 'Kullandığınız veri tabanında bu özellik bulunmuyor.';
$lang['db_transaction_failure']         = 'İşlem hatası: Geri alma gerçekleştirildi.';
$lang['db_unable_to_drop']              = 'Belirtilen veri tabanı silinemiyor.';
$lang['db_unsupported_feature']         = 'Kullandığınız veri tabanı ortamında bu özellik desteklenmiyor.';
$lang['db_unsupported_compression']     = 'Seçtiğiniz dosya sıkıştırma formatı sunucu tarafından desteklenmiyor.';
$lang['db_filepath_error']              = 'Veriler belirtilen dosya yoluna yazılamıyor.';
$lang['db_invalid_cache_path']          = 'Belirtilen önbellek yolu geçersiz veya yazılamıyor.';
$lang['db_table_name_required']         = 'Bu işlem için bir tablo adı gerekli.';
$lang['db_column_name_required']        = 'Bu işlem için bir sütun adı gerekli.';
$lang['db_column_definition_required']  = 'Bu işlem için bir sütun tanımlaması gerekli';
$lang['db_unable_to_set_charset']       = 'Kullanıcı bağlantı karakter seti ayarlanamıyor: %s';
$lang['db_error_heading']               = 'Bir Veri Tabanı Hatası Oluştu';
