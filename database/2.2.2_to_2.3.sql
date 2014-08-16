CREATE TABLE IF NOT EXISTS `ospos_stock_locations` (
  `location_id` int(11) NOT NULL AUTO_INCREMENT,
  `location_name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`location_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;

CREATE TABLE IF NOT EXISTS `ospos_item_quantities` (
  `item_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`item_id`,`location_id`),
  KEY `item_id` (`item_id`),
  KEY `location_id` (`location_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

ALTER TABLE ospos_inventory
 ADD COLUMN trans_location int(11) NOT NULL,
 ADD KEY `trans_location` (`trans_location`),
 ADD CONSTRAINT `ospos_inventory_ibfk_3` FOREIGN KEY (`trans_location`) REFERENCES `ospos_stock_locations` (`location_id`); 

-- ALTER TABLE ospos_items DROP COLUMN location;

INSERT INTO `ospos_stock_locations` ( `deleted`, `location_name` ) VALUES ('0', 'stock');

ALTER TABLE ospos_receivings_items
 ADD COLUMN item_location int(11) NOT NULL,
 ADD KEY `item_location` (`item_location`),
 ADD CONSTRAINT `ospos_receivings_items_ibfk_3` FOREIGN KEY (`item_location`) REFERENCES `ospos_stock_locations` (`location_id`);
 

ALTER TABLE ospos_sales_items
 ADD COLUMN item_location int(11) NOT NULL,
 ADD KEY `item_location` (`item_location`),
 ADD KEY `sale_id` (`sale_id`),
 ADD CONSTRAINT `ospos_sales_items_ibfk_3` FOREIGN KEY (`item_location`) REFERENCES `ospos_stock_locations` (`location_id`);

ALTER TABLE ospos_sales_items_taxes
 ADD KEY `sale_id` (`sale_id`);

ALTER TABLE ospos_sales_payments
 ADD KEY `sale_id` (`sale_id`);

ALTER TABLE ospos_sales_suspended_items
 ADD COLUMN item_location int(11) NOT NULL,
 ADD KEY `item_location` (`item_location`),
 ADD KEY `sale_id` (`sale_id`),
 ADD CONSTRAINT `ospos_sales_suspended_items_ibfk_3` FOREIGN KEY (`item_location`) REFERENCES `ospos_stock_locations` (`location_id`);

ALTER TABLE `ospos_item_quantities`
  ADD CONSTRAINT `ospos_item_quantities_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `ospos_items` (`item_id`),
  ADD CONSTRAINT `ospos_item_quantities_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `ospos_stock_locations` (`location_id`);

