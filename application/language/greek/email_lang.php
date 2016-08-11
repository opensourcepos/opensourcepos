<?php
/**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @author	Grigoris Charamidis
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['email_must_be_array'] = 'Η μέθοδος email validation πρέπει να περάσει απο έναν πίνακα.';
$lang['email_invalid_address'] = 'Μη έγκυρη διεύθυνση e-mail: %s';
$lang['email_attachment_missing'] = 'Αδυναμία να εντοπιστεί το ακόλουθο συνημμένο ηλεκτρονικού ταχυδρομείου: %s';
$lang['email_attachment_unreadable'] = 'Δεν είναι δυνατό να ανοίξει αυτό το συνημμένο: %s';
$lang['email_no_from'] = 'Δεν μπορείτε να στείλετε email χωρίς την "From" κεφαλίδα.';
$lang['email_no_recipients'] = 'Θα πρέπει να συμπεριλάβετε αποδέκτες: To, Cc, or Bcc';
$lang['email_send_failure_phpmail'] = 'Δεν μπορεί να σταλθεί e-mail χρησιμοποιώντας την PHP mail(). Ο διακομιστής σας δεν έχει ρυθμιστεί για να στέλνει email με αυτήν την μέθοδο.';
$lang['email_send_failure_sendmail'] = 'Δεν μπορεί να σταλθεί e-mail χρησιμοποιώντας την PHP Sendmail. Ο διακομιστής σας δεν έχει ρυθμιστεί για να στέλνει email με αυτήν την μέθοδο.';
$lang['email_send_failure_smtp'] = 'Δεν μπορεί να σταλθεί e-mail χρησιμοποιώντας την PHP SMTP. Ο διακομιστής σας δεν έχει ρυθμιστεί για να στέλνει email με αυτήν την μέθοδο.';
$lang['email_sent'] = 'Το μήνυμά σας έχει σταλεί με επιτυχία χρησιμοποιώντας το ακόλουθο πρωτόκολλο: %s';
$lang['email_no_socket'] = 'Δεν είναι δυνατό να ανοίξει μια υποδοχή με το Sendmail. Παρακαλώ ελέγξτε τις ρυθμίσεις του.';
$lang['email_no_hostname'] = 'Δεν καθορίσατε ένα όνομα SMTP.';
$lang['email_smtp_error'] = 'Το ακόλουθο σφάλμα SMTP έχει εμφανιστεί: %s';
$lang['email_no_smtp_unpw'] = 'Σφάλμα: Θα πρέπει να ορίσετε ένα όνομα χρήστη και κωδικό πρόσβασης SMTP.';
$lang['email_failed_smtp_login'] = 'Αποτυχία ελέγχου ταυτότητας που στείλατε. Σφαλμα: %s';
$lang['email_smtp_auth_un'] = 'Αποτυχία ελέγχου ταυτότητας username. Σφάλμα: %s';
$lang['email_smtp_auth_pw'] = 'Αποτυχία ελέγχου ταυτότητας password. Σφάλμα: %s';
$lang['email_smtp_data_failure'] = 'Αδυναμία αποστολής δεδομένων: %s';
$lang['email_exit_status'] = 'Κωδικός εξόδου κατάστασης: %s';
