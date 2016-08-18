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

$lang['migration_none_found']		= 'Er is geen enkele migratie gevonden.';
$lang['migration_not_found']		= 'Een migratie met dit versienummer is onvindbaar. %s.';
$lang['migration_sequence_gap']		= 'Er ontbreekt een deel in de migratiereeks omstreeks dit versienummer. %s.';
$lang['migration_multiple_version']	= 'Er zijn meerdere migraties met hetzelfde versienummer: %s.';
$lang['migration_class_doesnt_exist']	= 'De migratie-class "%s" kon niet worden gevonden.';
$lang['migration_missing_up_method']	= 'De migratie-class "%s" mist een "up"-methode';
$lang['migration_missing_down_method']	= 'De migratie-class "%s" mist een "down"-methode.';
$lang['migration_invalid_filename']	= 'De migratie "%s" heeft een ongeldige bestandsnaam.';