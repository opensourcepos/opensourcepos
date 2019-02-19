--
-- This migration script should be run after creating tables with the regular database script and before applying the constraints.
--

--
-- Copy data to table `app_config`
--

DELETE FROM `app_config` WHERE `key` = 'address';
INSERT INTO `app_config` (`key`, `value`)
SELECT  `key`, `value` FROM `phppos`.phppos_app_config WHERE `key` = 'address';
DELETE FROM `app_config` WHERE `key` = 'company';
INSERT INTO `app_config` (`key`, `value`)
SELECT  `key`, `value` FROM `phppos`.phppos_app_config WHERE `key` = 'company';
DELETE FROM `app_config` WHERE `key` = 'default_tax_1_name';
INSERT INTO `app_config` (`key`, `value`)
SELECT  `key`, `value` FROM `phppos`.phppos_app_config WHERE `key` = 'default_tax_1_name';
DELETE FROM `app_config` WHERE `key` = 'default_tax_1_rate';
INSERT INTO `app_config` (`key`, `value`)
SELECT  `key`, `value` FROM `phppos`.phppos_app_config WHERE `key` = 'default_tax_1_rate';
DELETE FROM `app_config` WHERE `key` = 'default_tax_2_name';
INSERT INTO `app_config` (`key`, `value`)
SELECT  `key`, `value` FROM `phppos`.phppos_app_config WHERE `key` = 'default_tax_2_name';
DELETE FROM `app_config` WHERE `key` = 'default_tax_2_rate';
INSERT INTO `app_config` (`key`, `value`)
SELECT  `key`, `value` FROM `phppos`.phppos_app_config WHERE `key` = 'default_tax_2_rate';
DELETE FROM `app_config` WHERE `key` = 'default_tax_rate';
INSERT INTO `app_config` (`key`, `value`)
SELECT  `key`, `value` FROM `phppos`.phppos_app_config WHERE `key` = 'default_tax_rate';
DELETE FROM `app_config` WHERE `key` = 'email';
INSERT INTO `app_config` (`key`, `value`)
SELECT  `key`, `value` FROM `phppos`.phppos_app_config WHERE `key` = 'email';
DELETE FROM `app_config` WHERE `key` = 'fax';
INSERT INTO `app_config` (`key`, `value`)
SELECT  `key`, `value` FROM `phppos`.phppos_app_config WHERE `key` = 'fax';
DELETE FROM `app_config` WHERE `key` = 'phone';
INSERT INTO `app_config` (`key`, `value`)
SELECT  `key`, `value` FROM `phppos`.phppos_app_config WHERE `key` = 'phone';
DELETE FROM `app_config` WHERE `key` = 'website';
INSERT INTO `app_config` (`key`, `value`)
SELECT  `key`, `value` FROM `phppos`.phppos_app_config WHERE `key` = 'website';
DELETE FROM `app_config` WHERE `key` = 'return_policy';
INSERT INTO `app_config` (`key`, `value`)
SELECT  `key`, `value` FROM `phppos`.phppos_app_config WHERE `key` = 'return_policy';

--
-- Copy data to table `customers`
--

INSERT INTO `customers` (`person_id`, `account_number`, `taxable`, `deleted`)
SELECT `person_id`, `account_number`, `taxable`, `deleted` FROM `phppos`.phppos_customers;   
UPDATE `customers` c1, `customers` c2 SET `c1`.`account_number` = NULL WHERE `c1`.`person_id` > `c2`.`person_id` AND `c1`.`account_number` = `c2`.`account_number`;

--
-- Copy data to table `employees`
--

INSERT INTO `employees` (`username`, `password`, `person_id`, `deleted`, `hash_version`)
SELECT `username`, `password`, `person_id`, `deleted`, 1 FROM `phppos`.phppos_employees;

--
-- Copy data to table `giftcards`
--

INSERT INTO `giftcards` (`giftcard_id`, `giftcard_number`, `value`, `deleted`, `person_id`)
SELECT `giftcard_id`, `giftcard_number`, `value`, `deleted`, `person_id` FROM `phppos`.phppos_giftcards;

--
-- Copy data to table `inventory`
--

INSERT INTO `inventory` (`trans_id`, `trans_items`, `trans_user`, `trans_date`, `trans_comment`, `trans_location`, `trans_inventory`) 
SELECT `trans_id`, `trans_items`, `trans_user`, `trans_date`, `trans_comment`, 1, `trans_inventory` FROM `phppos`.phppos_inventory;

--
-- Copy data to table `items`
--

INSERT INTO `items` (`name`, `category`, `supplier_id`, `item_number`, `description`, `cost_price`, `unit_price`, `reorder_level`, `receiving_quantity`, `item_id`, `pic_id`, `allow_alt_description`, `is_serialized`, `deleted`, `custom1`, `custom2`, `custom3`, `custom4`, `custom5`, `custom6`, `custom7`, `custom8`, `custom9`, `custom10`)
SELECT `name`, `category`, `supplier_id`, `item_number`, `description`, `cost_price`, `unit_price`, `reorder_level`, 1, `item_id`, NULL, `allow_alt_description`, `is_serialized`, `deleted`, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 FROM `phppos`.phppos_items;

--
-- Copy data to table `items_taxes`
--

INSERT INTO `items_taxes` (`item_id`, `name`, `percent`)
SELECT `item_id`, `name`, `percent` FROM `phppos`.phppos_items_taxes;

--
-- Copy data to table `item_kits`
--

INSERT INTO `item_kits` (`item_kit_id`, `name`, `description`)
SELECT `item_kit_id`, `name`, `description` FROM `phppos`.phppos_item_kits;

--
-- Copy data to table `item_kit_items`
--

INSERT INTO `item_kit_items` (`item_kit_id`, `item_id`, `quantity`)
SELECT `item_kit_id`, `item_id`, `quantity` FROM `phppos`.phppos_item_kit_items;

--
-- Copy data to table `people`
--

INSERT INTO `people` (`first_name`, `last_name`, `phone_number`, `email`, `address_1`, `address_2`, `city`, `state`, `zip`, `country`, `comments`, `person_id`)
SELECT `first_name`, `last_name`, `phone_number`, `email`, `address_1`, `address_2`, `city`, `state`, `zip`, `country`, `comments`, `person_id` FROM `phppos`.phppos_people;

--
-- Copy data to table `receivings`
--

INSERT INTO `receivings` (`receiving_time`, `supplier_id`, `employee_id`, `comment`, `receiving_id`, `payment_type`, `reference`) 
SELECT `receiving_time`, `supplier_id`, `employee_id`, `comment`, `receiving_id`, `payment_type`, NULL FROM `phppos`.phppos_receivings;

--
-- Copy data to table `receivings_items`
--

INSERT INTO `receivings_items` (`receiving_id`, `item_id`, `description`, `serialnumber`, `line`, `quantity_purchased`, `item_cost_price`, `item_unit_price`, `discount_percent`, `item_location`) 
SELECT `receiving_id`, `item_id`, `description`, `serialnumber`, `line`, `quantity_purchased`, `item_cost_price`, `item_unit_price`, `discount_percent`, 1 FROM `phppos`.phppos_receivings_items;

--
-- Copy data to table `sales`
--

INSERT INTO `sales` (`sale_time`, `customer_id`, `employee_id`, `comment`, `sale_id`, `invoice_number`) 
SELECT `sale_time`, `customer_id`, `employee_id`, `comment`, `sale_id`, NULL FROM `phppos`.phppos_sales;

--
-- Copy data to table `sales_items`
--

INSERT INTO `sales_items` (`sale_id`, `item_id`, `description`, `serialnumber`, `line`, `quantity_purchased`, `item_cost_price`, `item_unit_price`, `discount_percent`, `item_location`)
SELECT `sale_id`, `item_id`, `description`, `serialnumber`, `line`, `quantity_purchased`, `item_cost_price`, `item_unit_price`, `discount_percent`, 1 FROM `phppos`.phppos_sales_items;

--
-- Copy data to table `sales_items_taxes`
--

INSERT INTO `sales_items_taxes` (`sale_id`, `item_id`, `line`, `name`, `percent`) 
SELECT `sale_id`, `item_id`, `line`, `name`, `percent` FROM `phppos`.phppos_sales_items_taxes;

--
-- Copy data to table `sales_payments`
--

INSERT INTO `sales_payments` (`sale_id`, `payment_type`, `payment_amount`) 
SELECT `sale_id`, `payment_type`, `payment_amount` FROM `phppos`.phppos_sales_payments;

--
-- Copy data to table `item_quantities`
--

INSERT INTO  `item_quantities` (`item_id`, `location_id`, `quantity`)
SELECT `item_id`, 1, `quantity` FROM `phppos`.`phppos_items`;

--
-- Copy data to table `suppliers`
--

INSERT INTO `suppliers` (`person_id`, `company_name`, `account_number`, `deleted`)
SELECT `person_id`, `company_name`, `account_number`, `deleted` FROM `phppos`.phppos_suppliers;

-- 
-- Copy data to table `dinner_tables`
--

INSERT INTO `dinner_tables` (`dinner_table_id`, `name`, `status`, `deleted`)
SELECT `dinner_table_id`, `name`, `status`, `deleted` FROM `phppos`.phppos_dinner_tables;