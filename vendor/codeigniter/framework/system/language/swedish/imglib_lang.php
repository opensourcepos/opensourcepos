<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author CodeIgniter community
 * @author Peter Denk
 * @copyright Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license http://opensource.org/licenses/MIT MIT License
 * @link http://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['imglib_source_image_required']	= 'Ursprungsbild måste anges i inställningarna.';
$lang['imglib_gd_required']		= 'För den här funktionaliteten krävs GD Image Library.';
$lang['imglib_gd_required_for_props']	= 'GD Image Library måste vara installerat på servern för att kunna hämta bildegenskaperna.';
$lang['imglib_unsupported_imagecreate']	= 'Servern stöder inte GD-funktionen som krävs för att behandla bildtypen.';
$lang['imglib_gif_not_supported']	= 'GIF-formatet stöds ofta inte på grund av licenskrav. Använd JPG eller PNG-formatet istället.';
$lang['imglib_jpg_not_supported']	= 'JPG-formatet stöds inte.';
$lang['imglib_png_not_supported']	= 'PNG-formatet stöds inte.';
$lang['imglib_jpg_or_png_required']	= 'Protokollet för skalning av bilder angivet i inställningarna fungerar bara med bildtyperna JPEG och PNG.';
$lang['imglib_copy_error']		= 'Kunde inte byta ut filen. Kontrollera skrivrättigheterna för mappen.';
$lang['imglib_rotate_unsupported']	= 'Servern tycks sakna stöd för att rotera bilder.';
$lang['imglib_libpath_invalid']		= 'Sökvägen till bildmappen är felaktig. Ange rätt sökväg i bildinställningarna.';
$lang['imglib_image_process_failed']	= 'Bildbehandlingen misslyckades. Kontrollera att server stöder valt protokoll och att sökvägen till bildmappen är korrekt.';
$lang['imglib_rotation_angle_required']	= 'En rotationsvinkel måste anges för att rotera bilden.';
$lang['imglib_invalid_path']		= 'Sökvägen till bild-filen är inte korrekt.';
$lang['imglib_copy_failed']		= 'Kunde inte kopiera bilden.';
$lang['imglib_missing_font']		= 'Teckensnitt saknas.';
$lang['imglib_save_failed']		= 'Kunde inte spara bilden. Kontrollera skrivrättigheterna för filen och mappen.';
