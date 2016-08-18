<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @copyright	Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @copyright	Pieter Krul
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 */
defined('BASEPATH') OR exit('Directe toegang tot scripts is niet toegestaan');

$lang['db_invalid_connection_str'] = 'Het lukt niet om de instellingen voor de database te kunnen bepalen via de connectiegegevens.';
$lang['db_unable_to_connect'] = 'Het lukt niet om een verbinding naar de database te maken met de opgegeven informatie.';
$lang['db_unable_to_select'] = 'Benaderen van de database is mislukt: %s';
$lang['db_unable_to_create'] = 'Aanmaken van de database is mislukt: %s';
$lang['db_invalid_query'] = 'De aan de database gestelde query is niet juist geformuleerd.';
$lang['db_must_set_table'] = 'Het is verplicht de naam van de tabel uit de database te noemen.';
$lang['db_must_use_set'] = 'De database vindt dat, als je een regel wilt veranderen, het "SET"-commando hiervoor bedoeld is.';
$lang['db_must_use_index'] = 'De database vindt dat de naam van een INDEX genoemd moet voor deze batch-updates.';
$lang['db_batch_missing_index'] = 'De opgegeven INDEX ontbreekt, terwijl er wel regels voor een batch update zijn.';
$lang['db_must_use_where'] = 'Updates zijn niet toegestaan zonder een "WHERE" clausule.';
$lang['db_del_must_use_where'] = 'Verwijderingen zijn niet toegestaan zonder een "WHERE" of "LIKE" clausule.';
$lang['db_field_param_missing'] = 'Om velden op te kunnen halen dient de naam van de tabel ook genoemd te worden.';
$lang['db_unsupported_function'] = 'Die functie wordt door deze versie van de database niet ondersteund.';
$lang['db_transaction_failure'] = 'Transactiefout: Rollback is uitgevoerd.';
$lang['db_unable_to_drop'] = 'Het lukt niet om de opgegeven database te verwijderen van het systeem.';
$lang['db_unsupported_feature'] = 'Deze mogelijkheid wordt niet door het databaseplatform ondersteund.';
$lang['db_unsupported_compression'] = 'De server kent deze compressiemethode niet.';
$lang['db_filepath_error'] = 'Het systeem kan niet schrijven naar het genoemde bestand.';
$lang['db_invalid_cache_path'] = 'Het systeem kan niet schrijven naar dit cachebestand en het is onduidelijk of dit het juiste bestand is.';
$lang['db_table_name_required'] = 'Voor deze actie is de naam van de tabel verplicht.';
$lang['db_column_name_required'] = 'Voor deze actie is de kolomnaam verplicht.';
$lang['db_column_definition_required'] = 'Voor deze actie is het opgeven van een kolomdefinitie verplicht.';
$lang['db_unable_to_set_charset'] = 'Het systeem kan de karakterset voor de clientverbinding niet instellen: %s';
$lang['db_error_heading'] = 'Er is een database fout opgetreden.';