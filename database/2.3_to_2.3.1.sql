-- add granular report permissions
INSERT INTO ospos_permissions (permission_id, module_id, location_id) VALUES 
('reports_sales', 'reports', NULL),
('reports_receivings', 'reports', NULL),
('reports_items', 'reports', NULL),
('reports_inventory', 'reports', NULL),
('reports_customers', 'reports', NULL),
('reports_employees', 'reports', NULL),
('reports_suppliers', 'reports', NULL),
('reports_taxes', 'reports', NULL),
('reports_discounts', 'reports', NULL),
('reports_payments', 'reports', NULL),
('reports_categories', 'reports', NULL);

-- add modules for existing stock locations
INSERT INTO ospos_modules (name_lang_key, desc_lang_key, sort, module_id) (SELECT CONCAT('module_items_stock', location_id), CONCAT('module_items_stock', location_id, '_desc'), (SELECT MAX(sort)+1 FROM ospos_modules WHERE module_id LIKE 'items_stock%' OR module_id = 'items'), CONCAT('items_stock', location_id) from ospos_stock_locations);

-- add permissions for all employees
INSERT INTO `ospos_grants` (`permssion_id`, `person_id`) SELECT 'reports_customers', person_id from ospos_employees;
INSERT INTO `ospos_grants` (`permssion_id`, `person_id`) SELECT 'reports_receivings', person_id from ospos_employees;
INSERT INTO `ospos_grants` (`permssion_id`, `person_id`) SELECT 'reports_items', person_id from ospos_employees;
INSERT INTO `ospos_grants` (`permssion_id`, `person_id`) SELECT 'reports_inventory', person_id from ospos_employees;
INSERT INTO `ospos_grants` (`permssion_id`, `person_id`) SELECT 'reports_employees', person_id from ospos_employees;
INSERT INTO `ospos_grants` (`permssion_id`, `person_id`) SELECT 'reports_suppliers', person_id from ospos_employees;
INSERT INTO `ospos_grants` (`permssion_id`, `person_id`) SELECT 'reports_sales', person_id from ospos_employees;
INSERT INTO `ospos_grants` (`permssion_id`, `person_id`) SELECT 'reports_discounts', person_id from ospos_employees;
INSERT INTO `ospos_grants` (`permssion_id`, `person_id`) SELECT 'reports_taxes', person_id from ospos_employees;
INSERT INTO `ospos_grants` (`permssion_id`, `person_id`) SELECT 'reports_categories', person_id from ospos_employees;
INSERT INTO `ospos_grants` (`permssion_id`, `person_id`) SELECT 'reports_payments', person_id from ospos_employees;

-- add config options for tax inclusive sales
INSERT INTO `ospos_app_config` (`key`, `value`) VALUES 
('tax_included', '0'),
('recv_invoice_format', '');

-- add cascading deletes on modules
ALTER TABLE `ospos_permissions` DROP FOREIGN KEY `ospos_permissions_ibfk_1`; 
ALTER TABLE `ospos_permissions` ADD CONSTRAINT `ospos_permissions_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `ospos`.`ospos_stock_locations`(`location_id`) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE `ospos_grants` DROP FOREIGN KEY `ospos_grants_ibfk_1`; 
ALTER TABLE `ospos_grants` ADD CONSTRAINT `ospos_grants_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `ospos`.`ospos_permissions`(`permission_id`) ON DELETE CASCADE ON UPDATE RESTRICT;

-- add invoice_number column to receivings table
ALTER TABLE `ospos_receivings` ADD COLUMN `invoice_number` varchar(32) DEFAULT NULL;
ALTER TABLE `ospos_receivings` ADD UNIQUE `invoice_number` (`invoice_number`);



