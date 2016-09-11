<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @copyright	Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['db_invalid_connection_str'] = 'Datele conexiunii eronate. Nu s-au putut determina setările bazei de date.';
$lang['db_unable_to_connect'] = 'Conexiunea la baza de date nu se poate efectua utilizând setările furnizate.';
$lang['db_unable_to_select'] = 'Baza de date specificată nu poate fi selectată: %s';
$lang['db_unable_to_create'] = 'Baza de date specificată nu poate fi creată: %s';
$lang['db_invalid_query'] = 'Interogarea trimisă nu este validă.';
$lang['db_must_set_table'] = 'Tabelul bazei de date trebuie setat pentru a putea fi efectuată interogarea.';
$lang['db_must_use_set'] = 'Trebuie să folosiți metoda "set" pentru a actualiza o intrare.';
$lang['db_must_use_index'] = 'Trebuie să specificați un index pentru a corespunde actualizărilor în lot.';
$lang['db_batch_missing_index'] = 'Indexul specificat lipsește din unul sau mai multe rânduri trimise pentru actualizarea în lot.';
$lang['db_must_use_where'] = 'Actualizările nu sunt permise decât dacă conțin o clausă de tip "where".';
$lang['db_del_must_use_where'] = 'Ștergerile nu sunt permise decât dacă conțin o clausă de tip "where" sau "like".';
$lang['db_field_param_missing'] = 'Pentru a prelua câmpuri este necesar numele tabelului ca parametru.';
$lang['db_unsupported_function'] = 'Această opțiune nu este disponibilă pentru baza de date pe care o folosiți.';
$lang['db_transaction_failure'] = 'Tranzacție eșuată: Revenirea a fost realizată.';
$lang['db_unable_to_drop'] = 'Baza de date specificată nu a putut fi "aruncată".';
$lang['db_unsupported_feature'] = 'Platforma bazei de date utilizată nu acceptă această caracteristică.';
$lang['db_unsupported_compression'] = 'Formatul de compresie al fișierului ales nu este suportat de către server.';
$lang['db_filepath_error'] = 'Nu s-au putut scrie date pe calea fișierului specificat.';
$lang['db_invalid_cache_path'] = 'Calea specificată către cache nu este validă sau nu are permisiuni de scriere.';
$lang['db_table_name_required'] = 'Pentru această operațiune este necesar un nume de tabel.';
$lang['db_column_name_required'] = 'Pentru această operațiune este necesar un nume de coloană.';
$lang['db_column_definition_required'] = 'Pentru această operație este necesară o definiție de coloană.';
$lang['db_unable_to_set_charset'] = 'Unable to set client connection character set: %s'; //I must find a proper translation for this
$lang['db_error_heading'] = 'A apărut o eroare la baza de date.';
