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

$lang['email_must_be_array'] = "メールアドレスのバリデーションは、配列でデータを渡す必要があります。";
$lang['email_invalid_address'] = 'メールアドレスの形式が違います: %s';
$lang['email_attachment_missing'] = '次のメールの添付が見つかりません: %s';
$lang['email_attachment_unreadable'] = '次の添付ファイルが開けません: %s';
$lang['email_no_from'] = ' "From"ヘッダーがないためメールを送信できません';
$lang['email_no_recipients'] = '宛先 (To,Cc,Bcc) が指定されていません';
$lang['email_send_failure_phpmail'] = 'PHP mail() を使ってメールを送信できません。お使いのサーバでは、PHP mail() でメールを送信できるよう設定されていない可能性があります。';
$lang['email_send_failure_sendmail'] = 'PHP Sendmail を使ってメールを送信できません。お使いのサーバでは、PHP Sendmail でメールを送信できるよう設定されていない可能性があります。';
$lang['email_send_failure_smtp'] = 'PHP SMTP を使ってメールを送信できません。お使いのサーバでは、PHP SMTP でメールを送信できるよう設定されていない可能性があります';
$lang['email_sent'] = 'メッセージは次のプロトコルを使って正常に送信されました: %s';
$lang['email_no_socket'] = 'Sendmail に対しソケットを開くことができません。設定を見直してください。';
$lang['email_no_hostname'] = 'SMTP ホスト名が指定されていません';
$lang['email_smtp_error'] = '次の SMTP エラーが発生しました: %s';
$lang['email_no_smtp_unpw'] = 'エラー: SMTP のユーザ名とパスワードを指定する必要があります';
$lang['email_failed_smtp_login'] = 'AUTH LOGIN コマンドの送信に失敗しました。エラー: %s';
$lang['email_smtp_auth_un'] = 'ユーザ名の認証に失敗しました。エラー: %s';
$lang['email_smtp_auth_pw'] = 'パスワードの認証に失敗しました。エラー: %s';
$lang['email_smtp_data_failure'] = 'データを送信できません: %s';
$lang['email_exit_status'] = '終了ステータスコード: %s';
