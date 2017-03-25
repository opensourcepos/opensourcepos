<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2017, British Columbia Institute of Technology
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
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['email_must_be_array'] = 'Metoda za validacijo e-pošte zahteva en array.';
$lang['email_invalid_address'] = 'Neveljavni naslov e-pošte: %s';
$lang['email_attachment_missing'] = 'Ni možno najditi naslednje priloge: %s';
$lang['email_attachment_unreadable'] = 'Ni možno odpreti priloge: %s';
$lang['email_no_from'] = 'Ni možno poslati pošte brez "From" naslova.';
$lang['email_no_recipients'] = 'Morate vključiti prejemnike: To, Cc, or Bcc';
$lang['email_send_failure_phpmail'] = 'Ni možno poslati e-pošte z uporabo PHP mail(). Možno da vaš strežnik ni pravilno nameščen za uporabo te metode.';
$lang['email_send_failure_sendmail'] = 'Ni možno poslati e-pošte z uporabo PHP Sendmail. Možno da vaš strežnik ni pravilno nameščen za uporabo te metode.';
$lang['email_send_failure_smtp'] = 'Ni možno poslati e-pošte z uporabo PHP SMTP. Možno da vaš strežnik ni pravilno nameščen za uporabo te metode.';
$lang['email_sent'] = 'Vaše sporočilo je bilo uspešno poslano z naslednjim protokolom: %s';
$lang['email_no_socket'] = 'Ni možno odpreti socket za Sendmail. Preverite nastavitve.';
$lang['email_no_hostname'] = 'Niste določili SMTP strežnika.';
$lang['email_smtp_error'] = 'Naslednja SMTP napaka se je pojavlja: %s';
$lang['email_no_smtp_unpw'] = 'Napaka: Morate določiti SMTP uporabnika in geslo.';
$lang['email_failed_smtp_login'] = 'Pošiljanje AUTH LOGIN komande ni uspelo. Napaka: %s';
$lang['email_smtp_auth_un'] = 'Neuspešna avtentikacija uporabnika. Napaka: %s';
$lang['email_smtp_auth_pw'] = 'Neuspešna avtentikacija gesla. Napaka: %s';
$lang['email_smtp_data_failure'] = 'Ni možno poslati podatkov: %s';
$lang['email_exit_status'] = 'Izhodna statusna koda: %s';
