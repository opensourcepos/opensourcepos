<?php
/**
 * System messages translation for CodeIgniter(tm)
 * @author	CodeIgniter community
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['migration_none_found']            = "Aucune migration trouvée.";
$lang['migration_not_found']             = "Aucune migration n'a été trouvée avec le numéro de version : %d.";
$lang['migration_sequence_gap']          = "Il y a un trou dans la séquence de migration près de la version numéro : %s.";
$lang['migration_multiple_version']      = "Il y a plusieurs migrations avec le même numéro de version : %d.";
$lang['migration_class_doesnt_exist']    = "La classe de migration \"%s\" n'a pas pu être trouvée.";
$lang['migration_missing_up_method']     = "La classe de migration \"%s\" ne dispose pas d'une méthode 'up'.";
$lang['migration_missing_down_method']   = "La classe de migration \"%s\" ne dispose pas d'une méthode 'down'.";
$lang['migration_invalid_filename']      = "Le nom de fichier de la migration \"%s\" n'est pas valide.";
