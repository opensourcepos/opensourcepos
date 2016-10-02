<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @author	Ivan Tcholakov
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['db_invalid_connection_str'] = 'Невъзможно е да се определят настройките за базата данни от "connection string"-a, който сте въвели.';
$lang['db_unable_to_connect'] = 'Не може да се осъществи връзка с Вашата база данни чрез посочените данни.';
$lang['db_unable_to_select'] = 'Не може да избере посочената база данни: %s';
$lang['db_unable_to_create'] = 'Не може да бъде създадена посочената база данни: %s';
$lang['db_invalid_query'] = 'Подадената заявка не е валидна.';
$lang['db_must_set_table'] = 'Трябва да зададете таблица за да се използва от Вашата заявка.';
$lang['db_must_use_set'] = 'Трябва да използвате "set" метод за актуализиране на данните.';
$lang['db_must_use_index'] = 'Трябва да посочите съответен индекс за пакетно обновяване.';
$lang['db_batch_missing_index'] = 'Един или повече редове, представени за актуализиране на пакетното обновяване са с липсващ индекс.';
$lang['db_must_use_where'] = 'Актуализации не са позволени, освен ако не съдържат "WHERE" клауза.';
$lang['db_del_must_use_where'] = 'Изтриването не е позволено, освен ако не съдържа "WHERE" или "LIKE" клауза.';
$lang['db_field_param_missing'] = 'При изтеглянето на полета се изисква името на таблицата като параметър.';
$lang['db_unsupported_function'] = 'Функцията не е достъпна за вида база данни, която използвате.';
$lang['db_transaction_failure'] = 'Грешка в транзакция: Връщане на предишното състояние.';
$lang['db_unable_to_drop'] = 'Не може да се изтрие посочената база данни.';
$lang['db_unsupported_feature'] = 'Неподдържана функционалност от Вашата база данни.';
$lang['db_unsupported_compression'] = 'Форматът за компресиране, който сте избрали не се поддържа от вашия сървър.';
$lang['db_filepath_error'] = 'Не е възможно записването на данни в пътя на файла, който сте посочили.';
$lang['db_invalid_cache_path'] = 'Пътят до кеш директорията, който сте описали не е правилен или без права.';
$lang['db_table_name_required'] = 'Името на таблицата е задължително за тази операция.';
$lang['db_column_name_required'] = 'Името на колоната е задължително за тази операция.';
$lang['db_column_definition_required'] = 'Дефинирането на колоната е задължително за тази операция.';
$lang['db_unable_to_set_charset'] = 'Не може да настрои клиентът с правилен енкодинг: %s';
$lang['db_error_heading'] = 'Възникна грешка в базата данни';
