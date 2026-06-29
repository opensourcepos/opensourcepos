CREATE TABLE IF NOT EXISTS `ospos_plugin_migrations` (
    `plugin_id` varchar(100) NOT NULL,
    `version`   bigint(20) UNSIGNED NOT NULL DEFAULT 0,
    `ran_at`    datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`plugin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
