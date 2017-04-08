<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author CodeIgniter community
 * @author Mutasim Ridlo, S.Kom
 * @copyright Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license http://opensource.org/licenses/MIT MIT License
 * @link https://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['email_must_be_array'] = 'Metode validasi email harus melewati sebuah array.';
$lang['email_invalid_address'] = 'Alamat email tidak sah: %s';
$lang['email_attachment_missing'] = 'Tidak dapat menemukan lampiran email berikut: %s';
$lang['email_attachment_unreadable'] = 'Tidak dapat membuka lampiran ini: %s';
$lang['email_no_from'] = 'Tidak dapat mengirim email tanpa kepala "Dari".';
$lang['email_no_recipients'] = 'Anda harus menyertakan penerima: Kepada, CC, atau BCC';
$lang['email_send_failure_phpmail'] = 'Tidak dapat mengirim email menggunakan PHP mail(). Server Anda mungkin tidak dikonfigurasi untuk mengirim email menggunakan metode ini.';
$lang['email_send_failure_sendmail'] = 'Tidak dapat mengirim email menggunakan PHP Sendmail. Server Anda mungkin tidak dikonfigurasi untuk mengirim email menggunakan metode ini.';
$lang['email_send_failure_smtp'] = 'Tidak dapat mengirim email menggunakan PHP SMTP. Server Anda mungkin tidak dikonfigurasi untuk mengirim email menggunakan metode ini.';
$lang['email_sent'] = 'Pesan Anda telah berhasil dikirim menggunakan protokol berikut: %s';
$lang['email_no_socket'] = 'Tidak dapat membuka socket untuk Sendmail. Silakan periksa pengaturan.';
$lang['email_no_hostname'] = 'Anda tidak menentukan nama host SMTP.';
$lang['email_smtp_error'] = 'Berikut kesalahan SMTP ditemui: %s';
$lang['email_no_smtp_unpw'] = 'Kesalahan: Anda harus menetapkan nama pengguna dan password SMTP.';
$lang['email_failed_smtp_login'] = 'Gagal mengirim perintah AUTH LOGIN. Kesalahan: %s';
$lang['email_smtp_auth_un'] = 'Gagal untuk mengotentikasi nama pengguna. Kesalahan: %s';
$lang['email_smtp_auth_pw'] = 'Gagal untuk mengotentikasi password. Kesalahan: %s';
$lang['email_smtp_data_failure'] = 'Tidak dapat mengirim data: %s';
$lang['email_exit_status'] = 'Kode status keluar: %s';
