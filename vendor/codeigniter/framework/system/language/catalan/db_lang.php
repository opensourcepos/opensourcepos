<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @author	Ignasi Molsosa
 * @copyright	Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['db_invalid_connection_str'] = 'Impossible determinar la configuració de la base de dades basada en la informació de connecció presentada.';
$lang['db_unable_to_connect'] = 'Impossible connectar amb la base de dades usant la configuració proporcionada.';
$lang['db_unable_to_select'] = 'Impossible seleccionar la base de dades especificada: %s';
$lang['db_unable_to_create'] = 'Impossible crear la base de dades especificada: %s';
$lang['db_invalid_query'] = 'La consulta introduïda no és vàlida.';
$lang['db_must_set_table'] = 'Has d\'especificar la taula en la que vols executar la consulta.';
$lang['db_must_use_set'] = 'Has de fer servir el mètode "set" per actualitzar una entrada.';
$lang['db_must_use_index'] = 'Has d\'especificar un index sobre el que aparellar l\'actualització multiple.';
$lang['db_batch_missing_index'] = 'Una o més files presentades per actualització multiple no disposen de l\'index especificat.';
$lang['db_must_use_where'] = 'Les actualitzacions sense clausula "where" no estan permeses.';
$lang['db_del_must_use_where'] = 'Les supressions sense clausula "where" o "like" no estan permeses.';
$lang['db_field_param_missing'] = 'Per consultar camps, es necessari el nom de la taula com a paràmetre.';
$lang['db_unsupported_function'] = 'Aquesta funció no està disponible en la base de dades que estas fent servir.';
$lang['db_transaction_failure'] = 'Transacció erronia: Rollback executat.';
$lang['db_unable_to_drop'] = 'Impossible borrar la base de dades especificada.';
$lang['db_unsupported_feature'] = 'Aquesta característica no està disponible en la plataforma de base de dades que estas fent servir.';
$lang['db_unsupported_compression'] = 'El teu servidor no disposa del format de compressió que has seleccionat.';
$lang['db_filepath_error'] = 'Impossible escriure dades en el fitxer que has presentat.';
$lang['db_invalid_cache_path'] = 'El fitxer de cache que has presentat no és vàlid o no té permis d\'escriptura.';
$lang['db_table_name_required'] = 'El nom de la taula és necessari per aquesta operació.';
$lang['db_column_name_required'] = 'El nom de la columna és necessari per aquesta operació.';
$lang['db_column_definition_required'] = 'La definició de la columna és necessaria per aquesta operació.';
$lang['db_unable_to_set_charset'] = 'Impossible utilitzar el conjunt de caràcters del client: %s';
$lang['db_error_heading'] = 'Hi ha hagut un error de la base de dades.';
