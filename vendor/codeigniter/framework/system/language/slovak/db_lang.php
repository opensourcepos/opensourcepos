<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author    CodeIgniter community
 * @author    Gabriel Potkány <gadelat+codeigniter@gmail.com>
 * @copyright Copyright (c) 2014-2018, British Columbia Institute of Technology (http://bcit.ca/)
 * @license   http://opensource.org/licenses/MIT MIT License
 * @link      https://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['db_invalid_connection_str']     = 'Nebolo možné zistiť databázové nastavenie podľa vloženého reťazca';
$lang['db_unable_to_connect']          = 'Nepodarilo sa pripojiť k databáze s daným nastavením';
$lang['db_unable_to_select']           = 'Nepodarilo sa vybrať (select) databázu: %s';
$lang['db_unable_to_create']           = 'Nepodarilo sa vytvoriť danú databázu: %s';
$lang['db_invalid_query']              = 'Odoslaná požiadavka nie je platná.';
$lang['db_must_set_table']             = 'Musíte určiť databázovú tabuľku, ktorej sa požiadavka týka.';
$lang['db_must_use_set']               = 'Musíte nastaviť metódu "set" pre úpravu záznamu.';
$lang['db_must_use_index']             = 'Je nutné určiť párový index pre dávkový update).';
$lang['db_batch_missing_index']        = 'Pre jeden alebo viac daných riadkov určených k dávkovému updatu chýba špecifikovaný index.';
$lang['db_must_use_where']             = 'Nie je možné upravovať záznamy bez podmienky "where".';
$lang['db_del_must_use_where']         = 'Nie je možné mazať záznamy bez podmienky "where".';
$lang['db_field_param_missing']        = 'Spracovanie polí vyžaduje ako parameter názov tabuľky.';
$lang['db_unsupported_function']       = 'Táto funkcia nie je dostupná v tomto type databázy.';
$lang['db_transaction_failure']        = 'Chyba transakcie: aplikovaný Rollback';
$lang['db_unable_to_drop']             = 'Nie je možné odstrániť požadovanú databázu.';
$lang['db_unsupported_feature']        = 'Táto vlastnosť nie je dostupná pri aktuálnej platforme.';
$lang['db_unsupported_compression']    = 'Zvolená kompresia súborov nie je podporovaná serverom.';
$lang['db_filepath_error']             = 'Nepodarilo sa zapísať dáta do zadanej cesty.';
$lang['db_invalid_cache_path']         = 'Zadaná cesta pre kešovanie nie je platná alebo do nej nemožno zapisovať.';
$lang['db_table_name_required']        = 'Táto operácia potrebuje názov tabuľky.';
$lang['db_column_name_required']       = 'Táto operácia potrebuje názov stĺpca.';
$lang['db_column_definition_required'] = 'Táto operácia potrebuje definíciu stĺpca.';
$lang['db_unable_to_set_charset']      = 'Nie je možné nastaviť znakovú sadu pripojenia: %s';
$lang['db_error_heading']              = 'Nastala chyba databázy';