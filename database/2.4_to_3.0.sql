-- alter quantity fields to be all decimal

ALTER TABLE `ospos_items`
 MODIFY COLUMN `reorder_level` decimal(15,3) NOT NULL DEFAULT '0',
 MODIFY COLUMN `receiving_quantity` decimal(15,3) NOT NULL DEFAULT '1';

ALTER TABLE `ospos_item_kit_items`
 MODIFY COLUMN `quantity` decimal(15,3) NOT NULL;

ALTER TABLE `ospos_item_quantities`
 MODIFY COLUMN `quantity` decimal(15,3) NOT NULL DEFAULT '0';
 
ALTER TABLE `ospos_inventory`
 MODIFY COLUMN `trans_inventory` decimal(15,3) NOT NULL DEFAULT '0';
 
ALTER TABLE `ospos_receivings`
 DROP KEY `invoice_number`,
 CHANGE COLUMN `invoice_number` `reference` varchar(32) DEFAULT NULL,
 ADD KEY `reference` (`reference`);

ALTER TABLE `ospos_receivings_items`
 MODIFY COLUMN `quantity_purchased` decimal(15,3) NOT NULL DEFAULT '0',
 MODIFY COLUMN `receiving_quantity` decimal(15,3) NOT NULL DEFAULT '1';

ALTER TABLE `ospos_sales_items`
 MODIFY COLUMN `quantity_purchased` decimal(15,3) NOT NULL DEFAULT '0';

ALTER TABLE `ospos_sales_suspended_items`
 MODIFY COLUMN `quantity_purchased` decimal(15,3) NOT NULL DEFAULT '0';
 
ALTER TABLE `ospos_sales_items_taxes`
 MODIFY COLUMN `percent` decimal(15,3) NOT NULL;
 
ALTER TABLE `ospos_sales_suspended_items_taxes`
 MODIFY COLUMN `percent` decimal(15,3) NOT NULL;
 
ALTER TABLE `ospos_items_taxes`
 MODIFY COLUMN `percent` decimal(15,3) NOT NULL;

ALTER TABLE `ospos_customers`
 ADD COLUMN `discount_percent` decimal(15,2) NOT NULL DEFAULT '0';

 
-- alter config table

UPDATE `ospos_app_config` SET `key` = 'receipt_show_total_discount' WHERE `key` = 'show_total_discount';
 
INSERT INTO `ospos_app_config` (`key`, `value`) VALUES
('receipt_show_description', '1'),
('receipt_show_serialnumber', '1'),
('invoice_enable', '1'),
('country_codes', 'us'),
('notify_horizontal_position', 'right'),
('notify_vertical_position', 'top'),
('payment_options_order', 'cashdebitcredit'),
('protocol', 'mail'),
('mailpath', '/usr/sbin/sendmail'),
('smtp_port', '465'),
('smtp_timeout', '5'),
('smtp_crypto', 'ssl'),
('receipt_template', 'receipt_default');
 
DELETE FROM `ospos_app_config` WHERE `key` = 'use_invoice_template';


-- add messages (SMS) module and permissions

UPDATE `ospos_modules` SET `sort` = 110 WHERE `name_lang_key` = 'module_config';

INSERT INTO `ospos_modules` (`name_lang_key`, `desc_lang_key`, `sort`, `module_id`) VALUES
 ('module_messages', 'module_messages_desc', 100, 'messages');
 
INSERT INTO `ospos_permissions` (`permission_id`, `module_id`) VALUES
 ('messages', 'messages');
 
INSERT INTO `ospos_grants` (`permission_id`, `person_id`) VALUES
 ('messages', 1);


-- alter sessions table

DROP TABLE `ospos_sessions`;

CREATE TABLE `ospos_sessions` (
  `id` varchar(40) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) unsigned DEFAULT 0 NOT NULL,
  `data` blob NOT NULL,
  KEY `ci_sessions_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 