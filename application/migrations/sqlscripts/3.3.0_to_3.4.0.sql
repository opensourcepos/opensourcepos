--
-- Add support for Discount on Sales Fixed
--

INSERT INTO `ospos_app_config` (`key`, `value`) VALUES
('default_sales_discount_fixed', '0'),
('default_sales_discount_type', '0');

ALTER TABLE `ospos_item_kits`
	ADD COLUMN `kit_discount_fixed` DECIMAL(15,2) NOT NULL DEFAULT '0.00' AFTER `kit_discount_percent`;

ALTER TABLE `ospos_customers`
	ADD COLUMN `discount_fixed` DECIMAL(15,2) NOT NULL DEFAULT '0.00' AFTER `discount_percent`;


ALTER TABLE `ospos_sales_items`
	ADD COLUMN `discount_fixed` DECIMAL(15,2) NOT NULL DEFAULT '0.00' AFTER `discount_percent`,
	ADD COLUMN `discount_type` TINYINT(2) NOT NULL DEFAULT '0' AFTER `discount_fixed`;

ALTER TABLE `ospos_receivings_items`
	ADD COLUMN `discount_fixed` DECIMAL(15,2) NOT NULL DEFAULT '0.00' AFTER `discount_percent`,
	ADD COLUMN `discount_type` TINYINT(2) NOT NULL DEFAULT '0' AFTER `discount_fixed`;
