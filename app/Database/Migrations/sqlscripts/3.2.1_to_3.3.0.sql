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


--
-- Link Expenses with Suppliers
--

-- Add supplier id

ALTER TABLE `ospos_expenses`
  ADD COLUMN `supplier_id` int(10) NULL;

-- Link suppliers

UPDATE `ospos_expenses`
  INNER JOIN `ospos_suppliers`
    ON `ospos_expenses`.`supplier_name` = `ospos_suppliers`.`company_name`
SET `ospos_expenses`.`supplier_id` = `ospos_suppliers`.`person_id`;

-- Save name in description for those expenses whose supplier isn't registered

UPDATE `ospos_expenses`
  SET `description` = CONCAT(`description`, CONCAT('\nSupplier name: ', `supplier_name`))
  WHERE `supplier_id` is NULL;

-- Add foreign key

ALTER TABLE `ospos_expenses`
  ADD CONSTRAINT `ospos_expenses_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `ospos_suppliers` (`person_id`);

-- Delete supplier name

ALTER TABLE `ospos_expenses`
  DROP COLUMN `supplier_name`;

INSERT INTO `ospos_app_config` (`key`, `value`) VALUES
  ('default_receivings_discount_type', '0'),
  ('default_receivings_discount', '0');
