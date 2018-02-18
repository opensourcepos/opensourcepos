<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author    CodeIgniter community
 * @author    Gabriel Potkány <gadelat+codeigniter@gmail.com>
 * @copyright Copyright (c) 2014-2018, British Columbia Institute of Technology (http://bcit.ca/)
 * @license   http://opensource.org/licenses/MIT MIT License
 * @link      https://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['migration_none_found']          = 'Žiadne migrácie neboli nájdené.';
$lang['migration_not_found']           = 'Migrácia s číslom tejto verzie nebola nájdená: %s.';
$lang['migration_sequence_gap']        = 'Chýbajúca migrácia v sekvencii blízko: %s.';
$lang['migration_multiple_version']    = 'Existuje viacej migracií s rovnakým číslom verzie: %s.';
$lang['migration_class_doesnt_exist']  = 'Trieda pre migráciu "%s" nebola nájdená.';
$lang['migration_missing_up_method']   = 'V migračnej triede "%s" chýba "up" metoda.';
$lang['migration_missing_down_method'] = 'V migračnej triede "%s" chýba "down" metoda.';
$lang['migration_invalid_filename']    = 'Migrácia "%s" má chybné meno.';
