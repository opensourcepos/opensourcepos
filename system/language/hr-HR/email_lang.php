<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('Nije dozvoljen direktan pristup');

$lang['email_must_be_array'] = 'Provjera e-mail-a mora proći red.';
$lang['email_invalid_address'] = 'Neispravna e-mail adresa: %s';
$lang['email_attachment_missing'] = 'Ne mogu odrediti privitak: %s';
$lang['email_attachment_unreadable'] = 'Ne mogu otvoriti ovaj privitak: %s';
$lang['email_no_from'] = 'Ne mogu posalti mail bez "From" zaglavlja.';
$lang['email_no_recipients'] = 'Morate dodati primatelja: To, Cc, ili Bcc';
$lang['email_send_failure_phpmail'] = 'Ne može se poslati e-mai koristeći PHP mail(). Vaš server možda nije namješten za slanje mail ovom metodom.';
$lang['email_send_failure_sendmail'] = 'Ne može se poslati e-mai koristeći PHP Sendmail. Vaš server možda nije namješten za slanje mail ovom metodom.';
$lang['email_send_failure_smtp'] = 'Ne može se poslati e-mai koristeći PHP SMTP. Vaš server možda nije namješten za slanje mail ovom metodom.';
$lang['email_sent'] = 'Vaša je poruka uspješno poslana koristeći protokol: %s';
$lang['email_no_socket'] = 'Ne može se otvoriti port za slanje mail-a. Molim provjerite postavke.';
$lang['email_no_hostname'] = 'Niste odredili SMTP naziv domaćina.';
$lang['email_smtp_error'] = 'Slijedeća SMTP grška se pojavila: %s';
$lang['email_no_smtp_unpw'] = 'Greška: Morate dodijeliti SMTP korisničko ime i lozinku.';
$lang['email_failed_smtp_login'] = 'Greška slanja AUTH LOGIN naredbe. Greška: %s';
$lang['email_smtp_auth_un'] = 'Greška kod provjere korisničkog imena. Greška: %s';
$lang['email_smtp_auth_pw'] = 'Greška kod provjere lozinke. Greška: %s';
$lang['email_smtp_data_failure'] = 'Podaci se ne mogu poslati: %s';
$lang['email_exit_status'] = 'Status izlaznog koda: %s';
