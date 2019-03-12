INSERT INTO `ospos_app_config` (`key`, `value`) VALUES
('last_exchange_rate', '1.00'),
('number_locale_alt', 'en_US'),
('use_alternate_currency', '0'),
('currency_symbol_alt', '$');


ALTER TABLE `ospos_sales`
	ADD COLUMN `exchange_rate` decimal(28,14) DEFAULT 1.0 AFTER `sale_type`,
	ADD COLUMN `number_locale_alt` varchar(10) DEFAULT '' AFTER `exchange_rate`,
	ADD COLUMN `currency_symbol_alt` varchar(3) DEFAULT '' AFTER `number_locale_alt`;