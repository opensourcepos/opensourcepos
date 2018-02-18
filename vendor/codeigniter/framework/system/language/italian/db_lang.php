<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @author	Stefano Mazzega
 * @copyright	Copyright (c) 2014-2018, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['db_invalid_connection_str'] = 'Impossibile determinare i parametri del database dalla stringa di connessione immessa.';
$lang['db_unable_to_connect'] = 'Impossibile connettersi al database utilizzando i parametri inseriti.';
$lang['db_unable_to_select'] = 'Impossibile selezionare il database specificato: %s';
$lang['db_unable_to_create'] = 'Impossibile creare il database specificato: %s';
$lang['db_invalid_query'] = 'La query immessa non è valida.';
$lang['db_must_set_table'] = 'Impostare il database da utilizzare per la query immessa.';
$lang['db_must_use_set'] = 'Devi utilizzare il metodo "set" per eseguire l\'update del tuo record.';
$lang['db_must_use_index'] = 'Devi specificare un indice per poter effettuare l\'aggiornamento in batch.';
$lang['db_batch_missing_index'] = 'Una o più righe presenti per l\'aggiornamento in batch non contengono l\'indice specificato.';
$lang['db_must_use_where'] = 'Le query di "Update" sono consentite solo se contengono la clausola "where".';
$lang['db_del_must_use_where'] = 'Le query di "Delete" sono consentite solo se contengono la clausola "where".';
$lang['db_field_param_missing'] = 'Per eseguire il fetch dei campi è necessario il nome della tabella passato come parametro.';
$lang['db_unsupported_function'] = 'Funzionalità';
$lang['db_transaction_failure'] = 'Transazione fallita: Rollback eseguito.';
$lang['db_unable_to_drop'] = 'Impossibile eliminare il database selezionato.';
$lang['db_unsupported_feature'] = 'Funzionalità non supportata dalla piattaforma di database in uso.';
$lang['db_unsupported_compression'] = 'Il formato di compressione dei file che è stato scelto non è supportato dal server in uso.';
$lang['db_filepath_error'] = 'Impossibile scrivere i dati nel percorso che è stato immesso.';
$lang['db_invalid_cache_path'] = 'Il percorso della cache che è stato immesso non è valido o non è scrivibile.';
$lang['db_table_name_required'] = 'E\' necessario specificare una tabella per questa operazione.';
$lang['db_column_name_required'] = 'E\' necessario specificare il nome di una colonna per questa operazione';
$lang['db_column_definition_required'] = 'E\' necessario specificare la definizione di una colonna per questa operazione.';
$lang['db_unable_to_set_charset'] = 'Impossibile impostare il set di caratteri per la connessione: %s';
$lang['db_error_heading'] = 'Errore del database';
