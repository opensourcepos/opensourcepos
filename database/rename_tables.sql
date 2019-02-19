--
-- Rename tables
--

RENAME TABLE `app_config` TO `phppos_app_config`;
RENAME TABLE `customers` TO `phppos_customers`;
RENAME TABLE `employees` TO `phppos_employees`;
RENAME TABLE `giftcards` TO `phppos_giftcards`;
RENAME TABLE `inventory` TO `phppos_inventory`;
RENAME TABLE `items` TO `phppos_items`;
RENAME TABLE `items_taxes` TO `phppos_items_taxes`;
RENAME TABLE `item_kits` TO `phppos_item_kits`;
RENAME TABLE `item_kit_items` TO `phppos_item_kit_items`;
RENAME TABLE `modules` TO `phppos_modules`;
RENAME TABLE `people` TO `phppos_people`;
RENAME TABLE `permissions` TO `phppos_permissions`;
RENAME TABLE `receivings` TO `phppos_receivings`;
RENAME TABLE `receivings_items` TO `phppos_receivings_items`;
RENAME TABLE `sales` TO `phppos_sales`;
RENAME TABLE `sales_items` TO `phppos_sales_items`;
RENAME TABLE `sales_items_taxes` TO `phppos_sales_items_taxes`;
RENAME TABLE `sales_payments` TO `phppos_sales_payments`;
RENAME TABLE `sales_suspended` TO `phppos_sales_suspended`;
RENAME TABLE `sales_suspended_items` TO `phppos_sales_suspended_items`;
RENAME TABLE `sales_suspended_items_taxes` TO `phppos_sales_suspended_items_taxes`;
RENAME TABLE `sales_suspended_payments` TO `phppos_sales_suspended_payments`;
RENAME TABLE `sessions` TO `phppos_sessions`;
RENAME TABLE `suppliers` TO `phppos_suppliers`;
RENAME TABLE `dinner_tables` TO `phppos_dinner_tables`;