<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @copyright	Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @copyright	Pieter Krul
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 */
defined('BASEPATH') OR exit('Directe toegang tot scripts is niet toegestaan');

$lang['imglib_source_image_required']		= 'U dient een afbeeldingsbron bij de voorkeuren op te geven.';
$lang['imglib_gd_required']			= 'De GD image library is vereist voor deze optie.';
$lang['imglib_gd_required_for_props']		= 'De server dient de GD image library te ondersteunen om afbeeldingseigenschappen weer te kunnen geven.';
$lang['imglib_unsupported_imagecreate']		= 'De benodigde GD functie die nodig is om dit type afbeeldingen te vewerken wordt niet door de server ondersteund.';
$lang['imglib_gif_not_supported']		= 'GIF-afbeeldingen worden veelal niet ondersteund vanwege licensierestricties. U zou JPG- of PNG-afbeeldingen kunnen gebruiken.';
$lang['imglib_jpg_not_supported']		= 'JPG-afbeeldingen worden niet ondersteund.';
$lang['imglib_png_not_supported']		= 'PNG-afbeeldingen worden niet ondersteund.';
$lang['imglib_jpg_or_png_required']		= 'Het in de voorkeuren opgegeven protocol voor het schalen van afbeeldingen is alleen geschikt voor JPEG en PNG-afbeeldingen.';
$lang['imglib_copy_error']			= 'Er is een fout opgetreden tijdens het vervangen van het bestand. Controleer de bestandsrechten voor de directory.';
$lang['imglib_rotate_unsupported']		= 'Het roteren van afbeeldingen wordt blijkbaar niet door de server ondersteund.';
$lang['imglib_libpath_invalid']			= 'Het pad naar de image library is niet correct. Stel het juiste pad in bij de afbeeldingsvoorkeuren.';
$lang['imglib_image_process_failed']		= 'Het verwerken van de afbeelding is mislukt. Controleer of de server het gekozen protocol ondersteunt, en of het pad naar de image library klopt.';
$lang['imglib_rotation_angle_required']		= 'Om te afbeelding te kunnen roteren, dient een rotatiehoek opgegeven te worden.';
$lang['imglib_invalid_path']			= 'Het pad naar de afbeelding klopt niet.';
$lang['imglib_copy_failed']			= 'De kopieerroutine werkte niet.';
$lang['imglib_missing_font']			= 'Het systeem kon geen bruikbaar lettertype vinden.';
$lang['imglib_save_failed']			= 'Het opslaan van de afbeelding is mislukt. Controleer of er voldoende rechten zijn om te kunnen schrijven naar de bestandsdirectories';