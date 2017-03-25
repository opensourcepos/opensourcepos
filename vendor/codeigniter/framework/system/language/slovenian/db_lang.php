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

$lang['db_invalid_connection_str'] = 'Ni možno ugotoviti nastavitve podatkovne baze na podlagi določenega povezovalnega niza';
$lang['db_unable_to_connect'] = 'Ni možno uspostaviti povezave z podatkovno bazo na podlagi poslane nastavitve.';
$lang['db_unable_to_select'] = 'Ni možno izbrati navedene podatkovne baze: %s';
$lang['db_unable_to_create'] = 'Ni možno kreirati navedene podatkovne baze: %s';
$lang['db_invalid_query'] = 'Določena poizvedba ni veljavna.';
$lang['db_must_set_table'] = 'Morate nastaviti tabelo, ki bo v uporabi v svoji poizvedbi.';
$lang['db_must_use_set'] = 'Morate uporabljati metodo "set" za posodabljanje entitete.';
$lang['db_must_use_index'] = 'Morate specificirati indeks za ujemanje za paketne posodobitve.';
$lang['db_batch_missing_index'] = 'Ena ali več določenih vrstic v paketni posodobitvi nima specificiranega indeksa.';
$lang['db_must_use_where'] = 'Posodobitve niso možne razen z uporabo "where" stavka.';
$lang['db_del_must_use_where'] = 'Izbrisi niso možni razen z uporabo "where" ali "like" stavka.';
$lang['db_field_param_missing'] = 'Za prenos polje zahteva ime tabele kot parametra.';
$lang['db_unsupported_function'] = 'Funkcionalnost ni podprta v vaši podatkovni bazi';
$lang['db_transaction_failure'] = 'Neuspešna transakcija: Izveden rollback.';
$lang['db_unable_to_drop'] = 'Ni možno izbrisati navedene podatkovne baze.';
$lang['db_unsupported_feature'] = 'Funkcionalnost ni podprta na vašem platformu podatkovne baze.';
$lang['db_unsupported_compression'] = 'Komprecijski format ni podprt na vašem strežniku.';
$lang['db_filepath_error'] = 'Ni možno pisati podatkov v določeni poti.';
$lang['db_invalid_cache_path'] = 'Predpomnilnik poti ni veljaven ali nima pravice pisanje.';
$lang['db_table_name_required'] = 'Ime tabele je obvezen podatek za to operacijo.';
$lang['db_column_name_required'] = 'Ime stolpca je obvezen podatek za to operacijo.';
$lang['db_column_definition_required'] = 'Definicija stolpca je obvezna za to operacijo.';
$lang['db_unable_to_set_charset'] = 'Ni možno nastaviti črkovnega sistema pri povezavi klienta: %s';
$lang['db_error_heading'] = 'Pojavila se je napaka pri podatkovni bazi';
