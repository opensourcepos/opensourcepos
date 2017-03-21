<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['db_invalid_connection_str'] = 'Неможливо визначити параметри бази даних на основі рядка підключення, який Ви надали.';
$lang['db_unable_to_connect'] = 'Неможливо підключитись до серверу бази даних, використовуючи надані параметри.';
$lang['db_unable_to_select'] = 'Неможливо вибрати вказану базу даних: %s';
$lang['db_unable_to_create'] = 'Неможливо створити вказану базу даних: %s';
$lang['db_invalid_query'] = 'Наданий Вами запит є неприйнятним.';
$lang['db_must_set_table'] = 'Необхідно вказати таблицю бази даних, котра буде використана у Вашому запиті.';
$lang['db_must_use_set'] = 'Необхідно використовувати метод "set" для оновлення запису.';
$lang['db_must_use_index'] = 'Необхідно вказати індекс для пакетного оновлення.';
$lang['db_batch_missing_index'] = 'Один чи декілька рядків, наданих для пакетного оновлення, не має вказаного індекса.';
$lang['db_must_use_where'] = 'Оновлення неприйнятне без вказаної через оператор "where" умови.';
$lang['db_del_must_use_where'] = 'Видалення неприйнятне без умови, вказаної через параметр "where" або "like".';
$lang['db_field_param_missing'] = 'Для вибору полів необхідне ім’я таблиці в якості параметру.';
$lang['db_unsupported_function'] = 'Ця функція неприйнятна для вибраної бази даних.';
$lang['db_transaction_failure'] = 'Збій транзакції: відкат.';
$lang['db_unable_to_drop'] = 'Неможливо видалити вказану базу даних.';
$lang['db_unsupported_feature'] = 'Непідтримувана особливість платформи бази даних, яку Ви використовуєте.';
$lang['db_unsupported_compression'] = 'Формат стискання файлів, який Ви вибрали, не підтримується Вашим сервером.';
$lang['db_filepath_error'] = 'Неможливо записати дані в файл за вказаним шляхом.';
$lang['db_invalid_cache_path'] = 'Вказаний шлях до кеш файлів некорректний або недоступний для запису.';
$lang['db_table_name_required'] = 'Необхідно вказати ім’я таблиці для цієї операції.';
$lang['db_column_name_required'] = 'Необхідно вказани ім’я стовпця для цієї операції.';
$lang['db_column_definition_required'] = 'Назва стовпця обов’язкова для цієї операції.';
$lang['db_unable_to_set_charset'] = 'Неможливо встановити кодування з’єднання: %s';
$lang['db_error_heading'] = 'Помилка бази даних.';
