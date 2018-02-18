<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author CodeIgniter community
 * @author Peter Denk
 * @copyright Copyright (c) 2014-2018, British Columbia Institute of Technology (http://bcit.ca/)
 * @license http://opensource.org/licenses/MIT MIT License
 * @link https://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['migration_none_found']		= 'Det finns inga migrationer.';
$lang['migration_not_found']		= 'Det finns ingen migration med versionsnummer: %s.';
$lang['migration_sequence_gap']		= 'Det finns ett glapp i sekvensen av migrationer vid versionsnummer: %s.';
$lang['migration_multiple_version']	= 'Det finns flera migrationer med samma versionsnummer: %s.';
$lang['migration_class_doesnt_exist']	= 'Migrations-klassen "%s" finns inte.';
$lang['migration_missing_up_method']	= 'Migrations-klassen "%s" saknar en "up"-metod.';
$lang['migration_missing_down_method']	= 'Migrations-klassen "%s" saknar en "down"-metod.';
$lang['migration_invalid_filename']	= 'Migrationen "%s" har ett ogiltigt filnamn.';
