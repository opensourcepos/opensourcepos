-- alter sessions table
DROP TABLE `ospos_sessions`;

CREATE TABLE `ospos_sessions` (
  `id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) NOT NULL DEFAULT '0',
  `data` blob NOT NULL,
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;