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
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['email_must_be_array'] = 'El. laiško tikrinimo metodas turi būti perduotas kaip masyvas.';
$lang['email_invalid_address'] = 'Neteisingas el. pašto adresas: %s';
$lang['email_attachment_missing'] = 'Nepavyksta rasti šio el. laiško priedo: %s';
$lang['email_attachment_unreadable'] = 'Nepavyksta atverti šio priedo: %s';
$lang['email_no_from'] = 'Negalima siųsti el. laiško be „Nuo“ („From“) antraštės.';
$lang['email_no_recipients'] = 'Turite nurodyti gavėjus: „Kam“ („To“), „Kopija“ („Cc“), arba „Nematoma kopija“ („Bcc“)';
$lang['email_send_failure_phpmail'] = 'Nepavyksta išsiųsti el. laiško naudojant PHP mail() funkciją. Gali būti, kad serveris nėra konfigūruotas siųsti laiškus naudojant šį metodą.';
$lang['email_send_failure_sendmail'] = 'Nepavyksta išsiųsti el. laiško naudojant PHP Sendmail. Gali būti, kad serveris nėra konfigūruotas siųsti laiškus naudojant šį metodą.';
$lang['email_send_failure_smtp'] = 'Nepavyksta išsiųsti el. laiško naudojant PHP SMTP. Gali būti, kad serveris nėra konfigūruotas siųsti laiškus naudojant šį metodą.';
$lang['email_sent'] = 'Jūsų pranešimas buvo sėkmingai išsiųstas naudojant šį metodą: %s';
$lang['email_no_socket'] = 'Nepavyksta atverti Sendmail lizdo. Prašome patikrinti nustatymus.';
$lang['email_no_hostname'] = 'Nenurodytas SMTP mazgo vardas.';
$lang['email_smtp_error'] = 'Susidurta su šia SMTP klaida: %s';
$lang['email_no_smtp_unpw'] = 'Klaida: turite priskirti SMTP vartotojo vardą ir slaptažodį.';
$lang['email_failed_smtp_login'] = 'Nepavyko išsiųsti AUTH LOGIN komandos. Klaida: %s';
$lang['email_smtp_auth_un'] = 'Nepavyko autentifikuoti vartotojo vardo. Klaida: %s';
$lang['email_smtp_auth_pw'] = 'Nepavyko autentifikuoti slaptažodžio. Klaida: %s';
$lang['email_smtp_data_failure'] = 'Nepavyksta išsiųsti duomenų: %s';
$lang['email_exit_status'] = 'Išėjimo būklės kodas: %s';
