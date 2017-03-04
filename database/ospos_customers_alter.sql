ALTER TABLE ospos_customers
ADD COLUMN `package_id` int(11) DEFAULT NULL AFTER `discount_percent`,
ADD COLUMN `points` int(11) DEFAULT NULL AFTER `package_id`;