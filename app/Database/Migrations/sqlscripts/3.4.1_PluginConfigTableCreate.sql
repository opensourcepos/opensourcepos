CREATE TABLE `ospos_plugin_config` (
    `key` varchar(100) NOT NULL,
    `value` text NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `ospos_plugin_config` ADD PRIMARY KEY (`key`);