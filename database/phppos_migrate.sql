--
-- This migration script should be run after creating tables with the regular database script and before applying the constraints.
--

SOURCE tables.sql;

--
-- Dumping data for table `ospos_customers`
--
INSERT INTO `ospos_customers` (`person_id`, `account_number`, `taxable`, `deleted`) SELECT `person_id`, `account_number`, `taxable`, `deleted` FROM `phppos`.phppos_customers;   
UPDATE `ospos_customers` c1, `ospos_customers` c2 SET `c1`.`account_number` = NULL WHERE `c1`.`person_id` > `c2`.`person_id` AND `c1`.`account_number` = `c2`.`account_number`;

--
-- Dumping data for table `ospos_employees`
--

INSERT INTO `ospos_employees` (`username`, `password`, `person_id`, `deleted`) SELECT `username`, `password`, `person_id`, `deleted` FROM `phppos`.phppos_employees;

--
-- Dumping data for table `ospos_inventory`
--

INSERT INTO `ospos_inventory` (`trans_id`, `trans_items`, `trans_user`, `trans_date`, `trans_comment`, `trans_location`, `trans_inventory`) 
SELECT `trans_id`, `trans_items`, `trans_user`, `trans_date`, `trans_comment`, 1, `trans_inventory` FROM `phppos`.phppos_inventory;

--
-- Dumping data for table `ospos_items`
--

INSERT INTO `ospos_items` (`name`, `category`, `supplier_id`, `item_number`, `description`, `cost_price`, `unit_price`, `reorder_level`, `receiving_quantity`, `item_id`, `pic_id`, `allow_alt_description`, `is_serialized`, `deleted`, `custom1`, `custom2`, `custom3`, `custom4`, `custom5`, `custom6`, `custom7`, `custom8`, `custom9`, `custom10`) SELECT `name`, `category`, `supplier_id`, `item_number`, `description`, `cost_price`, `unit_price`, `reorder_level`, 1, `item_id`, NULL, `allow_alt_description`, `is_serialized`, `deleted`, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 FROM `phppos`.phppos_items;

--
-- Dumping data for table `ospos_items_taxes`
--

INSERT INTO `ospos_items_taxes` (`item_id`, `name`, `percent`) SELECT `item_id`, `name`, `percent` FROM `phppos`.phppos_items_taxes;

--
-- Dumping data for table `ospos_item_kits`
--

-- TODO add query here

--
-- Dumping data for table `ospos_people`
--

INSERT INTO `ospos_people` (`first_name`, `last_name`, `phone_number`, `email`, `address_1`, `address_2`, `city`, `state`, `zip`, `country`, `comments`, `person_id`)
SELECT `first_name`, `last_name`, `phone_number`, `email`, `address_1`, `address_2`, `city`, `state`, `zip`, `country`, `comments`, `person_id` FROM `phppos`.phppos_people;

--
-- Dumping data for table `ospos_receivings`
--

INSERT INTO `ospos_receivings` (`receiving_time`, `supplier_id`, `employee_id`, `comment`, `receiving_id`, `payment_type`, `invoice_number`) 
SELECT `receiving_time`, `supplier_id`, `employee_id`, `comment`, `receiving_id`, `payment_type`, NULL FROM `phppos`.phppos_receivings;

--
-- Dumping data for table `ospos_receivings_items`
--

INSERT INTO `ospos_receivings_items` (`receiving_id`, `item_id`, `description`, `serialnumber`, `line`, `quantity_purchased`, `item_cost_price`, `item_unit_price`, `discount_percent`) 
SELECT `receiving_id`, `item_id`, `description`, `serialnumber`, `line`, `quantity_purchased`, `item_cost_price`, `item_unit_price`, `discount_percent` FROM `phppos`.phppos_receivings_items;

--
-- Dumping data for table `ospos_sales`
--

INSERT INTO `ospos_sales` (`sale_time`, `customer_id`, `employee_id`, `comment`, `sale_id`, `invoice_number`) 
SELECT `sale_time`, `customer_id`, `employee_id`, `comment`, `sale_id`, `invoice_number` FROM `phppos`.phppos_sales;
UPDATE `ospos_sales` c1, `ospos_sales` c2 SET `c1`.`invoice_number` = NULL WHERE `c1`.`sale_id` > `c2`.`sale_id` AND `c1`.`invoice_number` = `c2`.`invoice_number`;

--
-- Dumping data for table `ospos_sales_items`
--

INSERT INTO `ospos_sales_items` (`sale_id`, `item_id`, `description`, `serialnumber`, `line`, `quantity_purchased`, `item_cost_price`, `item_unit_price`, `discount_percent`, `item_location`)
SELECT `sale_id`, `item_id`, `description`, `serialnumber`, `line`, `quantity_purchased`, `item_cost_price`, `item_unit_price`, `discount_percent`, 1 FROM `phppos`.phppos_sales_items;

--
-- Dumping data for table `ospos_sales_items_taxes`
--

INSERT INTO `ospos_sales_items_taxes` (`sale_id`, `item_id`, `line`, `name`, `percent`) 
SELECT `sale_id`, `item_id`, `line`, `name`, `percent` FROM `phppos`.phppos_sales_items_taxes;
-- --------------------------------------------------------

--
-- Table structure for table `ospos_sales_payments`
--

--
-- Dumping data for table `ospos_sales_payments`
--

INSERT INTO `ospos_sales_payments` (`sale_id`, `payment_type`, `payment_amount`) 
SELECT `sale_id`, `payment_type`, `payment_amount` FROM `phppos`.phppos_sales_payments;

INSERT INTO `ospos_stock_locations` ( `deleted`, `location_name` ) VALUES (0, 'stock');

--
-- Dumping data for table `ospos_item_quantities`
--

INSERT INTO  `ospos_item_quantities` (`item_id`, `location_id`, `quantity`) SELECT `item_id`, 1, `quantity` FROM `phppos`.`phppos_items`;

--
-- Dumping data for table `ospos_suppliers`
--

INSERT INTO `ospos_suppliers` (`person_id`, `company_name`, `account_number`, `deleted`) SELECT `person_id`, `company_name`, `account_number`, `deleted` FROM `phppos`.phppos_suppliers;

-- Add constraints on copied data
SOURCE constraints.sql;
