<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author CodeIgniter community
 * @author Peter Denk
 * @copyright Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license http://opensource.org/licenses/MIT MIT License
 * @link https://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['db_invalid_connection_str']	= 'Kunde inte fastställa databasens inställningar utifrån angiven anslutnings-sträng.';
$lang['db_unable_to_connect']		= 'Kan inte ansluta till databasservern med aktuella inställningar.';
$lang['db_unable_to_select']		= 'Kan inte välja den angivna databasen: %s';
$lang['db_unable_to_create']		= 'Kan inte skapa den angivna databasen: %s';
$lang['db_invalid_query']		= 'Frågan är inte giltig.';
$lang['db_must_set_table']		= 'Tabell måste anges för att kunna utföra frågan.';
$lang['db_must_use_set']		= 'För att uppdatera en post måste metoden "set" användas.';
$lang['db_must_use_index']		= 'Ett passande index måste anges för att kunna uppdatera flera rader samtidigt.';
$lang['db_batch_missing_index']		= 'En eller flera rader som skulle uppdateras samtidigt saknar det angivna indexet.';
$lang['db_must_use_where']		= 'Uppdatering utan urval tillåts inte.';
$lang['db_del_must_use_where']		= 'Borttagning utan urval tillåts inte.';
$lang['db_field_param_missing']		= 'Tabellnamnet måste anges som parameter för att kunna hämta fält.';
$lang['db_unsupported_function']	= 'Databastypen saknar stöd för funktionen.';
$lang['db_transaction_failure']		= 'Transaktionen misslyckades. Tillbakarullning utförd.';
$lang['db_unable_to_drop']		= 'Kunde inte ta bort databasen.';
$lang['db_unsupported_feature']		= 'Databastypen saknar stöd för funktionaliteten.';
$lang['db_unsupported_compression']	= 'Servern stöder inte valt komprimeringsformat.';
$lang['db_filepath_error']		= 'Kunde inte skriva data till angiven sökväg.';
$lang['db_invalid_cache_path']		= 'Sökvägen för buffertlagring är ogiltig eller skrivskyddad .';
$lang['db_table_name_required']		= 'Kommandot kräver ett tabellnamn.';
$lang['db_column_name_required']	= 'Kommandot kräver ett kolumnnamn.';
$lang['db_column_definition_required']	= 'Kommandot kräver en definition av kolumnen.';
$lang['db_unable_to_set_charset']	= 'Kunde inte välja teckenuppsättning för anslutningen : %s';
$lang['db_error_heading']		= 'Ett databasfel uppstod';
