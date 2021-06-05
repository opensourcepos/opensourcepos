#ospos_attribute_values table
ALTER TABLE `ospos_attribute_values` ADD UNIQUE(`attribute_date`);
ALTER TABLE `ospos_attribute_values` ADD UNIQUE(`attribute_decimal`); 

#opsos_attribute_definitions table
ALTER TABLE `ospos_attribute_definitions` MODIFY `definition_flags` tinyint(1) NOT NULL;
ALTER TABLE `ospos_attribute_definitions` ADD INDEX(`definition_name`); 
ALTER TABLE `ospos_attribute_definitions` ADD INDEX(`definition_type`);

#opsos_attribute_links table
ALTER TABLE `ospos_attribute_links` ADD UNIQUE INDEX `attribute_links_uq2` (`item_id`,`sale_id`,`receiving_id`,`definition_id`,`attribute_id`);

#ospos_cash_up table
ALTER TABLE `ospos_cash_up` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;

#ospos_customers table
DROP INDEX `person_id` ON `ospos_customers`;
ALTER TABLE `ospos_customers` MODIFY `taxable` tinyint(1) DEFAULT 1 NOT NULL;
ALTER TABLE `ospos_customers` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_customers` MODIFY `discount_type` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_customers` ADD PRIMARY KEY(`person_id`);
ALTER TABLE `ospos_customers` ADD INDEX(`company_name`);

#ospos_customers_packages table
ALTER TABLE `ospos_customers_packages` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;

#ospos_dinner_tables table
ALTER TABLE `ospos_dinner_tables` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_dinner_tables` ADD INDEX(`status`);

#ospos_employees table
DROP INDEX `person_id` ON `ospos_employees`;
ALTER TABLE `ospos_employees` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_employees` MODIFY `hash_version` tinyint(1) DEFAULT 2 NOT NULL;
ALTER TABLE `ospos_employees` ADD PRIMARY KEY(`person_id`);

#ospos_expenses table
ALTER TABLE `ospos_expenses` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_expenses` ADD INDEX(`payment_type`);
ALTER TABLE `ospos_expenses` ADD INDEX(`amount`);

#ospos_expenses_categories table
ALTER TABLE `ospos_expense_categories` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_expense_categories` ADD INDEX(`category_description`);

#ospos_giftcards table
ALTER TABLE `ospos_giftcards` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;

#ospos_items table
ALTER TABLE `ospos_items` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_items` MODIFY `stock_type` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_items` MODIFY `item_type` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_items` ADD INDEX(`deleted`, `item_type`);
ALTER TABLE `ospos_items` ADD INDEX(`PRIMARY`, `deleted`);
ALTER TABLE `ospos_items` ADD UNIQUE INDEX `items_uq1` (`supplier_id`, `item_id`, `deleted`, `item_type`);

#ospos_item_kits table
ALTER TABLE `ospos_item_kits` MODIFY `kit_discount_type` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_item_kits` MODIFY `price_option` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_item_kits` MODIFY `print_option` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_item_kits` ADD INDEX(`name`,`description`);

#ospos_item_quantities table
ALTER TABLE `ospos_item_quantities` ADD INDEX(`PRIMARY`,`item_id`,`location_id`);

#ospos_people table
ALTER TABLE `ospos_people` ADD INDEX(`first_name`, `last_name`, `email`, `phone_number`); 

#ospos_receivings_items
ALTER TABLE `ospos_receivings_items` MODIFY `discount_type` tinyint(1) DEFAULT 0 NOT NULL;

#ospos_sales
ALTER TABLE `ospos_sales` MODIFY `sale_status` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_sales` MODIFY `sale_type` tinyint(1) DEFAULT 0 NOT NULL;

#ospos_sales_items
ALTER TABLE `ospos_sales_items` MODIFY `discount_type` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_sales_items` MODIFY `print_option` tinyint(1) DEFAULT 0 NOT NULL;

#ospos_sales_items_taxes
ALTER TABLE `ospos_sales_items_taxes` MODIFY `tax_type` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_sales_items_taxes` MODIFY `rounding_code` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_sales_items_taxes` MODIFY `cascade_sequence` tinyint(1) DEFAULT 0 NOT NULL;

#ospos_sales_taxes
ALTER TABLE `ospos_sales_taxes` MODIFY `print_sequence` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_sales_taxes` MODIFY `rounding_code` tinyint(1) DEFAULT 0 NOT NULL;

#ospos_sessions table
ALTER TABLE `ospos_sessions` ADD INDEX(`id`); 
ALTER TABLE `ospos_sessions` ADD INDEX(`ip_address`);

#ospos_stock_locations table
ALTER TABLE `ospos_stock_locations` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;

#ospos_suppliers table
DROP INDEX `person_id` ON `ospos_suppliers`;
ALTER TABLE `ospos_suppliers` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_suppliers` MODIFY `category` tinyint(1) NOT NULL;
ALTER TABLE `ospos_suppliers` ADD PRIMARY KEY(`person_id`);
ALTER TABLE `ospos_suppliers` ADD INDEX(`category`);
ALTER TABLE `ospos_suppliers` ADD INDEX(`company_name`, `deleted`);

#ospos_tax_categories table
ALTER TABLE `ospos_tax_categories` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_tax_categories` MODIFY `tax_group_sequence` tinyint(1) NOT NULL;

#ospos_tax_codes table
ALTER TABLE `ospos_tax_codes` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;

#ospos_tax_jurisdictions table
ALTER TABLE `ospos_tax_jurisdictions` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_tax_jurisdictions` MODIFY `tax_group_sequence` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_tax_jurisdictions` MODIFY `cascade_sequence` tinyint(1) DEFAULT 0 NOT NULL;

#ospos_tax_rates table
ALTER TABLE `ospos_tax_rates` MODIFY `tax_rounding_code` tinyint(1) DEFAULT 0 NOT NULL;