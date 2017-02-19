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
 ADD COLUMN `dinner_table_id` int(11) NULL;

ALTER TABLE `ospos_sales`
 ADD KEY `dinner_table_id` (`dinner_table_id`),
 ADD CONSTRAINT `ospos_sales_ibfk_3` FOREIGN KEY (`dinner_table_id`) REFERENCES `ospos_dinner_tables` (`dinner_table_id`);

-- alter ospos_sales_suspended table
ALTER TABLE `ospos_sales_suspended`
 ADD COLUMN `dinner_table_id` int(11) NULL;

ALTER TABLE `ospos_sales_suspended`
 ADD KEY `dinner_table_id` (`dinner_table_id`),
 ADD CONSTRAINT `ospos_sales_suspended_ibfk_3` FOREIGN KEY (`dinner_table_id`) REFERENCES `ospos_dinner_tables` (`dinner_table_id`);