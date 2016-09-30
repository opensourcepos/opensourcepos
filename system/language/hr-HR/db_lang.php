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

$lang['db_invalid_connection_str'] = 'Ne mogu odrediti postavke baze na osnovu unesenog stringa za spajanje.';
$lang['db_unable_to_connect'] = 'Ne mogu se spojiit na bazu koriteći postavke provajdera.';
$lang['db_unable_to_select'] = 'Ne mogu odabrati određenu bazu: %s';
$lang['db_unable_to_create'] = 'Ne mogu stvoriti određenu bazu: %s';
$lang['db_invalid_query'] = 'Uneseni upit nije ispravan.';
$lang['db_must_set_table'] = 'Morate postaviti bazu da bi koristili upit.';
$lang['db_must_use_set'] = 'Morate koristiti "set" metodu za ažuriranje unosa.';
$lang['db_must_use_index'] = 'Morate odrediti index koji odgovara seriji ažuriranja.';
$lang['db_batch_missing_index'] = 'Jednom ili više unesenih redova za ažuriranje nedostaje index.';
$lang['db_must_use_where'] = 'Ažuriranje nije dozvoljeno sve dok sadrži "where" riječ.';
$lang['db_del_must_use_where'] = 'Ažuriranje nije dozvoljeno sve dok sadrži "where" ili "like" riječ.';
$lang['db_field_param_missing'] = 'Za dohvat polja potrebno je ime tablice kao parametar.';
$lang['db_unsupported_function'] = 'Ova opcija nije moguća za bazu koju koristite.';
$lang['db_transaction_failure'] = 'Greška u transakciji: Izvršen povrat.';
$lang['db_unable_to_drop'] = 'Nije moguće obrisati navedenu tablicu.';
$lang['db_unsupported_feature'] = 'Koristite nepodržano svojstvo baze.';
$lang['db_unsupported_compression'] = 'Format kompresije koji ste odabrali nije podržan na serveru.';
$lang['db_filepath_error'] = 'NE mogu se upisati podaci u unesenu putanju.';
$lang['db_invalid_cache_path'] = 'Unesena putanje za memoriju nije ispravna ili nije omogućeno pisanje.';
$lang['db_table_name_required'] = 'Naziv tablice je obavezan za tu operaciju.';
$lang['db_column_name_required'] = 'Naziv kolone je obavezna za tu operaciju.';
$lang['db_column_definition_required'] = 'Definica kolone je obavezna za tu operaciju.';
$lang['db_unable_to_set_charset'] = 'Ne mogu postaviti korisnički prikaz znakova: %s';
$lang['db_error_heading'] = 'Pojavila se greška u bazi';
