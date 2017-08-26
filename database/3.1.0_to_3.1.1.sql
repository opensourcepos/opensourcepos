-- Add support for office menu group
ALTER TABLE `ospos_grants`
   ADD COLUMN `menu_group` varchar(32) DEFAULT 'home';

INSERT INTO `ospos_modules` (`name_lang_key`, `desc_lang_key`, `sort`, `module_id`) VALUES
  (('module_office', 'module_office_desc', 1, 'office'),
  ('module_home', 'module_home_desc', 1, 'home'));

INSERT INTO `ospos_permissions` (`permission_id`, `module_id`) VALUES
  (('office', 'office'),
  ('home', 'home'));

INSERT INTO `ospos_grants` (`permission_id`, `person_id`, `menu_group`) VALUES
  (('office', 1, 'home'),
   ('home', 1, 'office'));

update `ospos_grants`
set menu_group = 'office'
where permission_id in ('config', 'home', 'employees', 'messages', 'taxes')
and person_id = 1;