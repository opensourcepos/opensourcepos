CREATE TABLE IF NOT EXISTS `ospos_plugin_config` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `plugin_id` varchar(100) NOT NULL,
    `key` varchar(100) NOT NULL,
    `value` text NOT NULL,
    `is_control` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_plugin_key` (`plugin_id`, `key`),
    KEY `idx_plugin_id` (`plugin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

CREATE TABLE IF NOT EXISTS `ospos_plugin_migrations` (
    `plugin_id` varchar(100) NOT NULL,
    `version`   bigint(20) UNSIGNED NOT NULL DEFAULT 0,
    `ran_at`    datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`plugin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT IGNORE INTO `ospos_modules` (`name_lang_key`, `desc_lang_key`, `sort`, `module_id`) VALUES
    ('module_plugins', 'module_plugins_desc', 111, 'plugins');

INSERT IGNORE INTO `ospos_permissions` (`permission_id`, `module_id`) VALUES
   ('plugins', 'plugins');

INSERT IGNORE INTO `ospos_grants` (`permission_id`, `person_id`, `menu_group`)
SELECT 'plugins', `person_id`, 'office' FROM `ospos_grants` WHERE `permission_id` = 'config';
