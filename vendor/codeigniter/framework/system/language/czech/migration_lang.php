<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @copyright	Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['migration_none_found']          = 'Migrace nebyla nalezena.';
$lang['migration_not_found']           = 'Migrace s číslem této verze nebyla nalezena: %s.';
$lang['migration_sequence_gap']        = 'V sekvenci se nachází mezera blízko: %s.';
$lang['migration_multiple_version']    = 'Existuje více migrací se stejným číslem verze: %s.';
$lang['migration_class_doesnt_exist']  = 'Třída pro migraci "%s" nebyla nalezena.';
$lang['migration_missing_up_method']   = 'V migrační třídě "%s" chybí "up" metoda.';
$lang['migration_missing_down_method'] = 'V migrační třídě "%s" chybí "down" metoda.';
$lang['migration_invalid_filename']    = 'Migrace "%s" má chybné jméno.';
