<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @author	Ignasi Molsosa
 * @copyright	Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['migration_none_found'] = 'No s\'ha trobat cap migració.';
$lang['migration_not_found'] = 'No s\'ha trobat cap migració amb versió: %s.';
$lang['migration_sequence_gap'] = 'Hi ha un forat en les seqüencies de migració prop de la versió: %s.';
$lang['migration_multiple_version'] = 'Hi ha multiples migracions amb el mateix número de versió: %s.';
$lang['migration_class_doesnt_exist'] = 'La classe de migració "%s" no s\'ha trobat.';
$lang['migration_missing_up_method'] = 'La classe de migració "%s" no conté el mètode "up".';
$lang['migration_missing_down_method'] = 'La classe de migració "%s" no conté el mètode "down".';
$lang['migration_invalid_filename'] = 'Migració "%s" conté un nom de fitxer invàlid.';
