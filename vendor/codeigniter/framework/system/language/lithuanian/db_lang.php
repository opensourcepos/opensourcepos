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
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['db_invalid_connection_str'] = 'Pagal Jūsų pateiktą prisijungimo eilutę nepavyksta nustatyti duomenų bazės nustatymų.';
$lang['db_unable_to_connect'] = 'Nepavyksta prisijungti prie Jūsų duomenų bazės serverio naudojant pateiktus nustatymus.';
$lang['db_unable_to_select'] = 'Nepavyksta pasirinkti nurodytos duomenų bazės: %s';
$lang['db_unable_to_create'] = 'Nepavyksta sukurti nurodytos duomenų bazės: %s';
$lang['db_invalid_query'] = 'Pateikta užklausa neteisinga.';
$lang['db_must_set_table'] = 'Turite nurodyti duomenų bazės lentelę, kuri bus naudojama užklausoje.';
$lang['db_must_use_set'] = 'Norėdami atnaujinti įrašą turite naudoti „set“ metodą.';
$lang['db_must_use_index'] = 'Turite nurodyti indeksą, pagal kurį bus atliekamas paketinis atnaujinimas.';
$lang['db_batch_missing_index'] = 'Viena ar daugiau paketiniam atnaujinimui pateiktų eilučių neturi nurodyto indekso.';
$lang['db_must_use_where'] = 'Atnaujinimai neleidžiami, jei nėra nurodoma „where“ išlyga.';
$lang['db_del_must_use_where'] = 'Trynimai neleidžiami jei nėra nurodomos „where“ arba „like“ išlygos.';
$lang['db_field_param_missing'] = 'Norint gauti laukų reikšmes reikia nurodyti lentelės pavadinimą kaip parametrą.';
$lang['db_unsupported_function'] = 'Ši savybė nėra prieinama su duomenų baze, kurią naudojate.';
$lang['db_transaction_failure'] = 'Tranzakcija nepavyko: atkuriami duomenys.';
$lang['db_unable_to_drop'] = 'Nepavyksta ištrinti („drop“) nurodytos duomenų bazės.';
$lang['db_unsupported_feature'] = 'Naudojamoje duomenų bazių platformoje ši savybė nėra palaikoma.';
$lang['db_unsupported_compression'] = 'Failų suspaudimo formatas, kurį pasirinkote, nėra palaikomas serverio.';
$lang['db_filepath_error'] = 'Nepavyksta rašyti duomenų į nurodytą failo kelią.';
$lang['db_invalid_cache_path'] = 'Nurodytas tarpinės atminties („cache“) kelias neteisingas, arba į jį negalima rašyti.';
$lang['db_table_name_required'] = 'Šiai operacijai reikia lentelės pavadinimo.';
$lang['db_column_name_required'] = 'Šiai operacijai reikia stulpelio pavadinimo.';
$lang['db_column_definition_required'] = 'Šiai operacijai reikia stulpelio apibrėžimo.';
$lang['db_unable_to_set_charset'] = 'Nepavyksta nustatyti klientinio prisijungimo koduotės: %s';
$lang['db_error_heading'] = 'Įvyko duomenų bazės klaida';
