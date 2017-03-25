<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @copyright	Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['db_invalid_connection_str'] = '指定された接続文字列では、データベース設定を特定できません。';
$lang['db_unable_to_connect'] = '指定された設定でデータベースサーバに接続できません。';
$lang['db_unable_to_select'] = '指定されたデータベースを選択できません: %s';
$lang['db_unable_to_create'] = '指定されたデータベースを作成できません: %s';
$lang['db_invalid_query'] = '指定されたクエリは正しくありません。';
$lang['db_must_set_table'] = '指定したクエリを利用するには、データベーステーブルを指定する必要があります。';
$lang['db_must_use_set'] = 'レコードを更新するには、"set" メソッドを使う必要があります。';
$lang['db_must_use_index'] = 'バッチ更新のためのマッチするインデックスを指定する必要があります。';
$lang['db_batch_missing_index'] = 'バッチ更新で指定された 1つ以上の行に対するインデックスがありません。';
$lang['db_must_use_where'] = '"where"句が指定されていないデータベースの更新は許可されません。';
$lang['db_del_must_use_where'] = '"where"もしくは"like"句が指定されていない削除は許可されません。';
$lang['db_field_param_missing'] = 'フィールドを取り出すには、テーブル名をパラメータで指定する必要があります。';
$lang['db_unsupported_function'] = 'この機能はお使いのデータベースでは利用できません。';
$lang['db_transaction_failure'] = 'トランザクションが失敗しました: ロールバックが実行されました。';
$lang['db_unable_to_drop'] = '指定したデータベースを削除できません。';
$lang['db_unsupported_feature'] = 'お使いのデータベースプラットフォームではサポートされない機能です。';
$lang['db_unsupported_compression'] = 'お使いのサーバでは、選択したファイル圧縮フォーマットはサポートされていません。';
$lang['db_filepath_error'] = '指定したファイルパスにデータを書き込むことができませんでした。';
$lang['db_invalid_cache_path'] = '指定したキャッシュパスは正しくないか書き込みできません。';
$lang['db_table_name_required'] = 'この操作にはテーブル名が必要です。';
$lang['db_column_name_required'] = 'この操作にはコラム名が必要です。';
$lang['db_column_definition_required'] = 'この操作にはカランの定義が必要です。';
$lang['db_unable_to_set_charset'] = 'クライアント接続文字エンコードを設定できませんでした: %s';
$lang['db_error_heading'] = 'データベースエラーが発生しました。';
