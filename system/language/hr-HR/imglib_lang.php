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
defined('BASEPATH') OR exit('Nije dozvoljen direktan pristup');

$lang['imglib_source_image_required'] = 'Morate odrediti izvor slike u svojim prioritetima.';
$lang['imglib_gd_required'] = 'GD biblioteka slika je obvezna za ovu opciju.';
$lang['imglib_gd_required_for_props'] = 'Tvoj server mora podržavati GD biblioteku slika kako bi se utvrtidilo svojstvo slike.';
$lang['imglib_unsupported_imagecreate'] = 'Tvoj server mora podržavati GD funkciju potrebnu za obradu ovog tipa slike.';
$lang['imglib_gif_not_supported'] = 'GIF slike često nisu podržane radi linencnih ograničenja. Umjesto toga koristite JPG ili PNG slike.';
$lang['imglib_jpg_not_supported'] = 'JPG misu podržane.';
$lang['imglib_png_not_supported'] = 'PNG misu podržane.';
$lang['imglib_jpg_or_png_required'] = 'Protokol promjene veličine slike određen u vašim prioritetima radi samo s JPEG ili PNG tipovima slika.';
$lang['imglib_copy_error'] = 'Pojavila se greška prilikom zamjene datoteke. Molim vas provjerite da se u vaš direktorij može zapisivati.';
$lang['imglib_rotate_unsupported'] = 'Rotacija slike nije podržana od vašeg servera.';
$lang['imglib_libpath_invalid'] = 'Putanja do biblioteke slika nije točna. Molim postavite ispravnu putanju u postavkama slika.';
$lang['imglib_image_process_failed'] = 'Greška kod obrada slike. Molim provjerite da vaš server podržava odabrani portokol i da je put do biblioteke slika ispravan.';
$lang['imglib_rotation_angle_required'] = 'Kut rotacije je obavezan za rotiranje slike.';
$lang['imglib_invalid_path'] = 'Putanja do slike je neispravna.';
$lang['imglib_copy_failed'] = 'Greška kod kopiranja slike.';
$lang['imglib_missing_font'] = 'Ne mogu pronaći font za korištenje.';
$lang['imglib_save_failed'] = 'Ne može se snimiti slika. Molim vas provjerite da se u vaš direktorij može zapisivati.';
