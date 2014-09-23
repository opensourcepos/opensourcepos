-- add granular report permissions
INSERT INTO ospos_modules (name_lang_key, desc_lang_key, sort, module_id) VALUES 
('module_reports_sales', 'module_reports_sales_desc', 51, 'reports_sales'),
('module_reports_receivings', 'module_reports_receivings_desc', 52, 'reports_receivings'),
('module_reports_items', 'module_reports_items_desc', 54, 'reports_items'),
('module_reports_inventory', 'module_reports_inventory_desc', 55, 'reports_inventory'),
('module_reports_customers', 'module_reports_customers_desc', 56, 'reports_customers'),
('module_reports_employees', 'module_reports_employees_desc', 57, 'reports_employees'),
('module_reports_suppliers', 'module_reports_suppliers_desc', 57, 'reports_suppliers');

-- add modules for existing stock locations
INSERT INTO ospos_modules (name_lang_key, desc_lang_key, sort, module_id) (SELECT CONCAT('module_items_stock', location_id), CONCAT('module_items_stock', location_id, '_desc'), (SELECT MAX(sort)+1 FROM ospos_modules WHERE module_id LIKE 'items_stock%' OR module_id = 'items'), CONCAT('items_stock', location_id) from ospos_stock_locations);

-- add permissions for all employees
INSERT INTO `ospos_permissions` (`module_id`, `person_id`) SELECT 'reports_customers', person_id from ospos_employees;
INSERT INTO `ospos_permissions` (`module_id`, `person_id`) SELECT 'reports_receivings', person_id from ospos_employees;
INSERT INTO `ospos_permissions` (`module_id`, `person_id`) SELECT 'reports_items', person_id from ospos_employees;
INSERT INTO `ospos_permissions` (`module_id`, `person_id`) SELECT 'reports_inventory', person_id from ospos_employees;
INSERT INTO `ospos_permissions` (`module_id`, `person_id`) SELECT 'reports_employees', person_id from ospos_employees;
INSERT INTO `ospos_permissions` (`module_id`, `person_id`) SELECT 'reports_suppliers', person_id from ospos_employees;
INSERT INTO `ospos_permissions` (`module_id`, `person_id`) SELECT 'reports_sales', person_id from ospos_employees;

-- add config options for tax inclusive sales
INSERT INTO `ospos_app_config` (`key`, `value`) VALUES ('tax_included', '0');
