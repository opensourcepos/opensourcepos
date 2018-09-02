--
-- Add support for Multi-Package Items
--

INSERT INTO `ospos_app_config` (`key`, `value`) VALUES
('multi_pack_enabled', '0'),
('default_sales_discount_type', '0');

ALTER TABLE `ospos_items`
  ADD COLUMN `qty_per_pack` decimal(15,3) NOT NULL DEFAULT 1,
  ADD COLUMN `pack_name` varchar(8) DEFAULT 'Each',
  ADD COLUMN `low_sell_item_id` int(10) DEFAULT 0;

UPDATE `ospos_items`
  SET `low_sell_item_id` = `item_id`
  WHERE `low_sell_item_id` = 0;

--
-- Add support for Discount on Sales Fixed
--

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
