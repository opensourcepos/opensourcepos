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

$lang['db_invalid_connection_str'] = 'Невозможно определить параметры базы данных на основе строки подключения, которую вы предоставили.';
$lang['db_unable_to_connect'] = 'Невозможно подключиться к серверу базы данных, используя предоставленные параметры.';
$lang['db_unable_to_select'] = 'Невозможно выбрать указанную базу данных: %s';
$lang['db_unable_to_create'] = 'Невозможно создать указанную базу данных: %s';
$lang['db_invalid_query'] = 'Представленный вами запрос является недопустимым.';
$lang['db_must_set_table'] = 'Необходимо указать таблицу базы данных, которая будет использована в вашем запросе.';
$lang['db_must_use_set'] = 'Необходимо использовать метод "set" для обновления запись.';
$lang['db_must_use_index'] = 'Необходимо указать индекс для пакетного обновления.';
$lang['db_batch_missing_index'] = 'Одна или несколько строк представленных для пакетного обновления не содержит указанный индекс.';
$lang['db_must_use_where'] = 'Обновления не допускаются, без указания условия через "where" оператор.';
$lang['db_del_must_use_where'] = 'Удаления не допускаются, без указания условия через "where" или "like" параметр.';
$lang['db_field_param_missing'] = 'Для выборки полей необходимо имя таблицы в качестве параметра.';
$lang['db_unsupported_function'] = 'Эта функция не доступна для используемой базы данных.';
$lang['db_transaction_failure'] = 'Транзакция не удалась: Осуществляется откат.';
$lang['db_unable_to_drop'] = 'Невозможно удалить указанную базу данных.';
$lang['db_unsupported_feature'] = 'Неподдерживаемая особенность платформы базы данных которую вы используете.';
$lang['db_unsupported_compression'] = 'Формат сжатия файлов который вы выбрали не поддерживается вашим сервером.';
$lang['db_filepath_error'] = 'Невозможно записать данные в файл, используя путь который вы указали.';
$lang['db_invalid_cache_path'] = 'Путь до кеш файлов, указанный вами, некорректен или недоступен для записи.';
$lang['db_table_name_required'] = 'Необходимо указать имя таблицы для этой операции.';
$lang['db_column_name_required'] = 'Необходимо указать имя столбца для этой операции.';
$lang['db_column_definition_required'] = 'Указание столбца обязательно для этой операции.';
$lang['db_unable_to_set_charset'] = 'Невозможно установить кодировку соединения: %s';
$lang['db_error_heading'] = 'Ошибка базы данных.';
