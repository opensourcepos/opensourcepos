CREATE TABLE IF NOT EXISTS `ospos_stock_locations` (
  `location_id` int(11) NOT NULL AUTO_INCREMENT,
  `location_name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`location_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;

INSERT INTO `ospos_stock_locations` ( `deleted`, `location_name` ) VALUES ('0', 'stock');

CREATE TABLE IF NOT EXISTS `ospos_item_quantities` (
  `item_id` int(11) NOT NULL,
  `location_id` int(11),
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`item_id`,`location_id`),
  KEY `item_id` (`item_id`),
  KEY `location_id` (`location_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

UPDATE `ospos_item_quantities` SET location_id = (SELECT MIN(`location_id`) FROM `ospos_stock_locations`);

ALTER TABLE `ospos_inventory`
 ADD COLUMN trans_location int(11);

UPDATE `ospos_inventory` SET trans_location = (SELECT MIN(`location_id`) FROM `ospos_stock_locations`);

ALTER TABLE `ospos_inventory`
 MODIFY COLUMN trans_location int(11) NOT NULL,
 ADD KEY `trans_location` (`trans_location`),
 ADD CONSTRAINT `ospos_inventory_ibfk_3` FOREIGN KEY (`trans_location`) REFERENCES `ospos_stock_locations` (`location_id`); 

ALTER TABLE `ospos_receivings_items`
 ADD COLUMN item_location int(11);
 
UPDATE `ospos_receivings_items` SET item_location = (SELECT MIN(`location_id`) FROM `ospos_stock_locations`);

ALTER TABLE ospos_receivings_items
 MODIFY COLUMN `item_location` INT(11) NOT NULL,
 ADD KEY `item_location` (`item_location`),
 ADD CONSTRAINT `ospos_receivings_items_ibfk_3` FOREIGN KEY (`item_location`) REFERENCES `ospos_stock_locations` (`location_id`);
 
ALTER TABLE `ospos_sales_items`
 ADD COLUMN `item_location` int(11);

UPDATE `ospos_sales_items` SET item_location = (SELECT MIN(`location_id`) FROM `ospos_stock_locations`);

ALTER TABLE `ospos_sales_items`
 MODIFY `item_location` INT(11) NOT NULL,
 ADD KEY `item_location` (`item_location`),
 ADD KEY `sale_id` (`sale_id`),
 ADD CONSTRAINT `ospos_sales_items_ibfk_3` FOREIGN KEY (`item_location`) REFERENCES `ospos_stock_locations` (`location_id`);

ALTER TABLE `ospos_sales_items_taxes`
 ADD KEY `sale_id` (`sale_id`);

ALTER TABLE `ospos_sales_payments`
 ADD KEY `sale_id` (`sale_id`);

ALTER TABLE `ospos_sales_suspended_items`
 ADD COLUMN `item_location` int(11);

UPDATE `ospos_sales_suspended_items` SET item_location = (SELECT MIN(`location_id`) FROM `ospos_stock_locations`);

ALTER TABLE `ospos_sales_suspended_items`
 MODIFY COLUMN `item_location` int(11) NOT NULL,
 ADD KEY `item_location` (`item_location`),
 ADD KEY `sale_id` (`sale_id`),
 ADD CONSTRAINT `ospos_sales_suspended_items_ibfk_3` FOREIGN KEY (`item_location`) REFERENCES `ospos_stock_locations` (`location_id`);

ALTER TABLE `ospos_item_quantities`
  MODIFY COLUMN `location_id` INT(11) NOT NULL,
  ADD CONSTRAINT `ospos_item_quantities_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `ospos_items` (`item_id`),
  ADD CONSTRAINT `ospos_item_quantities_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `ospos_stock_locations` (`location_id`);

