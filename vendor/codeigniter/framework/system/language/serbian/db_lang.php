<?php
 /**
 * System messages translation for CodeIgniter(tm)
 *
 * @author	CodeIgniter community
 * @copyright	Copyright (c) 2014-2018, British Columbia Institute of Technology (http://bcit.ca/)
 * @copyright	Novak Urošević
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 */
defined('BASEPATH') OR exit('Nije dozvoljen direktan pristup');

$lang['db_invalid_connection_str'] = 'Ne mogu se odrediti podešavanja baze na osnovu stringova za konekciju koje ste vi uneli.';
$lang['db_unable_to_connect'] = 'Ne može se povezati na server baze sa pribavljenim podešavanjima.';
$lang['db_unable_to_select'] = 'Ne može se izabrati navedena baza: %s';
$lang['db_unable_to_create'] = 'Ne može se napraviti navedena baza: %s';
$lang['db_invalid_query'] = 'Upit koji ste uneli nije ispravan.';
$lang['db_must_set_table'] = 'Mora se podesiti tabela koja će se koristiti u upitu.';
$lang['db_must_use_set'] = 'Mora se koristiti "set" metod da bi ažurirali unos.';
$lang['db_must_use_index'] = 'Mora se navesti indeks vrednosti koje želite da ažurirate.';
$lang['db_batch_missing_index'] = 'Nedostaje indeks za jedan ili više redova koje želite da ažurirate.';
$lang['db_must_use_where'] = 'Ažuriranja nisu dozvoljena ako ne sadrže "where" uslov.';
$lang['db_del_must_use_where'] = 'Brisanja nisu dozvoljena ako ne sadrže "where" ili "like" uslov.';
$lang['db_field_param_missing'] = 'Da bi pribavili podatke iz polja potrebno je uneti ime tabele kao parametar.';
$lang['db_unsupported_function'] = 'Ova karakteristika nije dostupna u vrsti baze koju vi koristite.';
$lang['db_transaction_failure'] = 'Transakcija Neuspešna: Izvršeno vraćanje na prethodno stanje.';
$lang['db_unable_to_drop'] = 'Nemoguće je izvršiti brisanje željene baze.';
$lang['db_unsupported_feature'] = 'Nije podržana karakteristika za vrstu baze koju vi koristite.';
$lang['db_unsupported_compression'] = 'Format za kompresiju fajlova koji ste izabrali nije podržan na vašem serveru.';
$lang['db_filepath_error'] = 'Ne mogu se zapisati podaci u fajl koji ste vi naveli.';
$lang['db_invalid_cache_path'] = 'Putanja koju ste vi naveli za keš nije ispravna ili nije dozvoljena za pisanje.';
$lang['db_table_name_required'] = 'Ime tabele je obavezno za tu operaciju.';
$lang['db_column_name_required'] = 'Ime kolone je obavezno za tu operaciju.';
$lang['db_column_definition_required'] = 'Definisanje kolone je obavezno za tu operaciju.';
$lang['db_unable_to_set_charset'] = 'Ne može se podesiti klijentovo slovno kodiranje: %s';
$lang['db_error_heading'] = 'Desila se greška u bazi podataka.';
