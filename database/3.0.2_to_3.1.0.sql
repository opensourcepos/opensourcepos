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

INSERT INTO `ospos_app_config` (`key`, `value`) VALUES
('date_or_time_format', ''),
('sales_quote_format', 'Q%y{QSEQ:6}'),
('default_register_mode', 'sale'),
('last_used_invoice_number', '0'),
('last_used_quote_number', '0'),
('line_sequence', '0');

-- alter pic_id field, to rather contain a file name

ALTER TABLE `ospos_items` CHANGE `pic_id` `pic_filename` VARCHAR(255);

