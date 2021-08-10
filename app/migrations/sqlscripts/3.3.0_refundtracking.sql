ALTER TABLE `ospos_sales_payments`
	ADD COLUMN `cash_refund` decimal(15,2) NOT NULL DEFAULT 0 AFTER `payment_amount`,
	CHANGE `payment_user` `employee_id` int(11) DEFAULT NULL,
	CHANGE `payment_date` `payment_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP;
