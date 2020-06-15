#ospos_attribute_values table
ALTER TABLE `ospos_attribute_values` ADD UNIQUE(`attribute_date`);
ALTER TABLE `ospos_attribute_values` ADD UNIQUE(`attribute_decimal`); 

#opsos_attribute_definitions table
ALTER TABLE `ospos_attribute_definitions` ADD INDEX(`definition_name`); 
ALTER TABLE `ospos_attribute_definitions` ADD INDEX(`definition_type`);
ALTER TABLE `ospos_attribute_definitions` ADD INDEX(`deleted`); 

#ospos_cash_up table
ALTER TABLE `ospos_cash_up` MODIFY `deleted` tinyint DEFAULT 0 NOT NULL;

#ospos_customers table
DROP INDEX `person_id` ON `ospos_customers`;
ALTER TABLE `ospos_customers` MODIFY `taxable` tinyint DEFAULT 1 NOT NULL;
ALTER TABLE `ospos_customers` MODIFY `deleted` tinyint DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_customers` ADD PRIMARY KEY(`person_id`);
ALTER TABLE `ospos_customers` ADD INDEX(`deleted`); 
ALTER TABLE `ospos_customers` ADD INDEX(`company_name`);

#ospos_customers_packages table
ALTER TABLE `ospos_customers_packages` MODIFY `deleted` tinyint DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_customers_packages` ADD INDEX(`deleted`); 

#ospos_dinner_tables table
ALTER TABLE `ospos_dinner_tables` MODIFY `deleted` tinyint DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_dinner_tables` ADD INDEX(`status`);
ALTER TABLE `ospos_dinner_tables` ADD INDEX(`deleted`);  

#ospos_employees table
DROP INDEX `person_id` ON `ospos_employees`;
ALTER TABLE `ospos_employees` MODIFY `deleted` tinyint DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_employees` MODIFY `hash_version` tinyint DEFAULT 2 NOT NULL;
ALTER TABLE `ospos_employees` ADD PRIMARY KEY(`person_id`);

#ospos_expenses table
ALTER TABLE `ospos_expenses` MODIFY `deleted` tinyint DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_expenses` ADD INDEX(`deleted`);
ALTER TABLE `ospos_expenses` ADD INDEX(`payment_type`);
ALTER TABLE `ospos_expenses` ADD INDEX(`amount`);

#ospos_expenses_categories table
ALTER TABLE `ospos_expense_categories` MODIFY `deleted` tinyint DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_expense_categories` ADD INDEX(`category_description`);
ALTER TABLE `ospos_expense_categories` ADD INDEX(`deleted`);

#ospos_giftcards table
ALTER TABLE `ospos_giftcards` MODIFY `deleted` tinyint DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_giftcards` ADD INDEX(`deleted`); 

#ospos_items table
ALTER TABLE `ospos_items` MODIFY `deleted` tinyint DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_items` ADD INDEX(`deleted`);

#ospos_item_kits table
ALTER TABLE `ospos_item_kits` ADD INDEX(`name`,`description`);

#ospos_people table
ALTER TABLE `ospos_people` ADD INDEX(`first_name`, `last_name`, `email`, `phone_number`); 

#ospos_sessions table
ALTER TABLE `ospos_sessions` ADD INDEX(`id`); 
ALTER TABLE `ospos_sessions` ADD INDEX(`ip_address`);

#ospos_stock_locations table
ALTER TABLE `ospos_stock_locations` MODIFY `deleted` tinyint DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_stock_locations` ADD INDEX(`deleted`);

#ospos_suppliers table
DROP INDEX `person_id` ON `ospos_suppliers`;
ALTER TABLE `ospos_suppliers` MODIFY `deleted` tinyint DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_suppliers` ADD PRIMARY KEY(`person_id`);
ALTER TABLE `ospos_suppliers` ADD INDEX(`category`); 
ALTER TABLE `ospos_suppliers` ADD INDEX(`deleted`);

#ospos_tax_categories table
ALTER TABLE `ospos_tax_categories` MODIFY `deleted` tinyint DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_tax_categories` ADD INDEX(`deleted`); 

#ospos_tax_codes table
ALTER TABLE `ospos_tax_codes` MODIFY `deleted` tinyint DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_tax_codes` ADD INDEX(`deleted`);

#ospos_tax_jurisdictions table
ALTER TABLE `ospos_tax_jurisdictions` MODIFY `deleted` tinyint DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_tax_jurisdictions` ADD INDEX(`deleted`);  