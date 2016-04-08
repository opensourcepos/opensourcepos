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

$Lang['imglib_source_image_required'] = 'Você deve especificar uma imagem de origem em suas preferências.';
$Lang['imglib_gd_required'] = 'É necessária a biblioteca de imagem GD para este recurso.';
$Lang['imglib_gd_required_for_props'] = 'O seu servidor deve suportar a biblioteca de imagens GD, a fim de determinar as propriedades da imagem.';
$Lang['imglib_unsupported_imagecreate'] = 'O servidor não suporta a função GD necessário para processar este tipo de imagem.';
$Lang['imglib_gif_not_supported'] = 'imagens GIF muitas vezes não são suportados devido a restrições de licenciamento. Você pode ter que usar JPG ou PNG imagens em vez.';
$Lang['imglib_jpg_not_supported'] = 'imagens JPG não são suportados.';
$Lang['imglib_png_not_supported'] = 'imagens PNG não são suportados.';
$Lang['imglib_jpg_or_png_required'] = 'O protocolo de imagem redimensionamento especificado em suas preferências só funciona com JPEG ou PNG tipos de imagem.';
$Lang['imglib_copy_error'] = 'Foi encontrado um erro durante a tentativa de substituir o arquivo. Verifique se o seu diretório de arquivo é gravável.';
$Lang['imglib_rotate_unsupported'] = 'Rotação de imagem não parece ser compatível com o seu servidor.';
$Lang['imglib_libpath_invalid'] = 'O caminho para a sua biblioteca de imagens não é correto. Por favor, defina o caminho correcto nas suas preferências de imagem. ';
$Lang['imglib_image_process_failed'] = 'Processamento de imagem falhou. Verifique se o seu servidor suporta o protocolo escolhido e que o caminho para a sua biblioteca da imagem está correta. ';
$Lang['imglib_rotation_angle_required'] = 'Um ângulo de rotação é necessária para girar a imagem.';
$Lang['imglib_invalid_path'] = 'O caminho para a imagem não está correta.';
$Lang['imglib_copy_failed'] = 'A rotina de cópia de imagem falhou.';
$Lang['imglib_missing_font'] = 'Incapaz de encontrar uma fonte para usar.';
$Lang['imglib_save_failed'] = 'Não foi possível salvar a imagem. Por favor, certifique-se a imagem e diretório de arquivos são graváveis.';
