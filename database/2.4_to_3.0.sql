-- alter quantity fields to be all decimal

ALTER TABLE `ospos_items`
 MODIFY COLUMN `reorder_level` decimal(15,3) NOT NULL DEFAULT '0',
 MODIFY COLUMN `receiving_quantity` decimal(15,3) NOT NULL DEFAULT '1';

ALTER TABLE `ospos_item_kit_items`
 MODIFY COLUMN `quantity` decimal(15,3) NOT NULL;

ALTER TABLE `ospos_item_quantities`
 MODIFY COLUMN `quantity` decimal(15,3) NOT NULL DEFAULT '0';

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
 ADD COLUMN `discount_percent` decimal(15,2) NOT NULL DEFAULT '0.00';

UPDATE `ospos_app_config` SET `key` = 'receipt_show_total_discount' WHERE `key` = 'show_total_discount';
 
INSERT INTO `ospos_app_config` (`key`, `value`) VALUES
 ('receipt_show_description', '1'),
 ('receipt_show_serialnumber', '1'),
 ('invoice_enable', '1');

UPDATE `ospos_modules` SET `sort` = 110 WHERE `name_lang_key` = 'module_config';

INSERT INTO `ospos_modules` (`name_lang_key`, `desc_lang_key`, `sort`, `module_id`) VALUES
 ('module_messages', 'module_messages_desc', 100, 'messages');
 
INSERT INTO `ospos_permissions` (`permission_id`, `module_id`) VALUES
 ('messages', 'messages');
 
INSERT INTO `ospos_grants` (`permission_id`, `person_id`) VALUES
 ('messages', 1);
 