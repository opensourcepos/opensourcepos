CREATE TEMPORARY TABLE `src` (INDEX(`customer_id`))
(
    SELECT
    	MAX(`customer_id`) AS `customer_id`, MIN(`sale_time`) AS `sale_time`
    FROM `ospos_sales`
    WHERE `ospos_sales`.`customer_id` IS NOT NULL
    GROUP BY `ospos_sales`.`customer_id`
);

UPDATE `ospos_customers`
JOIN src ON `ospos_customers`.`person_id` = `src`.`customer_id`
SET `ospos_customers`.`date` = `src`.`sale_time`
WHERE `ospos_customers`.`date` = '0000-00-00 00:00:00' AND `ospos_customers`.`person_id` = `src`.`customer_id`
