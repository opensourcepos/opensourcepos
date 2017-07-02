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
