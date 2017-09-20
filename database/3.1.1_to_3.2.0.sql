--
-- Add support for office menu group
--

ALTER TABLE `ospos_grants`
  ADD COLUMN `menu_group` varchar(32) DEFAULT 'home';

INSERT INTO `ospos_modules` (`name_lang_key`, `desc_lang_key`, `sort`, `module_id`) VALUES
('module_office', 'module_office_desc', 1, 'office'),
('module_home', 'module_home_desc', 1, 'home');

INSERT INTO `ospos_permissions` (`permission_id`, `module_id`) VALUES
('office', 'office'),
('home', 'home');

INSERT INTO `ospos_grants` (`permission_id`, `person_id`, `menu_group`) VALUES
('office', 1, 'home'),
('home', 1, 'office');

UPDATE `ospos_grants`
SET menu_group = 'office'
WHERE permission_id in ('config', 'home', 'employees', 'taxes', 'migrate')
AND person_id = 1;

--
-- Add support for Work Orders
--

INSERT INTO `ospos_app_config` (`key`, `value`) VALUES
('work_order_enable', '0'),
('work_order_format', 'W%y{WSEQ:6}'),
('last_used_work_order_number', '0');

ALTER TABLE `ospos_sales`
  ADD COLUMN `work_order_number` varchar(32) DEFAULT NULL,
  ADD COLUMN `sale_type` tinyint(2) NOT NULL DEFAULT 0;

-- sale_type (0=pos, 1=invoice, 2=work order, 3=quote, 4=return)

UPDATE `ospos_sales`
  SET `sale_type` = 0;

UPDATE ospos_sales t1
  SET sale_type = 4
WHERE EXISTS (SELECT t2.sale_id FROM ospos_sales_items t2 WHERE t1.sale_id = t2.sale_id AND t2.quantity_purchased < 0);

UPDATE `ospos_sales`
  SET `sale_type` = 3
WHERE `quote_number` IS NOT NULL;

-- The following is needed only if quotes were being treated as work orders.
-- UPDATE `ospos_sales`
--   SET `sale_type` = 2, `work_order_number` = `quote_number`
-- WHERE quote_number IS NOT NULL;

-- Identify invoices
UPDATE `ospos_sales`
  SET `sale_type` = 1
WHERE `invoice_number` IS NOT NULL;


--  Add permissions for deleting sales and default grant for employee id 1

INSERT INTO `ospos_permissions` (`permission_id`, `module_id`) VALUES
('sales_delete', 'sales');

INSERT INTO `ospos_grants` (`permission_id`, `person_id`, `menu_group`) VALUES
('sales_delete', 1, '--');


-- Add columns to save per-user language selection

ALTER TABLE `ospos_employees` 
  ADD COLUMN `language` VARCHAR(48) DEFAULT NULL,
  ADD COLUMN `language_code` VARCHAR(8) DEFAULT NULL;


-- Add support for custom search suggestion format

INSERT INTO `ospos_app_config` (`key`, `value`) VALUES
('suggestions_first_column', 'name'),
('suggestions_second_column', ''),
('suggestions_third_column', '');


-- Add key->value to save setting for allowing duplicate barcodes

INSERT INTO `ospos_app_config` (`key`, `value`) VALUES
('allow_duplicate_barcodes', '0');


-- Modify items table to allow duplicate barcodes

ALTER TABLE `ospos_items`
  DROP INDEX `item_number`,
  ADD KEY `item_number` (item_number);
