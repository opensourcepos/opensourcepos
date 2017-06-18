<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author		CodeIgniter community
 * @copyright	Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license		http://opensource.org/licenses/MIT	MIT License
 * @link		https://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['migration_none_found']			= 'Nem találhatóak migrációk.';
$lang['migration_not_found']			= 'A megadott verziószámú migráció nem található: %s.';
$lang['migration_sequence_gap']			= 'A migrációk verziószámainak sorrendjében kihagyás található, a következő verziónál: %s.';
$lang['migration_multiple_version']		= 'Különöböző migrációk egyező verziószámmal: %s.';
$lang['migration_class_doesnt_exist']	= 'A(z) "%s" migrációs osztály nem található.';
$lang['migration_missing_up_method']	= 'A(z) "%s" migrációs osztály "up" metódusa nem található.';
$lang['migration_missing_down_method']	= 'A(z) "%s" migrációs osztály "down" metódusa nem található.';
$lang['migration_invalid_filename']		= 'A(z) "%s" migráció hibás fájlnévvel rendelkezik.';