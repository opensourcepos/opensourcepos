
--
-- Table structure for table `ospos_app_config`
--

CREATE TABLE `ospos_app_config` (
    `key` varchar(50) NOT NULL,
    `value` varchar(500) NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_app_config`
--

INSERT INTO `ospos_app_config` (`key`, `value`) VALUES
    ('address', '123 Nowhere street'),
    ('company', 'Open Source Point of Sale'),
    ('default_tax_rate', '8'),
    ('email', 'changeme@example.com'),
    ('fax', ''),
    ('phone', '555-555-5555'),
    ('return_policy', 'Test'),
    ('timezone', 'America/New_York'),
    ('website', ''),
    ('company_logo', ''),
    ('tax_included', '0'),
    ('barcode_content', 'id'),
    ('barcode_type', 'Code39'),
    ('barcode_width', '250'),
    ('barcode_height', '50'),
    ('barcode_quality', '100'),
    ('barcode_font', 'Arial'),
    ('barcode_font_size', '10'),
    ('barcode_first_row', 'category'),
    ('barcode_second_row', 'item_code'),
    ('barcode_third_row', 'unit_price'),
    ('barcode_num_in_row', '2'),
    ('barcode_page_width', '100'),
    ('barcode_page_cellspacing', '20'),
    ('barcode_generate_if_empty', '0'),
    ('receipt_show_taxes', '0'),
    ('receipt_show_total_discount', '1'),
    ('receipt_show_description', '1'),
    ('receipt_show_serialnumber', '1'),
    ('invoice_enable', '1'),
    ('recv_invoice_format', '$CO'),
    ('sales_invoice_format', '$CO'),
    ('invoice_email_message', 'Dear $CU, In attachment the receipt for sale $INV'),
    ('invoice_default_comments', 'This is a default comment'),
    ('print_silently', '1'),
    ('print_header', '0'),
    ('print_footer', '0'),
    ('print_top_margin', '0'),
    ('print_left_margin', '0'),
    ('print_bottom_margin', '0'),
    ('print_right_margin', '0'),
    ('default_sales_discount', '0'),
    ('lines_per_page', '25'),
    ('dateformat', 'm/d/Y'),
    ('timeformat', 'H:i:s'),
    ('currency_symbol', '$'),
    ('number_locale', 'en_US'),
    ('thousands_separator', '1'),
    ('currency_decimals', '2'),
    ('tax_decimals', '2'),
    ('quantity_decimals', '0'),
    ('country_codes', 'us'),
    ('msg_msg', ''),
    ('msg_uid', ''),
    ('msg_src', ''),
    ('msg_pwd', ''),
    ('notify_horizontal_position', 'center'),
    ('notify_vertical_position', 'bottom'),
    ('payment_options_order', 'cashdebitcredit'),
    ('protocol', 'mail'),
    ('mailpath', '/usr/sbin/sendmail'),
    ('smtp_port', '465'),
    ('smtp_timeout', '5'),
    ('smtp_crypto', 'ssl'),
    ('receipt_template', 'receipt_default'),
    ('theme', 'flatly'),
    ('statistics', '1'),
    ('language', 'english'),
    ('language_code', 'en');


-- --------------------------------------------------------

--
-- Table structure for table `ospos_customers`
--

CREATE TABLE `ospos_customers` (
    `person_id` int(10) NOT NULL,
    `company_name` varchar(255) DEFAULT NULL,
    `account_number` varchar(255) DEFAULT NULL,
    `taxable` int(1) NOT NULL DEFAULT '1',
    `discount_percent` decimal(15,2) NOT NULL DEFAULT '0',
    `deleted` int(1) NOT NULL DEFAULT '0',
    PRIMARY KEY `person_id` (`person_id`),
    UNIQUE KEY `account_number` (`account_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_customers`
--


-- --------------------------------------------------------

--
-- Table structure for table `ospos_employees`
--

CREATE TABLE `ospos_employees` (
    `username` varchar(255) NOT NULL,
    `password` varchar(255) NOT NULL,
    `person_id` int(10) NOT NULL,
    `deleted` int(1) NOT NULL DEFAULT '0',
    `hash_version` int(1) NOT NULL DEFAULT '2',
    PRIMARY KEY `person_id` (`person_id`),
    UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_employees`
--

INSERT INTO `ospos_employees` (`username`, `password`, `person_id`, `deleted`, `hash_version`) VALUES
    ('admin', '$2y$10$vJBSMlD02EC7ENSrKfVQXuvq9tNRHMtcOA8MSK2NYS748HHWm.gcG', 1, 0, 2);

-- --------------------------------------------------------

--
-- Table structure for table `ospos_giftcards`
--

CREATE TABLE `ospos_giftcards` (
    `record_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `giftcard_id` int(11) NOT NULL AUTO_INCREMENT,
    `giftcard_number` int(10) NOT NULL,
    `value` decimal(15,2) NOT NULL,
    `deleted` int(1) NOT NULL DEFAULT '0',
    `person_id` INT(10) DEFAULT NULL,
    PRIMARY KEY (`giftcard_id`),
    UNIQUE KEY `giftcard_number` (`giftcard_number`),
    KEY `person_id` (`person_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_giftcards`
--


-- --------------------------------------------------------

--
-- Table structure for table `ospos_inventory`
--

CREATE TABLE `ospos_inventory` (
    `trans_id` int(11) NOT NULL AUTO_INCREMENT,
    `trans_items` int(11) NOT NULL DEFAULT '0',
    `trans_user` int(11) NOT NULL DEFAULT '0',
    `trans_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `trans_comment` text NOT NULL,
    `trans_location` int(11) NOT NULL,
    `trans_inventory` decimal(15,3) NOT NULL DEFAULT '0',
    PRIMARY KEY (`trans_id`),
    KEY `trans_items` (`trans_items`),
    KEY `trans_user` (`trans_user`),
    KEY `trans_location` (`trans_location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8  ;

--
-- Dumping data for table `ospos_inventory`
--


-- --------------------------------------------------------

--
-- Table structure for table `ospos_items`
--

CREATE TABLE `ospos_items` (
    `name` varchar(255) NOT NULL,
    `category` varchar(255) NOT NULL,
    `supplier_id` int(11) DEFAULT NULL,
    `item_number` varchar(255) DEFAULT NULL,
    `description` varchar(255) NOT NULL,
    `cost_price` decimal(15,2) NOT NULL,
    `unit_price` decimal(15,2) NOT NULL,
    `reorder_level` decimal(15,3) NOT NULL DEFAULT '0',
    `receiving_quantity` decimal(15,3) NOT NULL DEFAULT '1',
    `item_id` int(10) NOT NULL AUTO_INCREMENT,
    `pic_id` int(10) DEFAULT NULL,
    `allow_alt_description` tinyint(1) NOT NULL,
    `is_serialized` tinyint(1) NOT NULL,
    `deleted` int(1) NOT NULL DEFAULT '0',
    `custom1` VARCHAR(25) NOT NULL,
    `custom2` VARCHAR(25) NOT NULL,
    `custom3` VARCHAR(25) NOT NULL,
    `custom4` VARCHAR(25) NOT NULL,
    `custom5` VARCHAR(25) NOT NULL,
    `custom6` VARCHAR(25) NOT NULL,
    `custom7` VARCHAR(25) NOT NULL,
    `custom8` VARCHAR(25) NOT NULL,
    `custom9` VARCHAR(25) NOT NULL,
    `custom10` VARCHAR(25) NOT NULL,
    PRIMARY KEY (`item_id`),
    UNIQUE KEY `item_number` (`item_number`),
    KEY `supplier_id` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8  ;

--
-- Dumping data for table `ospos_items`
--


-- --------------------------------------------------------

--
-- Table structure for table `ospos_items_taxes`
--

CREATE TABLE `ospos_items_taxes` (
    `item_id` int(10) NOT NULL,
    `name` varchar(255) NOT NULL,
    `percent` decimal(15,3) NOT NULL,
    PRIMARY KEY (`item_id`,`name`,`percent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_items_taxes`
--


-- --------------------------------------------------------

--
-- Table structure for table `ospos_item_kits`
--

CREATE TABLE `ospos_item_kits` (
    `item_kit_id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `description` varchar(255) NOT NULL,
    PRIMARY KEY (`item_kit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8  ;

--
-- Dumping data for table `ospos_item_kits`
--


-- --------------------------------------------------------

--
-- Table structure for table `ospos_item_kit_items`
--

CREATE TABLE `ospos_item_kit_items` (
    `item_kit_id` int(11) NOT NULL,
    `item_id` int(11) NOT NULL,
    `quantity` decimal(15,3) NOT NULL,
    PRIMARY KEY (`item_kit_id`,`item_id`,`quantity`),
    KEY `ospos_item_kit_items_ibfk_2` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_item_kit_items`
--

-- --------------------------------------------------------

--
-- Table structure for table `ospos_item_quantities`
--

CREATE TABLE IF NOT EXISTS `ospos_item_quantities` (
    `item_id` int(11) NOT NULL,
    `location_id` int(11) NOT NULL,
    `quantity` decimal(15,3) NOT NULL DEFAULT '0',
    PRIMARY KEY (`item_id`,`location_id`),
    KEY `item_id` (`item_id`),
    KEY `location_id` (`location_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- Table structure for table `ospos_modules`
--

CREATE TABLE `ospos_modules` (
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
    ('module_config', 'module_config_desc', 110, 'config'),
    ('module_customers', 'module_customers_desc', 10, 'customers'),
    ('module_employees', 'module_employees_desc', 80, 'employees'),
    ('module_giftcards', 'module_giftcards_desc', 90, 'giftcards'),
    ('module_items', 'module_items_desc', 20, 'items'),
    ('module_item_kits', 'module_item_kits_desc', 30, 'item_kits'),
    ('module_messages', 'module_messages_desc', 100, 'messages'),
    ('module_receivings', 'module_receivings_desc', 60, 'receivings'),
    ('module_reports', 'module_reports_desc', 50, 'reports'),
    ('module_sales', 'module_sales_desc', 70, 'sales'),
    ('module_suppliers', 'module_suppliers_desc', 40, 'suppliers');

-- --------------------------------------------------------

--
-- Table structure for table `ospos_people`
--

CREATE TABLE `ospos_people` (
    `first_name` varchar(255) NOT NULL,
    `last_name` varchar(255) NOT NULL,
    `gender` int(1) DEFAULT NULL,
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8  ;

--
-- Dumping data for table `ospos_people`
--

INSERT INTO `ospos_people` (`first_name`, `last_name`, `phone_number`, `email`, `address_1`, `address_2`, `city`, `state`, `zip`, `country`, `comments`, `person_id`) VALUES
    ('John', 'Doe', '555-555-5555', 'changeme@example.com', 'Address 1', '', '', '', '', '', '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `ospos_permissions`
--

CREATE TABLE `ospos_permissions` (
    `permission_id` varchar(255) NOT NULL,
    `module_id` varchar(255) NOT NULL,
    `location_id` int(10) DEFAULT NULL,
    PRIMARY KEY (`permission_id`),
    KEY `module_id` (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_permissions`
--

INSERT INTO `ospos_permissions` (`permission_id`, `module_id`) VALUES
    ('reports_customers', 'reports'),
    ('reports_receivings', 'reports'),
    ('reports_items', 'reports'),
    ('reports_employees', 'reports'),
    ('reports_suppliers', 'reports'),
    ('reports_sales', 'reports'),
    ('reports_discounts', 'reports'),
    ('reports_taxes', 'reports'),
    ('reports_inventory', 'reports'),
    ('reports_categories', 'reports'),
    ('reports_payments', 'reports'),
    ('customers', 'customers'),
    ('employees', 'employees'),
    ('giftcards', 'giftcards'),
    ('items', 'items'),
    ('item_kits', 'item_kits'),
    ('messages', 'messages'),
    ('receivings', 'receivings'),
    ('reports', 'reports'),
    ('sales', 'sales'),
    ('config', 'config'),
    ('suppliers', 'suppliers');

INSERT INTO `ospos_permissions` (`permission_id`, `module_id`, `location_id`) VALUES
    ('items_stock', 'items', 1),
    ('sales_stock', 'sales', 1),
    ('receivings_stock', 'receivings', 1);

-- --------------------------------------------------------

--
-- Table structure for table `ospos_grants`
--

CREATE TABLE `ospos_grants` (
    `permission_id` varchar(255) NOT NULL,
    `person_id` int(10) NOT NULL,
    PRIMARY KEY (`permission_id`,`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_grants`
--
-- --------------------------------------------------------

INSERT INTO `ospos_grants` (`permission_id`, `person_id`) VALUES
    ('reports_customers', 1),
    ('reports_receivings', 1),
    ('reports_items', 1),
    ('reports_inventory', 1),
    ('reports_employees', 1),
    ('reports_suppliers', 1),
    ('reports_sales', 1),
    ('reports_discounts', 1),
    ('reports_taxes', 1),
    ('reports_categories', 1),
    ('reports_payments', 1),
    ('customers', 1),
    ('employees', 1),
    ('giftcards', 1),
    ('items', 1),
    ('item_kits', 1),
    ('messages', 1),
    ('receivings', 1),
    ('reports', 1),
    ('sales', 1),
    ('config', 1),
    ('items_stock', 1),
    ('sales_stock', 1),
    ('receivings_stock', 1),
    ('suppliers', 1);

--
-- Table structure for table `ospos_receivings`
--

CREATE TABLE `ospos_receivings` (
    `receiving_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `supplier_id` int(10) DEFAULT NULL,
    `employee_id` int(10) NOT NULL DEFAULT '0',
    `comment` text NOT NULL,
    `receiving_id` int(10) NOT NULL AUTO_INCREMENT,
    `payment_type` varchar(20) DEFAULT NULL,
    `reference` varchar(32) DEFAULT NULL,
    PRIMARY KEY (`receiving_id`),
    KEY `supplier_id` (`supplier_id`),
    KEY `employee_id` (`employee_id`),
    KEY `reference` (`reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8  ;

--
-- Dumping data for table `ospos_receivings`
--


-- --------------------------------------------------------

--
-- Table structure for table `ospos_receivings_items`
--

CREATE TABLE `ospos_receivings_items` (
    `receiving_id` int(10) NOT NULL DEFAULT '0',
    `item_id` int(10) NOT NULL DEFAULT '0',
    `description` varchar(30) DEFAULT NULL,
    `serialnumber` varchar(30) DEFAULT NULL,
    `line` int(3) NOT NULL,
    `quantity_purchased` decimal(15,3) NOT NULL DEFAULT '0',
    `item_cost_price` decimal(15,2) NOT NULL,
    `item_unit_price` decimal(15,2) NOT NULL,
    `discount_percent` decimal(15,2) NOT NULL DEFAULT '0',
    `item_location` int(11) NOT NULL,
    `receiving_quantity` decimal(15,3) NOT NULL DEFAULT '1',
    PRIMARY KEY (`receiving_id`,`item_id`,`line`),
    KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_receivings_items`
--


-- --------------------------------------------------------

--
-- Table structure for table `ospos_sales`
--

CREATE TABLE `ospos_sales` (
    `sale_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `customer_id` int(10) DEFAULT NULL,
    `employee_id` int(10) NOT NULL DEFAULT '0',
    `comment` text NOT NULL,
    `invoice_number` varchar(32) DEFAULT NULL,
    `sale_id` int(10) NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (`sale_id`),
    KEY `customer_id` (`customer_id`),
    KEY `employee_id` (`employee_id`),
    KEY `sale_time` (`sale_time`),
    UNIQUE KEY `invoice_number` (`invoice_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8  ;

--
-- Dumping data for table `ospos_sales`
--


-- --------------------------------------------------------

--
-- Table structure for table `ospos_sales_items`
--

CREATE TABLE `ospos_sales_items` (
    `sale_id` int(10) NOT NULL DEFAULT '0',
    `item_id` int(10) NOT NULL DEFAULT '0',
    `description` varchar(30) DEFAULT NULL,
    `serialnumber` varchar(30) DEFAULT NULL,
    `line` int(3) NOT NULL DEFAULT '0',
    `quantity_purchased` decimal(15,3) NOT NULL DEFAULT '0',
    `item_cost_price` decimal(15,2) NOT NULL,
    `item_unit_price` decimal(15,2) NOT NULL,
    `discount_percent` decimal(15,2) NOT NULL DEFAULT '0',
    `item_location` int(11) NOT NULL,
    PRIMARY KEY (`sale_id`,`item_id`,`line`),
    KEY `sale_id` (`sale_id`),
    KEY `item_id` (`item_id`),
    KEY `item_location` (`item_location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_sales_items`
--


-- --------------------------------------------------------

--
-- Table structure for table `ospos_sales_items_taxes`
--

CREATE TABLE `ospos_sales_items_taxes` (
    `sale_id` int(10) NOT NULL,
    `item_id` int(10) NOT NULL,
    `line` int(3) NOT NULL DEFAULT '0',
    `name` varchar(255) NOT NULL,
    `percent` decimal(15,3) NOT NULL,
    PRIMARY KEY (`sale_id`,`item_id`,`line`,`name`,`percent`),
    KEY `sale_id` (`sale_id`),
    KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_sales_items_taxes`
--

-- --------------------------------------------------------

--
-- Table structure for table `ospos_sales_payments`
--

CREATE TABLE `ospos_sales_payments` (
    `sale_id` int(10) NOT NULL,
    `payment_type` varchar(40) NOT NULL,
    `payment_amount` decimal(15,2) NOT NULL,
    PRIMARY KEY (`sale_id`,`payment_type`),
    KEY `sale_id` (`sale_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_sales_payments`
--


-- --------------------------------------------------------

--
-- Table structure for table `ospos_sales_suspended`
--

CREATE TABLE `ospos_sales_suspended` (
     `sale_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
     `customer_id` int(10) DEFAULT NULL,
     `employee_id` int(10) NOT NULL DEFAULT '0',
     `comment` text NOT NULL,
     `invoice_number` varchar(32) DEFAULT NULL,
     `sale_id` int(10) NOT NULL AUTO_INCREMENT,
     PRIMARY KEY (`sale_id`),
     KEY `customer_id` (`customer_id`),
     KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8  ;

--
-- Dumping data for table `ospos_sales_suspended`
--


-- --------------------------------------------------------

--
-- Table structure for table `ospos_sales_suspended_items`
--

CREATE TABLE `ospos_sales_suspended_items` (
    `sale_id` int(10) NOT NULL DEFAULT '0',
    `item_id` int(10) NOT NULL DEFAULT '0',
    `description` varchar(30) DEFAULT NULL,
    `serialnumber` varchar(30) DEFAULT NULL,
    `line` int(3) NOT NULL DEFAULT '0',
    `quantity_purchased` decimal(15,3) NOT NULL DEFAULT '0',
    `item_cost_price` decimal(15,2) NOT NULL,
    `item_unit_price` decimal(15,2) NOT NULL,
    `discount_percent` decimal(15,2) NOT NULL DEFAULT '0',
    `item_location` int(11) NOT NULL,
    PRIMARY KEY (`sale_id`,`item_id`,`line`),
    KEY `sale_id` (`sale_id`),
    KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_sales_suspended_items`
--


-- --------------------------------------------------------

--
-- Table structure for table `ospos_sales_suspended_items_taxes`
--

CREATE TABLE `ospos_sales_suspended_items_taxes` (
    `sale_id` int(10) NOT NULL,
    `item_id` int(10) NOT NULL,
    `line` int(3) NOT NULL DEFAULT '0',
    `name` varchar(255) NOT NULL,
    `percent` decimal(15,3) NOT NULL,
    PRIMARY KEY (`sale_id`,`item_id`,`line`,`name`,`percent`),
    KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_sales_suspended_items_taxes`
--


-- --------------------------------------------------------

--
-- Table structure for table `ospos_sales_suspended_payments`
--

CREATE TABLE `ospos_sales_suspended_payments` (
    `sale_id` int(10) NOT NULL,
    `payment_type` varchar(40) NOT NULL,
    `payment_amount` decimal(15,2) NOT NULL,
    PRIMARY KEY (`sale_id`,`payment_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_sales_suspended_payments`
--


-- --------------------------------------------------------

--
-- Table structure for table `ospos_sessions`
--

CREATE TABLE `ospos_sessions` (
    `id` varchar(40) NOT NULL,
    `ip_address` varchar(45) NOT NULL,
    `timestamp` int(10) unsigned DEFAULT 0 NOT NULL,
    `data` blob NOT NULL,
    KEY `ci_sessions_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_sessions`
--

-- --------------------------------------------------------

--
-- Table structure for table `ospos_stock_locations`
--

CREATE TABLE `ospos_stock_locations` (
    `location_id` int(11) NOT NULL AUTO_INCREMENT,
    `location_name` varchar(255) DEFAULT NULL,
    `deleted` int(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`location_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Dumping data for table `ospos_stock_locations`
--

INSERT INTO `ospos_stock_locations` ( `deleted`, `location_name` ) VALUES ('0', 'stock');

-- --------------------------------------------------------

--
-- Table structure for table `ospos_suppliers`
--

CREATE TABLE `ospos_suppliers` (
    `person_id` int(10) NOT NULL,
    `company_name` varchar(255) NOT NULL,
    `agency_name` varchar(255) NOT NULL,
    `account_number` varchar(255) DEFAULT NULL,
    `deleted` int(1) NOT NULL DEFAULT '0',
    PRIMARY KEY `person_id` (`person_id`),
    UNIQUE KEY `account_number` (`account_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ospos_suppliers`
--
