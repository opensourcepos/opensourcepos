--
-- Add support for Discount on Sales Fixed
--

INSERT INTO `ospos_app_config` (`key`, `value`) VALUES
('default_sales_discount_type', '0');

ALTER TABLE `ospos_item_kits`
	CHANGE COLUMN `kit_discount_percent` `kit_discount` DECIMAL(15,2) NOT NULL DEFAULT '0.00' AFTER `item_id`,
	ADD COLUMN `kit_discount_type` TINYINT(2) NOT NULL DEFAULT '0' AFTER `kit_discount`;

ALTER TABLE `ospos_customers`
	CHANGE COLUMN `discount_percent` `discount` DECIMAL(15,2) NOT NULL DEFAULT '0.00' AFTER `sales_tax_code`,
	ADD COLUMN `discount_type` TINYINT(2) NOT NULL DEFAULT '0' AFTER `discount`;


ALTER TABLE `ospos_sales_items`
	CHANGE COLUMN `discount_percent` `discount` DECIMAL(15,2) NOT NULL DEFAULT '0.00' AFTER `item_unit_price`,
	ADD COLUMN `discount_type` TINYINT(2) NOT NULL DEFAULT '0' AFTER `discount`;

ALTER TABLE `ospos_receivings_items`
	CHANGE COLUMN `discount_percent` `discount` DECIMAL(15,2) NOT NULL DEFAULT '0.00' AFTER `item_unit_price`,
	ADD COLUMN `discount_type` TINYINT(2) NOT NULL DEFAULT '0' AFTER `discount`;
