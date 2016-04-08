<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['db_invalid_connection_str'] = 'Não foi possível determinar as configurações de banco de dados com base na seqüência de conexão que você enviou.';
$lang['db_unable_to_connect'] = 'Não é possível conectar ao servidor de banco de dados usando as configurações fornecidas.';
$lang['db_unable_to_select'] = 'Não foi possível selecionar o banco de dados especificado: %s';
$lang['db_unable_to_create'] = 'Não é possível criar o banco de dados especificado: %s';
$lang['db_invalid_query'] = 'A consulta que você apresentou não é válida.';
$lang['db_must_set_table'] = 'Você deve definir a tabela de banco de dados para ser usado com sua consulta.';
$lang['db_must_use_set'] = 'Você deve usar o método "set" para atualizar uma entrada.';
$lang['db_must_use_index'] = 'Você deve especificar um índice para corresponder por atualizações em lote.';
$lang['db_batch_missing_index'] = 'Uma ou mais linhas apresentadas para atualização em lote está faltando índice especificado.';
$lang['db_must_use_where'] = 'As atualizações não são permitidos a menos que contenham uma cláusula "where".';
$lang['db_del_must_use_where'] = 'Exclui não são permitidos a menos que contenham um "wheree" ou "like" cláusula.';
$lang['db_field_param_missing'] = 'Para buscar campos requer o nome da tabela como um parâmetro.';
$lang['db_unsupported_function'] = 'Este recurso não está disponível para o banco de dados que você está usando.';
$lang['db_transaction_failure'] = 'Falha na transação: Reversão realizada.';
$lang['db_unable_to_drop'] = 'Incapaz de deixar cair o banco de dados especificado.';
$lang['db_unsupported_feature'] = 'Recurso sem suporte da plataforma de banco de dados que você está usando.';
$lang['db_unsupported_compression'] = 'O formato de compressão de arquivo que você escolheu não é suportada pelo seu servidor.';
$lang['db_filepath_error'] = 'Não é possível gravar dados para o caminho do arquivo que você enviou.';
$lang['db_invalid_cache_path'] = 'O caminho de cache que você enviou não é válido ou gravável.';
$lang['db_table_name_required'] = 'Um nome de tabela é necessária para que a operação.';
$lang['db_column_name_required'] = 'Um nome de coluna é necessária para que a operação.';
$lang['db_column_definition_required'] = 'A definição da coluna é necessária para que a operação.';
$lang['db_unable_to_set_charset'] = 'Não é possível definir o conjunto de caracteres de conexão do cliente: %s';
$lang['db_error_heading'] = 'Ocorreu um erro na base de dados.';
