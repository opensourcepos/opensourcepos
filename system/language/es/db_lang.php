<?php

$lang['db_invalid_connection_str'] = 'No se puede determinar la configuración de base de datos basado en los datos de conexión que ha enviado.';
$lang['db_unable_to_connect'] = 'No se puede conectar al servidor de base de datos utilizando la configuración proporcionada.';
$lang['db_unable_to_select'] = 'No se puede seleccionar la base de datos especificada: %s';
$lang['db_unable_to_create'] = 'No se puede crear la base de datos especificada: %s';
$lang['db_invalid_query'] = 'La consulta que ha enviado no es válida.';
$lang['db_must_set_table'] = 'Debe especificar la tabla en base de datos para utilizar esta consulta.';
$lang['db_must_use_set'] = 'Debe utilizar el método de "set" para actualizar una entrada.';
$lang['db_must_use_index'] = 'Debe especificar un índice para que coincida con el de actualizaciones por lotes.';
$lang['db_batch_missing_index'] = 'Falta el índice para una o más consultas para la actualización por lote.';
$lang['db_must_use_where'] = 'Las actualizaciones no están permitidas a menos que contengan una cláusula "where".';
$lang['db_del_must_use_where'] = 'No se pemite eliminar a menos que contengan un "where" o "like" en la consulta.';
$lang['db_field_param_missing'] = 'Para traer campos requiere el nombre de la tabla como un parámetro.';
$lang['db_unsupported_function'] = 'Esta función no está disponible para la base de datos que está utilizando.';
$lang['db_transaction_failure'] = 'Transaction failure: Rollback performed.';
$lang['db_unable_to_drop'] = 'No se puede eliminar la base de datos especificada.';
$lang['db_unsuported_feature'] = 'Característica no soportada por la base de datos que estás utilizando.';
$lang['db_unsuported_compression'] = 'El formato de compresión no está soportado por el servidor..';
$lang['db_filepath_error'] = 'No se puede escribir en el la ruta solicificada.';
$lang['db_invalid_cache_path'] = 'La ruta del cache no es válida o no se puede escribir..';
$lang['db_table_name_required'] = 'Se requiere del nombre de una tabla para esta operación.';
$lang['db_column_name_required'] = 'Se requiere del nombre de una columna para esta operación..';
$lang['db_column_definition_required'] = 'Se debe definir una columna para esta opración.';
$lang['db_unable_to_set_charset'] = 'No se puede definir el juego de caracteres: %s';
$lang['db_error_heading'] = 'Error en la base de datos';

/* End of file db_lang.php */
/* Location: ./system/language/english/db_lang.php */