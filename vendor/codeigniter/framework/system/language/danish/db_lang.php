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

$lang['db_invalid_connection_str'] = 'Kan ikke fastsætte database indstillingerne baseret på forbindelsen strengen du angav.';
$lang['db_unable_to_connect'] = 'Kan ikke forbinde til din database server udfra de angivne indstillinger.';
$lang['db_unable_to_select'] = 'Kan ikke vælge den angivne database: %s';
$lang['db_unable_to_create'] = 'Kunne ikke oprette den angivne database: %s';
$lang['db_invalid_query'] = 'Forespørgslen du sendte er ikke gyldig.';
$lang['db_must_set_table'] = 'Du skal angive databasetabellen, der skal bruges i din forespørgsel.';
$lang['db_must_use_set'] = 'Du skal anvende "set" metoden for at opdatere en post.';
$lang['db_must_use_index'] = 'Du skal angive et indeks til at matche på for batch opdateringer.';
$lang['db_batch_missing_index'] = 'En eller flere rækker indsendt til batch opdatering mangler det angivne indeks.';
$lang['db_must_use_where'] = 'Opdateringer er ikke tilladt, medmindre de indeholder en "where" klausul.';
$lang['db_del_must_use_where'] = 'Sletning er ikke tilladt, medmindre de indeholder en "where" eller "like" klausul.';
$lang['db_field_param_missing'] = 'For at hente felter kræves navnet på tabellen som en parameter.';
$lang['db_unsupported_function'] = 'Denne funktion er ikke tilgængelig for den database du anvender.';
$lang['db_transaction_failure'] = 'Transaktion fejl: Tilbagerulning udføres';
$lang['db_unable_to_drop'] = 'Kan ikke slette den angivne database.';
$lang['db_unsupported_feature'] = 'Ikke-understøttet funktion på databaseplatformen du anvender.';
$lang['db_unsupported_compression'] = 'Filkompression formattet du har valgt er ikke understøttet af din server.';
$lang['db_filepath_error'] = 'Kan ikke skrive data til filen stien du har angivet.';
$lang['db_invalid_cache_path'] = 'Cache stien du angivet er ikke gyldig eller skrivbar.';
$lang['db_table_name_required'] = 'Et tabelnavn er nødvendig for denne operation.';
$lang['db_column_name_required'] = 'Et kolonnenavn er nødvendig for denne operation.';
$lang['db_column_definition_required'] = 'Der kræves en kolonnedefinition for denne operation.';
$lang['db_unable_to_set_charset'] = 'Kan ikke sætte klientforbindelsens tegnsæt: %s';
$lang['db_error_heading'] = 'Der opstod en Databasefejl';
