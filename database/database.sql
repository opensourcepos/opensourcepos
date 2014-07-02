-- phpMyAdmin SQL Dump
-- version 4.0.9
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jul 02, 2014 at 06:13 AM
-- Server version: 5.5.34
-- PHP Version: 5.4.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ospos_stock_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `ospos_app_config`
--

CREATE TABLE IF NOT EXISTS `ospos_app_config` (
  `key` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_app_config`
--

INSERT INTO `ospos_app_config` (`key`, `value`) VALUES
('address', '123 Nowhere street'),
('company', 'Open Source Point of Sale'),
('currency_side', '0'),
('currency_symbol', ''),
('custom10_name', ''),
('custom1_name', ''),
('custom2_name', ''),
('custom3_name', ''),
('custom4_name', ''),
('custom5_name', ''),
('custom6_name', ''),
('custom7_name', ''),
('custom8_name', ''),
('custom9_name', ''),
('default_tax_1_name', 'Sales Tax'),
('default_tax_1_rate', ''),
('default_tax_2_name', 'Sales Tax 2'),
('default_tax_2_rate', ''),
('default_tax_rate', '8'),
('email', 'admin@pappastech.com'),
('fax', ''),
('language', 'en'),
('phone', '555-555-5555'),
('print_after_sale', '0'),
('return_policy', 'Test'),
('stock_location', 'stockD,stockE,stockA'),
('timezone', 'America/New_York'),
('website', '');

-- --------------------------------------------------------

--
-- Table structure for table `ospos_customers`
--

CREATE TABLE IF NOT EXISTS `ospos_customers` (
  `person_id` int(10) NOT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `taxable` int(1) NOT NULL DEFAULT '1',
  `deleted` int(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `account_number` (`account_number`),
  KEY `person_id` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ospos_employees`
--

CREATE TABLE IF NOT EXISTS `ospos_employees` (
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `person_id` int(10) NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `username` (`username`),
  KEY `person_id` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_employees`
--

INSERT INTO `ospos_employees` (`username`, `password`, `person_id`, `deleted`) VALUES
('admin', '439a6de57d475c1a0ba9bcb1c39f0af6', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `ospos_giftcards`
--

CREATE TABLE IF NOT EXISTS `ospos_giftcards` (
  `giftcard_id` int(11) NOT NULL AUTO_INCREMENT,
  `giftcard_number` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `value` double(15,2) NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `person_id` int(11) NOT NULL,
  PRIMARY KEY (`giftcard_id`),
  UNIQUE KEY `giftcard_number` (`giftcard_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `ospos_inventory`
--

CREATE TABLE IF NOT EXISTS `ospos_inventory` (
  `trans_id` int(11) NOT NULL AUTO_INCREMENT,
  `trans_items` int(11) NOT NULL DEFAULT '0',
  `trans_user` int(11) NOT NULL DEFAULT '0',
  `location_id` int(11) NOT NULL,
  `trans_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `trans_comment` text NOT NULL,
  `trans_inventory` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`trans_id`),
  KEY `ospos_inventory_ibfk_1` (`trans_items`),
  KEY `ospos_inventory_ibfk_2` (`trans_user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=152 ;

--
-- Dumping data for table `ospos_inventory`
--

INSERT INTO `ospos_inventory` (`trans_id`, `trans_items`, `trans_user`, `location_id`, `trans_date`, `trans_comment`, `trans_inventory`) VALUES
(133, 1, 1, 4, '2014-06-25 13:36:39', 'Manual Edit of Quantity', 0),
(134, 1, 1, 6, '2014-06-25 13:36:39', 'Manual Edit of Quantity', 0),
(135, 1, 1, 7, '2014-06-25 13:36:39', 'Manual Edit of Quantity', 0),
(136, 1, 1, 4, '2014-06-25 13:37:33', '', -3),
(137, 1, 1, 4, '2014-06-25 13:41:20', 'POS 24', -2),
(138, 1, 1, 4, '2014-07-01 04:42:02', 'Manual Edit of Quantity', 0),
(139, 1, 1, 6, '2014-07-01 04:42:02', 'Manual Edit of Quantity', 0),
(140, 1, 1, 7, '2014-07-01 04:42:02', 'Manual Edit of Quantity', 0),
(141, 1, 1, 4, '2014-07-01 05:58:00', 'Manual Edit of Quantity', 0),
(142, 1, 1, 6, '2014-07-01 05:58:00', 'Manual Edit of Quantity', 0),
(143, 1, 1, 7, '2014-07-01 05:58:01', 'Manual Edit of Quantity', 0),
(144, 13, 1, 4, '2014-07-01 17:08:41', 'Manual Edit of Quantity', 10),
(145, 13, 1, 6, '2014-07-01 17:08:41', 'Manual Edit of Quantity', 20),
(146, 13, 1, 7, '2014-07-01 17:08:41', 'Manual Edit of Quantity', 30),
(147, 15, 1, 4, '2014-07-01 17:09:00', 'Manual Edit of Quantity', 40),
(148, 15, 1, 6, '2014-07-01 17:09:00', 'Manual Edit of Quantity', 50),
(149, 15, 1, 7, '2014-07-01 17:09:00', 'Manual Edit of Quantity', 60),
(150, 13, 1, 6, '2014-07-01 17:10:02', 'POS 25', -1),
(151, 1, 1, 6, '2014-07-01 17:10:02', 'POS 25', -2);

-- --------------------------------------------------------

--
-- Table structure for table `ospos_items`
--

CREATE TABLE IF NOT EXISTS `ospos_items` (
  `name` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `item_number` varchar(255) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `cost_price` double(15,2) NOT NULL,
  `unit_price` double(15,2) NOT NULL,
  `reorder_level` double(15,2) NOT NULL DEFAULT '0.00',
  `location` varchar(255) NOT NULL,
  `item_id` int(10) NOT NULL AUTO_INCREMENT,
  `allow_alt_description` tinyint(1) NOT NULL,
  `is_serialized` tinyint(1) NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  `stock_type` enum('sale_stock','warehouse') NOT NULL DEFAULT 'warehouse',
  `custom1` varchar(25) NOT NULL,
  `custom2` varchar(25) NOT NULL,
  `custom3` varchar(25) NOT NULL,
  `custom4` varchar(25) NOT NULL,
  `custom5` varchar(25) NOT NULL,
  `custom6` varchar(25) NOT NULL,
  `custom7` varchar(25) NOT NULL,
  `custom8` varchar(25) NOT NULL,
  `custom9` varchar(25) NOT NULL,
  `custom10` varchar(25) NOT NULL,
  PRIMARY KEY (`item_id`),
  UNIQUE KEY `item_id` (`item_id`),
  UNIQUE KEY `item_id_2` (`item_id`),
  UNIQUE KEY `item_number_4` (`item_id`),
  KEY `ospos_items_ibfk_1` (`supplier_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=17 ;

--
-- Dumping data for table `ospos_items`
--

INSERT INTO `ospos_items` (`name`, `category`, `supplier_id`, `item_number`, `description`, `cost_price`, `unit_price`, `reorder_level`, `location`, `item_id`, `allow_alt_description`, `is_serialized`, `deleted`, `stock_type`, `custom1`, `custom2`, `custom3`, `custom4`, `custom5`, `custom6`, `custom7`, `custom8`, `custom9`, `custom10`) VALUES
('Chang beer', 'Drinking', NULL, '01', '', 50.00, 55.00, 5.00, '', 1, 0, 0, 0, 'warehouse', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'),
('(Pack) Chang beer', 'Drinking', NULL, '02', '', 6000.00, 6500.00, 1.00, '', 2, 0, 0, 1, 'warehouse', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'),
('Chang beer', 'Drinking', NULL, '01', '', 50.00, 60.00, 1.00, '', 12, 0, 0, 1, 'sale_stock', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'),
('Sing Ha', 'Drinking', NULL, '04', '', 60.00, 65.00, 1.00, '', 13, 0, 0, 0, 'warehouse', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'),
('Tiger beer', 'Drinking', NULL, '05', '', 50.00, 55.00, 1.00, '', 14, 0, 0, 1, 'warehouse', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'),
('Tiger beer', 'Drinking', NULL, '05', '', 50.00, 55.00, 1.00, '', 15, 0, 0, 0, 'warehouse', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'),
('test', 'Drinking', NULL, '06', '', 100.00, 100.00, 2.00, '', 16, 0, 0, 0, 'warehouse', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');

-- --------------------------------------------------------

--
-- Table structure for table `ospos_items_taxes`
--

CREATE TABLE IF NOT EXISTS `ospos_items_taxes` (
  `item_id` int(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `percent` double(15,3) NOT NULL,
  PRIMARY KEY (`item_id`,`name`,`percent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ospos_item_kits`
--

CREATE TABLE IF NOT EXISTS `ospos_item_kits` (
  `item_kit_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`item_kit_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `ospos_item_kits`
--

INSERT INTO `ospos_item_kits` (`item_kit_id`, `name`, `description`) VALUES
(1, '(Pack) Chang beer', '12 bottles of beer');

-- --------------------------------------------------------

--
-- Table structure for table `ospos_item_kit_items`
--

CREATE TABLE IF NOT EXISTS `ospos_item_kit_items` (
  `item_kit_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` double(15,2) NOT NULL,
  PRIMARY KEY (`item_kit_id`,`item_id`,`quantity`),
  KEY `ospos_item_kit_items_ibfk_2` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_item_kit_items`
--

INSERT INTO `ospos_item_kit_items` (`item_kit_id`, `item_id`, `quantity`) VALUES
(1, 1, 12.00);

-- --------------------------------------------------------

--
-- Table structure for table `ospos_item_quantitys`
--

CREATE TABLE IF NOT EXISTS `ospos_item_quantitys` (
  `item_quantity_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`item_quantity_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=69 ;

--
-- Dumping data for table `ospos_item_quantitys`
--

INSERT INTO `ospos_item_quantitys` (`item_quantity_id`, `item_id`, `location_id`, `quantity`) VALUES
(60, 1, 4, -1),
(61, 1, 6, 3),
(62, 1, 7, 6),
(63, 13, 4, 10),
(64, 13, 6, 19),
(65, 13, 7, 30),
(66, 15, 4, 40),
(67, 15, 6, 50),
(68, 15, 7, 60);

-- --------------------------------------------------------

--
-- Table structure for table `ospos_item_unit`
--

CREATE TABLE IF NOT EXISTS `ospos_item_unit` (
  `item_id` int(11) NOT NULL,
  `unit_quantity` int(11) NOT NULL,
  `related_number` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ospos_item_unit`
--

INSERT INTO `ospos_item_unit` (`item_id`, `unit_quantity`, `related_number`) VALUES
(1, 1, '01'),
(2, 12, '01'),
(12, 1, '01');

-- --------------------------------------------------------

--
-- Table structure for table `ospos_modules`
--

CREATE TABLE IF NOT EXISTS `ospos_modules` (
  `name_lang_key` varchar(255) NOT NULL,
  `desc_lang_key` varchar(255) NOT NULL,
  `sort` int(10) NOT NULL,
  `module_id` varchar(255) NOT NULL,
  PRIMARY KEY (`module_id`),
  UNIQUE KEY `desc_lang_key` (`desc_lang_key`),
  UNIQUE KEY `name_lang_key` (`name_lang_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_modules`
--

INSERT INTO `ospos_modules` (`name_lang_key`, `desc_lang_key`, `sort`, `module_id`) VALUES
('module_config', 'module_config_desc', 100, 'config'),
('module_customers', 'module_customers_desc', 10, 'customers'),
('module_employees', 'module_employees_desc', 80, 'employees'),
('module_giftcards', 'module_giftcards_desc', 90, 'giftcards'),
('module_items', 'module_items_desc', 20, 'items'),
('module_item_kits', 'module_item_kits_desc', 30, 'item_kits'),
('module_receivings', 'module_receivings_desc', 60, 'receivings'),
('module_reports', 'module_reports_desc', 50, 'reports'),
('module_sales', 'module_sales_desc', 70, 'sales'),
('module_suppliers', 'module_suppliers_desc', 40, 'suppliers');

-- --------------------------------------------------------

--
-- Table structure for table `ospos_people`
--

CREATE TABLE IF NOT EXISTS `ospos_people` (
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `phone_number` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address_1` varchar(255) NOT NULL,
  `address_2` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `zip` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `comments` text NOT NULL,
  `person_id` int(10) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`person_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `ospos_people`
--

INSERT INTO `ospos_people` (`first_name`, `last_name`, `phone_number`, `email`, `address_1`, `address_2`, `city`, `state`, `zip`, `country`, `comments`, `person_id`) VALUES
('John', 'Doe', '555-555-5555', 'admin@pappastech.com', 'Address 1', '', '', '', '', '', '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `ospos_permissions`
--

CREATE TABLE IF NOT EXISTS `ospos_permissions` (
  `module_id` varchar(255) NOT NULL,
  `person_id` int(10) NOT NULL,
  PRIMARY KEY (`module_id`,`person_id`),
  KEY `person_id` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_permissions`
--

INSERT INTO `ospos_permissions` (`module_id`, `person_id`) VALUES
('config', 1),
('customers', 1),
('employees', 1),
('giftcards', 1),
('items', 1),
('item_kits', 1),
('receivings', 1),
('reports', 1),
('sales', 1),
('suppliers', 1);

-- --------------------------------------------------------

--
-- Table structure for table `ospos_receivings`
--

CREATE TABLE IF NOT EXISTS `ospos_receivings` (
  `receiving_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `supplier_id` int(10) DEFAULT NULL,
  `employee_id` int(10) NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  `receiving_id` int(10) NOT NULL AUTO_INCREMENT,
  `payment_type` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`receiving_id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

--
-- Dumping data for table `ospos_receivings`
--

INSERT INTO `ospos_receivings` (`receiving_time`, `supplier_id`, `employee_id`, `comment`, `receiving_id`, `payment_type`) VALUES
('2014-06-17 17:09:02', NULL, 1, '', 1, 'Cash'),
('2014-06-17 17:10:40', NULL, 1, '', 2, 'Cash'),
('2014-06-22 09:57:57', NULL, 1, '', 3, 'Cash'),
('2014-06-22 09:59:54', NULL, 1, '', 4, 'Cash'),
('2014-06-22 11:20:34', NULL, 1, '', 7, 'Cash'),
('2014-06-22 11:22:28', NULL, 1, '', 8, 'Cash'),
('2014-06-22 11:24:33', NULL, 1, '', 9, 'Cash'),
('2014-06-23 17:18:55', NULL, 1, '', 10, 'Cash'),
('2014-06-23 17:19:52', NULL, 1, '', 11, 'Cash'),
('2014-06-23 17:23:01', NULL, 1, '', 12, 'Cash'),
('2014-06-23 17:26:49', NULL, 1, '', 13, 'Cash'),
('2014-06-23 17:27:31', NULL, 1, '', 14, 'Cash');

-- --------------------------------------------------------

--
-- Table structure for table `ospos_receivings_items`
--

CREATE TABLE IF NOT EXISTS `ospos_receivings_items` (
  `receiving_id` int(10) NOT NULL DEFAULT '0',
  `item_id` int(10) NOT NULL DEFAULT '0',
  `description` varchar(30) DEFAULT NULL,
  `serialnumber` varchar(30) DEFAULT NULL,
  `line` int(3) NOT NULL,
  `quantity_purchased` int(10) NOT NULL DEFAULT '0',
  `item_cost_price` decimal(15,2) NOT NULL,
  `item_unit_price` double(15,2) NOT NULL,
  `discount_percent` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`receiving_id`,`item_id`,`line`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_receivings_items`
--

INSERT INTO `ospos_receivings_items` (`receiving_id`, `item_id`, `description`, `serialnumber`, `line`, `quantity_purchased`, `item_cost_price`, `item_unit_price`, `discount_percent`) VALUES
(1, 1, '', '0', 1, 50, '50.00', 50.00, 0),
(2, 1, '', '0', 1, -2, '50.00', 50.00, 0),
(3, 1, '', '0', 1, 10, '50.00', 50.00, 0),
(4, 1, '', '0', 1, 10, '50.00', 50.00, 0),
(7, 13, '', '0', 1, 2, '60.00', 60.00, 0),
(8, 13, '', '0', 1, 2, '60.00', 60.00, 0),
(9, 1, '', '0', 1, 4, '50.00', 50.00, 0),
(9, 13, '', '0', 2, 2, '60.00', 60.00, 0),
(10, 1, '', '0', 1, 3, '50.00', 50.00, 0),
(11, 1, '', '', 1, 1, '50.00', 50.00, 0),
(12, 1, '', '', 1, 1, '50.00', 50.00, 0),
(13, 1, '', '', 1, 1, '50.00', 50.00, 0),
(14, 1, '', '', 1, 1, '50.00', 50.00, 0);

-- --------------------------------------------------------

--
-- Table structure for table `ospos_requisitions`
--

CREATE TABLE IF NOT EXISTS `ospos_requisitions` (
  `requisition_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `employee_id` int(11) NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  `requisition_id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`requisition_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `ospos_requisitions`
--

INSERT INTO `ospos_requisitions` (`requisition_time`, `employee_id`, `comment`, `requisition_id`) VALUES
('2014-06-17 15:57:59', 1, '', 7),
('2014-06-17 16:09:07', 1, '', 8),
('2014-06-17 16:10:58', 1, '', 9),
('2014-06-17 16:40:03', 1, '', 10);

-- --------------------------------------------------------

--
-- Table structure for table `ospos_requisitions_items`
--

CREATE TABLE IF NOT EXISTS `ospos_requisitions_items` (
  `requisition_id` int(10) NOT NULL DEFAULT '0',
  `item_id` int(11) NOT NULL DEFAULT '0',
  `line` int(3) NOT NULL,
  `requisition_quantity` int(10) NOT NULL DEFAULT '0',
  `related_item_id` int(10) NOT NULL DEFAULT '0',
  `related_item_quantity` int(11) NOT NULL,
  `related_item_total_quantity` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`requisition_id`,`item_id`,`line`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_requisitions_items`
--

INSERT INTO `ospos_requisitions_items` (`requisition_id`, `item_id`, `line`, `requisition_quantity`, `related_item_id`, `related_item_quantity`, `related_item_total_quantity`) VALUES
(7, 2, 1, 2, 12, 12, 24),
(8, 1, 2, 10, 12, 1, 10),
(8, 2, 1, 1, 12, 12, 12),
(9, 1, 1, 4, 12, 1, 4),
(10, 1, 1, 1, 12, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `ospos_sales`
--

CREATE TABLE IF NOT EXISTS `ospos_sales` (
  `sale_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `customer_id` int(10) DEFAULT NULL,
  `employee_id` int(10) NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  `sale_id` int(10) NOT NULL AUTO_INCREMENT,
  `payment_type` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`sale_id`),
  KEY `customer_id` (`customer_id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=26 ;

--
-- Dumping data for table `ospos_sales`
--

INSERT INTO `ospos_sales` (`sale_time`, `customer_id`, `employee_id`, `comment`, `sale_id`, `payment_type`) VALUES
('2014-06-22 06:18:43', NULL, 1, '0', 1, 'Cash: $480.00<br />'),
('2014-06-22 06:46:30', NULL, 1, '0', 2, 'Cash: $480.00<br />'),
('2014-06-22 06:54:07', NULL, 1, '0', 3, 'Cash: $480.00<br />'),
('2014-06-22 06:56:13', NULL, 1, '0', 4, 'Cash: $55.00<br />'),
('2014-06-22 13:21:14', NULL, 1, '0', 5, 'Cash: $480.00<br />'),
('2014-06-22 13:31:30', NULL, 1, '0', 6, 'Cash: $480.00<br />'),
('2014-06-22 14:05:37', NULL, 1, '0', 7, 'Cash: $480.00<br />'),
('2014-06-22 14:07:33', NULL, 1, '0', 8, 'Cash: $260.00<br />'),
('2014-06-23 03:57:39', NULL, 1, '0', 9, 'Cash: $285.00<br />'),
('2014-06-23 04:00:37', NULL, 1, '0', 10, 'Cash: $65.00<br />'),
('2014-06-23 04:04:30', NULL, 1, '0', 13, 'Cash: $55.00<br />'),
('2014-06-23 04:16:29', NULL, 1, '0', 14, 'Cash: -$55.00<br />'),
('2014-06-23 04:18:18', NULL, 1, '0', 15, 'Cash: $55.00<br />'),
('2014-06-23 05:34:09', NULL, 1, '0', 16, 'Cash: $55.00<br />'),
('2014-06-25 03:00:07', NULL, 1, '0', 23, 'Cash: $2750.00<br />'),
('2014-06-25 13:41:20', NULL, 1, '0', 24, 'Cash: $110.00<br />'),
('2014-07-01 17:10:02', NULL, 1, '0', 25, 'Cash: $175.00<br />');

-- --------------------------------------------------------

--
-- Table structure for table `ospos_sales_items`
--

CREATE TABLE IF NOT EXISTS `ospos_sales_items` (
  `sale_id` int(10) NOT NULL DEFAULT '0',
  `item_id` int(10) NOT NULL DEFAULT '0',
  `description` varchar(30) DEFAULT NULL,
  `serialnumber` varchar(30) DEFAULT NULL,
  `line` int(3) NOT NULL DEFAULT '0',
  `quantity_purchased` double(15,2) NOT NULL DEFAULT '0.00',
  `item_cost_price` decimal(15,2) NOT NULL,
  `item_unit_price` double(15,2) NOT NULL,
  `discount_percent` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sale_id`,`item_id`,`line`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_sales_items`
--

INSERT INTO `ospos_sales_items` (`sale_id`, `item_id`, `description`, `serialnumber`, `line`, `quantity_purchased`, `item_cost_price`, `item_unit_price`, `discount_percent`) VALUES
(1, 1, '', '', 1, 4.00, '50.00', 55.00, 0),
(1, 13, '', '', 2, 4.00, '60.00', 65.00, 0),
(2, 1, '', '', 1, 4.00, '50.00', 55.00, 0),
(2, 13, '', '', 2, 4.00, '60.00', 65.00, 0),
(3, 1, '', '', 1, 4.00, '50.00', 55.00, 0),
(3, 13, '', '', 2, 4.00, '60.00', 65.00, 0),
(4, 1, '', '', 1, 1.00, '50.00', 55.00, 0),
(5, 1, '', '', 1, 4.00, '50.00', 55.00, 0),
(5, 13, '', '', 2, 4.00, '60.00', 65.00, 0),
(6, 1, '', '', 1, 4.00, '50.00', 55.00, 0),
(6, 13, '', '', 2, 4.00, '60.00', 65.00, 0),
(7, 1, '', '', 1, 4.00, '50.00', 55.00, 0),
(7, 13, '', '', 2, 4.00, '60.00', 65.00, 0),
(8, 13, '', '', 1, 4.00, '60.00', 65.00, 0),
(9, 1, '', '', 1, 4.00, '50.00', 55.00, 0),
(9, 13, '', '', 2, 1.00, '60.00', 65.00, 0),
(10, 13, '', '', 1, 1.00, '60.00', 65.00, 0),
(13, 1, '', '', 1, 1.00, '50.00', 55.00, 0),
(14, 1, '', '', 1, -1.00, '50.00', 55.00, 0),
(15, 1, '', '', 1, 1.00, '50.00', 55.00, 0),
(16, 1, '', '', 1, 1.00, '50.00', 55.00, 0),
(23, 1, '', '', 1, 50.00, '50.00', 55.00, 0),
(24, 1, '', '', 2, 2.00, '50.00', 55.00, 0),
(25, 1, '', '', 2, 2.00, '50.00', 55.00, 0),
(25, 13, '', '', 1, 1.00, '60.00', 65.00, 0);

-- --------------------------------------------------------

--
-- Table structure for table `ospos_sales_items_taxes`
--

CREATE TABLE IF NOT EXISTS `ospos_sales_items_taxes` (
  `sale_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  `line` int(3) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `percent` double(15,3) NOT NULL,
  PRIMARY KEY (`sale_id`,`item_id`,`line`,`name`,`percent`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ospos_sales_payments`
--

CREATE TABLE IF NOT EXISTS `ospos_sales_payments` (
  `sale_id` int(10) NOT NULL,
  `payment_type` varchar(40) NOT NULL,
  `payment_amount` decimal(15,2) NOT NULL,
  PRIMARY KEY (`sale_id`,`payment_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_sales_payments`
--

INSERT INTO `ospos_sales_payments` (`sale_id`, `payment_type`, `payment_amount`) VALUES
(1, 'Cash', '480.00'),
(2, 'Cash', '480.00'),
(3, 'Cash', '480.00'),
(4, 'Cash', '55.00'),
(5, 'Cash', '480.00'),
(6, 'Cash', '480.00'),
(7, 'Cash', '480.00'),
(8, 'Cash', '260.00'),
(9, 'Cash', '285.00'),
(10, 'Cash', '65.00'),
(13, 'Cash', '55.00'),
(14, 'Cash', '-55.00'),
(15, 'Cash', '55.00'),
(16, 'Cash', '55.00'),
(23, 'Cash', '2750.00'),
(24, 'Cash', '110.00'),
(25, 'Cash', '175.00');

-- --------------------------------------------------------

--
-- Table structure for table `ospos_sales_suspended`
--

CREATE TABLE IF NOT EXISTS `ospos_sales_suspended` (
  `sale_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `customer_id` int(10) DEFAULT NULL,
  `employee_id` int(10) NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  `sale_id` int(10) NOT NULL AUTO_INCREMENT,
  `payment_type` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`sale_id`),
  KEY `customer_id` (`customer_id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `ospos_sales_suspended_items`
--

CREATE TABLE IF NOT EXISTS `ospos_sales_suspended_items` (
  `sale_id` int(10) NOT NULL DEFAULT '0',
  `item_id` int(10) NOT NULL DEFAULT '0',
  `description` varchar(30) DEFAULT NULL,
  `serialnumber` varchar(30) DEFAULT NULL,
  `line` int(3) NOT NULL DEFAULT '0',
  `quantity_purchased` double(15,2) NOT NULL DEFAULT '0.00',
  `item_cost_price` decimal(15,2) NOT NULL,
  `item_unit_price` double(15,2) NOT NULL,
  `discount_percent` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sale_id`,`item_id`,`line`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ospos_sales_suspended_items_taxes`
--

CREATE TABLE IF NOT EXISTS `ospos_sales_suspended_items_taxes` (
  `sale_id` int(10) NOT NULL,
  `item_id` int(10) NOT NULL,
  `line` int(3) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `percent` double(15,3) NOT NULL,
  PRIMARY KEY (`sale_id`,`item_id`,`line`,`name`,`percent`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ospos_sales_suspended_payments`
--

CREATE TABLE IF NOT EXISTS `ospos_sales_suspended_payments` (
  `sale_id` int(10) NOT NULL,
  `payment_type` varchar(40) NOT NULL,
  `payment_amount` decimal(15,2) NOT NULL,
  PRIMARY KEY (`sale_id`,`payment_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ospos_sessions`
--

CREATE TABLE IF NOT EXISTS `ospos_sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) NOT NULL DEFAULT '0',
  `user_agent` varchar(120) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` text,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_sessions`
--

INSERT INTO `ospos_sessions` (`session_id`, `ip_address`, `user_agent`, `last_activity`, `user_data`) VALUES
('48a2db903a522a52236bafa2466d941f', '0.0.0.0', 'Mozilla/5.0 (Windows NT 6.2; rv:30.0) Gecko/20100101 Firefox/30.0 FirePHP/0.7.4', 1403454059, 'a:9:{s:9:"user_data";s:0:"";s:9:"person_id";s:1:"1";s:9:"recv_mode";s:6:"stockA";s:8:"supplier";i:-1;s:9:"sale_mode";b:0;s:4:"cart";a:0:{}s:8:"customer";i:-1;s:8:"payments";a:0:{}s:8:"cartRecv";a:0:{}}'),
('bc68acd9512665d9fa88cb32a0c5cc9d', '0.0.0.0', 'Mozilla/5.0 (Windows NT 6.2; rv:30.0) Gecko/20100101 Firefox/30.0', 1403624516, 'a:12:{s:9:"user_data";s:0:"";s:9:"person_id";s:1:"1";s:13:"sale_location";s:7:"stock_7";s:9:"sale_mode";s:4:"sale";s:4:"cart";a:1:{i:1;a:11:{s:7:"item_id";s:1:"1";s:4:"line";i:1;s:4:"name";s:10:"Chang beer";s:11:"item_number";s:2:"01";s:11:"description";s:0:"";s:12:"serialnumber";s:0:"";s:21:"allow_alt_description";s:1:"0";s:13:"is_serialized";s:1:"0";s:8:"quantity";i:2;s:8:"discount";i:0;s:5:"price";s:5:"55.00";}}s:8:"customer";i:-1;s:8:"payments";a:0:{}s:9:"recv_mode";s:6:"return";s:17:"recv_stock_source";s:7:"stock_6";s:22:"recv_stock_destination";s:7:"stock_4";s:8:"supplier";i:-1;s:8:"cartRecv";a:0:{}}'),
('bf7ea1962265e53d4c6ad896b14cb702', '0.0.0.0', 'Mozilla/5.0 (Windows NT 6.2; rv:30.0) Gecko/20100101 Firefox/30.0 FirePHP/0.7.4', 1404201007, 'a:12:{s:9:"user_data";s:0:"";s:9:"person_id";s:1:"1";s:9:"recv_mode";s:7:"receive";s:17:"recv_stock_source";s:7:"stock_4";s:22:"recv_stock_destination";s:7:"stock_4";s:13:"sale_location";s:7:"stock_6";s:8:"supplier";i:-1;s:8:"cartRecv";a:0:{}s:4:"cart";a:0:{}s:9:"sale_mode";s:4:"sale";s:8:"customer";i:-1;s:8:"payments";a:0:{}}'),
('c51abb8d7500cd47aa11a90c2f748a65', '0.0.0.0', 'Mozilla/5.0 (Windows NT 6.2; rv:30.0) Gecko/20100101 Firefox/30.0 FirePHP/0.7.4', 1403702064, 'a:12:{s:9:"user_data";s:0:"";s:9:"person_id";s:1:"1";s:13:"sale_location";s:7:"stock_7";s:9:"recv_mode";s:7:"receive";s:17:"recv_stock_source";s:7:"stock_4";s:22:"recv_stock_destination";s:7:"stock_4";s:8:"supplier";i:-1;s:8:"cartRecv";a:0:{}s:9:"sale_mode";s:4:"sale";s:4:"cart";a:0:{}s:8:"customer";i:-1;s:8:"payments";a:0:{}}'),
('c7739efbefa39eef2422d0d0e68e6741', '0.0.0.0', 'Mozilla/5.0 (Windows NT 6.2; rv:30.0) Gecko/20100101 Firefox/30.0', 1403460585, ''),
('da0595ba7dbcb49c52fa2d30aa1c5618', '0.0.0.0', 'Mozilla/5.0 (Windows NT 6.2; rv:29.0) Gecko/20100101 Firefox/29.0', 1403017869, 'a:3:{s:9:"user_data";s:0:"";s:9:"person_id";s:1:"1";s:9:"recv_mode";s:6:"return";}');

-- --------------------------------------------------------

--
-- Table structure for table `ospos_stock_locations`
--

CREATE TABLE IF NOT EXISTS `ospos_stock_locations` (
  `location_id` int(11) NOT NULL AUTO_INCREMENT,
  `location_name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`location_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `ospos_stock_locations`
--

INSERT INTO `ospos_stock_locations` (`location_id`, `location_name`, `deleted`) VALUES
(3, 'stockC', 1),
(4, 'stockA', 0),
(5, 'stockB', 1),
(6, 'stockD', 0),
(7, 'stockE', 0);

-- --------------------------------------------------------

--
-- Table structure for table `ospos_suppliers`
--

CREATE TABLE IF NOT EXISTS `ospos_suppliers` (
  `person_id` int(10) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `deleted` int(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `account_number` (`account_number`),
  KEY `person_id` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ospos_customers`
--
ALTER TABLE `ospos_customers`
  ADD CONSTRAINT `ospos_customers_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `ospos_people` (`person_id`);

--
-- Constraints for table `ospos_employees`
--
ALTER TABLE `ospos_employees`
  ADD CONSTRAINT `ospos_employees_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `ospos_people` (`person_id`);

--
-- Constraints for table `ospos_inventory`
--
ALTER TABLE `ospos_inventory`
  ADD CONSTRAINT `ospos_inventory_ibfk_1` FOREIGN KEY (`trans_items`) REFERENCES `ospos_items` (`item_id`),
  ADD CONSTRAINT `ospos_inventory_ibfk_2` FOREIGN KEY (`trans_user`) REFERENCES `ospos_employees` (`person_id`);

--
-- Constraints for table `ospos_items`
--
ALTER TABLE `ospos_items`
  ADD CONSTRAINT `ospos_items_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `ospos_suppliers` (`person_id`);

--
-- Constraints for table `ospos_items_taxes`
--
ALTER TABLE `ospos_items_taxes`
  ADD CONSTRAINT `ospos_items_taxes_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `ospos_items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `ospos_item_kit_items`
--
ALTER TABLE `ospos_item_kit_items`
  ADD CONSTRAINT `ospos_item_kit_items_ibfk_1` FOREIGN KEY (`item_kit_id`) REFERENCES `ospos_item_kits` (`item_kit_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ospos_item_kit_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `ospos_items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `ospos_item_unit`
--
ALTER TABLE `ospos_item_unit`
  ADD CONSTRAINT `ospos_item_unit_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `ospos_items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `ospos_permissions`
--
ALTER TABLE `ospos_permissions`
  ADD CONSTRAINT `ospos_permissions_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `ospos_employees` (`person_id`),
  ADD CONSTRAINT `ospos_permissions_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `ospos_modules` (`module_id`);

--
-- Constraints for table `ospos_receivings`
--
ALTER TABLE `ospos_receivings`
  ADD CONSTRAINT `ospos_receivings_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `ospos_employees` (`person_id`),
  ADD CONSTRAINT `ospos_receivings_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `ospos_suppliers` (`person_id`);

--
-- Constraints for table `ospos_receivings_items`
--
ALTER TABLE `ospos_receivings_items`
  ADD CONSTRAINT `ospos_receivings_items_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `ospos_items` (`item_id`),
  ADD CONSTRAINT `ospos_receivings_items_ibfk_2` FOREIGN KEY (`receiving_id`) REFERENCES `ospos_receivings` (`receiving_id`);

--
-- Constraints for table `ospos_requisitions_items`
--
ALTER TABLE `ospos_requisitions_items`
  ADD CONSTRAINT `ospos_requisitions_items_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `ospos_requisitions` (`requisition_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ospos_requisitions_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `ospos_items` (`item_id`);

--
-- Constraints for table `ospos_sales`
--
ALTER TABLE `ospos_sales`
  ADD CONSTRAINT `ospos_sales_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `ospos_employees` (`person_id`),
  ADD CONSTRAINT `ospos_sales_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `ospos_customers` (`person_id`);

--
-- Constraints for table `ospos_sales_items`
--
ALTER TABLE `ospos_sales_items`
  ADD CONSTRAINT `ospos_sales_items_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `ospos_items` (`item_id`),
  ADD CONSTRAINT `ospos_sales_items_ibfk_2` FOREIGN KEY (`sale_id`) REFERENCES `ospos_sales` (`sale_id`) ON DELETE CASCADE;

--
-- Constraints for table `ospos_sales_items_taxes`
--
ALTER TABLE `ospos_sales_items_taxes`
  ADD CONSTRAINT `ospos_sales_items_taxes_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `ospos_sales_items` (`sale_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ospos_sales_items_taxes_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `ospos_items` (`item_id`);

--
-- Constraints for table `ospos_sales_payments`
--
ALTER TABLE `ospos_sales_payments`
  ADD CONSTRAINT `ospos_sales_payments_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `ospos_sales` (`sale_id`) ON DELETE CASCADE;

--
-- Constraints for table `ospos_sales_suspended`
--
ALTER TABLE `ospos_sales_suspended`
  ADD CONSTRAINT `ospos_sales_suspended_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `ospos_employees` (`person_id`),
  ADD CONSTRAINT `ospos_sales_suspended_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `ospos_customers` (`person_id`);

--
-- Constraints for table `ospos_sales_suspended_items`
--
ALTER TABLE `ospos_sales_suspended_items`
  ADD CONSTRAINT `ospos_sales_suspended_items_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `ospos_items` (`item_id`),
  ADD CONSTRAINT `ospos_sales_suspended_items_ibfk_2` FOREIGN KEY (`sale_id`) REFERENCES `ospos_sales_suspended` (`sale_id`) ON DELETE CASCADE;

--
-- Constraints for table `ospos_sales_suspended_items_taxes`
--
ALTER TABLE `ospos_sales_suspended_items_taxes`
  ADD CONSTRAINT `ospos_sales_suspended_items_taxes_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `ospos_sales_suspended_items` (`sale_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ospos_sales_suspended_items_taxes_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `ospos_items` (`item_id`);

--
-- Constraints for table `ospos_sales_suspended_payments`
--
ALTER TABLE `ospos_sales_suspended_payments`
  ADD CONSTRAINT `ospos_sales_suspended_payments_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `ospos_sales_suspended` (`sale_id`) ON DELETE CASCADE;

--
-- Constraints for table `ospos_suppliers`
--
ALTER TABLE `ospos_suppliers`
  ADD CONSTRAINT `ospos_suppliers_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `ospos_people` (`person_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
