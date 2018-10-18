--
-- Add support for Multi-Package Items
--

INSERT INTO `ospos_app_config` (`key`, `value`) VALUES
('multi_pack_enabled', '0');

ALTER TABLE `ospos_items`
  ADD COLUMN `qty_per_pack` decimal(15,3) NOT NULL DEFAULT 1 AFTER `tax_category_id`,
  ADD COLUMN `pack_name` varchar(8) DEFAULT 'Each' AFTER `qty_per_pack`,
  ADD COLUMN `low_sell_item_id` int(10) DEFAULT 0 AFTER `pack_name`;

UPDATE `ospos_items`
  SET `low_sell_item_id` = `item_id`
  WHERE `low_sell_item_id` = 0;

--
-- Add support for Discount on Sales Fixed
--

INSERT INTO `ospos_app_config` (`key`, `value`) VALUES
('default_sales_discount_type', '0');

ALTER TABLE `ospos_item_kits`
	CHANGE COLUMN `kit_discount_percent` `kit_discount` DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `item_id`,
	ADD COLUMN `kit_discount_type` TINYINT(2) NOT NULL DEFAULT '0' AFTER `kit_discount`;

ALTER TABLE `ospos_customers`
	CHANGE COLUMN `discount_percent` `discount` DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `sales_tax_code`,
	ADD COLUMN `discount_type` TINYINT(2) NOT NULL DEFAULT '0' AFTER `discount`;

ALTER TABLE `ospos_sales_items`
	CHANGE COLUMN `discount_percent` `discount` DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `item_unit_price`,
	ADD COLUMN `discount_type` TINYINT(2) NOT NULL DEFAULT '0' AFTER `discount`;

ALTER TABLE `ospos_receivings_items`
	CHANGE COLUMN `discount_percent` `discount` DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `item_unit_price`,
	ADD COLUMN `discount_type` TINYINT(2) NOT NULL DEFAULT '0' AFTER `discount`;

--
-- Add support for module cashups
--

-- Set config module sort number to one of the latest

UPDATE `ospos_modules`
SET `sort` = 900
WHERE `name_lang_key` = 'module_config';

-- Add cashup module

INSERT INTO `ospos_modules` (`name_lang_key`, `desc_lang_key`, `sort`, `module_id`) VALUES
('module_cashups', 'module_cashups_desc', 110, 'cashups');

INSERT INTO `ospos_permissions` (`permission_id`, `module_id`) VALUES
('cashups', 'cashups');

INSERT INTO `ospos_grants` (`permission_id`, `person_id`) VALUES
('cashups', 1);

-- Table structure for table `ospos_cash_up`

CREATE TABLE `ospos_cash_up` (
  `cashup_id` int(10) NOT NULL,
  `open_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `close_date` timestamp NULL,
  `open_amount_cash` decimal(15,2) NOT NULL,
  `transfer_amount_cash` decimal(15,2) NOT NULL,
  `note` int(1) NOT NULL,
  `closed_amount_cash` decimal(15,2) NOT NULL,
  `closed_amount_card` decimal(15,2) NOT NULL,
  `closed_amount_check` decimal(15,2) NOT NULL,
  `closed_amount_total` decimal(15,2) NOT NULL,
  `description` varchar(255) NOT NULL,
  `open_employee_id` int(10) NOT NULL,
  `close_employee_id` int(10) NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Indexes for table `ospos_cash_up`

ALTER TABLE `ospos_cash_up`
  ADD PRIMARY KEY (`cashup_id`),
  ADD KEY `open_employee_id` (`open_employee_id`),
  ADD KEY `close_employee_id` (`close_employee_id`),
  ADD CONSTRAINT `ospos_cash_up_ibfk_1` FOREIGN KEY (`open_employee_id`) REFERENCES `ospos_employees` (`person_id`),
  ADD CONSTRAINT `ospos_cash_up_ibfk_2` FOREIGN KEY (`close_employee_id`) REFERENCES `ospos_employees` (`person_id`);

-- AUTO_INCREMENT for table `ospos_cash_up`

ALTER TABLE `ospos_cash_up`
  MODIFY `cashup_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

-- Change collation on columns to be utf8_general_ci

ALTER TABLE ospos_cash_up CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE ospos_expense_categories CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE ospos_expenses CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;

-- Add amount due

ALTER TABLE `ospos_cash_up`
  ADD `closed_amount_due` decimal(15,2) NOT NULL;

--
-- Add Suppliers category
--

ALTER TABLE `ospos_suppliers`
  ADD COLUMN `category` TINYINT NOT NULL;

UPDATE `ospos_suppliers`
  SET `category` = 0;


-- --------------------------------
-- Start of India GST Tax Changes
-- --------------------------------

INSERT INTO `ospos_app_config` (`key`, `value`) VALUES
('include_hsn', '0'),
('invoice_format', 'dynamic'),
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

INSERT INTO `ospos_tax_codes` (`tax_code`,`tax_code_name`,`city`,`state`)
SELECT `tax_code`,`tax_code_name`,`city`,`state`
FROM `ospos_tax_codes_backup`;

DROP TABLE `ospos_tax_codes_backup`;

ALTER TABLE `ospos_customers`
  ADD COLUMN `tax_id` varchar(32) NOT NULL DEFAULT '' AFTER `taxable`;

ALTER TABLE `ospos_customers`
  ADD COLUMN `sales_tax_code_id` int(11) DEFAULT NULL AFTER `tax_id`;

UPDATE `ospos_customers` AS fa SET fa.`sales_tax_code_id` = (
SELECT `tax_code_id` FROM `ospos_tax_codes` AS fb WHERE fa.`sales_tax_code` =  fb.`tax_code`);

ALTER TABLE `ospos_customers`
  DROP COLUMN `sales_tax_code`;

ALTER TABLE `ospos_items`
  ADD COLUMN `hsn_code` varchar(32) NOT NULL DEFAULT '' AFTER `low_sell_item_id`;

ALTER TABLE `ospos_sales_items_taxes`
  ADD COLUMN `sales_tax_code_id` int(11) DEFAULT NULL AFTER `item_tax_amount`,
  ADD COLUMN `jurisdiction_id` int(11) DEFAULT NULL AFTER `sales_tax_code_id`,
  ADD COLUMN `tax_category_id` int(11) DEFAULT NULL AFTER `jurisdiction_id`,
  DROP COLUMN `cascade_tax`;

ALTER TABLE `ospos_sales_taxes`
  ADD COLUMN `sales_tax_code_id` int(11) DEFAULT NULL AFTER `sales_tax_code`,
  ADD COLUMN `jurisdiction_id` int(11) DEFAULT NULL AFTER `sales_tax_code_id`,
  ADD COLUMN `tax_category_id` int(11) DEFAULT NULL AFTER `jurisdiction_id`;

-- TODO update sales_tax_code_id with the id of the tax code found in sales_tax_code
UPDATE `ospos_sales_taxes` as fa set fa.`sales_tax_code_id` = (
SELECT `tax_code_id` FROM `ospos_tax_codes` AS fb WHERE fa.`sales_tax_code` =  fb.`tax_code`);

ALTER TABLE `ospos_sales_taxes`
  DROP COLUMN `sales_tax_code`;

ALTER TABLE `ospos_suppliers`
  ADD COLUMN `tax_id` varchar(32) NOT NULL DEFAULT '' AFTER `account_number`;

ALTER TABLE `ospos_tax_categories`
  ADD COLUMN `default_tax_rate` decimal(15,4) NOT NULL DEFAULT 0.0000 AFTER `tax_category`,
  ADD COLUMN `deleted` int(1) NOT NULL DEFAULT 0 AFTER `tax_group_sequence`;

-- TODO I believe that the insert statement into ospos_tax_categories still needs to be removed from tables.sql after it is restore to the 3.0.0 version

-- The tax rates table will need to be manually set up after the upgrade
-- There are too many variables to automate the process.

DROP TABLE `ospos_tax_code_rates`;
CREATE TABLE IF NOT EXISTS `ospos_tax_rates` (
  `tax_rate_id` int(11) NOT NULL AUTO_INCREMENT,
  `rate_tax_code_id` int(11) NOT NULL,
  `rate_tax_category_id` int(10) NOT NULL,
  `rate_jurisdiction_id` int(11) NOT NULL,
  `tax_rate` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `tax_rounding_code` tinyint(2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`tax_rate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ospos_tax_jurisdictions` (
  `jurisdiction_id` int(11) NOT NULL AUTO_INCREMENT,
  `jurisdiction_name` varchar(255) DEFAULT NULL,
  `tax_type` smallint(2) NOT NULL,
  `reporting_authority` varchar(255) DEFAULT NULL,
  `tax_group_sequence` tinyint(2) NOT NULL DEFAULT 0,
  `cascade_sequence` tinyint(2) NOT NULL DEFAULT 0,
  `deleted` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`jurisdiction_id`),
  KEY `jurisdiction_id` (`jurisdiction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
