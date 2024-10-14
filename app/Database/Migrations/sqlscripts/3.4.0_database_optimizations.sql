#ospos_attribute_values table
ALTER TABLE `ospos_attribute_values` ADD UNIQUE(`attribute_date`);
ALTER TABLE `ospos_attribute_values` ADD UNIQUE(`attribute_decimal`);

#opsos_attribute_definitions table
ALTER TABLE `ospos_attribute_definitions` MODIFY `definition_flags` tinyint(1) NOT NULL;
ALTER TABLE `ospos_attribute_definitions` ADD INDEX(`definition_name`);
ALTER TABLE `ospos_attribute_definitions` ADD INDEX(`definition_type`);

#ospos_cash_up table
ALTER TABLE `ospos_cash_up` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;

#ospos_customers table
ALTER TABLE `ospos_customers` DROP FOREIGN KEY `ospos_customers_ibfk_1`;
ALTER TABLE `ospos_customers_points` DROP FOREIGN KEY `ospos_customers_points_ibfk_1`;
ALTER TABLE `ospos_sales` DROP FOREIGN KEY `ospos_sales_ibfk_2`;

ALTER TABLE `ospos_customers` MODIFY `taxable` tinyint(1) DEFAULT 1 NOT NULL;
ALTER TABLE `ospos_customers` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_customers` MODIFY `discount_type` tinyint(1) DEFAULT 0 NOT NULL;

ALTER TABLE `ospos_customers` ADD CONSTRAINT `ospos_customers_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `ospos_people`(`person_id`);
ALTER TABLE `ospos_customers_points` ADD CONSTRAINT `ospos_customers_points_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `ospos_customers` (`person_id`);
ALTER TABLE `ospos_sales` ADD CONSTRAINT `ospos_sales_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `ospos_customers` (`person_id`);

#ospos_customers_packages table
ALTER TABLE `ospos_customers_packages` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;

#ospos_dinner_tables table
ALTER TABLE `ospos_dinner_tables` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_dinner_tables` ADD INDEX(`status`);

#ospos_employees table
ALTER TABLE `ospos_sales_payments` DROP FOREIGN KEY `ospos_sales_payments_ibfk_2`;
ALTER TABLE `ospos_sales` DROP FOREIGN KEY `ospos_sales_ibfk_1`;
ALTER TABLE `ospos_receivings` DROP FOREIGN KEY `ospos_receivings_ibfk_1`;
ALTER TABLE `ospos_inventory` DROP FOREIGN KEY `ospos_inventory_ibfk_2`;
ALTER TABLE `ospos_grants` DROP FOREIGN KEY `ospos_grants_ibfk_2`;
ALTER TABLE `ospos_expenses` DROP FOREIGN KEY `ospos_expenses_ibfk_2`;
ALTER TABLE `ospos_employees` DROP FOREIGN KEY `ospos_employees_ibfk_1`;
ALTER TABLE `ospos_cash_up` DROP FOREIGN KEY `ospos_cash_up_ibfk_1`;
ALTER TABLE `ospos_cash_up` DROP FOREIGN KEY `ospos_cash_up_ibfk_2`;

ALTER TABLE `ospos_employees` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_employees` MODIFY `hash_version` tinyint(1) DEFAULT 2 NOT NULL;

ALTER TABLE `ospos_sales_payments` ADD CONSTRAINT `ospos_sales_payments_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `ospos_employees` (`person_id`);
ALTER TABLE `ospos_sales` ADD CONSTRAINT `ospos_sales_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `ospos_employees` (`person_id`);
ALTER TABLE `ospos_receivings` ADD CONSTRAINT `ospos_receivings_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `ospos_employees` (`person_id`);
ALTER TABLE `ospos_inventory` ADD CONSTRAINT `ospos_inventory_ibfk_2` FOREIGN KEY (`trans_user`) REFERENCES `ospos_employees` (`person_id`);
ALTER TABLE `ospos_grants` ADD CONSTRAINT `ospos_grants_ibfk_2` foreign key (`person_id`) references `ospos_employees` (`person_id`) ON DELETE CASCADE;
ALTER TABLE `ospos_expenses` ADD CONSTRAINT `ospos_expenses_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `ospos_employees` (`person_id`);
ALTER TABLE `ospos_employees` ADD CONSTRAINT `ospos_employees_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `ospos_people` (`person_id`);
ALTER TABLE `ospos_cash_up` ADD CONSTRAINT `ospos_cash_up_ibfk_1` FOREIGN KEY (`open_employee_id`) REFERENCES `ospos_employees` (`person_id`);
ALTER TABLE `ospos_cash_up` ADD CONSTRAINT `ospos_cash_up_ibfk_2` FOREIGN KEY (`close_employee_id`) REFERENCES `ospos_employees` (`person_id`);

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
ALTER TABLE `ospos_items` DROP FOREIGN KEY `ospos_items_ibfk_1`;
ALTER TABLE `ospos_items` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_items` MODIFY `stock_type` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_items` MODIFY `item_type` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_items` ADD INDEX(`deleted`, `item_type`);
ALTER TABLE `ospos_items` ADD INDEX (`item_id`,`deleted`);
ALTER TABLE `ospos_items` ADD UNIQUE INDEX `items_uq1` (`supplier_id`, `item_id`, `deleted`, `item_type`);

#ospos_item_kits table
ALTER TABLE `ospos_item_kits` MODIFY `kit_discount_type` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_item_kits` MODIFY `price_option` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_item_kits` MODIFY `print_option` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_item_kits` ADD INDEX(`name`,`description`);

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
ALTER TABLE `ospos_expenses` DROP FOREIGN KEY `ospos_expenses_ibfk_3`;
ALTER TABLE `ospos_receivings` DROP FOREIGN KEY `ospos_receivings_ibfk_2`;
ALTER TABLE `ospos_suppliers` DROP FOREIGN KEY `ospos_suppliers_ibfk_1`;

ALTER TABLE `ospos_suppliers` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_suppliers` MODIFY `category` tinyint(1) NOT NULL;
ALTER TABLE `ospos_suppliers` ADD INDEX(`category`);
ALTER TABLE `ospos_suppliers` ADD INDEX(`company_name`, `deleted`);

ALTER TABLE `ospos_expenses` ADD CONSTRAINT `ospos_expenses_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `ospos_suppliers` (`person_id`);
ALTER TABLE `ospos_items` ADD CONSTRAINT `ospos_items_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `ospos_suppliers` (`person_id`);
ALTER TABLE `ospos_receivings` ADD CONSTRAINT `ospos_receivings_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `ospos_suppliers` (`person_id`);
ALTER TABLE `ospos_suppliers` ADD CONSTRAINT `ospos_suppliers_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `ospos_people` (`person_id`);

#ospos_tax_categories table
ALTER TABLE `ospos_tax_categories` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_tax_categories` MODIFY `tax_group_sequence` tinyint(1) NOT NULL;

#ospos_tax_jurisdictions table
ALTER TABLE `ospos_tax_jurisdictions` MODIFY `deleted` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_tax_jurisdictions` MODIFY `tax_group_sequence` tinyint(1) DEFAULT 0 NOT NULL;
ALTER TABLE `ospos_tax_jurisdictions` MODIFY `cascade_sequence` tinyint(1) DEFAULT 0 NOT NULL;

#ospos_tax_rates table
ALTER TABLE `ospos_tax_rates` MODIFY `tax_rounding_code` tinyint(1) DEFAULT 0 NOT NULL;
