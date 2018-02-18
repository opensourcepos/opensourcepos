<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @copyright	Copyright (c) 2014-2018, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['db_invalid_connection_str'] = 'Die Datenbank-Einstellungen sind anhand der vorliegenden Verbindungsdaten nicht zu ermitteln.';
$lang['db_unable_to_connect'] = 'Mit den vorliegenden Einstellungen ist keine Verbindung zum Datenbank-Server möglich.';
$lang['db_unable_to_select'] = 'Die folgende Datenbank kann nicht gewählt werden: %s';
$lang['db_unable_to_create'] = 'Die folgende Datenbank kann nicht erstellt werden: %s';
$lang['db_invalid_query'] = 'Die vorliegende SQL-Abfrage ist ungültig.';
$lang['db_must_set_table'] = 'Sie müssen die Tabelle der Datenbank für Ihre Abfrage angegeben.';
$lang['db_must_use_set'] = 'Zum Aktualisieren eines Eintrages müssen Sie die "set" Methode verwenden.';
$lang['db_must_use_index'] = 'Für eine Stapelaktualisierung müssen Sie einen passenden Index bestimmen.';
$lang['db_batch_missing_index'] = 'Eine oder mehrere angegebene Zeilen für eine Stapelaktualisierung haben einen fehlenden Index.';
$lang['db_must_use_where'] = 'Aktualisierungen sind nicht erlaubt, solange Sie nicht eine "where" Bedingung enthalten.';
$lang['db_del_must_use_where'] = 'Löschungen sind nicht erlaubt, solange Sie nicht eine Bedingung wie "where" oder "like" enthalten.';
$lang['db_field_param_missing'] = 'Um Felder abzufragen benötigen Sie den Namen der Tabelle als Parameter.';
$lang['db_unsupported_function'] = 'Die verwendete Datenbank-Plattform unterstützt diese Funktion nicht.';
$lang['db_transaction_failure'] = 'Transaktions-Fehler: Veränderungen wurden zurückgesetzt.';
$lang['db_unable_to_drop'] = 'Die angegebene Datenbank kann nicht gelöscht werden.';
$lang['db_unsupported_feature'] = 'Ihre Datenbank-Plattform unterstützt diese Funktion nicht.';
$lang['db_unsupported_compression'] = 'Das angegebene Format für die Daten-Komprimierung wird auf Ihrem Server nicht unterstützt.';
$lang['db_filepath_error'] = 'Die Daten konnten nicht in das angegebene Verzeichnis geschrieben werden.';
$lang['db_invalid_cache_path'] = 'Der angegebene Pfad für den Cache ist ungültig oder nicht beschreibbar.';
$lang['db_table_name_required'] = 'Ein Tabellenname wird für diesen Vorgang benötigt.';
$lang['db_column_name_required'] = 'Ein Spaltenname wird für diesen Vorgang benötigt.';
$lang['db_column_definition_required'] = 'Eine Spalten-Definition wird für diesen Vorgang benötigt.';
$lang['db_unable_to_set_charset'] = 'Der Verbindungs-Zeichensatz konnte nicht gesetzt werden: %s';
$lang['db_error_heading'] = 'Es ist ein Datenbank-Fehler aufgetreten.';
