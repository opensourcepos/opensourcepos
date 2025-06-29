DROP TABLE IF EXISTS `ospos_sessions`;
CREATE TABLE IF NOT EXISTS `ospos_sessions` (
    `id` varchar(128) NOT null,
    `ip_address` varchar(45) NOT null,
    `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP NOT null,
    `data` blob NOT null,
    KEY `ospos_sessions_timestamp` (`timestamp`)
);

ALTER TABLE ospos_sessions ADD PRIMARY KEY (id, ip_address);
