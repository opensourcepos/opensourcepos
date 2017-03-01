-- alter quantity fields to be all decimal

ALTER TABLE `ospos_items`
 ADD COLUMN `stock_type` TINYINT(2) NOT NULL DEFAULT 0,
 ADD COLUMN `item_type` TINYINT(2) NOT NULL DEFAULT 0;

ALTER TABLE `ospos_item_kits`
 ADD COLUMN `item_id` INT(10) NOT NULL DEFAULT 0,
 ADD COLUMN `kit_discount_percent` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
 ADD COLUMN `price_option` TINYINT(2) NOT NULL DEFAULT 0,
 ADD COLUMN `print_option` TINYINT(2) NOT NULL DEFAULT 0;


ALTER TABLE `ospos_item_kit_items`
 ADD COLUMN `kit_sequence` INT(3) NOT NULL DEFAULT 0;

ALTER TABLE `ospos_sales_items`
 ADD COLUMN `print_option` TINYINT(2) NOT NULL DEFAULT 0;

ALTER TABLE `ospos_sales_suspended`
 ADD COLUMN `quote_number` varchar(32) DEFAULT NULL AFTER `invoice_number`;

ALTER TABLE `ospos_sales_suspended_items`
 ADD COLUMN `print_option` TINYINT(2) NOT NULL DEFAULT 0;

-- alter pic_id field, to rather contain a file name

ALTER TABLE `ospos_items` CHANGE `pic_id` `pic_filename` VARCHAR(255);

--
-- Table structure for table `ospos_dinner_tables`
--

CREATE TABLE IF NOT EXISTS `ospos_dinner_tables` (
  `dinner_table_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`dinner_table_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ospos_dinner_tables` (`dinner_table_id`, `name`, `status`, `deleted`) VALUES
(1, 'Delivery', 0, 0),
(2, 'Take Away', 0, 0);

-- alter ospos_sales table
ALTER TABLE `ospos_sales`
 ADD COLUMN `dinner_table_id` int(11) NULL AFTER `invoice_number`;

ALTER TABLE `ospos_sales`
 ADD KEY `dinner_table_id` (`dinner_table_id`),
 ADD CONSTRAINT `ospos_sales_ibfk_3` FOREIGN KEY (`dinner_table_id`) REFERENCES `ospos_dinner_tables` (`dinner_table_id`);

-- alter ospos_sales_suspended table
ALTER TABLE `ospos_sales_suspended`
 ADD COLUMN `dinner_table_id` int(11) NULL AFTER `quote_number`;

ALTER TABLE `ospos_sales_suspended`
 ADD KEY `dinner_table_id` (`dinner_table_id`),
 ADD CONSTRAINT `ospos_sales_suspended_ibfk_3` FOREIGN KEY (`dinner_table_id`) REFERENCES `ospos_dinner_tables` (`dinner_table_id`);

-- add enabled dinner tables key into config
INSERT INTO `ospos_app_config` (`key`, `value`) VALUES
('date_or_time_format', ''),
('sales_quote_format', 'Q%y{QSEQ:6}'),
('default_register_mode', 'sale'),
('last_used_invoice_number', '0'),
('last_used_quote_number', '0'),
('line_sequence', '0'),
('dinner_table_enable','');