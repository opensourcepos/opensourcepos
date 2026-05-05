CREATE TABLE IF NOT EXISTS `ospos_plugin_config` (
    `key` varchar(100) NOT NULL,
    `value` text NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `ospos_modules` (`name_lang_key`, `desc_lang_key`, `sort`, `module_id`) VALUES
    ('module_plugins', 'module_plugins_desc', 111, 'plugins');

INSERT IGNORE INTO `ospos_permissions` (`permission_id`, `module_id`) VALUES
   ('plugins', 'plugins');

INSERT IGNORE INTO `ospos_grants` (`permission_id`, `person_id`, `menu_group`)
SELECT 'plugins', `person_id`, 'office' FROM `ospos_grants` WHERE `permission_id` = 'config';
