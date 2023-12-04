
DROP TABLE `ospos_sessions`;

CREATE TABLE IF NOT EXISTS `ospos_sessions` (
	`id` varchar(128) NOT NULL,
	`ip_address` varchar(45) NOT NULL,
	`timestamp` timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
	`data` blob NOT NULL,
	KEY `ospos_sessions_timestamp` (`timestamp`)
	);

ALTER TABLE ospos_sessions ADD PRIMARY KEY (id, ip_address);
