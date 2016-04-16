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

 