<?php
/**
 * System messages translation for CodeIgniter(tm)
 * @author	CodeIgniter community
 * @copyright	Copyright (c) 2014-2018, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['db_invalid_connection_str']      = "Impossible de déterminer les paramètres d'accès à la base de données en se basant sur la chaîne de connexion que vous avez fournie.";
$lang['db_unable_to_connect']           = "Impossible de se connecter à la base de données en utilisant les paramètres fournis.";
$lang['db_unable_to_select']            = "Impossible de sélectionner cette base de données : %s";
$lang['db_unable_to_create']            = "Impossible de créer cette base de données : %s";
$lang['db_invalid_query']               = "La requête que vous avez fournie est invalide.";
$lang['db_must_set_table']              = "Vous devez spécifier une table pour effectuer votre requête.";
$lang['db_must_use_set']                = "Vous devez utiliser la méthode \"set()\" pour mettre à jour une entrée.";
$lang['db_must_use_index']              = "Vous devez spécifier un index pour les mises à jour groupées.";
$lang['db_batch_missing_index']         = "Une ou plusieurs rangées de la mise à jour groupée ne disposent pas de l'index requis.";
$lang['db_must_use_where']              = "Il faut obligatoirement spécifier la clause \"WHERE\" pour mettre à jour une entrée.";
$lang['db_del_must_use_where']          = "Il faut obligatoirement spécifier la clause \"WHERE\" pour supprimer une entrée.";
$lang['db_field_param_missing']         = "La méthode \"fetch_fields()\" requiert le nom de la table cible en paramètre.";
$lang['db_unsupported_function']        = "Cette fonctionnalité n'est pas disponible pour la base de données utilisée.";
$lang['db_transaction_failure']         = "Erreur de transaction : la transaction est annulée.";
$lang['db_unable_to_drop']              = "Impossible d'effacer la base de données spécifiée.";
$lang['db_unsupported_feature']         = "Cette fonctionnalité n'est pas supportée par le système de gestion de bases de données utilisé.";
$lang['db_unsupported_compression']     = "Le format de compression choisi n'est pas supporté par votre serveur.";
$lang['db_filepath_error']              = "Impossible d'écrire des données au chemin de fichiers indiqué.";
$lang['db_invalid_cache_path']          = "Le chemin de mise en cache indiqué n'est pas valide ou inscriptible.";
$lang['db_table_name_required']         = "Un nom de table est requis pour cette opération.";
$lang['db_column_name_required']        = "Un nom de champ est requis pour cette opération.";
$lang['db_column_definition_required']  = "Une définition de champ est requise pour cette opération.";
$lang['db_unable_to_set_charset']       = "Impossible de définir le jeu de caractères de la connexion client : %s";
$lang['db_error_heading']               = "Une erreur de la base de données s'est produite.";
