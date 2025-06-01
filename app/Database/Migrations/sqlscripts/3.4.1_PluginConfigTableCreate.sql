CREATE TABLE `ospos_plugin_config` (
    `key` varchar(50) NOT NULL,
    `value` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `ospos_plugin_config` ADD PRIMARY KEY (`key`);
