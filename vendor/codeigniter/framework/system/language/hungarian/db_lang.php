<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author		CodeIgniter community
 * @copyright	Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @link		https://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['db_invalid_connection_str']		= 'A megadott karaterláncből nem sikerült megállapítani az adatbázis beállításait.';
$lang['db_unable_to_connect']			= 'Nem sikerült az adatbázishoz kapcsolódni a megadott beállításokkal.';
$lang['db_unable_to_select']			= 'A megadott adatbázis kiválasztása sikertelen: %s';
$lang['db_unable_to_create']			= 'A megadott adatbázis létrehozása sikertelen: %s';
$lang['db_invalid_query']				= 'A megadott lekérdezés nem érvényes.';
$lang['db_must_set_table']				= 'Az adatbázis táblát meg kell adni a lekérdezés futtatásához.';
$lang['db_must_use_set']				= 'A "set" metódust kell használni egy bejegyzés frissítéséhez.';
$lang['db_must_use_index']				= 'A kötegelt frissítéshez egy egyező index megadása szükséges.';
$lang['db_batch_missing_index']			= 'A kötegelt frissítés egy vagy több sorában hiányzik az index megadása.';
$lang['db_must_use_where']				= 'A frissítések csak akkor engedélyezettek, ha tartalmaznak "where" szelekciót.';
$lang['db_del_must_use_where']			= 'A törlések csak akkor engedélyezettek, ha tartalmaznak "where" vagy "like" szelekciót.';
$lang['db_field_param_missing']			= 'A mezők lekéréséhez a tábla nevének paraméterként történő megadása szükséges.';
$lang['db_unsupported_function']		= 'Ez a függvény nem elérhető a használt adatbázis esetén.';
$lang['db_transaction_failure']			= 'Tranzakció hiba, a visszavonás (rollback) megtörtént.';
$lang['db_unable_to_drop']				= 'A megadott adatbázis eldobása sikertelen.';
$lang['db_unsupported_feature']			= 'Ez a szolgáltatás nem elérhető a használt adatbázis esetén.';
$lang['db_unsupported_compression']		= 'A választott fájl tömörítési eljárást nem támogatja a szerver.';
$lang['db_filepath_error']				= 'Nem sikerült adatot írni a megadott könyvtárba.';
$lang['db_invalid_cache_path']			= 'A megadott gyorsítótár könyvtár érvénytelen vagy nem írható.';
$lang['db_table_name_required']			= 'Ehhez a művelethez egy táblanév megadása szükséges.';
$lang['db_column_name_required']		= 'Ehhez a művelethez egy oszlopnév megadása szükséges.';
$lang['db_column_definition_required']	= 'Ehhez a művelethez egy oszlop specifikáció megadása szükséges.';
$lang['db_unable_to_set_charset']		= 'Nem sikerült az adatbázis kapcsolat karakterkódolását beállítani: %s';
$lang['db_error_heading']				= 'Adatbázis hiba';