-- --------------------------------
-- Start of India GST Tax Changes
-- --------------------------------

INSERT INTO `ospos_app_config` (`key`, `value`) VALUES
('include_hsn', '0'),
('invoice_type', 'invoice'),
('default_tax_jurisdiction', ''),
('tax_id', '');

UPDATE `ospos_app_config`
	SET `key` = 'use_destination_based_tax'
	WHERE `key` = 'customer_sales_tax_support';

UPDATE `ospos_app_config`
	SET `key` = 'default_tax_code'
	WHERE `key` = 'default_origin_tax_code';

RENAME TABLE `ospos_tax_codes` TO `ospos_tax_codes_backup`;

CREATE TABLE IF NOT EXISTS `ospos_tax_codes` (
	`tax_code_id` int(11) NOT NULL AUTO_INCREMENT,
	`tax_code` varchar(32) NOT NULL,
	`tax_code_name` varchar(255) NOT NULL DEFAULT '',
	`city` varchar(255) NOT NULL DEFAULT '',
	`state` varchar(255) NOT NULL DEFAULT '',
	`deleted` int(1) NOT NULL DEFAULT 0,
	PRIMARY KEY (`tax_code_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `ospos_customers`
	ADD COLUMN `tax_id` varchar(32) NOT NULL DEFAULT '' AFTER `taxable`,
	ADD COLUMN `sales_tax_code_id` int(11) DEFAULT NULL AFTER `tax_id`;

ALTER TABLE `ospos_items`
	ADD COLUMN `hsn_code` varchar(32) NOT NULL DEFAULT '' AFTER `low_sell_item_id`;

ALTER TABLE `ospos_sales_items_taxes`
	ADD COLUMN `sales_tax_code_id` int(11) DEFAULT NULL AFTER `item_tax_amount`,
	ADD COLUMN `jurisdiction_id` int(11) DEFAULT NULL AFTER `sales_tax_code_id`,
	ADD COLUMN `tax_category_id` int(11) DEFAULT NULL AFTER `jurisdiction_id`,
	DROP COLUMN `cascade_tax`;

RENAME TABLE `ospos_sales_taxes` TO `ospos_sales_taxes_backup`;

CREATE TABLE `ospos_sales_taxes` (
	`sales_taxes_id` int(11) NOT NULL AUTO_INCREMENT,
	`sale_id` int(10) NOT NULL,
	`jurisdiction_id` int(11) DEFAULT NULL,
	`tax_category_id` int(11) DEFAULT NULL,
	`tax_type` smallint(2) NOT NULL,
	`tax_group` varchar(32) NOT NULL,
	`sale_tax_basis` decimal(15,4) NOT NULL,
	`sale_tax_amount` decimal(15,4) NOT NULL,
	`print_sequence` tinyint(2) NOT NULL DEFAULT 0,
	`name` varchar(255) NOT NULL,
	`tax_rate` decimal(15,4) NOT NULL,
	`sales_tax_code_id` int(11)  DEFAULT NULL,
	`rounding_code` tinyint(2) NOT NULL DEFAULT 0,
	PRIMARY KEY (`sales_taxes_id`),
	KEY `print_sequence` (`sale_id`,`print_sequence`,`tax_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ospos_tax_jurisdictions` (
	`jurisdiction_id` int(11) NOT NULL AUTO_INCREMENT,
	`jurisdiction_name` varchar(255) DEFAULT NULL,
	`tax_group` varchar(32) NOT NULL,
	`tax_type` smallint(2) NOT NULL,
	`reporting_authority` varchar(255) DEFAULT NULL,
	`tax_group_sequence` tinyint(2) NOT NULL DEFAULT 0,
	`cascade_sequence` tinyint(2) NOT NULL DEFAULT 0,
	`deleted` int(1) NOT NULL DEFAULT 0,
	PRIMARY KEY (`jurisdiction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

ALTER TABLE `ospos_suppliers`
	ADD COLUMN `tax_id` varchar(32) DEFAULT NULL AFTER `account_number`;

ALTER TABLE `ospos_tax_categories`
	ADD COLUMN `deleted` int(1) NOT NULL DEFAULT 0 AFTER `tax_group_sequence`;

RENAME TABLE `ospos_tax_code_rates` TO `ospos_tax_code_rates_backup`;

CREATE TABLE IF NOT EXISTS `ospos_tax_rates` (
	`tax_rate_id` int(11) NOT NULL AUTO_INCREMENT,
	`rate_tax_code_id` int(11) NOT NULL,
	`rate_tax_category_id` int(10) NOT NULL,
	`rate_jurisdiction_id` int(11) NOT NULL,
	`tax_rate` decimal(15,4) NOT NULL DEFAULT 0.0000,
	`tax_rounding_code` tinyint(2) NOT NULL DEFAULT 0,
	PRIMARY KEY (`tax_rate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Add support for sales tax report

INSERT INTO `ospos_permissions` (`permission_id`, `module_id`) VALUES
('reports_sales_taxes', 'reports');

INSERT INTO `ospos_grants` (`permission_id`, `person_id`, `menu_group`) VALUES
('reports_sales_taxes', 1, 'home');
